<?php

namespace App\Http\Controllers;

use App\Models\InboundDetail;
use App\Models\MasterBarang;
use App\Models\OutboundDetail;
use App\Models\RackLocation;
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
        $search = $request->query('search');
        $kategori = $request->query('kategori');

        $query = MasterBarang::with(['rackLocation'])
            ->withSum('inboundDetails as inbound_qty', 'Qty')
            ->withSum('outboundDetails as outbound_qty', 'Qty')
            ->withSum('completedOutboundDetails as completed_outbound_qty', 'Qty')
            ->withSum('reservedOutboundDetails as reserved_qty', 'Qty');

        if ($search) {
            $searchLower = strtolower($search);
            $query->where(function ($q) use ($searchLower) {
                $q->whereRaw('LOWER("SKU") LIKE ?', ['%'.$searchLower.'%'])
                    ->orWhereRaw('LOWER("Nama") LIKE ?', ['%'.$searchLower.'%'])
                    ->orWhereRaw('LOWER("Kategori") LIKE ?', ['%'.$searchLower.'%']);
            });
        }

        if ($kategori) {
            $query->whereRaw('LOWER("Kategori") = ?', [strtolower($kategori)]);
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
            ->withSum('completedOutboundDetails as completed_outbound_qty', 'Qty')
            ->withSum('reservedOutboundDetails as reserved_qty', 'Qty')
            ->where('SKU', $sku)
            ->firstOrFail();

        $item->computed_stok = max(0, (int) ($item->inbound_qty ?? 0) - (int) ($item->outbound_qty ?? 0));
        $item->physical_stock = max(0, (int) ($item->inbound_qty ?? 0) - (int) ($item->completed_outbound_qty ?? 0));
        $item->reserved_stock = (int) ($item->reserved_qty ?? 0);

        // Hitung distribusi stok per rak (dari InboundDetail - OutboundDetail per Rack_ID)
        $inboundPerRak = InboundDetail::where('SKU', $sku)
            ->with('rackLocation')
            ->selectRaw('"Rack_ID", SUM("Qty") as in_qty')
            ->groupBy('Rack_ID')
            ->get()
            ->keyBy('Rack_ID');

        $completedOutboundPerRak = OutboundDetail::where('SKU', $sku)
            ->whereHas('outboundTransaction', fn ($query) => $query
                ->where('transaction_status', 'active')
                ->where('picking_status', 'complete'))
            ->selectRaw('"Rack_ID", SUM("Qty") as out_qty')
            ->groupBy('Rack_ID')
            ->get()
            ->keyBy('Rack_ID');

        $reservedOutboundPerRak = OutboundDetail::where('SKU', $sku)
            ->whereHas('outboundTransaction', fn ($query) => $query
                ->where('transaction_status', 'active')
                ->where('picking_status', 'not_complete'))
            ->selectRaw('"Rack_ID", SUM("Qty") as out_qty')
            ->groupBy('Rack_ID')
            ->get()
            ->keyBy('Rack_ID');

        // Gabungkan dan hitung net stok per rak, filter hanya yang > 0
        $allRakIds = $inboundPerRak->keys()
            ->merge($completedOutboundPerRak->keys())
            ->merge($reservedOutboundPerRak->keys())
            ->unique();
        $stokPerRak = $allRakIds->map(function ($rackId) use ($inboundPerRak, $completedOutboundPerRak, $reservedOutboundPerRak) {
            $inQty = (int) ($inboundPerRak->get($rackId)?->in_qty ?? 0);
            $completedQty = (int) ($completedOutboundPerRak->get($rackId)?->out_qty ?? 0);
            $reservedQty = (int) ($reservedOutboundPerRak->get($rackId)?->out_qty ?? 0);
            $physical = max(0, $inQty - $completedQty);
            $available = max(0, $physical - $reservedQty);
            if ($physical <= 0) {
                return null;
            }

            $rak = $inboundPerRak->get($rackId)?->rackLocation
                ?? RackLocation::find($rackId);

            return [
                'rack_id' => $rackId,
                'kode_rak' => $rak ? $rak->Kode_Rak : '?',
                'aisle' => $rak ? $rak->Aisle : '-',
                'level' => $rak ? $rak->Level : '-',
                'fisik' => $physical,
                'reservasi' => $reservedQty,
                'tersedia' => $available,
            ];
        })->filter()->values();

        // rackName: jika di lebih dari 1 rak, tampilkan "Beberapa Rak"
        if ($stokPerRak->count() > 1) {
            $rackName = 'Beberapa Rak ('.$stokPerRak->count().' lokasi)';
        } elseif ($stokPerRak->count() === 1) {
            $r = $stokPerRak->first();
            $rackName = "{$r['kode_rak']} (Lorong {$r['aisle']} - Level {$r['level']})";
        } else {
            $rackName = $item->rackLocation
                ? "{$item->rackLocation->Kode_Rak} (Lorong {$item->rackLocation->Aisle} - Level {$item->rackLocation->Level})"
                : 'Belum Ditentukan';
        }

        $qrString = "{$item->SKU} - {$item->Nama} - {$rackName}";

        $inboundHistory = InboundDetail::with('inboundTransaction.supplier')
            ->where('SKU', $sku)
            ->orderByDesc('created_at')
            ->take(2)
            ->get();
        $outboundHistory = OutboundDetail::with('outboundTransaction.customer')
            ->where('SKU', $sku)
            ->orderByDesc('created_at')
            ->take(2)
            ->get();

        return view('master.barang.show', compact(
            'item', 'rackName', 'qrString',
            'inboundHistory', 'outboundHistory', 'stokPerRak'
        ));
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
        $writer = new PngWriter;
        $result = $writer->write($qrCode);
        $qrBase64 = base64_encode($result->getString());

        $pdf = Pdf::loadView('master.barang.label-pdf', [
            'item' => $item,
            'rackName' => $rackName,
            'qrString' => $qrString,
            'qrBase64' => $qrBase64,
            'printedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("label-{$item->SKU}.pdf");
    }
}
