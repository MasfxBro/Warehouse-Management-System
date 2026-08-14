<?php

namespace App\Http\Controllers;

use App\Models\MasterBarang;
use App\Models\InboundDetail;
use App\Models\OutboundDetail;
use Illuminate\Http\Request;

class KartuStokController extends Controller
{
    public function index()
    {
        $barangs = MasterBarang::select('SKU', 'Nama', 'stok_real', 'Min_Stok', 'harga', 'satuan')
            ->when(request('search'), function($q, $search) {
                return $q->where('Nama', 'like', "%{$search}%")
                         ->orWhere('SKU', 'like', "%{$search}%");
            })
            ->get()
            ->map(function($b) {
                return [
                    'sku' => $b->SKU,
                    'nama' => $b->Nama,
                    'stok_real' => $b->stok_real,
                    'min_stok' => $b->Min_Stok,
                    'satuan' => $b->satuan,
                    'nilai' => $b->getNilaiPersediaan(),
                    'status' => $b->getStockStatus(),
                ];
            });
        
        return view('inventory.kartu-stok', compact('barangs'));
    }

    public function show($sku)
    {
        $barang = MasterBarang::findOrFail($sku);
        
        // Build ledger: inbound + outbound transactions
        $inbounds = InboundDetail::where('SKU', $sku)
            ->with('inboundTransaction')
            ->get()
            ->map(fn($d) => [
                'tanggal' => $d->inboundTransaction->Tanggal,
                'no_trans' => $d->inboundTransaction->No_Receiving,
                'jenis' => 'INBOUND',
                'qty_in' => $d->Qty,
                'qty_out' => 0,
                'batch' => $d->Batch,
            ]);
        
        $outbounds = OutboundDetail::where('SKU', $sku)
            ->with('outboundTransaction')
            ->get()
            ->map(fn($d) => [
                'tanggal' => $d->outboundTransaction->Tanggal,
                'no_trans' => $d->outboundTransaction->No_Shipping,
                'jenis' => 'OUTBOUND',
                'qty_in' => 0,
                'qty_out' => $d->Qty,
                'batch' => '-',
            ]);
        
        // Merge and calculate running balance
        $ledger = $inbounds->merge($outbounds)
            ->sortBy('tanggal')
            ->values();
        
        $saldo = 0;
        $ledger = $ledger->map(function($item) use (&$saldo) {
            $saldo += ($item['qty_in'] - $item['qty_out']);
            $item['saldo'] = $saldo;
            return $item;
        });
        
        return view('inventory.kartu-stok-detail', compact('barang', 'ledger'));
    }
}
