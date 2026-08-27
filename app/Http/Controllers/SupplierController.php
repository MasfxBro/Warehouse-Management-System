<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Tampilkan daftar Master Supplier (PURE READ-ONLY DIRECTORY).
     * Data supplier hanya bertambah secara otomatis melalui Transaksi Inbound.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Supplier::withCount('inboundTransactions')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('Nama', 'like', "%{$search}%")
                  ->orWhere('No_Kontak', 'like', "%{$search}%")
                  ->orWhere('Email', 'like', "%{$search}%")
                  ->orWhere('Alamat', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->paginate(15)->withQueryString();

        return view('master.supplier.index', compact('suppliers', 'search'));
    }
}
