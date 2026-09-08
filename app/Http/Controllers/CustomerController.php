<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Tampilkan daftar Master Customer.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Customer::withCount('outboundTransactions')->orderBy('Nama');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('Nama', 'like', "%{$search}%")
                    ->orWhere('No_Kontak', 'like', "%{$search}%")
                    ->orWhere('Email', 'like', "%{$search}%")
                    ->orWhere('Alamat', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(15)->withQueryString();

        return view('master.customer.index', compact('customers', 'search'));
    }

    /**
     * Update data customer (Admin only).
     */
    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'Nama' => ['required', 'string', 'max:255'],
            'No_Kontak' => ['nullable', 'string', 'max:20', 'regex:/^\d+$/'],
            'Email' => ['nullable', 'email', 'max:255'],
            'Alamat' => ['nullable', 'string', 'max:500'],
        ], [
            'Nama.required' => 'Nama customer wajib diisi.',
            'No_Kontak.regex' => 'No. Kontak hanya boleh berisi angka.',
            'Email.email' => 'Email harus mengandung karakter @.',
        ]);

        $customer->update([
            'Nama' => $request->Nama,
            'No_Kontak' => $request->No_Kontak,
            'Kontak' => $request->No_Kontak,
            'Email' => $request->Email,
            'Alamat' => $request->Alamat,
        ]);

        ActivityLog::record("Admin memperbarui data Customer: {$customer->Nama}.");

        return redirect()->route('master.customer.index')
            ->with('success', "Data customer {$customer->Nama} berhasil diperbarui.");
    }
}
