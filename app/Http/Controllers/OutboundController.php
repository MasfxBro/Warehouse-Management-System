<?php

namespace App\Http\Controllers;

use App\Models\OutboundTransaction;
use App\Models\OutboundDetail;
use App\Models\InboundDetail;
use App\Models\Customer;
use App\Models\MasterBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OutboundController extends Controller
{
    public function index()
    {
        $outbounds = OutboundTransaction::with(['customer', 'user'])
            ->when(request('search'), function($q, $search) {
                return $q->where('No_Shipping', 'like', "%{$search}%");
            })
            ->latest('Tanggal')
            ->paginate(20);
        
        return view('outbound.index', compact('outbounds'));
    }

    public function create()
    {
        $customers = Customer::all();
        $barangs = MasterBarang::where('stok_real', '>', 0)->get();
        
        return view('outbound.create', compact('customers', 'barangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'customer_id' => 'required|exists:customers,Customer_ID',
            'notes' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.sku' => 'required|exists:master_barang,SKU',
            'details.*.qty' => 'required|integer|min:1',
        ]);
        
        DB::beginTransaction();
        try {
            // Validate stock availability
            foreach ($validated['details'] as $detail) {
                $barang = MasterBarang::find($detail['sku']);
                if ($barang->stok_real < $detail['qty']) {
                    throw new \Exception("Stok {$barang->Nama} tidak mencukupi. Tersedia: {$barang->stok_real}");
                }
            }
            
            // Generate No_Shipping
            $today = date('Ymd');
            $count = OutboundTransaction::whereDate('created_at', today())->count();
            $no = 'SHP-' . $today . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            
            // Create header
            $outbound = OutboundTransaction::create([
                'No_Shipping' => $no,
                'Tanggal' => $validated['tanggal'],
                'Customer_ID' => $validated['customer_id'],
                'User_ID' => auth()->id(),
                'status' => 'completed',
                'notes' => $validated['notes'],
            ]);
            
            // Create details & update stock (FIFO)
            foreach ($validated['details'] as $detail) {
                OutboundDetail::create([
                    'Outbound_ID' => $outbound->Outbound_ID,
                    'SKU' => $detail['sku'],
                    'Qty' => $detail['qty'],
                ]);
                
                // FIFO: Reduce from oldest batches first
                $remaining = $detail['qty'];
                $inboundDetails = InboundDetail::where('SKU', $detail['sku'])
                    ->where('Qty', '>', 0)
                    ->orderBy('expired_date', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                foreach ($inboundDetails as $inbound) {
                    if ($remaining <= 0) break;
                    
                    $take = min($remaining, $inbound->Qty);
                    $inbound->decrement('Qty', $take);
                    $remaining -= $take;
                    
                    // Update rack capacity
                    $inbound->rackLocation()->decrement('kapasitas_terisi', $take);
                }
                
                // Update stok barang
                $barang = MasterBarang::find($detail['sku']);
                $barang->decrement('stok_real', $detail['qty']);
            }
            
            DB::commit();
            
            return redirect()->route('outbound.show', $outbound->Outbound_ID)
                ->with('success', 'Transaksi outbound berhasil disimpan.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $outbound = OutboundTransaction::with([
            'customer',
            'user',
            'outboundDetails.masterBarang'
        ])->findOrFail($id);
        
        return view('outbound.show', compact('outbound'));
    }

    public function pickingList($id)
    {
        $outbound = OutboundTransaction::with('outboundDetails.masterBarang')->findOrFail($id);
        
        $pickingData = [];
        foreach ($outbound->outboundDetails as $detail) {
            // FIFO: Get oldest batches
            $inboundDetails = InboundDetail::where('SKU', $detail->SKU)
                ->where('Qty', '>', 0)
                ->with('rackLocation')
                ->orderBy('expired_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();
            
            $remaining = $detail->Qty;
            $picks = [];
            
            foreach ($inboundDetails as $inbound) {
                if ($remaining <= 0) break;
                
                $take = min($remaining, $inbound->Qty);
                $picks[] = [
                    'batch' => $inbound->Batch,
                    'rack' => $inbound->rackLocation->Kode_Rak,
                    'qty' => $take,
                    'expired' => $inbound->expired_date,
                ];
                $remaining -= $take;
            }
            
            $pickingData[] = [
                'sku' => $detail->SKU,
                'nama' => $detail->masterBarang->Nama,
                'qty_total' => $detail->Qty,
                'picks' => $picks,
            ];
        }
        
        return view('outbound.picking-list', compact('outbound', 'pickingData'));
    }

    public function suratJalan($id)
    {
        $outbound = OutboundTransaction::with([
            'customer',
            'user',
            'outboundDetails.masterBarang'
        ])->findOrFail($id);
        
        $pdf = PDF::loadView('outbound.surat-jalan-pdf', compact('outbound'));
        return $pdf->stream('Surat-Jalan-' . $outbound->No_Shipping . '.pdf');
    }
}
