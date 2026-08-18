<?php

namespace App\Http\Controllers;

use App\Models\RackLocation;
use Illuminate\Http\Request;

class RackLocationController extends Controller
{
    public function index()
    {
        $racks = RackLocation::when(request('search'), function($q, $search) {
                return $q->where('Kode_Rak', 'like', "%{$search}%")
                         ->orWhere('Lokasi', 'like', "%{$search}%");
            })
            ->paginate(20);
        
        return view('master.rack.index', compact('racks'));
    }

    public function create()
    {
        return view('master.rack.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Kode_Rak' => 'required|unique:rack_locations,Kode_Rak|max:50',
            'Lokasi' => 'required|max:255',
            'Kapasitas' => 'required|integer|min:1',
        ]);
        
        $validated['kapasitas_terisi'] = 0;
        
        RackLocation::create($validated);
        
        return redirect()->route('master.rack.index')
            ->with('success', 'Rak berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $rack = RackLocation::findOrFail($id);
        return view('master.rack.edit', compact('rack'));
    }

    public function update(Request $request, $id)
    {
        $rack = RackLocation::findOrFail($id);
        
        $validated = $request->validate([
            'Kode_Rak' => 'required|max:50|unique:rack_locations,Kode_Rak,' . $id . ',Rack_ID',
            'Lokasi' => 'required|max:255',
            'Kapasitas' => 'required|integer|min:1',
        ]);
        
        $rack->update($validated);
        
        return redirect()->route('master.rack.index')
            ->with('success', 'Rak berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $rack = RackLocation::findOrFail($id);
        
        if ($rack->masterBarang()->exists()) {
            return redirect()->route('master.rack.index')
                ->with('error', 'Tidak dapat menghapus rak yang masih berisi barang.');
        }
        
        $rack->delete();
        
        return redirect()->route('master.rack.index')
            ->with('success', 'Rak berhasil dihapus.');
    }
}
