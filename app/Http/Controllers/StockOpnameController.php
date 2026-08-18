<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\MasterBarang;
use Illuminate\Http\Request;

class StockOpnameController extends Controller
{
    public function index()
    {
        $opnames = StockOpname::with(['masterBarang', 'user'])
            ->latest('tanggal_opname')
            ->paginate(20);
        
        return view('inventory.stock-opname.index', compact('opnames'));
    }

    public function create()
    {
        $barangs = MasterBarang::all();
        return view('inventory.stock-opname.create', compact('barangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'SKU' => 'required|exists:master_barang,SKU',
            'tanggal_opname' => 'required|date',
            'stok_fisik' => 'required|integer|min:0',
            'action_taken' => 'nullable|string',
            'notes' => 'nullable|string',
            'auto_correct' => 'nullable|boolean',
        ]);
        
        $barang = MasterBarang::find($validated['SKU']);
        
        // Create stock opname record (variance calculated automatically by model)
        $opname = StockOpname::create([
            'SKU' => $validated['SKU'],
            'tanggal_opname' => $validated['tanggal_opname'],
            'stok_sistem' => $barang->stok_real,
            'stok_fisik' => $validated['stok_fisik'],
            'action_taken' => $validated['action_taken'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'user_id' => auth()->id(),
        ]);
        
        // Auto-correct stock if requested
        if ($request->has('auto_correct') && $request->auto_correct) {
            $barang->update(['stok_real' => $validated['stok_fisik']]);
        }
        
        return redirect()->route('inventory.stock-opname.index')
            ->with('success', 'Stock opname berhasil disimpan.' . ($request->auto_correct ? ' Stok telah dikoreksi.' : ''));
    }
}
