<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\RackLocation;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API Controller: Master Data (Supplier, Customer, Rack Location)
 *
 * GET /api/suppliers           → Daftar supplier
 * GET /api/customers           → Daftar customer
 * GET /api/rack-locations      → Daftar lokasi rak
 */
class MasterDataApiController extends Controller
{
    public function suppliers(Request $request): JsonResponse
    {
        $query = Supplier::withCount('inboundTransactions')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('Nama', 'like', "%{$s}%")
                ->orWhere('No_Kontak', 'like', "%{$s}%")
                ->orWhere('Email', 'like', "%{$s}%")
            );
        }

        $perPage = (int) $request->query('per_page', 50);
        $paginator = $query->paginate($perPage);

        $data = collect($paginator->items())->map(fn($s) => [
            'supplier_id'     => $s->Supplier_ID,
            'nama'            => $s->Nama,
            'no_kontak'       => $s->No_Kontak,
            'email'           => $s->Email,
            'alamat'          => $s->Alamat,
            'total_transaksi' => $s->inbound_transactions_count,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function customers(Request $request): JsonResponse
    {
        $query = Customer::withCount('outboundTransactions')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('Nama', 'like', "%{$s}%")
                ->orWhere('No_Kontak', 'like', "%{$s}%")
                ->orWhere('Email', 'like', "%{$s}%")
            );
        }

        $perPage = (int) $request->query('per_page', 50);
        $paginator = $query->paginate($perPage);

        $data = collect($paginator->items())->map(fn($c) => [
            'customer_id'     => $c->Customer_ID,
            'nama'            => $c->Nama,
            'no_kontak'       => $c->No_Kontak,
            'email'           => $c->Email,
            'alamat'          => $c->Alamat,
            'total_transaksi' => $c->outbound_transactions_count,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function rackLocations(Request $request): JsonResponse
    {
        $query = RackLocation::with(['inboundDetails', 'outboundDetails']);

        if ($request->filled('search')) {
            $s = strtolower($request->search);
            $query->where(fn($q) => $q
                ->whereRaw('LOWER("Kode_Rak") LIKE ?', ["%{$s}%"])
                ->orWhereRaw('LOWER("Aisle") LIKE ?', ["%{$s}%"])
            );
        }

        $racks = $query->orderBy('Kode_Rak')->get()->map(fn($r) => [
            'rack_id'           => $r->Rack_ID,
            'kode_rak'          => $r->Kode_Rak,
            'aisle'             => $r->Aisle,
            'level'             => $r->Level,
            'kapasitas'         => $r->Kapasitas,
            'kapasitas_terpakai'=> $r->kapasitas_terpakai,
            'status_kapasitas'  => $r->status_kapasitas,
        ]);

        return response()->json(['success' => true, 'data' => $racks]);
    }
}
