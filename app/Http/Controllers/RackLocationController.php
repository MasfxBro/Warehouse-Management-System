<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\InboundDetail;
use App\Models\MasterBarang;
use App\Models\OutboundDetail;
use App\Models\RackLocation;
use App\Support\WarehouseCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RackLocationController extends Controller
{
    /**
     * Tampilkan daftar lokasi rak.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = RackLocation::withSum('inboundDetails as inbound_qty', 'Qty')
            ->withSum('completedOutboundDetails as outbound_qty', 'Qty');

        if ($search) {
            $s = strtolower($search);
            $query->where(function ($q) use ($s) {
                $q->whereRaw('LOWER("Kode_Rak") LIKE ?', ["%{$s}%"])
                    ->orWhereRaw('LOWER("Aisle") LIKE ?', ["%{$s}%"])
                    ->orWhereRaw('LOWER("Level") LIKE ?', ["%{$s}%"]);
            });
        }

        $racks = $query->orderBy('Kode_Rak')->paginate(15)->withQueryString();

        return view('master.rak.index', compact('racks', 'search'));
    }

    /**
     * Tampilkan halaman detail rak + daftar barang di rak tersebut.
     * Accessible: Admin & Siswa.
     */
    public function show(string $id)
    {
        $rack = RackLocation::findOrFail($id);

        // Tampilkan barang yang BENAR-BENAR ada stok di rak ini
        // berdasarkan InboundDetail.Rack_ID bukan MasterBarang.Rack_ID
        $barangDiRak = InboundDetail::where('Rack_ID', $rack->Rack_ID)
            ->selectRaw('"SKU", SUM("Qty") as inbound_qty')
            ->groupBy('SKU')
            ->pluck('inbound_qty', 'SKU');

        $outboundDiRak = OutboundDetail::where('Rack_ID', $rack->Rack_ID)
            ->whereHas('outboundTransaction', fn ($query) => $query
                ->where('transaction_status', 'active')
                ->where('picking_status', 'complete'))
            ->selectRaw('"SKU", SUM("Qty") as outbound_qty')
            ->groupBy('SKU')
            ->pluck('outbound_qty', 'SKU');

        // Hanya barang yang net stok > 0 di rak ini
        $skuDiRak = $barangDiRak->filter(function ($inQty, $sku) use ($outboundDiRak) {
            $outQty = $outboundDiRak->get($sku, 0);

            return ($inQty - $outQty) > 0;
        })->keys();

        $barangs = MasterBarang::with('rackLocation')
            ->whereIn('SKU', $skuDiRak)
            ->orderBy('SKU')
            ->paginate(15, ['*'], 'barang_page')
            ->through(function ($b) use ($barangDiRak, $outboundDiRak, $rack) {
                $b->stok_di_rak = max(0,
                    ($barangDiRak->get($b->SKU, 0)) - ($outboundDiRak->get($b->SKU, 0))
                );
                $b->reserved_di_rak = OutboundDetail::where('SKU', $b->SKU)
                    ->where('Rack_ID', $rack->Rack_ID)
                    ->whereHas('outboundTransaction', fn ($query) => $query
                        ->where('transaction_status', 'active')
                        ->where('picking_status', 'not_complete'))
                    ->sum('Qty');
                $b->tersedia_di_rak = max(0, $b->stok_di_rak - $b->reserved_di_rak);

                return $b;
            });

        $otherRacks = RackLocation::where('Rack_ID', '!=', $rack->Rack_ID)
            ->withSum('inboundDetails as inbound_qty', 'Qty')
            ->withSum('completedOutboundDetails as outbound_qty', 'Qty')
            ->orderBy('Kode_Rak')
            ->get()
            ->map(function ($r) {
                $terpakai = max(0, (int) ($r->inbound_qty ?? 0) - (int) ($r->outbound_qty ?? 0));
                $r->sisa_kapasitas = max(0, $r->Kapasitas - $terpakai);
                $r->terpakai = $terpakai;

                return $r;
            });

        return view('master.rak.show', compact('rack', 'barangs', 'otherRacks'));
    }

    /**
     * Pindahkan barang ke rak lain dengan qty tertentu.
     * Accessible: Admin & Siswa.
     *
     * Logic kapasitas:
     * - kapasitas_terpakai dihitung dari SUM(inbound_details.Qty) - SUM(outbound_details.Qty) WHERE Rack_ID = X
     * - Memindah barang = update Rack_ID di inbound_details sebanyak qty yang dipindahkan
     * - Ini otomatis mengurangi kapasitas rak asal dan menambah kapasitas rak tujuan
     * - Jika qty = seluruh stok barang di rak asal → update juga MasterBarang.Rack_ID (default rak)
     */
    public function pindahBarang(Request $request, string $id)
    {
        $request->validate([
            'sku' => 'required|exists:master_barang,SKU',
            'new_rack_id' => 'required|exists:rack_locations,Rack_ID',
            'qty' => 'required|integer|min:1',
        ], [
            'qty.min' => 'Jumlah barang yang dipindahkan minimal 1.',
        ]);

        if ($request->new_rack_id === $id) {
            return back()->with('error', 'Rak tujuan tidak boleh sama dengan rak asal.');
        }

        DB::beginTransaction();
        try {
            $barang = MasterBarang::lockForUpdate()->findOrFail($request->sku);
            $lockedRacks = RackLocation::whereIn('Rack_ID', [$id, $request->new_rack_id])
                ->orderBy('Rack_ID')
                ->lockForUpdate()
                ->get()
                ->keyBy('Rack_ID');
            $rakAsal = $lockedRacks->get($id) ?? abort(404);
            $rakTujuan = $lockedRacks->get($request->new_rack_id) ?? abort(404);

            // Hitung stok barang di rak asal (inbound - outbound untuk SKU + Rack_ID ini)
            $inboundQtyAsal = InboundDetail::where('SKU', $barang->SKU)
                ->where('Rack_ID', $rakAsal->Rack_ID)->sum('Qty');
            $outboundQtyAsal = OutboundDetail::where('SKU', $barang->SKU)
                ->where('Rack_ID', $rakAsal->Rack_ID)->sum('Qty');
            $stokAsal = max(0, $inboundQtyAsal - $outboundQtyAsal);

            if ($stokAsal <= 0) {
                DB::rollBack();

                return back()->with('error', "Tidak ada stok barang {$barang->Nama} di rak {$rakAsal->Kode_Rak}.");
            }

            if ($request->qty > $stokAsal) {
                DB::rollBack();

                return back()->with('error', "Jumlah yang dipindahkan ({$request->qty}) melebihi stok barang di rak ini ({$stokAsal} unit).");
            }

            // Hitung sisa kapasitas rak tujuan
            $terpakaiTujuan = max(0,
                InboundDetail::where('Rack_ID', $rakTujuan->Rack_ID)->sum('Qty')
                - OutboundDetail::where('Rack_ID', $rakTujuan->Rack_ID)
                    ->whereHas('outboundTransaction', fn ($query) => $query
                        ->where('transaction_status', 'active')
                        ->where('picking_status', 'complete'))
                    ->sum('Qty')
            );
            $sisaTujuan = max(0, $rakTujuan->Kapasitas - $terpakaiTujuan);

            if ($sisaTujuan <= 0) {
                DB::rollBack();

                return back()->with('error', "Rak {$rakTujuan->Kode_Rak} sudah penuh dan tidak dapat menerima barang.");
            }

            // Batasi qty ke sisa kapasitas rak tujuan
            $qtyDipindah = min($request->qty, $sisaTujuan);
            $sisaTidakDipindah = $request->qty - $qtyDipindah;

            // Ubah Rack_ID di inbound_details sebanyak qty yang dipindahkan
            // Ambil inbound detail records untuk SKU ini di rak asal, update satu per satu
            $remaining = $qtyDipindah;
            $inboundDetails = InboundDetail::where('SKU', $barang->SKU)
                ->where('Rack_ID', $rakAsal->Rack_ID)
                ->orderBy('created_at')
                ->get();

            foreach ($inboundDetails as $detail) {
                if ($remaining <= 0) {
                    break;
                }

                if ($detail->Qty <= $remaining) {
                    // Pindahkan seluruh record ini
                    $detail->update(['Rack_ID' => $rakTujuan->Rack_ID]);
                    $remaining -= $detail->Qty;
                } else {
                    // Split record: sebagian tetap di rak asal, sebagian pindah
                    $detail->update(['Qty' => $detail->Qty - $remaining]);
                    InboundDetail::create([
                        'Inbound_ID' => $detail->Inbound_ID,
                        'SKU' => $detail->SKU,
                        'Rack_ID' => $rakTujuan->Rack_ID,
                        'Qty' => $remaining,
                        'Harga_Satuan' => $detail->Harga_Satuan,
                        'No_Resi_Supplier' => $detail->No_Resi_Supplier,
                        'Batch' => $detail->Batch,
                    ]);
                    $remaining = 0;
                }
            }

            if ($remaining > 0) {
                throw new \RuntimeException('Detail inbound tidak cukup untuk menyelesaikan relokasi.');
            }

            $physicalAfterMove = InboundDetail::where('SKU', $barang->SKU)
                ->where('Rack_ID', $rakAsal->Rack_ID)
                ->sum('Qty') - OutboundDetail::where('SKU', $barang->SKU)
                ->where('Rack_ID', $rakAsal->Rack_ID)
                ->whereHas('outboundTransaction', fn ($query) => $query
                    ->where('transaction_status', 'active')
                    ->where('picking_status', 'complete'))
                ->sum('Qty');

            // Rak default berpindah hanya jika tidak ada unit fisik yang tertinggal.
            if ($physicalAfterMove <= 0) {
                $barang->update(['Rack_ID' => $rakTujuan->Rack_ID]);
            }

            DB::commit();

            WarehouseCache::clearDashboard();

            $sisaMsg = $sisaTidakDipindah > 0
                ? " Sisa {$sisaTidakDipindah} unit tetap di rak {$rakAsal->Kode_Rak} karena kapasitas rak tujuan hanya tersedia {$sisaTujuan} unit."
                : '';

            ActivityLog::record("Barang [{$barang->SKU} - {$barang->Nama}] dipindah {$qtyDipindah} unit dari rak [{$rakAsal->Kode_Rak}] ke [{$rakTujuan->Kode_Rak}].{$sisaMsg}");

            return redirect()->route('master.rak.show', $id)
                ->with('success', "{$qtyDipindah} unit {$barang->Nama} berhasil dipindahkan ke rak {$rakTujuan->Kode_Rak}.{$sisaMsg}");

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->with('error', 'Barang gagal dipindahkan. Silakan coba kembali atau hubungi administrator.');
        }
    }

    /**
     * Simpan lokasi rak baru (Admin only).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Kode_Rak' => 'required|string|max:50|unique:rack_locations,Kode_Rak',
            'Aisle' => 'required|string|max:20',
            'Level' => 'required|string|max:20',
            'Kapasitas' => 'required|integer|min:1',
        ]);

        $rack = RackLocation::create($validated);
        ActivityLog::record("Guru/Admin membuat Lokasi Rak baru: {$rack->Kode_Rak}");

        return redirect()->route('master.rak.index')
            ->with('success', "Lokasi Rak {$rack->Kode_Rak} berhasil ditambahkan!");
    }

    /**
     * Update lokasi rak (Admin only). Termasuk upload foto jika ada.
     */
    public function update(Request $request, string $id)
    {
        $rack = RackLocation::findOrFail($id);

        $validated = $request->validate([
            'Kode_Rak' => "required|string|max:50|unique:rack_locations,Kode_Rak,{$rack->Rack_ID},Rack_ID",
            'Aisle' => 'required|string|max:20',
            'Level' => 'required|string|max:20',
            'Kapasitas' => 'required|integer|min:1',
        ]);

        if ((int) $validated['Kapasitas'] < $rack->kapasitas_terpakai) {
            return back()->withInput()->with(
                'error',
                "Kapasitas tidak boleh lebih kecil dari stok fisik saat ini ({$rack->kapasitas_terpakai} unit)."
            );
        }

        $rack->update($validated);
        ActivityLog::record("Guru/Admin memperbarui Lokasi Rak: {$rack->Kode_Rak}");

        return redirect()->route('master.rak.show', $id)
            ->with('success', "Data Lokasi Rak {$rack->Kode_Rak} berhasil diperbarui!");
    }

    /**
     * Hapus foto rak (Admin only).
     */
    public function hapusFoto(string $id)
    {
        $rack = RackLocation::findOrFail($id);

        if ($rack->foto_path && Storage::disk('public')->exists($rack->foto_path)) {
            Storage::disk('public')->delete($rack->foto_path);
        }

        $rack->update(['foto_path' => null]);
        ActivityLog::record("Admin menghapus foto Rak {$rack->Kode_Rak}.");

        return redirect()->route('master.rak.show', $id)
            ->with('success', "Foto rak {$rack->Kode_Rak} berhasil dihapus.");
    }

    /**
     * Upload foto rak (Admin only) — endpoint terpisah.
     */
    public function uploadFoto(Request $request, string $id)
    {
        $rack = RackLocation::findOrFail($id);

        $request->validate([
            'foto' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048',
        ], [
            'foto.required' => 'Pilih foto terlebih dahulu.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        // Hapus foto lama jika ada
        if ($rack->foto_path && Storage::disk('public')->exists($rack->foto_path)) {
            Storage::disk('public')->delete($rack->foto_path);
        }

        $path = $request->file('foto')->store('rak-foto', 'public');
        $rack->update(['foto_path' => $path]);

        ActivityLog::record("Admin mengupload foto Rak {$rack->Kode_Rak}.");

        return redirect()->route('master.rak.show', $id)
            ->with('success', "Foto rak {$rack->Kode_Rak} berhasil diupload.");
    }

    /**
     * Hapus lokasi rak (Admin only) — hanya bisa jika tidak ada barang.
     */
    public function destroy(string $id)
    {
        $rack = RackLocation::findOrFail($id);

        $jumlahBarang = MasterBarang::where('Rack_ID', $rack->Rack_ID)->count();
        $memilikiRiwayat = InboundDetail::withTrashed()->where('Rack_ID', $rack->Rack_ID)->exists()
            || OutboundDetail::withTrashed()->where('Rack_ID', $rack->Rack_ID)->exists();
        if ($jumlahBarang > 0 || $memilikiRiwayat) {
            return redirect()->route('master.rak.show', $id)
                ->with('error', "Rak {$rack->Kode_Rak} tidak dapat dihapus karena masih digunakan sebagai lokasi barang atau memiliki riwayat transaksi.");
        }

        // Hapus foto jika ada
        if ($rack->foto_path && Storage::disk('public')->exists($rack->foto_path)) {
            Storage::disk('public')->delete($rack->foto_path);
        }

        $kode = $rack->Kode_Rak;
        $rack->delete();
        ActivityLog::record("Guru/Admin menghapus Lokasi Rak: {$kode}");

        return redirect()->route('master.rak.index')
            ->with('success', "Lokasi Rak {$kode} berhasil dihapus!");
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

}
