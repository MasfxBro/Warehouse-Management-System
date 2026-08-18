<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\MasterBarang;
use App\Models\RackLocation;
use App\Models\OutboundTransaction;
use App\Models\OutboundDetail;
use App\Models\InboundDetail;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OutboundController extends Controller
{
    public function index()
    {
        // Ambil semua transaksi outbound dengan relasi customer dan outbound details
        // Urutkan dari yang terbaru
        $outbounds = OutboundTransaction::with(['customer', 'outboundDetails.masterBarang'])
            ->orderBy('Tanggal', 'desc')
            ->orderBy('Outbound_ID', 'desc')
            ->get();

        return view('outbound.index', compact('outbounds'));
    }

    public function create()
    {
        $customers = Customer::all();
        $barang = MasterBarang::all();
        $racks = RackLocation::all();

        return view('outbound.create', compact(
            'customers',
            'barang',
            'racks'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'No_Shipping' => 'required|string|max:100|unique:outbound_transactions,No_Shipping',
            'Tanggal' => 'required|date',
            'Customer_ID' => 'required|exists:customers,Customer_ID',
            'No_Surat_Jalan' => 'nullable|string|max:100',
            'SKU' => 'required|exists:master_barang,SKU',
            'Rack_ID' => 'required|exists:rack_locations,Rack_ID',
            'Qty' => 'required|integer|min:1',
        ]);

        // VALIDASI STOK: Hitung stok tersedia untuk SKU yang diminta
        $stokTersedia = $this->getAvailableStock($validated['SKU']);

        // Jika Qty yang diminta lebih besar dari stok tersedia, tolak transaksi
        if ($validated['Qty'] > $stokTersedia) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'Qty' => "Stok tidak mencukupi! SKU {$validated['SKU']} hanya tersedia {$stokTersedia} unit, Anda meminta {$validated['Qty']} unit."
                ]);
        }

        // GENERATE PICKING LIST berdasarkan FIFO sebelum menyimpan transaksi
        $pickingList = $this->generatePickingList($validated['SKU'], $validated['Qty']);

        // Variable untuk menyimpan Outbound_ID yang baru dibuat
        $outboundId = null;

        // Gunakan database transaction untuk memastikan data tersimpan lengkap
        DB::transaction(function () use ($validated, &$outboundId) {
            $outbound = OutboundTransaction::create([
                'No_Shipping' => $validated['No_Shipping'],
                'Tanggal' => $validated['Tanggal'],
                'Customer_ID' => $validated['Customer_ID'],
                'No_Surat_Jalan' => $validated['No_Surat_Jalan'] ?? null,
                // Temporary: gunakan user pertama jika auth belum tersedia
                // Nanti akan diganti setelah authentication terintegrasi
                'User_ID' => auth()->id() ?? 1,
            ]);

            // Simpan ID untuk digunakan di luar transaction
            $outboundId = $outbound->Outbound_ID;

            OutboundDetail::create([
                'Outbound_ID' => $outbound->Outbound_ID,
                'SKU' => $validated['SKU'],
                'Rack_ID' => $validated['Rack_ID'],
                'Qty' => $validated['Qty'],
            ]);
        });

        // Redirect ke halaman picking list dengan data picking
        return redirect()
            ->route('outbound.picking-list', $outboundId)
            ->with('pickingList', $pickingList)
            ->with('success', 'Pengiriman berhasil disimpan. Berikut adalah Picking List.');
    }

    /**
     * Menghitung stok tersedia untuk SKU tertentu.
     * 
     * RUMUS: STOK TERSEDIA = SUM(inbound_details.Qty) - SUM(outbound_details.Qty)
     * 
     * @param string $sku
     * @return int
     */
    private function getAvailableStock(string $sku): int
    {
        // Hitung total barang masuk (inbound) untuk SKU ini
        $totalInbound = InboundDetail::where('SKU', $sku)->sum('Qty');

        // Hitung total barang keluar (outbound) untuk SKU ini
        $totalOutbound = OutboundDetail::where('SKU', $sku)->sum('Qty');

        // Stok tersedia = inbound - outbound
        return $totalInbound - $totalOutbound;
    }

    /**
     * Generate Picking List berdasarkan FIFO (First In First Out).
     * 
     * ALGORITMA FIFO:
     * 1. Ambil semua inbound_details untuk SKU tertentu
     * 2. Urutkan berdasarkan tanggal inbound (paling lama dulu)
     * 3. Hitung stok tersisa di setiap inbound detail
     * 4. Alokasikan qty yang diminta dari stok paling lama
     * 5. Jika satu sumber tidak cukup, lanjut ke sumber berikutnya (split)
     * 
     * @param string $sku
     * @param int $qtyNeeded
     * @return array
     */
    /**
     * Generate Picking List berdasarkan FIFO (First In First Out).
     * 
     * @param string $sku
     * @param int $qtyNeeded
     * @param int|null $upToOutboundId ID outbound jika meregenerasi untuk transaksi historis
     * @return array
     */
    private function generatePickingList(string $sku, int $qtyNeeded, ?int $upToOutboundId = null): array
    {
        $pickingList = [];
        $remainingQty = $qtyNeeded;

        // Ambil semua inbound_details untuk SKU ini dengan relasi inboundTransaction dan rackLocation
        // Urutkan berdasarkan Tanggal inbound (FIFO) dan Inbound_ID sebagai tie-breaker
        $inboundDetails = InboundDetail::with(['inboundTransaction', 'rackLocation', 'masterBarang'])
            ->where('SKU', $sku)
            ->get()
            ->sortBy(function ($detail) {
                return [
                    $detail->inboundTransaction->Tanggal->format('Y-m-d'),
                    $detail->inboundTransaction->Inbound_ID
                ];
            });

        // Loop setiap inbound detail untuk alokasi FIFO
        foreach ($inboundDetails as $inboundDetail) {
            // Jika sudah terpenuhi, hentikan loop
            if ($remainingQty <= 0) {
                break;
            }

            // Hitung stok tersisa dari inbound detail ini
            $qtyTersisa = $this->getAvailableStockFromInboundDetail($inboundDetail, $upToOutboundId);

            // Jika tidak ada stok tersisa di inbound detail ini, skip
            if ($qtyTersisa <= 0) {
                continue;
            }

            // Tentukan berapa banyak yang akan diambil dari sumber ini
            $qtyToTake = min($remainingQty, $qtyTersisa);

            // Tambahkan ke picking list
            $pickingList[] = [
                'sku' => $inboundDetail->SKU,
                'nama_barang' => $inboundDetail->masterBarang->Nama,
                'rack_id' => $inboundDetail->Rack_ID,
                'kode_rak' => $inboundDetail->rackLocation->Kode_Rak,
                'batch' => $inboundDetail->Batch ?? '-',
                'qty_pick' => $qtyToTake,
                'tanggal_inbound' => $inboundDetail->inboundTransaction->Tanggal->format('Y-m-d'),
                'inbound_id' => $inboundDetail->inboundTransaction->Inbound_ID,
                'no_receiving' => $inboundDetail->inboundTransaction->No_Receiving,
            ];

            // Kurangi remaining qty
            $remainingQty -= $qtyToTake;
        }

        return $pickingList;
    }

    /**
     * Menghitung stok tersisa dari satu inbound_detail tertentu.
     * 
     * @param InboundDetail $inboundDetail
     * @param int|null $upToOutboundId
     * @return int
     */
    private function getAvailableStockFromInboundDetail(InboundDetail $inboundDetail, ?int $upToOutboundId = null): int
    {
        $sku = $inboundDetail->SKU;
        
        $allInboundDetails = InboundDetail::with('inboundTransaction')
            ->where('SKU', $sku)
            ->get()
            ->sortBy(function ($detail) {
                return [
                    $detail->inboundTransaction->Tanggal->format('Y-m-d'),
                    $detail->inboundTransaction->Inbound_ID
                ];
            });

        // Ambil total outbound details untuk SKU ini
        $query = OutboundDetail::where('SKU', $sku);
        if ($upToOutboundId !== null) {
            $query->where('Outbound_ID', '<', $upToOutboundId);
        }
        $totalOutboundQty = $query->sum('Qty');

        // Simulasi FIFO: kurangi qty outbound dari inbound details secara berurutan
        $remainingOutbound = $totalOutboundQty;
        
        foreach ($allInboundDetails as $detail) {
            if ($detail->Detail_ID === $inboundDetail->Detail_ID) {
                $qtyUsed = min($remainingOutbound, $detail->Qty);
                return $detail->Qty - $qtyUsed;
            }

            $remainingOutbound -= $detail->Qty;

            if ($remainingOutbound <= 0) {
                return $inboundDetail->Qty;
            }
        }

        return $inboundDetail->Qty;
    }

    /**
     * Tampilkan Picking List setelah transaksi outbound berhasil.
     * 
     * @param int $outboundId
     * @return \Illuminate\View\View
     */
    public function showPickingList(int $outboundId)
    {
        // Ambil data outbound transaction dengan relasi
        $outbound = OutboundTransaction::with(['customer', 'outboundDetails.masterBarang', 'outboundDetails.rackLocation'])
            ->findOrFail($outboundId);

        // Ambil picking list dari session (jika ada)
        $pickingList = session('pickingList', []);

        // Jika tidak ada di session, generate ulang berdasarkan outbound detail
        if (empty($pickingList)) {
            $outboundDetail = $outbound->outboundDetails->first();
            if ($outboundDetail) {
                $pickingList = $this->generatePickingList($outboundDetail->SKU, $outboundDetail->Qty, $outbound->Outbound_ID);
            }
        }

        return view('outbound.picking-list', compact('outbound', 'pickingList'));
    }

    /**
     * Generate dan download PDF Surat Jalan.
     * 
     * @param int $outboundId
     * @return \Illuminate\Http\Response
     */
    public function downloadSuratJalan(int $outboundId)
    {
        // Ambil data outbound transaction dengan relasi
        $outbound = OutboundTransaction::with(['customer', 'outboundDetails.masterBarang', 'outboundDetails.rackLocation'])
            ->findOrFail($outboundId);

        // Generate picking list untuk ditampilkan di surat jalan
        $pickingList = [];
        foreach ($outbound->outboundDetails as $detail) {
            $pickingList = array_merge(
                $pickingList,
                $this->generatePickingList($detail->SKU, $detail->Qty, $outbound->Outbound_ID)
            );
        }

        // Load view dan generate PDF
        $pdf = Pdf::loadView('outbound.surat-jalan-pdf', compact('outbound', 'pickingList'));
        
        // Set paper size dan orientasi
        $pdf->setPaper('a4', 'portrait');

        // Download PDF dengan nama file yang sesuai
        $filename = 'Surat_Jalan_' . $outbound->No_Shipping . '.pdf';
        
        return $pdf->download($filename);
    }
}
