<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Tampilkan daftar Master Customer (PURE READ-ONLY DIRECTORY).
     * Data customer hanya bertambah secara otomatis melalui Transaksi Outbound.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Customer::withCount('outboundTransactions')->latest();

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
}
