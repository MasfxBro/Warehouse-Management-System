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
        $masterBarangs = MasterBarang::with('rackLocation')
            ->withSum('inboundDetails as inbound_qty', 'Qty')
            ->withSum('outboundDetails as outbound_qty', 'Qty')
            ->orderBy('Nama')
            ->get()
            ->map(function ($b) {
                $b->computed_stok = max(0, (int)($b->inbound_qty ?? 0) - (int)($b->outbound_qty ?? 0));
                return $b;
            });
        $rackLocations = RackLocation::withSum('inboundDetails as inbound_qty', 'Qty')
            ->withSum('outboundDetails as outbound_qty', 'Qty')
            ->orderBy('Kode_Rak')
            ->get()
            ->map(function ($r) {
                $terpakai        = max(0, (int)($r->inbound_qty ?? 0) - (int)($r->outbound_qty ?? 0));
                $r->sisa         = max(0, $r->Kapasitas - $terpakai);
                $r->terpakai     = $terpakai;
                return $r;
            })
            ->filter(fn ($r) => $r->sisa > 0)  // hanya rak yang masih ada ruang
            ->values();

        // Kategori unik yang sudah ada (untuk SKU Prefix Engine JS)
        $kategoriList  = MasterBarang::select('Kategori')
            ->distinct()
            ->orderBy('Kategori')
            ->pluck('Kategori')
            ->filter()
            ->values();

        // Pre-mapped arrays untuk JS — disiapkan di controller supaya
        // @json() di Blade tidak perlu arrow function (hindari ParseError)
        $masterBarangsJs = $masterBarangs->map(function ($b) {
            return [
                'sku'      => $b->SKU,
                'nama'     => $b->Nama,
                'kategori' => $b->Kategori,
                'rack_id'  => $b->Rack_ID,
                'min_stok' => $b->Min_Stok,
            ];
        })->values()->all();

        $rackLocationsJs = $rackLocations->map(function ($r) {
            return [
                'id'    => $r->Rack_ID,
                'label' => $r->Kode_Rak . ' (Aisle ' . $r->Aisle . ', Lvl ' . $r->Level . ') — Sisa: ' . $r->sisa . ' unit',
                'sisa'  => $r->sisa,
            ];
        })->values()->all();

        return view('inbound.create', compact(
            'suppliers',
            'masterBarangs',
            'rackLocations',
            'kategoriList',
            'masterBarangsJs',
            'rackLocationsJs'
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

            // Validasi kapasitas rak per item sebelum mulai transaksi
            foreach ($request->items as $i => $item) {
                $rackId = null;
                if (($item['jenis'] ?? '') === 'lama' && !empty($item['SKU_lama'])) {
                    $barang = MasterBarang::find($item['SKU_lama']);
                    // Barang lama: cek kapasitas rak yang dipilih di form
                    $rackId = $item['Rack_ID_lama'] ?? null;
                } elseif (($item['jenis'] ?? '') === 'baru' && !empty($item['Rack_ID_baru'])) {
                    $rackId = $item['Rack_ID_baru'];
                }

                if ($rackId) {
                    $rak = RackLocation::withSum('inboundDetails as in_qty', 'Qty')
                        ->withSum('outboundDetails as out_qty', 'Qty')
                        ->find($rackId);
                    if ($rak) {
                        $terpakai  = max(0, (int)($rak->in_qty ?? 0) - (int)($rak->out_qty ?? 0));
                        $sisa      = max(0, $rak->Kapasitas - $terpakai);
                        $qty       = (int)($item['Qty'] ?? 0);
                        if ($qty > $sisa) {
                            $label = ($item['jenis'] === 'lama')
                                ? ($item['SKU_lama'] ?? "Item #" . ($i + 1))
                                : ($item['Nama_baru'] ?? "Item #" . ($i + 1));
                            return back()->withInput()->with('error',
                                "Qty barang \"{$label}\" ({$qty} unit) melebihi sisa kapasitas rak {$rak->Kode_Rak} ({$sisa} unit tersisa)."
                            );
                        }
                    }
                }
            }

            // Cek duplikat nama barang baru dalam satu transaksi
            $namaBaruList = [];
            foreach ($request->items as $i => $item) {
                if (($item['jenis'] ?? '') === 'baru' && !empty($item['Nama_baru'])) {
                    $namaLower = strtolower(trim($item['Nama_baru']));
                    if (in_array($namaLower, $namaBaruList)) {
                        return back()->withInput()->with('error',
                            "Terdapat dua baris barang baru dengan nama yang sama: \"{$item['Nama_baru']}\". " .
                            "Jika ingin memasukkan barang yang sama ke rak berbeda, gunakan jenis Barang Lama setelah baris pertama disimpan."
                        );
                    }
                    $namaBaruList[] = $namaLower;

                    // Cek juga apakah nama sudah ada di master_barang (sudah pernah diinput sebelumnya)
                    $sudahAda = MasterBarang::whereRaw('LOWER("Nama") = ?', [$namaLower])->exists();
                    if ($sudahAda) {
                        return back()->withInput()->with('error',
                            "Barang \"{$item['Nama_baru']}\" sudah terdaftar di master data. Gunakan jenis Barang Lama untuk menambah stok barang yang sudah ada."
                        );
                    }
                }
            }

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
                    // Selalu pakai Rack_ID_lama dari form (user bebas pilih rak tujuan)
                    $rackId = $item['Rack_ID_lama'] ?? null;

                    if (!$rackId) {
                        DB::rollBack();
                        return back()->withInput()->with('error', "Barang \"{$barang?->Nama}\" belum dipilih rak tujuannya. Pilih rak terlebih dahulu.");
                    }

                    // Validasi kapasitas rak tujuan
                    $rakTujuan = RackLocation::withSum('inboundDetails as in_qty', 'Qty')
                        ->withSum('outboundDetails as out_qty', 'Qty')
                        ->find($rackId);
                    if ($rakTujuan) {
                        $terpakai = max(0, (int)($rakTujuan->in_qty ?? 0) - (int)($rakTujuan->out_qty ?? 0));
                        $sisa     = max(0, $rakTujuan->Kapasitas - $terpakai);
                        $qty      = (int)($item['Qty'] ?? 0);
                        if ($qty > $sisa) {
                            DB::rollBack();
                            return back()->withInput()->with('error',
                                "Qty barang \"{$barang?->Nama}\" ({$qty} unit) melebihi sisa kapasitas rak {$rakTujuan->Kode_Rak} ({$sisa} unit tersisa)."
                            );
                        }
                    }
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

            session()->save(); // Paksa tulis session sebelum redirect

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

    public function show(string $id)
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
