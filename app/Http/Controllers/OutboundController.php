<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\MasterBarang;
use App\Models\OutboundDetail;
use App\Models\OutboundTransaction;
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
        $query = OutboundTransaction::with(['customer', 'outboundDetails'])
            ->orderBy('Tanggal', 'desc')
            ->orderBy('Outbound_ID', 'desc');

        // Filter Customer
        if ($request->filled('customer_id')) {
            $query->where('Customer_ID', $request->customer_id);
        }

        // Tabel 1 — Picking Task Queue (belum complete)
        $pickingQueue = (clone $query)->where('picking_status', 'not_complete')->get();

        // Tabel 2 — Riwayat Outbound (sudah complete)
        $riwayat = (clone $query)->where('picking_status', 'complete')->paginate(15)->withQueryString();

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
        $barangs = MasterBarang::with('rackLocation')
            ->get()
            ->filter(fn ($b) => $b->stok > 0)
            ->values();

        return view('outbound.create', compact('customers', 'barangs'));
    }

    // =========================================================
    // STORE — Simpan Transaksi Outbound
    // =========================================================

    public function store(Request $request)
    {
        $request->validate([
            'Tanggal'                  => ['required', 'date'],
            'Customer_ID'              => ['required', 'exists:customers,Customer_ID'],
            'Nama_Penerima'            => ['required', 'string', 'max:255'],
            'Catatan'                  => ['nullable', 'string'],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.SKU'              => ['required', 'exists:master_barang,SKU'],
            'items.*.Qty'              => ['required', 'integer', 'min:1'],
        ], [
            'Customer_ID.required' => 'Customer wajib dipilih.',
            'Nama_Penerima.required' => 'Nama penerima wajib diisi.',
            'items.required'       => 'Minimal harus ada satu baris barang.',
        ]);

        // Validasi stok sebelum menyimpan
        foreach ($request->items as $i => $item) {
            $barang = MasterBarang::find($item['SKU']);
            if (!$barang || $barang->stok < $item['Qty']) {
                $available = $barang ? $barang->stok : 0;
                return back()->withInput()->with('error',
                    "Stok tidak mencukupi untuk SKU {$item['SKU']}. Tersedia: {$available}, diminta: {$item['Qty']}."
                );
            }
        }

        DB::beginTransaction();

        try {
            $noShipping = $this->generateNoShipping();

            // Hitung total qty untuk auto-priority
            $totalQty = collect($request->items)->sum('Qty');
            $priority = $this->calculatePriority($totalQty);

            $outbound = OutboundTransaction::create([
                'No_Shipping'    => $noShipping,
                'Tanggal'        => $request->Tanggal,
                'Customer_ID'    => $request->Customer_ID,
                'User_ID'        => Auth::id(),
                'picking_status' => 'not_complete',
                'priority'       => $priority,
                'Nama_Penerima'  => trim($request->Nama_Penerima),
                'Catatan'        => $request->filled('Catatan') ? trim($request->Catatan) : null,
            ]);

            foreach ($request->items as $item) {
                $barang = MasterBarang::find($item['SKU']);
                OutboundDetail::create([
                    'Outbound_ID' => $outbound->Outbound_ID,
                    'SKU'         => $item['SKU'],
                    'Rack_ID'     => $barang?->Rack_ID,
                    'Qty'         => (int) $item['Qty'],
                ]);
            }

            DB::commit();

            ActivityLog::record("Transaksi Outbound baru dibuat dengan No. [{$noShipping}] oleh [{$this->operatorLabel()}].");

            return redirect()->route('outbound.show', $outbound->Outbound_ID)
                ->with('success', "Outbound {$noShipping} berhasil dibuat. Selesaikan Picking List untuk mencetak Surat Jalan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    // =========================================================
    // SHOW — Detail Outbound
    // =========================================================

    public function show(int $id)
    {
        $outbound = OutboundTransaction::with([
            'customer',
            'user',
            'outboundDetails.masterBarang',
            'outboundDetails.rackLocation',
        ])->findOrFail($id);

        return view('outbound.show', compact('outbound'));
    }

    // =========================================================
    // SHOW PICKING LIST — Detail Picking
    // =========================================================

    public function showPickingList(int $id)
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

    public function completePicking(int $id)
    {
        $outbound = OutboundTransaction::findOrFail($id);

        if ($outbound->isComplete()) {
            return back()->with('info', 'Picking List ini sudah selesai sebelumnya.');
        }

        $outbound->update(['picking_status' => 'complete']);

        ActivityLog::record("Picking List untuk Outbound [{$outbound->No_Shipping}] di-mark Complete oleh [{$this->operatorLabel()}].");

        return redirect()->route('outbound.show', $outbound->Outbound_ID)
            ->with('success', "Picking List {$outbound->No_Shipping} selesai. Surat Jalan siap diunduh.");
    }

    // =========================================================
    // DOWNLOAD SURAT JALAN PDF — Gatekeeping: harus complete
    // =========================================================

    public function downloadSuratJalan(int $id)
    {
        $outbound = OutboundTransaction::with([
            'customer',
            'outboundDetails.masterBarang',
            'outboundDetails.rackLocation',
        ])->findOrFail($id);

        // Gatekeeping ketat sesuai arahan
        if (!$outbound->isComplete()) {
            abort(403, 'Surat Jalan hanya dapat dicetak setelah Picking List selesai.');
        }

        $pdf = Pdf::loadView('outbound.surat-jalan-pdf', compact('outbound'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'Surat_Jalan_' . $outbound->No_Shipping . '.pdf';
        return $pdf->download($filename);
    }

    // =========================================================
    // STORE CUSTOMER AJAX — Tambah Customer via Modal
    // =========================================================

    public function storeCustomerAjax(Request $request)
    {
        $request->validate([
            'Nama'      => ['required', 'string', 'max:255'],
            'No_Kontak' => ['nullable', 'string', 'max:20'],
            'Email'     => ['nullable', 'email', 'max:255'],
            'Alamat'    => ['nullable', 'string', 'max:500'],
        ]);

        $customer = Customer::create([
            'Nama'      => $request->Nama,
            'Kontak'    => $request->No_Kontak,
            'No_Kontak' => $request->No_Kontak,
            'Email'     => $request->Email,
            'Alamat'    => $request->Alamat,
        ]);

        ActivityLog::record("Customer baru [{$customer->Nama}] ditambahkan via modal Outbound oleh [{$this->operatorLabel()}].");

        return response()->json([
            'success'  => true,
            'customer' => [
                'id'   => $customer->Customer_ID,
                'nama' => $customer->Nama,
            ],
        ]);
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Generate No. Shipping format: SJ-YYYYMMDD-XXXX
     */
    private function generateNoShipping(): string
    {
        $today = now()->format('Ymd');
        $count = OutboundTransaction::whereDate('Tanggal', today())->count();
        return 'SJ-' . $today . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Auto-priority berdasarkan total qty.
     */
    private function calculatePriority(int $totalQty): string
    {
        if ($totalQty > 50)  return 'high';
        if ($totalQty > 10)  return 'normal';
        return 'decent';
    }

    /**
     * Label operator untuk Activity Log.
     */
    private function operatorLabel(): string
    {
        $user = Auth::user();
        if (!$user) return 'Sistem';
        if ($user->isAdmin()) return 'Guru: ' . $user->name;

        $identity = session('student_identity');
        if ($identity && !empty($identity['name'])) {
            return "Operator: {$identity['name']} | {$identity['class']}";
        }
        return 'Siswa: ' . $user->name;
    }
}
