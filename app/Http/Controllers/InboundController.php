<?php

namespace App\Http\Controllers;

use App\Models\InboundTransaction;
use App\Models\InboundDetail;
use App\Models\Supplier;
use App\Models\MasterBarang;
use App\Models\RackLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Picqer\Barcode\BarcodeGeneratorPNG;

class InboundController extends Controller
{
    public function index()
    {
        $inbounds = InboundTransaction::with(['supplier', 'user'])
            ->when(request('search'), function($q, $search) {
                return $q->where('No_Receiving', 'like', "%{$search}%");
            })
            ->latest('Tanggal')
            ->paginate(20);
        
        return view('inbound.index', compact('inbounds'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $barangs = MasterBarang::all();
        $racks = RackLocation::whereRaw('kapasitas_terisi < Kapasitas')->get();
        
        return view('inbound.create', compact('suppliers', 'barangs', 'racks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,Supplier_ID',
            'notes' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.sku' => 'required|exists:master_barang,SKU',
            'details.*.rack_id' => 'required|exists:rack_locations,Rack_ID',
            'details.*.qty' => 'required|integer|min:1',
            'details.*.batch' => 'required|string',
            'details.*.expired_date' => 'nullable|date',
        ]);
        
        DB::beginTransaction();
        try {
            // Generate No_Receiving
            $today = date('Ymd');
            $count = InboundTransaction::whereDate('created_at', today())->count();
            $no = 'RCV-' . $today . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            
            // Create header
            $inbound = InboundTransaction::create([
                'No_Receiving' => $no,
                'Tanggal' => $validated['tanggal'],
                'Supplier_ID' => $validated['supplier_id'],
                'User_ID' => auth()->id(),
                'status' => 'completed',
                'notes' => $validated['notes'],
            ]);
            
            // Create details & update stock
            foreach ($validated['details'] as $detail) {
                InboundDetail::create([
                    'Inbound_ID' => $inbound->Inbound_ID,
                    'SKU' => $detail['sku'],
                    'Rack_ID' => $detail['rack_id'],
                    'Qty' => $detail['qty'],
                    'Batch' => $detail['batch'],
                    'expired_date' => $detail['expired_date'] ?? null,
                ]);
                
                // Update stok barang
                $barang = MasterBarang::find($detail['sku']);
                $barang->increment('stok_real', $detail['qty']);
                
                // Update kapasitas rak
                $rack = RackLocation::find($detail['rack_id']);
                $rack->increment('kapasitas_terisi', $detail['qty']);
            }
            
            DB::commit();
            
            return redirect()->route('inbound.show', $inbound->Inbound_ID)
                ->with('success', 'Transaksi inbound berhasil disimpan.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $inbound = InboundTransaction::with([
            'supplier',
            'user',
            'inboundDetails.masterBarang',
            'inboundDetails.rackLocation'
        ])->findOrFail($id);
        
        return view('inbound.show', compact('inbound'));
    }

    public function barcode($id)
    {
        $inbound = InboundTransaction::with('inboundDetails.masterBarang')->findOrFail($id);
        
        $generator = new BarcodeGeneratorPNG();
        $barcodes = [];
        
        foreach ($inbound->inboundDetails as $detail) {
            $barcodes[] = [
                'sku' => $detail->SKU,
                'nama' => $detail->masterBarang->Nama,
                'batch' => $detail->Batch,
                'qty' => $detail->Qty,
                'image' => base64_encode($generator->getBarcode($detail->SKU, $generator::TYPE_CODE_128, 2, 50)),
            ];
        }
        
        return view('inbound.barcode', compact('inbound', 'barcodes'));
    }
}
