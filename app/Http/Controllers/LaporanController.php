<?php

namespace App\Http\Controllers;

use App\Models\MasterBarang;
use App\Models\InboundTransaction;
use App\Models\OutboundTransaction;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LaporanController extends Controller
{
    public function index()
    {
        return view('laporan.index');
    }

    public function exportInventory(Request $request)
    {
        $barangs = MasterBarang::with('rackLocation')->get();
        
        return Excel::download(new class($barangs) implements FromCollection, WithHeadings {
            public function __construct(private $data) {}
            
            public function collection()
            {
                return $this->data->map(fn($b) => [
                    'SKU' => $b->SKU,
                    'Nama' => $b->Nama,
                    'Kategori' => $b->Kategori,
                    'Stok Real' => $b->stok_real,
                    'Min Stok' => $b->Min_Stok,
                    'Satuan' => $b->satuan,
                    'Harga' => $b->harga,
                    'Nilai' => $b->getNilaiPersediaan(),
                    'Status' => $b->getStockStatus(),
                    'Rack' => $b->rackLocation?->Kode_Rak ?? '-',
                ]);
            }
            
            public function headings(): array
            {
                return ['SKU', 'Nama', 'Kategori', 'Stok Real', 'Min Stok', 'Satuan', 'Harga', 'Nilai', 'Status', 'Rack'];
            }
        }, 'Laporan-Inventory-' . date('Ymd') . '.xlsx');
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
        
        return Excel::download(new class($inbounds) implements FromCollection, WithHeadings {
            public function __construct(private $data) {}
            
            public function collection()
            {
                $rows = collect();
                foreach ($this->data as $inbound) {
                    foreach ($inbound->inboundDetails as $detail) {
                        $rows->push([
                            'No Receiving' => $inbound->No_Receiving,
                            'Tanggal' => $inbound->Tanggal->format('Y-m-d'),
                            'Supplier' => $inbound->supplier->Nama,
                            'SKU' => $detail->SKU,
                            'Nama Barang' => $detail->masterBarang->Nama,
                            'Qty' => $detail->Qty,
                            'Batch' => $detail->Batch,
                            'Expired Date' => $detail->expired_date?->format('Y-m-d') ?? '-',
                        ]);
                    }
                }
                return $rows;
            }
            
            public function headings(): array
            {
                return ['No Receiving', 'Tanggal', 'Supplier', 'SKU', 'Nama Barang', 'Qty', 'Batch', 'Expired Date'];
            }
        }, 'Laporan-Inbound-' . date('Ymd') . '.xlsx');
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
        
        return Excel::download(new class($outbounds) implements FromCollection, WithHeadings {
            public function __construct(private $data) {}
            
            public function collection()
            {
                $rows = collect();
                foreach ($this->data as $outbound) {
                    foreach ($outbound->outboundDetails as $detail) {
                        $rows->push([
                            'No Shipping' => $outbound->No_Shipping,
                            'Tanggal' => $outbound->Tanggal->format('Y-m-d'),
                            'Customer' => $outbound->customer->Nama,
                            'SKU' => $detail->SKU,
                            'Nama Barang' => $detail->masterBarang->Nama,
                            'Qty' => $detail->Qty,
                        ]);
                    }
                }
                return $rows;
            }
            
            public function headings(): array
            {
                return ['No Shipping', 'Tanggal', 'Customer', 'SKU', 'Nama Barang', 'Qty'];
            }
        }, 'Laporan-Outbound-' . date('Ymd') . '.xlsx');
    }
}
