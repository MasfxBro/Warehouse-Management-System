<?php

namespace App\Http\Controllers;

use App\Models\MasterBarang;
use App\Models\InboundDetail;
use App\Models\OutboundDetail;
use Illuminate\Http\Request;

class MasterBarangController extends Controller
{
    /**
     * Tampilkan daftar Master Data Barang (PURE READ-ONLY).
     */
    public function index(Request $request)
    {
        $search   = $request->query('search');
        $kategori = $request->query('kategori');

        $query = MasterBarang::with(['rackLocation', 'inboundDetails', 'outboundDetails']);

        if ($search) {
            $searchLower = strtolower($search);
            $query->where(function ($q) use ($searchLower) {
                $q->whereRaw("LOWER(\"SKU\") LIKE ?", ['%' . $searchLower . '%'])
                  ->orWhereRaw("LOWER(\"Nama\") LIKE ?", ['%' . $searchLower . '%'])
                  ->orWhereRaw("LOWER(\"Kategori\") LIKE ?", ['%' . $searchLower . '%']);
            });
        }

        if ($kategori) {
            $query->whereRaw("LOWER(\"Kategori\") = ?", [strtolower($kategori)]);
        }

        $items = $query->paginate(15)->withQueryString();
        $kategoriList = MasterBarang::distinct()->pluck('Kategori');

        return view('master.barang.index', compact('items', 'search', 'kategori', 'kategoriList'));
    }

    /**
     * Tampilkan detail lengkap barang beserta QR Barcode generator.
     */
    public function show($sku)
    {
        $item = MasterBarang::with(['rackLocation', 'inboundDetails.inboundTransaction.supplier', 'outboundDetails.outboundTransaction.customer'])
            ->where('SKU', $sku)
            ->firstOrFail();

        $rackName = $item->rackLocation ? "{$item->rackLocation->Kode_Rak} (Lorong {$item->rackLocation->Aisle} - Level {$item->rackLocation->Level})" : 'Belum Ditentukan';
        
        // Payload string QR Barcode: [Kode SKU] - [Nama Barang] - [Lokasi Rak]
        $qrString = "{$item->SKU} - {$item->Nama} - {$rackName}";

        // Histori Inbound & Outbound per-barang
        $inboundHistory  = InboundDetail::with('inboundTransaction.supplier')->where('SKU', $sku)->latest()->take(10)->get();
        $outboundHistory = OutboundDetail::with('outboundTransaction.customer')->where('SKU', $sku)->latest()->take(10)->get();

        return view('master.barang.show', compact('item', 'rackName', 'qrString', 'inboundHistory', 'outboundHistory'));
    }
}
