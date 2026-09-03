<?php

namespace App\Http\Controllers;

use App\Models\InboundDetail;
use App\Models\MasterBarang;
use App\Models\OutboundDetail;

class InventoryController extends Controller
{
    // =========================================================
    // KARTU STOK — Index semua barang
    // =========================================================

    public function kartuStokIndex()
    {
        // Hanya Admin yang boleh akses Kartu Stok
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Fitur Kartu Stok hanya tersedia untuk Guru (Admin).');
        }
        $items = MasterBarang::with('rackLocation')
            ->withSum('inboundDetails as inbound_qty', 'Qty')
            ->withSum('outboundDetails as outbound_qty', 'Qty')
            ->paginate(15)
            ->withQueryString();
        return view('inventory.kartu-stok', compact('items'));
    }

    // =========================================================
    // KARTU STOK — Detail timeline mutasi per SKU
    // =========================================================

    public function kartuStokDetail(string $sku)
    {
        // Hanya Admin yang boleh akses detail Kartu Stok
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Fitur Kartu Stok hanya tersedia untuk Guru (Admin).');
        }

        $barang = MasterBarang::with('rackLocation')->findOrFail($sku);

        // Ambil semua inbound untuk SKU ini
        $inbounds = InboundDetail::with(['inboundTransaction.supplier', 'inboundTransaction.user'])
            ->where('SKU', $sku)
            ->get()
            ->map(fn ($d) => [
                'tanggal'  => $d->inboundTransaction->Tanggal,
                'jenis'    => 'Inbound',
                'no_ref'   => $d->inboundTransaction->No_Receiving,
                'qty_in'   => $d->Qty,
                'qty_out'  => 0,
                'operator' => $d->inboundTransaction->user->name ?? '-',
            ]);

        // Ambil semua outbound untuk SKU ini
        $outbounds = OutboundDetail::with(['outboundTransaction.user'])
            ->where('SKU', $sku)
            ->get()
            ->map(fn ($d) => [
                'tanggal'  => $d->outboundTransaction->Tanggal,
                'jenis'    => 'Outbound',
                'no_ref'   => $d->outboundTransaction->No_Shipping,
                'qty_in'   => 0,
                'qty_out'  => $d->Qty,
                'operator' => $d->outboundTransaction->user->name ?? '-',
            ]);

        // Gabung dan urutkan berdasarkan tanggal (ascending — paling lama di atas)
        $mutations = collect()
            ->concat($inbounds)
            ->concat($outbounds)
            ->sortBy([
                ['tanggal', 'asc'],
            ])
            ->values();

        // Hitung running saldo
        $saldo = 0;
        $mutations = $mutations->map(function ($m) use (&$saldo) {
            $saldo     += ($m['qty_in'] - $m['qty_out']);
            $m['saldo']  = $saldo;
            return $m;
        });

        // Untuk tampilan terbaru di atas, reverse setelah kalkulasi saldo
        $mutations = $mutations->reverse()->values();

        return view('inventory.kartu-stok-detail', compact('barang', 'mutations'));
    }
}
