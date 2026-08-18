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
        $barangs = MasterBarang::withSum('inboundDetails as total_masuk', 'Qty')
            ->withSum('outboundDetails as total_keluar', 'Qty')
            ->when(request('search'), function($q, $search) {
                return $q->where('Nama', 'like', "%{$search}%")
                         ->orWhere('SKU', 'like', "%{$search}%");
            })
            ->get()
            ->map(function($b) {
                $stokAwal = 0;
                $totalMasuk = (int) ($b->total_masuk ?? 0);
                $totalKeluar = (int) ($b->total_keluar ?? 0);
                $stokAkhir = $stokAwal + $totalMasuk - $totalKeluar;
                $status = ($stokAkhir <= $b->Min_Stok) ? 'REORDER' : 'AMAN';

                return [
                    'sku' => $b->SKU,
                    'nama' => $b->Nama,
                    'stok_awal' => $stokAwal,
                    'total_masuk' => $totalMasuk,
                    'total_keluar' => $totalKeluar,
                    'stok_akhir' => $stokAkhir,
                    'min_stok' => $b->Min_Stok,
                    'satuan' => $b->satuan ?? 'PCS',
                    'nilai' => $stokAkhir * ($b->harga ?? 0),
                    'status' => $status,
                ];
            });
        
        return view('inventory.kartu-stok', compact('barangs'));
    }

    public function show($sku)
    {
        $barang = MasterBarang::withSum('inboundDetails as total_masuk', 'Qty')
            ->withSum('outboundDetails as total_keluar', 'Qty')
            ->findOrFail($sku);

        $stokAwal = 0;
        $totalMasuk = (int) ($barang->total_masuk ?? 0);
        $totalKeluar = (int) ($barang->total_keluar ?? 0);
        $stokAkhir = $stokAwal + $totalMasuk - $totalKeluar;
        $status = ($stokAkhir <= $barang->Min_Stok) ? 'REORDER' : 'AMAN';
        
        // Build ledger: inbound + outbound transactions
        $inbounds = InboundDetail::where('SKU', $sku)
            ->with('inboundTransaction')
            ->get()
            ->filter(fn($d) => $d->inboundTransaction !== null)
            ->map(fn($d) => [
                'id' => $d->inboundTransaction->Inbound_ID ?? $d->Inbound_ID,
                'tanggal' => $d->inboundTransaction->Tanggal,
                'no_trans' => $d->inboundTransaction->No_Receiving,
                'jenis' => 'INBOUND',
                'sku' => $d->SKU,
                'qty' => (int) $d->Qty,
                'qty_in' => (int) $d->Qty,
                'qty_out' => 0,
                'batch' => $d->Batch ?? '-',
            ]);
        
        $outbounds = OutboundDetail::where('SKU', $sku)
            ->with('outboundTransaction')
            ->get()
            ->filter(fn($d) => $d->outboundTransaction !== null)
            ->map(fn($d) => [
                'id' => $d->outboundTransaction->Outbound_ID ?? $d->Outbound_ID,
                'tanggal' => $d->outboundTransaction->Tanggal,
                'no_trans' => $d->outboundTransaction->No_Shipping,
                'jenis' => 'OUTBOUND',
                'sku' => $d->SKU,
                'qty' => (int) $d->Qty,
                'qty_in' => 0,
                'qty_out' => (int) $d->Qty,
                'batch' => '-',
            ]);
        
        // Merge and sort by date ASC, then ID ASC as tie-breaker
        $ledger = $inbounds->concat($outbounds)
            ->sort(function ($a, $b) {
                $dateA = strtotime($a['tanggal']);
                $dateB = strtotime($b['tanggal']);
                if ($dateA === $dateB) {
                    return $a['id'] <=> $b['id'];
                }
                return $dateA <=> $dateB;
            })
            ->values();
        
        $saldo = 0;
        $ledger = $ledger->map(function($item) use (&$saldo) {
            $saldo += ($item['qty_in'] - $item['qty_out']);
            $item['saldo'] = $saldo;
            return $item;
        });
        
        return view('inventory.kartu-stok-detail', compact('barang', 'stokAwal', 'totalMasuk', 'totalKeluar', 'stokAkhir', 'status', 'ledger'));
    }

    public function exportExcel()
    {
        $barangs = MasterBarang::withSum('inboundDetails as total_masuk', 'Qty')
            ->withSum('outboundDetails as total_keluar', 'Qty')
            ->get()
            ->map(function($b) {
                $stokAwal = 0;
                $totalMasuk = (int) ($b->total_masuk ?? 0);
                $totalKeluar = (int) ($b->total_keluar ?? 0);
                $stokAkhir = $stokAwal + $totalMasuk - $totalKeluar;
                $status = ($stokAkhir <= $b->Min_Stok) ? 'REORDER' : 'AMAN';

                return [
                    'sku' => $b->SKU,
                    'nama' => $b->Nama,
                    'stok_awal' => $stokAwal,
                    'total_masuk' => $totalMasuk,
                    'total_keluar' => $totalKeluar,
                    'stok_akhir' => $stokAkhir,
                    'min_stok' => $b->Min_Stok,
                    'status' => $status,
                ];
            });

        $filename = 'Kartu-Stok-' . date('Ymd-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($barangs) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, ['SKU', 'Nama Barang', 'Stok Awal', 'Total Masuk', 'Total Keluar', 'Stok Akhir', 'Min Stok', 'Status']);

            foreach ($barangs as $b) {
                fputcsv($file, [
                    $b['sku'],
                    $b['nama'],
                    $b['stok_awal'],
                    $b['total_masuk'],
                    $b['total_keluar'],
                    $b['stok_akhir'],
                    $b['min_stok'],
                    $b['status'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        $barangs = MasterBarang::withSum('inboundDetails as total_masuk', 'Qty')
            ->withSum('outboundDetails as total_keluar', 'Qty')
            ->get()
            ->map(function($b) {
                $stokAwal = 0;
                $totalMasuk = (int) ($b->total_masuk ?? 0);
                $totalKeluar = (int) ($b->total_keluar ?? 0);
                $stokAkhir = $stokAwal + $totalMasuk - $totalKeluar;
                $status = ($stokAkhir <= $b->Min_Stok) ? 'REORDER' : 'AMAN';

                return [
                    'sku' => $b->SKU,
                    'nama' => $b->Nama,
                    'stok_awal' => $stokAwal,
                    'total_masuk' => $totalMasuk,
                    'total_keluar' => $totalKeluar,
                    'stok_akhir' => $stokAkhir,
                    'min_stok' => $b->Min_Stok,
                    'status' => $status,
                ];
            });

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.kartu-stok-pdf', compact('barangs'));
            return $pdf->download('Kartu-Stok-' . date('Ymd-His') . '.pdf');
        }

        return view('exports.kartu-stok-pdf', compact('barangs'));
    }
}
