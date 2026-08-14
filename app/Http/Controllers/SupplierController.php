<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::when(request('search'), function($q, $search) {
                return $q->where('Nama', 'like', "%{$search}%")
                         ->orWhere('Kontak', 'like', "%{$search}%");
            })
            ->paginate(20);
        
        return view('master.supplier.index', compact('suppliers'));
    }

    public function create()
    {
        return view('master.supplier.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Nama' => 'required|max:255',
            'Kontak' => 'required|max:100',
            'Alamat' => 'nullable|max:500',
        ]);
        
        Supplier::create($validated);
        
        return redirect()->route('master.supplier.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('master.supplier.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        
        $validated = $request->validate([
            'Nama' => 'required|max:255',
            'Kontak' => 'required|max:100',
            'Alamat' => 'nullable|max:500',
        ]);
        
        $supplier->update($validated);
        
        return redirect()->route('master.supplier.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        
        if ($supplier->inboundTransactions()->exists()) {
            return redirect()->route('master.supplier.index')
                ->with('error', 'Tidak dapat menghapus supplier yang sudah memiliki transaksi.');
        }
        
        $supplier->delete();
        
        return redirect()->route('master.supplier.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }
}
