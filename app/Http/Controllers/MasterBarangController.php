<?php

namespace App\Http\Controllers;

use App\Models\MasterBarang;
use App\Models\RackLocation;
use Illuminate\Http\Request;

class MasterBarangController extends Controller
{
    public function index()
    {
        $barangs = MasterBarang::with('rackLocation')
            ->when(request('search'), function($q, $search) {
                return $q->where('Nama', 'like', "%{$search}%")
                         ->orWhere('SKU', 'like', "%{$search}%");
            })
            ->paginate(20);
        
        return view('master.barang.index', compact('barangs'));
    }

    public function create()
    {
        $racks = RackLocation::all();
        return view('master.barang.create', compact('racks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'SKU' => 'required|unique:master_barang,SKU|max:50',
            'Nama' => 'required|max:255',
            'Kategori' => 'required|max:100',
            'Min_Stok' => 'required|integer|min:0',
            'harga' => 'required|numeric|min:0',
            'satuan' => 'required|max:50',
            'Rack_ID' => 'nullable|exists:rack_locations,Rack_ID',
        ]);
        
        $validated['stok_real'] = 0; // Initial stock is 0
        
        MasterBarang::create($validated);
        
        return redirect()->route('master.barang.index')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    public function show($sku)
    {
        $barang = MasterBarang::with('rackLocation')->findOrFail($sku);
        return view('master.barang.show', compact('barang'));
    }

    public function edit($sku)
    {
        $barang = MasterBarang::findOrFail($sku);
        $racks = RackLocation::all();
        return view('master.barang.edit', compact('barang', 'racks'));
    }

    public function update(Request $request, $sku)
    {
        $barang = MasterBarang::findOrFail($sku);
        
        $validated = $request->validate([
            'Nama' => 'required|max:255',
            'Kategori' => 'required|max:100',
            'Min_Stok' => 'required|integer|min:0',
            'harga' => 'required|numeric|min:0',
            'satuan' => 'required|max:50',
            'Rack_ID' => 'nullable|exists:rack_locations,Rack_ID',
        ]);
        
        $barang->update($validated);
        
        return redirect()->route('master.barang.index')
            ->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy($sku)
    {
        $barang = MasterBarang::findOrFail($sku);
        
        // Check if has transactions
        if ($barang->inboundDetails()->exists() || $barang->outboundDetails()->exists()) {
            return redirect()->route('master.barang.index')
                ->with('error', 'Tidak dapat menghapus barang yang sudah memiliki transaksi.');
        }
        
        $barang->delete();
        
        return redirect()->route('master.barang.index')
            ->with('success', 'Barang berhasil dihapus.');
    }
}
