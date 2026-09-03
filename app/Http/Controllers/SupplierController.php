<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Tampilkan daftar Master Supplier.
     * Admin: bisa edit. Siswa: read-only.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Supplier::withCount('inboundTransactions')->latest();

        if ($search) {
            $searchLower = strtolower($search);
            $query->where(function ($q) use ($searchLower) {
                $q->whereRaw("LOWER(\"Nama\") LIKE ?", ['%' . $searchLower . '%'])
                  ->orWhereRaw("LOWER(COALESCE(\"No_Kontak\", '')) LIKE ?", ['%' . $searchLower . '%'])
                  ->orWhereRaw("LOWER(COALESCE(\"Email\", '')) LIKE ?", ['%' . $searchLower . '%'])
                  ->orWhereRaw("LOWER(COALESCE(\"Alamat\", '')) LIKE ?", ['%' . $searchLower . '%']);
            });
        }

        $suppliers = $query->paginate(15)->withQueryString();

        return view('master.supplier.index', compact('suppliers', 'search'));
    }

    /**
     * Update data supplier (Admin only).
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'Nama'      => 'required|string|max:255',
            'No_Kontak' => ['nullable', 'regex:/^\d*$/', 'max:20'],
            'Email'     => ['nullable', 'string', 'max:255', function ($attr, $val, $fail) {
                if ($val && !str_contains($val, '@')) $fail('Email harus mengandung karakter @.');
            }],
            'Alamat'    => 'nullable|string|max:500',
        ], [
            'Nama.required'       => 'Nama supplier wajib diisi.',
            'No_Kontak.regex'     => 'No. Kontak hanya boleh berisi angka.',
        ]);

        $supplier->update([
            'Nama'      => $request->Nama,
            'No_Kontak' => $request->No_Kontak ?: null,
            'Kontak'    => $request->No_Kontak ?: null,
            'Email'     => $request->Email ?: null,
            'Alamat'    => $request->Alamat ?: null,
        ]);

        return redirect()->route('master.supplier.index')
            ->with('success', "Data supplier {$supplier->Nama} berhasil diperbarui.");
    }
}
