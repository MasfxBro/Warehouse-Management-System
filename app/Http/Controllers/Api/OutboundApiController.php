<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\MasterBarang;
use App\Models\OutboundDetail;
use App\Models\OutboundTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * API Controller: Transaksi Outbound
 *
 * GET  /api/outbound                         → Daftar (filter: customer_id, status)
 * GET  /api/outbound/{id}                    → Detail
 * POST /api/outbound                         → Buat baru
 * POST /api/outbound/{id}/picking-complete   → Mark picking selesai
 */
class OutboundApiController extends Controller
{
    /**
     * Daftar transaksi outbound.
     * Query: ?customer_id=1&status=not_complete|complete&per_page=15
     */
    public function index(Request $request): JsonResponse
    {
        $query = OutboundTransaction::with(['customer', 'outboundDetails'])
            ->orderBy('Tanggal', 'desc')
            ->orderBy('Outbound_ID', 'desc');

        if ($request->filled('customer_id')) {
            $query->where('Customer_ID', $request->customer_id);
        }

        if ($request->filled('status')) {
            $query->where('picking_status', $request->status);
        }

        $perPage   = (int) $request->query('per_page', 15);
        $paginator = $query->paginate($perPage);

        $items = collect($paginator->items())->map(fn($trx) => [
            'outbound_id'    => $trx->Outbound_ID,
            'no_shipping'    => $trx->No_Shipping,
            'tanggal'        => $trx->Tanggal->format('d/m/Y'),
            'tanggal_raw'    => $trx->Tanggal->format('Y-m-d'),
            'customer'       => $trx->customer->Nama ?? '-',
            'customer_id'    => $trx->Customer_ID,
            'nama_penerima'  => $trx->Nama_Penerima,
            'picking_status' => $trx->picking_status,
            'is_complete'    => $trx->isComplete(),
            'priority'       => $trx->priority,
            'priority_label' => $trx->priorityLabel(),
            'total_item'     => $trx->outboundDetails->count(),
            'total_qty'      => $trx->outboundDetails->sum('Qty'),
            'catatan'        => $trx->Catatan,
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
     * Detail transaksi outbound + detail baris.
     */
    public function show(int $id): JsonResponse
    {
        $trx = OutboundTransaction::with([
            'customer', 'user',
            'outboundDetails.masterBarang',
            'outboundDetails.rackLocation',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'outbound_id'    => $trx->Outbound_ID,
                'no_shipping'    => $trx->No_Shipping,
                'tanggal'        => $trx->Tanggal->format('d/m/Y'),
                'customer'       => $trx->customer ? [
                    'id'        => $trx->customer->Customer_ID,
                    'nama'      => $trx->customer->Nama,
                    'no_kontak' => $trx->customer->No_Kontak,
                ] : null,
                'nama_penerima'  => $trx->Nama_Penerima,
                'operator'       => $trx->user->name ?? '-',
                'picking_status' => $trx->picking_status,
                'is_complete'    => $trx->isComplete(),
                'priority'       => $trx->priority,
                'priority_label' => $trx->priorityLabel(),
                'catatan'        => $trx->Catatan,
                'details'        => $trx->outboundDetails->map(fn($d) => [
                    'detail_id'   => $d->Detail_ID,
                    'sku'         => $d->SKU,
                    'nama_barang' => $d->masterBarang->Nama ?? '-',
                    'kategori'    => $d->masterBarang->Kategori ?? '-',
                    'qty'         => $d->Qty,
                    'rack'        => $d->rackLocation ? $d->rackLocation->Kode_Rak : '-',
                ]),
            ],
        ]);
    }

    /**
     * Buat transaksi outbound baru.
     *
     * Request JSON:
     * {
     *   "Tanggal": "2026-08-31",
     *   "Customer_ID": 1,
     *   "Nama_Penerima": "Budi",
     *   "Catatan": "...",
     *   "items": [
     *     { "SKU": "ELK-00001", "Qty": 5 }
     *   ]
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'Tanggal'         => ['required', 'date'],
            'Customer_ID'     => ['required', 'exists:customers,Customer_ID'],
            'Nama_Penerima'   => ['required', 'string', 'max:255'],
            'items'           => ['required', 'array', 'min:1'],
            'items.*.SKU'     => ['required', 'exists:master_barang,SKU'],
            'items.*.Qty'     => ['required', 'integer', 'min:1'],
        ]);

        // Validasi stok
        foreach ($request->items as $item) {
            $barang = MasterBarang::find($item['SKU']);
            if (!$barang || $barang->stok < $item['Qty']) {
                $available = $barang ? $barang->stok : 0;
                return response()->json([
                    'success' => false,
                    'message' => "Stok tidak cukup untuk SKU {$item['SKU']}. Tersedia: {$available}, diminta: {$item['Qty']}.",
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            $today      = now()->format('Ymd');
            $count      = OutboundTransaction::whereDate('Tanggal', today())->count();
            $noShipping = 'SJ-' . $today . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

            $totalQty = collect($request->items)->sum('Qty');
            $priority = $totalQty > 50 ? 'high' : ($totalQty > 10 ? 'normal' : 'decent');

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

            ActivityLog::record("Outbound [{$noShipping}] dibuat via Flutter oleh [{$this->operatorLabel()}].");

            return response()->json([
                'success'     => true,
                'message'     => "Outbound {$noShipping} berhasil dibuat.",
                'outbound_id' => $outbound->Outbound_ID,
                'no_shipping' => $noShipping,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark picking list sebagai complete.
     */
    public function completePicking(int $id): JsonResponse
    {
        $outbound = OutboundTransaction::findOrFail($id);

        if ($outbound->isComplete()) {
            return response()->json(['success' => false, 'message' => 'Picking sudah selesai sebelumnya.'], 422);
        }

        $outbound->update(['picking_status' => 'complete']);

        ActivityLog::record("Picking [{$outbound->No_Shipping}] selesai via Flutter oleh [{$this->operatorLabel()}].");

        return response()->json([
            'success' => true,
            'message' => "Picking {$outbound->No_Shipping} berhasil diselesaikan.",
        ]);
    }

    private function operatorLabel(): string
    {
        $user = Auth::user();
        if (!$user) return 'Flutter/API';
        return $user->isAdmin() ? 'Guru: ' . $user->name : 'Siswa: ' . $user->name;
    }
}
