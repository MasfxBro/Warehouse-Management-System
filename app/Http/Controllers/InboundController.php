<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\InboundDetail;
use App\Models\InboundTransaction;
use App\Models\MasterBarang;
use App\Models\RackLocation;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InboundController extends Controller
{
    // =========================================================
    // INDEX — Daftar Transaksi Inbound
    // =========================================================

    public function index(Request $request)
    {
        $query = InboundTransaction::with(['supplier', 'inboundDetails'])
            ->orderBy('Tanggal', 'desc')
            ->orderBy('Inbound_ID', 'desc');

        // Filter by Supplier
        if ($request->filled('supplier_id')) {
            $query->where('Supplier_ID', $request->supplier_id);
        }

        $transactions = $query->paginate(15)->withQueryString();
        $suppliers     = Supplier::orderBy('Nama')->get();

        return view('inbound.index', compact('transactions', 'suppliers'));
    }

    // =========================================================
    // CREATE — Form Tambah Inbound
    // =========================================================

    public function create()
    {
        $suppliers     = Supplier::orderBy('Nama')->get();
        $masterBarangs = MasterBarang::orderBy('Nama')->get();
        $rackLocations = RackLocation::orderBy('Kode_Rak')->get();

        // Kategori unik yang sudah ada (untuk SKU Prefix Engine JS)
        $kategoriList  = MasterBarang::select('Kategori')
            ->distinct()
            ->orderBy('Kategori')
            ->pluck('Kategori')
            ->filter()
            ->values();

        return view('inbound.create', compact(
            'suppliers',
            'masterBarangs',
            'rackLocations',
            'kategoriList'
        ));
    }

    // =========================================================
    // STORE — Simpan Transaksi Inbound
    // =========================================================

    public function store(Request $request)
    {
        $request->validate([
            'Tanggal'                    => ['required', 'date'],
            'Supplier_ID'                => ['required', 'exists:suppliers,Supplier_ID'],
            'items'                      => ['required', 'array', 'min:1'],
            'items.*.jenis'              => ['required', 'in:lama,baru'],
            'items.*.Qty'                => ['required', 'integer', 'min:1'],
            // Barang lama
            'items.*.SKU_lama'           => ['nullable', 'string'],
            // Barang baru
            'items.*.Nama_baru'          => ['nullable', 'string', 'max:255'],
            'items.*.Kategori_baru'      => ['nullable', 'string', 'max:100'],
            'items.*.Rack_ID_baru'       => ['nullable', 'exists:rack_locations,Rack_ID'],
            'items.*.Min_Stok_baru'      => ['nullable', 'integer', 'min:0'],
            // Resi
            'items.*.No_Resi_Supplier'   => ['nullable', 'string', 'max:100'],
            'items.*.tanpa_resi'         => ['nullable'],
        ], [
            'Supplier_ID.required' => 'Supplier wajib dipilih.',
            'items.required'       => 'Minimal harus ada satu baris barang.',
            'items.*.Qty.min'      => 'Qty minimal 1.',
        ]);

        DB::beginTransaction();

        try {
            $noReceiving = $this->generateNoReceiving();

            // Buat header transaksi
            $inbound = InboundTransaction::create([
                'No_Receiving' => $noReceiving,
                'Tanggal'      => $request->Tanggal,
                'Supplier_ID'  => $request->Supplier_ID,
                'User_ID'      => Auth::id(),
                'Catatan'      => $request->filled('Catatan') ? trim($request->Catatan) : null,
            ]);

            foreach ($request->items as $item) {
                $sku    = null;
                $rackId = null;

                if ($item['jenis'] === 'lama') {
                    // ---- Barang Lama ----
                    $sku    = $item['SKU_lama'] ?? null;
                    $barang = MasterBarang::find($sku);
                    $rackId = $barang?->Rack_ID;
                } else {
                    // ---- Barang Baru ----
                    $prefix = $this->generateSkuPrefix($item['Kategori_baru'] ?? 'XXX');
                    $sku    = $this->generateSku($prefix);
                    $rackId = $item['Rack_ID_baru'] ?? null;

                    MasterBarang::create([
                        'SKU'      => $sku,
                        'Nama'     => trim($item['Nama_baru']),
                        'Kategori' => trim($item['Kategori_baru'] ?? ''),
                        'Rack_ID'  => $rackId,
                        'Min_Stok' => (int) ($item['Min_Stok_baru'] ?? 0),
                    ]);
                }

                // Tentukan No_Resi_Supplier
                $noResi = null;
                if (empty($item['tanpa_resi'])) {
                    $noResi = $item['No_Resi_Supplier'] ?? null;
                    $noResi = ($noResi && trim($noResi) !== '') ? trim($noResi) : null;
                }

                InboundDetail::create([
                    'Inbound_ID'       => $inbound->Inbound_ID,
                    'SKU'              => $sku,
                    'Rack_ID'          => $rackId,
                    'Qty'              => (int) $item['Qty'],
                    'No_Resi_Supplier' => $noResi,
                ]);
            }

            DB::commit();

            ActivityLog::record("Transaksi Inbound baru dibuat dengan No. Resi [{$noReceiving}] oleh [{$this->operatorLabel()}].");

            return redirect()->route('inbound.index')
                ->with('success', "Transaksi Inbound {$noReceiving} berhasil disimpan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    // =========================================================
    // SHOW — Detail Inbound
    // =========================================================

    public function show(int $id)
    {
        $inbound = InboundTransaction::with([
            'supplier',
            'user',
            'inboundDetails.masterBarang',
            'inboundDetails.rackLocation',
        ])->findOrFail($id);

        return view('inbound.show', compact('inbound'));
    }

    // =========================================================
    // STORE SUPPLIER AJAX — Tambah Supplier via Modal
    // =========================================================

    public function storeSupplierAjax(Request $request)
    {
        $request->validate([
            'Nama'      => ['required', 'string', 'max:255'],
            'No_Kontak' => ['nullable', 'string', 'max:20'],
            'Email'     => ['nullable', 'email', 'max:255'],
            'Alamat'    => ['nullable', 'string', 'max:500'],
        ]);

        $supplier = Supplier::create([
            'Nama'      => $request->Nama,
            'Kontak'    => $request->No_Kontak,
            'No_Kontak' => $request->No_Kontak,
            'Email'     => $request->Email,
            'Alamat'    => $request->Alamat,
        ]);

        ActivityLog::record("Supplier baru [{$supplier->Nama}] ditambahkan via modal Inbound oleh [{$this->operatorLabel()}].");

        return response()->json([
            'success'  => true,
            'supplier' => [
                'id'   => $supplier->Supplier_ID,
                'nama' => $supplier->Nama,
            ],
        ]);
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Generate No. Receiving format: RSI-YYYYMMDD-XXXX
     */
    private function generateNoReceiving(): string
    {
        $today = now()->format('Ymd');
        $count = InboundTransaction::whereDate('Tanggal', today())->count();
        return 'RSI-' . $today . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Ambil 3 konsonan pertama dari nama kategori.
     */
    private function generateSkuPrefix(string $kategori): string
    {
        $konsonan = preg_replace('/[aeiou\s]/i', '', $kategori);
        $prefix   = strtoupper(substr($konsonan, 0, 3));
        return str_pad($prefix, 3, 'X');
    }

    /**
     * Generate SKU unik: PREFIX-00001
     */
    private function generateSku(string $prefix): string
    {
        $count = MasterBarang::where('SKU', 'LIKE', $prefix . '-%')->count();
        return $prefix . '-' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
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
