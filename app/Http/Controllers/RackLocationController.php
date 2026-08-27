<?php

namespace App\Http\Controllers;

use App\Models\RackLocation;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class RackLocationController extends Controller
{
    /**
     * Tampilkan daftar lokasi rak (Akses: Admin & Siswa).
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = RackLocation::with(['inboundDetails', 'outboundDetails']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('Kode_Rak', 'like', "%{$search}%")
                  ->orWhere('Aisle', 'like', "%{$search}%")
                  ->orWhere('Level', 'like', "%{$search}%");
            });
        }

        $racks = $query->paginate(15)->withQueryString();

        return view('master.rak.index', compact('racks', 'search'));
    }

    /**
     * Simpan lokasi rak baru (Akses: HANYA Admin).
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

        ActivityLog::record("Guru/Admin membuat Lokasi Rak baru: {$rack->Kode_Rak} (Lorong {$rack->Aisle}, Level {$rack->Level}, Kapasitas {$rack->Kapasitas})");

        return redirect()->route('master.rak.index')->with('success', "Lokasi Rak {$rack->Kode_Rak} berhasil ditambahkan!");
    }

    /**
     * Update data lokasi rak (Akses: HANYA Admin).
     */
    public function update(Request $request, $id)
    {
        $rack = RackLocation::findOrFail($id);

        $validated = $request->validate([
            'Kode_Rak'  => 'required|string|max:50|unique:rack_locations,Kode_Rak,' . $rack->Rack_ID . ',Rack_ID',
            'Aisle'     => 'required|string|max:20',
            'Level'     => 'required|string|max:20',
            'Kapasitas' => 'required|integer|min:1',
        ]);

        $rack->update($validated);

        ActivityLog::record("Guru/Admin memperbarui Lokasi Rak: {$rack->Kode_Rak}");

        return redirect()->route('master.rak.index')->with('success', "Data Lokasi Rak {$rack->Kode_Rak} berhasil diperbarui!");
    }

    /**
     * Hapus lokasi rak (Akses: HANYA Admin).
     */
    public function destroy($id)
    {
        $rack = RackLocation::findOrFail($id);

        if ($rack->masterBarang()->count() > 0 || $rack->inboundDetails()->count() > 0) {
            return redirect()->route('master.rak.index')->with('error', "Lokasi Rak {$rack->Kode_Rak} tidak dapat dihapus karena sedang digunakan oleh data barang/transaksi.");
        }

        $kode = $rack->Kode_Rak;
        $rack->delete();

        ActivityLog::record("Guru/Admin menghapus Lokasi Rak: {$kode}");

        return redirect()->route('master.rak.index')->with('success', "Lokasi Rak {$kode} berhasil dihapus!");
    }
}
