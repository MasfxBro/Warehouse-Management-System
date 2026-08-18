<?php

namespace App\Http\Controllers;

use App\Models\MasterBarang;
use App\Models\InboundTransaction;
use App\Models\OutboundTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LaporanController extends Controller
{
    public function index()
    {
        return view('laporan.index');
    }

    public function exportInventory(Request $request)
    {
        $barangs = MasterBarang::with('rackLocation')->get();
        
        $filename = 'Laporan-Inventory-' . date('Ymd') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($barangs) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['SKU', 'Nama', 'Kategori', 'Stok Real', 'Min Stok', 'Harga Beli', 'Harga Jual', 'Nilai Persediaan', 'Rack']);
            
            // Data
            foreach ($barangs as $b) {
                fputcsv($file, [
                    $b->SKU,
                    $b->Nama,
                    $b->Kategori,
                    $b->stok_real,
                    $b->Min_Stok,
                    $b->Harga_Beli,
                    $b->Harga_Jual,
                    $b->stok_real * $b->Harga_Beli,
                    $b->rackLocation?->Kode_Rak ?? '-',
                ]);
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }

    public function exportInbound(Request $request)
    {
        $query = InboundTransaction::with(['supplier', 'inboundDetails.masterBarang']);
        
        if ($request->start_date) {
            $query->whereDate('Tanggal', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('Tanggal', '<=', $request->end_date);
        }
        
        $inbounds = $query->get();
        
        $filename = 'Laporan-Inbound-' . date('Ymd') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($inbounds) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['No Receiving', 'Tanggal', 'Supplier', 'SKU', 'Nama Barang', 'Qty', 'Batch', 'Expired Date']);
            
            // Data
            foreach ($inbounds as $inbound) {
                foreach ($inbound->inboundDetails as $detail) {
                    fputcsv($file, [
                        $inbound->No_Receiving,
                        $inbound->Tanggal->format('Y-m-d'),
                        $inbound->supplier->Nama,
                        $detail->SKU,
                        $detail->masterBarang->Nama,
                        $detail->Qty,
                        $detail->Batch,
                        $detail->expired_date?->format('Y-m-d') ?? '-',
                    ]);
                }
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }

    public function exportOutbound(Request $request)
    {
        $query = OutboundTransaction::with(['customer', 'outboundDetails.masterBarang']);
        
        if ($request->start_date) {
            $query->whereDate('Tanggal', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('Tanggal', '<=', $request->end_date);
        }
        
        $outbounds = $query->get();
        
        $filename = 'Laporan-Outbound-' . date('Ymd') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($outbounds) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['No Shipping', 'Tanggal', 'Customer', 'SKU', 'Nama Barang', 'Qty']);
            
            // Data
            foreach ($outbounds as $outbound) {
                foreach ($outbound->outboundDetails as $detail) {
                    fputcsv($file, [
                        $outbound->No_Shipping,
                        $outbound->Tanggal->format('Y-m-d'),
                        $outbound->customer->Nama,
                        $detail->SKU,
                        $detail->masterBarang->Nama,
                        $detail->Qty,
                    ]);
                }
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }
}
