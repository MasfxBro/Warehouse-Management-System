<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\MasterBarang;
use App\Models\OutboundDetail;
use App\Models\OutboundTransaction;
use App\Models\PracticeSession;
use App\Services\DocumentNumberService;
use App\Services\StockAllocationService;
use App\Support\WarehouseCache;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OutboundController extends Controller
{
    // =========================================================
    // INDEX — Daftar Outbound (2 tabel: Picking Queue & Riwayat)
    // =========================================================

    public function index(Request $request)
    {
        $query = OutboundTransaction::with(['customer', 'outboundDetails', 'allOutboundDetails'])
            ->orderBy('Tanggal', 'desc')
            ->orderBy('Outbound_ID', 'desc');

        // Filter Customer
        if ($request->filled('customer_id')) {
            $query->where('Customer_ID', $request->customer_id);
        }

        // Tabel 1 — Picking Task Queue (belum complete) — paginate 15
        $pickingQueue = (clone $query)->where('transaction_status', 'active')->where('picking_status', 'not_complete')->paginate(15, ['*'], 'queue_page')->withQueryString();

        // Tabel 2 — Riwayat Outbound (sudah complete) — paginate 15
        $riwayat = (clone $query)->where(function ($history) {
            $history->where('picking_status', 'complete')->orWhere('transaction_status', 'cancelled');
        })->paginate(15)->withQueryString();

        $customers = Customer::orderBy('Nama')->get();

        return view('outbound.index', compact('pickingQueue', 'riwayat', 'customers'));
    }

    // =========================================================
    // CREATE — Form Tambah Outbound
    // =========================================================

    public function create()
    {
        $customers = Customer::orderBy('Nama')->get();

        // Hanya tampilkan barang yang stok > 0
        // Eager load sum untuk menghindari N+1 query (60+ queries jadi 3)
        $barangs = MasterBarang::with('rackLocation')
            ->withSum('inboundDetails as inbound_qty', 'Qty')
            ->withSum('outboundDetails as outbound_qty', 'Qty')
            ->get()
            ->map(function ($b) {
                // Hitung stok dari sum yang sudah di-eager load
                $b->computed_stok = max(0, (int) ($b->inbound_qty ?? 0) - (int) ($b->outbound_qty ?? 0));

                return $b;
            })
            ->filter(fn ($b) => $b->computed_stok > 0)
            ->values();

        // Pre-mapped array untuk JS — hindari arrow function PHP di @json() Blade (ParseError)
        $barangsJs = $barangs->map(function ($b) {
            return [
                'sku' => $b->SKU,
                'nama' => $b->Nama,
                'stok' => $b->computed_stok,
                'satuan' => $b->Satuan,
                'rack_id' => $b->Rack_ID,
                'kode_rak' => $b->rackLocation ? $b->rackLocation->Kode_Rak : '-',
            ];
        })->values()->all();

        return view('outbound.create', compact('customers', 'barangs', 'barangsJs'));
    }

    // =========================================================
    // STORE — Simpan Transaksi Outbound
    // =========================================================

    public function store(Request $request, StockAllocationService $stockAllocation, DocumentNumberService $documentNumbers)
    {
        $request->validate([
            'Tanggal' => ['required', 'date', 'before_or_equal:today'],
            'Customer_ID' => ['required', 'exists:customers,Customer_ID'],
            'Nama_Penerima' => ['required', 'string', 'max:255'],
            'Catatan' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.SKU' => ['required', 'exists:master_barang,SKU'],
            'items.*.Qty' => ['required', 'integer', 'min:1'],
        ], [
            'Customer_ID.required' => 'Customer wajib dipilih.',
            'Nama_Penerima.required' => 'Nama penerima wajib diisi.',
            'items.required' => 'Minimal harus ada satu baris barang.',
        ]);

        // Gabungkan SKU yang sama agar beberapa baris tidak dapat melampaui stok.
        $requestedBySku = collect($request->items)
            ->groupBy('SKU')
            ->map(fn ($items) => (int) $items->sum('Qty'));

        $practiceSession = PracticeSession::current();
        if (! $practiceSession) {
            return back()->withInput()->with('error', 'Belum ada sesi praktikum aktif. Minta Guru/Admin membuka sesi terlebih dahulu.');
        }

        DB::beginTransaction();

        try {
            $noShipping = $documentNumbers->next('SJ', $request->Tanggal);

            // Kunci setiap SKU selama validasi dan alokasi agar dua request
            // bersamaan tidak mengambil saldo rak yang sama.
            $allocationBySku = [];
            foreach ($requestedBySku as $sku => $qty) {
                MasterBarang::whereKey($sku)->lockForUpdate()->firstOrFail();
                $allocationBySku[$sku] = $stockAllocation->allocate($sku, $qty);
            }

            // Hitung total qty untuk auto-priority
            $totalQty = $requestedBySku->sum();
            $priority = $this->calculatePriority($totalQty);

            $outbound = OutboundTransaction::create([
                'No_Shipping' => $noShipping,
                'Tanggal' => $request->Tanggal,
                'Customer_ID' => $request->Customer_ID,
                'User_ID' => Auth::id(),
                'picking_status' => 'not_complete',
                'priority' => $priority,
                'Nama_Penerima' => trim($request->Nama_Penerima),
                'Catatan' => $request->filled('Catatan') ? trim($request->Catatan) : null,
                'Practice_Session_ID' => $practiceSession->Practice_Session_ID,
            ]);

            foreach ($allocationBySku as $sku => $allocations) {
                foreach ($allocations as $allocation) {
                    OutboundDetail::create([
                        'Outbound_ID' => $outbound->Outbound_ID,
                        'SKU' => $sku,
                        'Rack_ID' => $allocation['rack_id'],
                        'Qty' => $allocation['qty'],
                    ]);
                }
            }

            DB::commit();

            WarehouseCache::clearDashboard();
            ActivityLog::record("Transaksi Outbound baru dibuat dengan No. [{$noShipping}].");

            session()->save();

            return redirect()->route('outbound.show', $outbound->Outbound_ID)
                ->with('success', "Outbound {$noShipping} berhasil dibuat. Selesaikan Picking List untuk mencetak Surat Jalan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withInput()->with('error', 'Transaksi outbound gagal disimpan. Silakan coba kembali atau hubungi administrator.');
        }
    }

    // =========================================================
    // SHOW — Detail Outbound
    // =========================================================

    public function show(string $id)
    {
        $outbound = OutboundTransaction::with([
            'customer',
            'user',
            'outboundDetails.masterBarang',
            'outboundDetails.rackLocation',
            'allOutboundDetails.masterBarang',
            'allOutboundDetails.rackLocation',
            'cancelledBy',
            'practiceSession',
        ])->findOrFail($id);

        return view('outbound.show', compact('outbound'));
    }

    // =========================================================
    // SHOW PICKING LIST — Detail Picking
    // =========================================================

    public function showPickingList(string $id)
    {
        $outbound = OutboundTransaction::with([
            'customer',
            'user',
            'outboundDetails.masterBarang',
            'outboundDetails.rackLocation',
        ])->findOrFail($id);

        return view('outbound.picking-list', compact('outbound'));
    }

    // =========================================================
    // COMPLETE PICKING — Mark Picking List as Complete
    // =========================================================

    public function completePicking(string $id)
    {
        $result = DB::transaction(function () use ($id): array {
            $outbound = OutboundTransaction::lockForUpdate()->findOrFail($id);
            if ($outbound->isCancelled()) {
                return ['status' => 'cancelled', 'outbound' => $outbound];
            }
            if ($outbound->isComplete()) {
                return ['status' => 'complete', 'outbound' => $outbound];
            }

            $outbound->update(['picking_status' => 'complete']);

            return ['status' => 'updated', 'outbound' => $outbound];
        });
        /** @var OutboundTransaction $outbound */
        $outbound = $result['outbound'];

        if ($result['status'] === 'cancelled') {
            return back()->with('error', 'Picking tidak dapat diselesaikan karena transaksi sudah dibatalkan.');
        }
        if ($result['status'] === 'complete') {
            return back()->with('info', 'Picking List ini sudah selesai sebelumnya.');
        }

        WarehouseCache::clearDashboard();
        ActivityLog::record("Picking List untuk Outbound [{$outbound->No_Shipping}] ditandai selesai.");

        return redirect()->route('outbound.show', $outbound->Outbound_ID)
            ->with('success', "Picking List {$outbound->No_Shipping} selesai. Surat Jalan siap diunduh.");
    }

    // =========================================================
    // DOWNLOAD SURAT JALAN PDF — Gatekeeping: harus complete
    // =========================================================

    public function downloadSuratJalan(string $id)
    {
        $outbound = OutboundTransaction::with([
            'customer',
            'outboundDetails.masterBarang',
            'outboundDetails.rackLocation',
        ])->findOrFail($id);

        // Gatekeeping ketat sesuai arahan
        if (! $outbound->isComplete()) {
            abort(403, 'Surat Jalan hanya dapat dicetak setelah Picking List selesai.');
        }

        $pdf = Pdf::loadView('outbound.surat-jalan-pdf', compact('outbound'))
            ->setPaper('a4', 'portrait')
            ->setOption('margin_top', 0)
            ->setOption('margin_bottom', 0)
            ->setOption('margin_left', 0)
            ->setOption('margin_right', 0)
            ->setOption('isRemoteEnabled', false);

        $filename = 'Surat_Jalan_'.$outbound->No_Shipping.'.pdf';

        return $pdf->download($filename);
    }

    public function cancel(Request $request, string $id)
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'reason.required' => 'Alasan pembatalan wajib diisi.',
            'reason.min' => 'Alasan pembatalan minimal 10 karakter.',
        ]);

        DB::beginTransaction();

        try {
            $outbound = OutboundTransaction::with('outboundDetails')->lockForUpdate()->findOrFail($id);
            if ($outbound->isCancelled()) {
                DB::rollBack();

                return back()->with('info', 'Transaksi outbound ini sudah dibatalkan sebelumnya.');
            }

            $outbound->outboundDetails->each->delete();
            $outbound->update([
                'transaction_status' => 'cancelled',
                'Cancelled_At' => now(),
                'Cancelled_By' => Auth::id(),
                'Cancellation_Reason' => trim($request->reason),
            ]);
            DB::commit();

            WarehouseCache::clearDashboard();
            ActivityLog::record("Transaksi Outbound [{$outbound->No_Shipping}] dibatalkan. Alasan: ".trim($request->reason));

            return back()->with('success', "Outbound {$outbound->No_Shipping} berhasil dibatalkan dan reservasi stok dilepas.");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->with('error', 'Pembatalan outbound gagal diproses.');
        }
    }

    // =========================================================
    // STORE CUSTOMER AJAX — Tambah Customer via Modal
    // =========================================================

    public function storeCustomerAjax(Request $request)
    {
        $request->validate([
            'Nama' => ['required', 'string', 'max:255'],
            'No_Kontak' => ['nullable', 'string', 'max:20'],
            'Email' => ['nullable', 'email', 'max:255'],
            'Alamat' => ['nullable', 'string', 'max:500'],
        ]);

        $customer = Customer::create([
            'Nama' => $request->Nama,
            'Kontak' => $request->No_Kontak,
            'No_Kontak' => $request->No_Kontak,
            'Email' => $request->Email,
            'Alamat' => $request->Alamat,
        ]);

        ActivityLog::record("Customer baru [{$customer->Nama}] ditambahkan melalui transaksi Outbound.");

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->Customer_ID,
                'nama' => $customer->Nama,
            ],
        ]);
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Auto-priority berdasarkan total qty.
     */
    private function calculatePriority(int $totalQty): string
    {
        if ($totalQty > 50) {
            return 'high';
        }
        if ($totalQty > 10) {
            return 'normal';
        }

        return 'decent';
    }
}
