<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterBarang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API Controller: Master Data Barang
 *
 * GET    /api/barang            → Daftar semua barang (dengan search & kategori filter)
 * GET    /api/barang/{sku}      → Detail satu barang + histori
 * GET    /api/barang/kategori   → Daftar kategori unik
 */
class MasterBarangApiController extends Controller
{
    /**
     * Daftar Barang — mendukung ?search=xxx&kategori=xxx&per_page=15
     */
    public function index(Request $request): JsonResponse
    {
        $search   = $request->query('search');
        $kategori = $request->query('kategori');
        $perPage  = (int) $request->query('per_page', 15);

        $query = MasterBarang::with(['rackLocation', 'inboundDetails', 'outboundDetails']);

        if ($search) {
            $s = strtolower($search);
            $query->where(function ($q) use ($s) {
                $q->whereRaw('LOWER("SKU") LIKE ?', ["%{$s}%"])
                  ->orWhereRaw('LOWER("Nama") LIKE ?', ["%{$s}%"])
                  ->orWhereRaw('LOWER("Kategori") LIKE ?', ["%{$s}%"]);
            });
        }

        if ($kategori) {
            $query->whereRaw('LOWER("Kategori") = ?', [strtolower($kategori)]);
        }

        $paginator = $query->paginate($perPage);

        $items = collect($paginator->items())->map(fn($item) => $this->formatItem($item));

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    /**
     * Detail satu barang + histori 10 inbound/outbound terakhir.
     */
    public function show(string $sku): JsonResponse
    {
        $item = MasterBarang::with([
            'rackLocation',
            'inboundDetails.inboundTransaction.supplier',
            'outboundDetails.outboundTransaction.customer',
        ])->where('SKU', $sku)->firstOrFail();

        $inboundHistory = $item->inboundDetails->sortByDesc('created_at')->take(10)->map(fn($d) => [
            'no_receiving'   => $d->inboundTransaction->No_Receiving ?? '-',
            'tanggal'        => optional($d->inboundTransaction->Tanggal)->format('d/m/Y'),
            'supplier'       => $d->inboundTransaction->supplier->Nama ?? '-',
            'qty'            => $d->Qty,
            'no_resi'        => $d->No_Resi_Supplier,
        ]);

        $outboundHistory = $item->outboundDetails->sortByDesc('created_at')->take(10)->map(fn($d) => [
            'no_shipping'  => $d->outboundTransaction->No_Shipping ?? '-',
            'tanggal'      => optional($d->outboundTransaction->Tanggal)->format('d/m/Y'),
            'customer'     => $d->outboundTransaction->customer->Nama ?? '-',
            'qty'          => $d->Qty,
        ]);

        return response()->json([
            'success' => true,
            'data'    => array_merge($this->formatItem($item), [
                'inbound_history'  => $inboundHistory->values(),
                'outbound_history' => $outboundHistory->values(),
            ]),
        ]);
    }

    /**
     * Daftar kategori unik untuk dropdown filter.
     */
    public function kategori(): JsonResponse
    {
        $list = MasterBarang::distinct()->orderBy('Kategori')->pluck('Kategori')->filter()->values();

        return response()->json([
            'success' => true,
            'data'    => $list,
        ]);
    }

    // =========================================================
    // PRIVATE HELPER
    // =========================================================

    private function formatItem(MasterBarang $item): array
    {
        return [
            'sku'           => $item->SKU,
            'nama'          => $item->Nama,
            'kategori'      => $item->Kategori,
            'stok'          => $item->stok,
            'min_stok'      => $item->Min_Stok,
            'barcode_id'    => $item->Barcode_ID,
            'nilai_barang'  => $item->nilai_barang,
            'status_stok'   => $item->stok == 0 ? 'Habis' : ($item->stok <= $item->Min_Stok ? 'Reorder' : 'Aman'),
            'rack'          => $item->rackLocation ? [
                'rack_id'  => $item->rackLocation->Rack_ID,
                'kode_rak' => $item->rackLocation->Kode_Rak,
                'aisle'    => $item->rackLocation->Aisle,
                'level'    => $item->rackLocation->Level,
            ] : null,
        ];
    }
}
