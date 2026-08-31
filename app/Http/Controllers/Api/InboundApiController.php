<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\InboundDetail;
use App\Models\InboundTransaction;
use App\Models\MasterBarang;
use App\Models\RackLocation;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * API Controller: Transaksi Inbound
 *
 * GET  /api/inbound         → Daftar transaksi (filter: supplier_id, per_page)
 * GET  /api/inbound/{id}    → Detail transaksi
 * POST /api/inbound         → Buat transaksi baru
 */
class InboundApiController extends Controller
{
    /**
     * Daftar semua transaksi inbound (paginated).
     * Query: ?supplier_id=1&per_page=15
     */
    public function index(Request $request): JsonResponse
    {
        $query = InboundTransaction::with(['supplier', 'inboundDetails'])
            ->orderBy('Tanggal', 'desc')
            ->orderBy('Inbound_ID', 'desc');

        if ($request->filled('supplier_id')) {
            $query->where('Supplier_ID', $request->supplier_id);
        }

        $perPage    = (int) $request->query('per_page', 15);
        $paginator  = $query->paginate($perPage);

        $items = collect($paginator->items())->map(fn($trx) => [
            'inbound_id'    => $trx->Inbound_ID,
            'no_receiving'  => $trx->No_Receiving,
            'tanggal'       => $trx->Tanggal->format('d/m/Y'),
            'tanggal_raw'   => $trx->Tanggal->format('Y-m-d'),
            'supplier'      => $trx->supplier->Nama ?? '-',
            'supplier_id'   => $trx->Supplier_ID,
            'total_item'    => $trx->inboundDetails->count(),
            'total_qty'     => $trx->inboundDetails->sum('Qty'),
            'catatan'       => $trx->Catatan,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    /**
     * Detail transaksi inbound + semua detail baris.
     */
    public function show(int $id): JsonResponse
    {
        $trx = InboundTransaction::with([
            'supplier', 'user',
            'inboundDetails.masterBarang',
            'inboundDetails.rackLocation',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'inbound_id'   => $trx->Inbound_ID,
                'no_receiving' => $trx->No_Receiving,
                'tanggal'      => $trx->Tanggal->format('d/m/Y'),
                'supplier'     => $trx->supplier ? [
                    'id'   => $trx->supplier->Supplier_ID,
                    'nama' => $trx->supplier->Nama,
                    'no_kontak' => $trx->supplier->No_Kontak,
                ] : null,
                'operator'     => $trx->user->name ?? '-',
                'catatan'      => $trx->Catatan,
                'details'      => $trx->inboundDetails->map(fn($d) => [
                    'detail_id'        => $d->Detail_ID,
                    'sku'              => $d->SKU,
                    'nama_barang'      => $d->masterBarang->Nama ?? '-',
                    'kategori'         => $d->masterBarang->Kategori ?? '-',
                    'qty'              => $d->Qty,
                    'rack'             => $d->rackLocation ? $d->rackLocation->Kode_Rak : '-',
                    'no_resi_supplier' => $d->No_Resi_Supplier,
                    'batch'            => $d->Batch,
                ]),
            ],
        ]);
    }

    /**
     * Buat transaksi inbound baru.
     *
     * Request JSON:
     * {
     *   "Tanggal": "2026-08-31",
     *   "Supplier_ID": 1,
     *   "Catatan": "...",
     *   "items": [
     *     {
     *       "jenis": "lama",
     *       "SKU_lama": "ELK-00001",
     *       "Qty": 10,
     *       "No_Resi_Supplier": "..."
     *     }
     *   ]
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'Tanggal'                  => ['required', 'date'],
            'Supplier_ID'              => ['required', 'exists:suppliers,Supplier_ID'],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.jenis'            => ['required', 'in:lama,baru'],
            'items.*.Qty'              => ['required', 'integer', 'min:1'],
            'items.*.SKU_lama'         => ['nullable', 'string'],
            'items.*.Nama_baru'        => ['nullable', 'string', 'max:255'],
            'items.*.Kategori_baru'    => ['nullable', 'string', 'max:100'],
            'items.*.Rack_ID_baru'     => ['nullable', 'exists:rack_locations,Rack_ID'],
            'items.*.Min_Stok_baru'    => ['nullable', 'integer', 'min:0'],
            'items.*.No_Resi_Supplier' => ['nullable', 'string', 'max:100'],
        ]);

        DB::beginTransaction();

        try {
            $today       = now()->format('Ymd');
            $count       = InboundTransaction::whereDate('Tanggal', today())->count();
            $noReceiving = 'RSI-' . $today . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

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
                    $sku    = $item['SKU_lama'];
                    $barang = MasterBarang::find($sku);
                    $rackId = $barang?->Rack_ID;
                } else {
                    $kategori = $item['Kategori_baru'] ?? 'XXX';
                    $konsonan = preg_replace('/[aeiou\s]/i', '', $kategori);
                    $prefix   = strtoupper(str_pad(substr($konsonan, 0, 3), 3, 'X'));
                    $count2   = MasterBarang::where('SKU', 'LIKE', $prefix . '-%')->count();
                    $sku      = $prefix . '-' . str_pad($count2 + 1, 5, '0', STR_PAD_LEFT);
                    $rackId   = $item['Rack_ID_baru'] ?? null;

                    MasterBarang::create([
                        'SKU'      => $sku,
                        'Nama'     => trim($item['Nama_baru']),
                        'Kategori' => trim($kategori),
                        'Rack_ID'  => $rackId,
                        'Min_Stok' => (int) ($item['Min_Stok_baru'] ?? 0),
                    ]);
                }

                $noResi = !empty($item['No_Resi_Supplier']) ? trim($item['No_Resi_Supplier']) : null;

                InboundDetail::create([
                    'Inbound_ID'       => $inbound->Inbound_ID,
                    'SKU'              => $sku,
                    'Rack_ID'          => $rackId,
                    'Qty'              => (int) $item['Qty'],
                    'No_Resi_Supplier' => $noResi,
                ]);
            }

            DB::commit();

            ActivityLog::record("Inbound [{$noReceiving}] dibuat via Flutter oleh [{$this->operatorLabel()}].");

            return response()->json([
                'success'      => true,
                'message'      => "Inbound {$noReceiving} berhasil disimpan.",
                'inbound_id'   => $inbound->Inbound_ID,
                'no_receiving' => $noReceiving,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function operatorLabel(): string
    {
        $user = Auth::user();
        if (!$user) return 'Flutter/API';
        return $user->isAdmin() ? 'Guru: ' . $user->name : 'Siswa: ' . $user->name;
    }
}
