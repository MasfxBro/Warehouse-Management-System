<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BaseUnit;
use App\Models\InboundDetail;
use App\Models\InboundTransaction;
use App\Models\MasterBarang;
use App\Models\OutboundDetail;
use App\Models\PracticeSession;
use App\Models\RackLocation;
use App\Models\Supplier;
use App\Services\DocumentNumberService;
use App\Services\SkuNumberService;
use App\Support\UnitNormalizer;
use App\Support\WarehouseCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InboundController extends Controller
{
    // =========================================================
    // INDEX — Daftar Transaksi Inbound
    // =========================================================

    public function index(Request $request)
    {
        $query = InboundTransaction::with(['supplier', 'user', 'inboundDetails', 'allInboundDetails'])
            ->orderBy('Tanggal', 'desc')
            ->orderBy('Inbound_ID', 'desc');

        // Filter by Supplier
        if ($request->filled('supplier_id')) {
            $query->where('Supplier_ID', $request->supplier_id);
        }

        $transactions = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('Nama')->get();

        return view('inbound.index', compact('transactions', 'suppliers'));
    }

    // =========================================================
    // CREATE — Form Tambah Inbound
    // =========================================================

    public function create()
    {
        $suppliers = Supplier::orderBy('Nama')->get();
        $masterBarangs = MasterBarang::with('rackLocation')
            ->withSum('inboundDetails as inbound_qty', 'Qty')
            ->withSum('outboundDetails as outbound_qty', 'Qty')
            ->orderBy('Nama')
            ->get()
            ->map(function ($b) {
                $b->computed_stok = max(0, (int) ($b->inbound_qty ?? 0) - (int) ($b->outbound_qty ?? 0));

                return $b;
            });
        $rackLocations = RackLocation::withSum('inboundDetails as inbound_qty', 'Qty')
            ->withSum('completedOutboundDetails as outbound_qty', 'Qty')
            ->orderBy('Kode_Rak')
            ->get()
            ->map(function ($r) {
                $terpakai = max(0, (int) ($r->inbound_qty ?? 0) - (int) ($r->outbound_qty ?? 0));
                $r->sisa = max(0, $r->Kapasitas - $terpakai);
                $r->terpakai = $terpakai;

                return $r;
            })
            ->filter(fn ($r) => $r->sisa > 0)  // hanya rak yang masih ada ruang
            ->values();

        // Kategori unik yang sudah ada (untuk SKU Prefix Engine JS)
        $kategoriList = MasterBarang::select('Kategori')
            ->distinct()
            ->orderBy('Kategori')
            ->pluck('Kategori')
            ->filter()
            ->values();

        $satuanList = BaseUnit::orderBy('Nama')->pluck('Nama')->values();

        // Pre-mapped arrays untuk JS — disiapkan di controller supaya
        // @json() di Blade tidak perlu arrow function (hindari ParseError)
        $masterBarangsJs = $masterBarangs->map(function ($b) {
            return [
                'sku' => $b->SKU,
                'nama' => $b->Nama,
                'kategori' => $b->Kategori,
                'satuan' => $b->Satuan,
                'harga' => $b->Harga_Dasar,
                'rack_id' => $b->Rack_ID,
                'min_stok' => $b->Min_Stok,
            ];
        })->values()->all();

        $rackLocationsJs = $rackLocations->map(function ($r) {
            return [
                'id' => $r->Rack_ID,
                'label' => $r->Kode_Rak.' (Aisle '.$r->Aisle.', Lvl '.$r->Level.') — Sisa: '.$r->sisa.' unit',
                'sisa' => $r->sisa,
            ];
        })->values()->all();

        return view('inbound.create', compact(
            'suppliers',
            'masterBarangs',
            'rackLocations',
            'kategoriList',
            'satuanList',
            'masterBarangsJs',
            'rackLocationsJs'
        ));
    }

    // =========================================================
    // STORE — Simpan Transaksi Inbound
    // =========================================================

    public function store(Request $request, DocumentNumberService $documentNumbers, SkuNumberService $skuNumbers)
    {
        $request->validate([
            'Tanggal' => ['required', 'date', 'before_or_equal:today'],
            'Supplier_ID' => ['required', 'exists:suppliers,Supplier_ID'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.jenis' => ['required', 'in:lama,baru'],
            'items.*.Qty' => ['required', 'integer', 'min:1'],
            'items.*.Harga_Satuan' => ['exclude_unless:items.*.jenis,baru', 'required', 'integer', 'min:0', 'max:999999999999'],
            // Barang lama
            'items.*.SKU_lama' => ['exclude_unless:items.*.jenis,lama', 'required', 'exists:master_barang,SKU'],
            'items.*.Rack_ID_lama' => ['exclude_unless:items.*.jenis,lama', 'required', 'exists:rack_locations,Rack_ID'],
            // Barang baru
            'items.*.Nama_baru' => ['exclude_unless:items.*.jenis,baru', 'required', 'string', 'max:255'],
            'items.*.Kategori_baru' => ['exclude_unless:items.*.jenis,baru', 'required', 'string', 'max:100'],
            'items.*.Satuan_baru' => ['exclude_unless:items.*.jenis,baru', 'required', 'string', 'max:50'],
            'items.*.Rack_ID_baru' => ['exclude_unless:items.*.jenis,baru', 'required', 'exists:rack_locations,Rack_ID'],
            'items.*.Min_Stok_baru' => ['exclude_unless:items.*.jenis,baru', 'required', 'integer', 'min:0'],
            // Resi
            'items.*.No_Resi_Supplier' => ['nullable', 'string', 'max:100'],
            'items.*.tanpa_resi' => ['nullable'],
        ], [
            'Supplier_ID.required' => 'Supplier wajib dipilih.',
            'items.required' => 'Minimal harus ada satu baris barang.',
            'items.*.Qty.min' => 'Qty minimal 1.',
            'items.*.Harga_Satuan.required' => 'Harga per satuan wajib diisi.',
            'items.*.Harga_Satuan.integer' => 'Harga per satuan harus berupa angka rupiah tanpa desimal.',
        ]);

        try {
            $practiceSession = PracticeSession::current();
            if (! $practiceSession) {
                return back()->withInput()->with('error', 'Belum ada sesi praktikum aktif. Minta Guru/Admin membuka sesi terlebih dahulu.');
            }

            // Validasi total Qty per rak, bukan per baris. Dua item yang masing-masing
            // lolos tidak boleh bersama-sama melampaui sisa kapasitas rak yang sama.
            $requestedByRack = collect($request->items)
                ->groupBy(fn ($item) => $item['jenis'] === 'lama' ? $item['Rack_ID_lama'] : $item['Rack_ID_baru'])
                ->map(fn ($items) => (int) $items->sum('Qty'));

            foreach ($requestedByRack as $rackId => $requestedQty) {
                $rak = RackLocation::withSum('inboundDetails as in_qty', 'Qty')
                    ->withSum('completedOutboundDetails as out_qty', 'Qty')
                    ->findOrFail($rackId);
                $terpakai = max(0, (int) ($rak->in_qty ?? 0) - (int) ($rak->out_qty ?? 0));
                $sisa = max(0, $rak->Kapasitas - $terpakai);

                if ($requestedQty > $sisa) {
                    return back()->withInput()->with('error',
                        "Total Qty ke rak {$rak->Kode_Rak} ({$requestedQty} unit) melebihi sisa kapasitasnya ({$sisa} unit)."
                    );
                }
            }

            // Cek duplikat nama barang baru dalam satu transaksi
            $namaBaruList = [];
            foreach ($request->items as $i => $item) {
                if (($item['jenis'] ?? '') === 'baru' && ! empty($item['Nama_baru'])) {
                    $namaLower = strtolower(trim($item['Nama_baru']));
                    if (in_array($namaLower, $namaBaruList)) {
                        return back()->withInput()->with('error',
                            "Terdapat dua baris barang baru dengan nama yang sama: \"{$item['Nama_baru']}\". ".
                            'Jika ingin memasukkan barang yang sama ke rak berbeda, gunakan jenis Barang Lama setelah baris pertama disimpan.'
                        );
                    }
                    $namaBaruList[] = $namaLower;

                    // Cek juga apakah nama sudah ada di master_barang (sudah pernah diinput sebelumnya)
                    $sudahAda = MasterBarang::whereRaw('LOWER("Nama") = ?', [$namaLower])->exists();
                    if ($sudahAda) {
                        return back()->withInput()->with('error',
                            "Barang \"{$item['Nama_baru']}\" sudah terdaftar di master data. Gunakan jenis Barang Lama untuk menambah stok barang yang sudah ada."
                        );
                    }
                }
            }

            // Semua validasi bisnis sudah lolos. Transaksi database baru dibuka
            // di sini agar return dari validasi tidak meninggalkan transaksi aktif.
            DB::beginTransaction();
            $noReceiving = $documentNumbers->next('RSI', $request->Tanggal);
            $requestedRackIds = collect($request->items)
                ->map(fn ($item) => $item['jenis'] === 'lama' ? $item['Rack_ID_lama'] : $item['Rack_ID_baru'])
                ->unique()
                ->sort()
                ->values();
            RackLocation::whereIn('Rack_ID', $requestedRackIds)->orderBy('Rack_ID')->lockForUpdate()->get();

            // Buat header transaksi
            $inbound = InboundTransaction::create([
                'No_Receiving' => $noReceiving,
                'Tanggal' => $request->Tanggal,
                'Supplier_ID' => $request->Supplier_ID,
                'User_ID' => Auth::id(),
                'Catatan' => $request->filled('Catatan') ? trim($request->Catatan) : null,
                'Practice_Session_ID' => $practiceSession->Practice_Session_ID,
            ]);

            foreach ($request->items as $item) {
                $sku = null;
                $rackId = null;
                $unitPrice = null;

                if ($item['jenis'] === 'lama') {
                    // ---- Barang Lama ----
                    $sku = $item['SKU_lama'] ?? null;
                    $barang = MasterBarang::query()->lockForUpdate()->find($sku);
                    $unitPrice = $barang->Harga_Dasar;
                    // Selalu pakai Rack_ID_lama dari form (user bebas pilih rak tujuan)
                    $rackId = $item['Rack_ID_lama'] ?? null;

                    if (! $rackId) {
                        DB::rollBack();

                        return back()->withInput()->with('error', "Barang \"{$barang?->Nama}\" belum dipilih rak tujuannya. Pilih rak terlebih dahulu.");
                    }

                    // Validasi kapasitas rak tujuan
                    $rakTujuan = RackLocation::withSum('inboundDetails as in_qty', 'Qty')
                        ->withSum('completedOutboundDetails as out_qty', 'Qty')
                        ->find($rackId);
                    if ($rakTujuan) {
                        $terpakai = max(0, (int) ($rakTujuan->in_qty ?? 0) - (int) ($rakTujuan->out_qty ?? 0));
                        $sisa = max(0, $rakTujuan->Kapasitas - $terpakai);
                        $qty = (int) ($item['Qty'] ?? 0);
                        if ($qty > $sisa) {
                            DB::rollBack();

                            return back()->withInput()->with('error',
                                "Qty barang \"{$barang?->Nama}\" ({$qty} unit) melebihi sisa kapasitas rak {$rakTujuan->Kode_Rak} ({$sisa} unit tersisa)."
                            );
                        }
                    }
                } else {
                    // ---- Barang Baru ----
                    $prefix = $this->generateSkuPrefix($item['Kategori_baru'] ?? 'XXX');
                    $sku = $skuNumbers->next($prefix);
                    $rackId = $item['Rack_ID_baru'] ?? null;
                    $unit = UnitNormalizer::normalize($item['Satuan_baru'] ?? null);
                    $unitPrice = (int) $item['Harga_Satuan'];

                    BaseUnit::firstOrCreate(['Nama' => $unit]);

                    MasterBarang::create([
                        'SKU' => $sku,
                        'Nama' => trim($item['Nama_baru']),
                        'Kategori' => trim($item['Kategori_baru'] ?? ''),
                        'Satuan' => $unit,
                        'Harga_Dasar' => $unitPrice,
                        'Rack_ID' => $rackId,
                        'Min_Stok' => (int) ($item['Min_Stok_baru'] ?? 0),
                    ]);
                }

                // Tentukan No_Resi_Supplier
                $noResi = null;
                if (empty($item['tanpa_resi'])) {
                    $noResi = $item['No_Resi_Supplier'] ?? null;
                    $noResi = ($noResi && trim($noResi) !== '') ? trim($noResi) : null;
                }

                InboundDetail::create([
                    'Inbound_ID' => $inbound->Inbound_ID,
                    'SKU' => $sku,
                    'Rack_ID' => $rackId,
                    'Qty' => (int) $item['Qty'],
                    'Harga_Satuan' => $unitPrice,
                    'No_Resi_Supplier' => $noResi,
                ]);
            }

            DB::commit();

            WarehouseCache::clearDashboard();
            ActivityLog::record(
                "Transaksi Inbound [{$noReceiving}] dibuat dengan total nilai Rp ".number_format($inbound->fresh('inboundDetails')->total_nilai, 0, ',', '.').'.'
            );

            session()->save(); // Paksa tulis session sebelum redirect

            return redirect()->route('inbound.index')
                ->with('success', "Transaksi Inbound {$noReceiving} berhasil disimpan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withInput()->with('error', 'Transaksi inbound gagal disimpan. Silakan coba kembali atau hubungi administrator.');
        }
    }

    // =========================================================
    // SHOW — Detail Inbound
    // =========================================================

    public function show(string $id)
    {
        $inbound = InboundTransaction::with([
            'supplier',
            'user',
            'inboundDetails.masterBarang',
            'inboundDetails.rackLocation',
            'allInboundDetails.masterBarang',
            'allInboundDetails.rackLocation',
            'cancelledBy',
            'practiceSession',
        ])->findOrFail($id);

        return view('inbound.show', compact('inbound'));
    }

    public function cancel(Request $request, string $id)
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'reason.required' => 'Alasan pembatalan wajib diisi.',
            'reason.min' => 'Alasan pembatalan minimal 10 karakter.',
        ]);

        DB::beginTransaction();

        try {
            $inbound = InboundTransaction::with('inboundDetails')->lockForUpdate()->findOrFail($id);
            if ($inbound->isCancelled()) {
                DB::rollBack();

                return back()->with('info', 'Transaksi inbound ini sudah dibatalkan sebelumnya.');
            }

            $requestedByRack = $inbound->inboundDetails
                ->groupBy(fn (InboundDetail $detail) => $detail->SKU.'|'.$detail->Rack_ID)
                ->map(fn ($details) => (int) $details->sum('Qty'));

            MasterBarang::whereIn('SKU', $inbound->inboundDetails->pluck('SKU')->unique())
                ->lockForUpdate()
                ->get();

            foreach ($requestedByRack as $key => $qtyToReverse) {
                [$sku, $rackId] = explode('|', $key, 2);
                $currentInbound = InboundDetail::where('SKU', $sku)->where('Rack_ID', $rackId)->sum('Qty');
                $currentOutbound = OutboundDetail::where('SKU', $sku)->where('Rack_ID', $rackId)->sum('Qty');

                if (($currentInbound - $currentOutbound - $qtyToReverse) < 0) {
                    DB::rollBack();

                    return back()->with('error', "Inbound tidak dapat dibatalkan karena stok {$sku} sudah dipakai oleh transaksi outbound.");
                }
            }

            $inbound->inboundDetails->each->delete();
            $inbound->update([
                'transaction_status' => 'cancelled',
                'Cancelled_At' => now(),
                'Cancelled_By' => Auth::id(),
                'Cancellation_Reason' => trim($request->reason),
            ]);
            DB::commit();

            WarehouseCache::clearDashboard();
            ActivityLog::record("Transaksi Inbound [{$inbound->No_Receiving}] dibatalkan. Alasan: ".trim($request->reason));

            return back()->with('success', "Inbound {$inbound->No_Receiving} berhasil dibatalkan dan stok dikembalikan.");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->with('error', 'Pembatalan inbound gagal diproses.');
        }
    }

    // =========================================================
    // STORE SUPPLIER AJAX — Tambah Supplier via Modal
    // =========================================================

    public function storeSupplierAjax(Request $request)
    {
        $request->validate([
            'Nama' => ['required', 'string', 'max:255'],
            'No_Kontak' => ['nullable', 'string', 'max:20'],
            'Email' => ['nullable', 'email', 'max:255'],
            'Alamat' => ['nullable', 'string', 'max:500'],
        ]);

        $supplier = Supplier::create([
            'Nama' => $request->Nama,
            'Kontak' => $request->No_Kontak,
            'No_Kontak' => $request->No_Kontak,
            'Email' => $request->Email,
            'Alamat' => $request->Alamat,
        ]);

        ActivityLog::record("Supplier baru [{$supplier->Nama}] ditambahkan melalui transaksi Inbound.");

        return response()->json([
            'success' => true,
            'supplier' => [
                'id' => $supplier->Supplier_ID,
                'nama' => $supplier->Nama,
            ],
        ]);
    }

    public function storeUnitAjax(Request $request)
    {
        $request->validate([
            'Nama' => ['required', 'string', 'max:50'],
        ], [
            'Nama.required' => 'Nama satuan wajib diisi.',
        ]);

        $unit = BaseUnit::firstOrCreate([
            'Nama' => UnitNormalizer::normalize($request->Nama),
        ]);

        if ($unit->wasRecentlyCreated) {
            ActivityLog::record("Satuan dasar baru [{$unit->Nama}] ditambahkan melalui transaksi Inbound.");
        }

        return response()->json([
            'success' => true,
            'created' => $unit->wasRecentlyCreated,
            'unit' => [
                'id' => $unit->Unit_ID,
                'nama' => $unit->Nama,
            ],
        ]);
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Ambil 3 konsonan pertama dari nama kategori.
     */
    private function generateSkuPrefix(string $kategori): string
    {
        $konsonan = preg_replace('/[aeiou\s]/i', '', $kategori);
        $prefix = strtoupper(substr($konsonan, 0, 3));

        return str_pad($prefix, 3, 'X');
    }
}
