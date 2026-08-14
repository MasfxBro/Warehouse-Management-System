<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::when(request('search'), function($q, $search) {
                return $q->where('Nama', 'like', "%{$search}%")
                         ->orWhere('Kontak', 'like', "%{$search}%");
            })
            ->paginate(20);
        
        return view('master.customer.index', compact('customers'));
    }

    public function create()
    {
        return view('master.customer.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Nama' => 'required|max:255',
            'Kontak' => 'required|max:100',
            'Alamat' => 'nullable|max:500',
        ]);
        
        Customer::create($validated);
        
        return redirect()->route('master.customer.index')
            ->with('success', 'Customer berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('master.customer.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        
        $validated = $request->validate([
            'Nama' => 'required|max:255',
            'Kontak' => 'required|max:100',
            'Alamat' => 'nullable|max:500',
        ]);
        
        $customer->update($validated);
        
        return redirect()->route('master.customer.index')
            ->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        
        if ($customer->outboundTransactions()->exists()) {
            return redirect()->route('master.customer.index')
                ->with('error', 'Tidak dapat menghapus customer yang sudah memiliki transaksi.');
        }
        
        $customer->delete();
        
        return redirect()->route('master.customer.index')
            ->with('success', 'Customer berhasil dihapus.');
    }
}
