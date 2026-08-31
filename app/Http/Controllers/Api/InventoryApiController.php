<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InboundDetail;
use App\Models\MasterBarang;
use App\Models\OutboundDetail;
use Illuminate\Http\JsonResponse;

/**
 * API Controller: Inventory / Kartu Stok
 *
 * GET /api/inventory/kartu-stok         → Semua barang + stok
 * GET /api/inventory/kartu-stok/{sku}   → Timeline mutasi per barang
 */
class InventoryApiController extends Controller
{
    /**
     * Kartu Stok — semua barang dengan stok real-time.
     */
    public function kartuStokIndex(): JsonResponse
    {
        $items = MasterBarang::with(['rackLocation', 'inboundDetails', 'outboundDetails'])->get();

        $data = $items->map(fn($item) => [
            'sku'          => $item->SKU,
            'nama'         => $item->Nama,
            'kategori'     => $item->Kategori,
            'stok'         => $item->stok,
            'min_stok'     => $item->Min_Stok,
            'status_stok'  => $item->stok == 0 ? 'Habis' : ($item->stok <= $item->Min_Stok ? 'Reorder' : 'Aman'),
            'rack'         => $item->rackLocation?->Kode_Rak ?? '-',
        ]);

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Timeline mutasi stok untuk satu SKU (Kartu Stok Detail).
     */
    public function kartuStokDetail(string $sku): JsonResponse
    {
        $barang = MasterBarang::with('rackLocation')->findOrFail($sku);

        $inbounds = InboundDetail::with(['inboundTransaction.supplier', 'inboundTransaction.user'])
            ->where('SKU', $sku)->get()
            ->map(fn($d) => [
                'tanggal'  => $d->inboundTransaction->Tanggal->format('Y-m-d'),
                'jenis'    => 'Inbound',
                'no_ref'   => $d->inboundTransaction->No_Receiving,
                'qty_in'   => $d->Qty,
                'qty_out'  => 0,
                'operator' => $d->inboundTransaction->user->name ?? '-',
            ]);

        $outbounds = OutboundDetail::with(['outboundTransaction.user'])
            ->where('SKU', $sku)->get()
            ->map(fn($d) => [
                'tanggal'  => $d->outboundTransaction->Tanggal->format('Y-m-d'),
                'jenis'    => 'Outbound',
                'no_ref'   => $d->outboundTransaction->No_Shipping,
                'qty_in'   => 0,
                'qty_out'  => $d->Qty,
                'operator' => $d->outboundTransaction->user->name ?? '-',
            ]);

        $mutations = collect()->concat($inbounds)->concat($outbounds)
            ->sortBy('tanggal')->values();

        $saldo = 0;
        $mutations = $mutations->map(function ($m) use (&$saldo) {
            $saldo      += ($m['qty_in'] - $m['qty_out']);
            $m['saldo']  = $saldo;
            return $m;
        })->reverse()->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'barang' => [
                    'sku'      => $barang->SKU,
                    'nama'     => $barang->Nama,
                    'kategori' => $barang->Kategori,
                    'stok'     => $barang->stok,
                    'rack'     => $barang->rackLocation?->Kode_Rak ?? '-',
                ],
                'mutations' => $mutations,
            ],
        ]);
    }
}
