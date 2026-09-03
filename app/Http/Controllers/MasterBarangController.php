<?php

namespace App\Http\Controllers;

use App\Models\MasterBarang;
use App\Models\InboundDetail;
use App\Models\OutboundDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
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

        $query = MasterBarang::with(['rackLocation'])
            ->withSum('inboundDetails as inbound_qty', 'Qty')
            ->withSum('outboundDetails as outbound_qty', 'Qty');

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
        $item = MasterBarang::with(['rackLocation'])
            ->withSum('inboundDetails as inbound_qty', 'Qty')
            ->withSum('outboundDetails as outbound_qty', 'Qty')
            ->where('SKU', $sku)
            ->firstOrFail();

        // Hitung stok dari sum (hindari N+1 accessor query)
        $item->computed_stok = max(0, (int)($item->inbound_qty ?? 0) - (int)($item->outbound_qty ?? 0));

        $rackName = $item->rackLocation
            ? "{$item->rackLocation->Kode_Rak} (Lorong {$item->rackLocation->Aisle} - Level {$item->rackLocation->Level})"
            : 'Belum Ditentukan';

        // Payload string QR Barcode
        $qrString = "{$item->SKU} - {$item->Nama} - {$rackName}";

        // Histori Inbound & Outbound per-barang — hanya 2 terbaru untuk tampilan ringkas
        $inboundHistory  = InboundDetail::with('inboundTransaction.supplier')
            ->where('SKU', $sku)
            ->orderByDesc('created_at')
            ->take(2)
            ->get();
        $outboundHistory = OutboundDetail::with('outboundTransaction.customer')
            ->where('SKU', $sku)
            ->orderByDesc('created_at')
            ->take(2)
            ->get();

        return view('master.barang.show', compact('item', 'rackName', 'qrString', 'inboundHistory', 'outboundHistory'));
    }

    /**
     * Generate dan stream PDF label QR untuk barang.
     */
    public function labelPdf($sku)
    {
        $item = MasterBarang::with(['rackLocation'])
            ->where('SKU', $sku)
            ->firstOrFail();

        $rackName = $item->rackLocation
            ? "{$item->rackLocation->Kode_Rak} (Lorong {$item->rackLocation->Aisle} - Level {$item->rackLocation->Level})"
            : 'Belum Ditentukan';

        $qrString = "{$item->SKU} - {$item->Nama} - {$rackName}";

        // Generate QR sebagai base64 PNG — DomPDF tidak bisa render JS QR library
        $qrCode = new QrCode(
            data: $qrString,
            size: 200,
            margin: 10,
        );
        $writer  = new PngWriter();
        $result  = $writer->write($qrCode);
        $qrBase64 = base64_encode($result->getString());

        $pdf = Pdf::loadView('master.barang.label-pdf', [
            'item'     => $item,
            'rackName' => $rackName,
            'qrString' => $qrString,
            'qrBase64' => $qrBase64,
            'printedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("label-{$item->SKU}.pdf");
    }
}
