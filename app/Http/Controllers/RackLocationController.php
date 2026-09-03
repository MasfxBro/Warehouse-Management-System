<?php

namespace App\Http\Controllers;

use App\Models\MasterBarang;
use App\Models\RackLocation;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class RackLocationController extends Controller
{
    /**
     * Tampilkan daftar lokasi rak.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query  = RackLocation::withSum('inboundDetails as inbound_qty', 'Qty')
                               ->withSum('outboundDetails as outbound_qty', 'Qty');

        if ($search) {
            $s = strtolower($search);
            $query->where(function ($q) use ($s) {
                $q->whereRaw("LOWER(\"Kode_Rak\") LIKE ?", ["%{$s}%"])
                  ->orWhereRaw("LOWER(\"Aisle\") LIKE ?", ["%{$s}%"])
                  ->orWhereRaw("LOWER(\"Level\") LIKE ?", ["%{$s}%"]);
            });
        }

        $racks = $query->paginate(15)->withQueryString();
        return view('master.rak.index', compact('racks', 'search'));
    }

    /**
     * Tampilkan halaman detail rak + daftar barang di rak tersebut.
     */
    public function show(string $id)
    {
        $rack    = RackLocation::findOrFail($id);
        // Barang yang default rack-nya adalah rak ini
        $barangs = MasterBarang::with('rackLocation')
            ->where('Rack_ID', $rack->Rack_ID)
            ->get();
        // Rak lain untuk opsi pindah (kecuali rak ini sendiri)
        $otherRacks = RackLocation::where('Rack_ID', '!=', $rack->Rack_ID)
            ->orderBy('Kode_Rak')
            ->get();

        return view('master.rak.show', compact('rack', 'barangs', 'otherRacks'));
    }

    /**
     * Pindahkan semua/satu barang ke rak lain (Admin only).
     */
    public function pindahBarang(Request $request, string $id)
    {
        $request->validate([
            'sku'        => 'required|exists:master_barang,SKU',
            'new_rack_id' => 'required|exists:rack_locations,Rack_ID',
        ]);

        $barang = MasterBarang::findOrFail($request->sku);
        $oldRak = $barang->rackLocation->Kode_Rak ?? '-';
        $newRak = RackLocation::findOrFail($request->new_rack_id);

        $barang->update(['Rack_ID' => $request->new_rack_id]);

        ActivityLog::record("Barang [{$barang->SKU} - {$barang->Nama}] dipindah dari rak [{$oldRak}] ke rak [{$newRak->Kode_Rak}].");

        return redirect()->route('master.rak.show', $id)
            ->with('success', "Barang {$barang->Nama} berhasil dipindahkan ke rak {$newRak->Kode_Rak}.");
    }

    /**
     * Simpan lokasi rak baru (Admin only).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Kode_Rak'  => 'required|string|max:50|unique:rack_locations,Kode_Rak',
            'Aisle'     => 'required|string|max:20',
            'Level'     => 'required|string|max:20',
            'Kapasitas' => 'required|integer|min:1',
        ]);

        $rack = RackLocation::create($validated);
        ActivityLog::record("Guru/Admin membuat Lokasi Rak baru: {$rack->Kode_Rak}");

        return redirect()->route('master.rak.index')
            ->with('success', "Lokasi Rak {$rack->Kode_Rak} berhasil ditambahkan!");
    }

    /**
     * Update lokasi rak (Admin only).
     */
    public function update(Request $request, string $id)
    {
        $rack = RackLocation::findOrFail($id);

        $validated = $request->validate([
            'Kode_Rak'  => "required|string|max:50|unique:rack_locations,Kode_Rak,{$rack->Rack_ID},Rack_ID",
            'Aisle'     => 'required|string|max:20',
            'Level'     => 'required|string|max:20',
            'Kapasitas' => 'required|integer|min:1',
        ]);

        $rack->update($validated);
        ActivityLog::record("Guru/Admin memperbarui Lokasi Rak: {$rack->Kode_Rak}");

        return redirect()->route('master.rak.show', $id)
            ->with('success', "Data Lokasi Rak {$rack->Kode_Rak} berhasil diperbarui!");
    }

    /**
     * Hapus lokasi rak (Admin only) — hanya bisa jika tidak ada barang.
     */
    public function destroy(string $id)
    {
        $rack = RackLocation::findOrFail($id);

        $jumlahBarang = MasterBarang::where('Rack_ID', $rack->Rack_ID)->count();
        if ($jumlahBarang > 0) {
            return redirect()->route('master.rak.show', $id)
                ->with('error', "Rak {$rack->Kode_Rak} tidak dapat dihapus karena masih ada {$jumlahBarang} barang. Pindahkan semua barang terlebih dahulu.");
        }

        $kode = $rack->Kode_Rak;
        $rack->delete();
        ActivityLog::record("Guru/Admin menghapus Lokasi Rak: {$kode}");

        return redirect()->route('master.rak.index')
            ->with('success', "Lokasi Rak {$kode} berhasil dihapus!");
    }
}
