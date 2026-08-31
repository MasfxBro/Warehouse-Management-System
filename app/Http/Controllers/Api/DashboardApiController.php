<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InboundTransaction;
use App\Models\MasterBarang;
use App\Models\OutboundTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * API Controller: Dashboard
 *
 * GET /api/dashboard → Stat cards + chart + picking queue + low stock
 */
class DashboardApiController extends Controller
{
    public function index(): JsonResponse
    {
        // Stat Cards (cache 2 menit)
        $stats = Cache::remember('api_dashboard_stats', 120, function () {
            $items = MasterBarang::with(['inboundDetails', 'outboundDetails'])->get();

            return [
                'total_sku'    => $items->count(),
                'total_stok'   => $items->sum(fn($item) => $item->stok),
                'nilai_gudang' => $items->sum(fn($item) => $item->nilai_barang),
                'low_stock_count' => $items->filter(fn($item) => $item->stok <= $item->Min_Stok)->count(),
                'low_stock_items' => $items->filter(fn($item) => $item->stok <= $item->Min_Stok)
                    ->values()
                    ->map(fn($item) => [
                        'sku'      => $item->SKU,
                        'nama'     => $item->Nama,
                        'kategori' => $item->Kategori,
                        'stok'     => $item->stok,
                        'min_stok' => $item->Min_Stok,
                        'status'   => $item->stok == 0 ? 'Habis' : 'Reorder',
                    ]),
            ];
        });

        $today = now()->toDateString();

        $inboundToday  = Cache::remember('api_inbound_today_' . $today, 120,
            fn() => InboundTransaction::whereDate('Tanggal', $today)->count()
        );
        $outboundToday = Cache::remember('api_outbound_today_' . $today, 120,
            fn() => OutboundTransaction::whereDate('Tanggal', $today)->count()
        );

        // Picking Queue (6 teratas)
        $pickingQueue = Cache::remember('api_picking_queue', 60, function () {
            return OutboundTransaction::with('customer')
                ->where('picking_status', 'not_complete')
                ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'normal' THEN 2 ELSE 3 END")
                ->limit(6)
                ->get()
                ->map(fn($trx) => [
                    'outbound_id'    => $trx->Outbound_ID,
                    'no_shipping'    => $trx->No_Shipping,
                    'customer_nama'  => $trx->customer->Nama ?? '-',
                    'priority'       => $trx->priority,
                    'priority_label' => $trx->priorityLabel(),
                    'tanggal'        => $trx->Tanggal->format('d/m/Y'),
                ]);
        });

        $pendingCount = Cache::remember('api_picking_count', 60,
            fn() => OutboundTransaction::where('picking_status', 'not_complete')->count()
        );

        // Chart Data (7 hari terakhir)
        $chartData = Cache::remember('api_chart_7days', 300, function () {
            $labels = [];
            $inboundData  = [];
            $outboundData = [];

            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();
                $labels[]       = now()->subDays($i)->format('d/m');
                $inboundData[]  = (int) DB::table('inbound_details')
                    ->join('inbound_transactions', 'inbound_details.Inbound_ID', '=', 'inbound_transactions.Inbound_ID')
                    ->whereDate('inbound_transactions.Tanggal', $date)
                    ->sum('inbound_details.Qty');
                $outboundData[] = (int) DB::table('outbound_details')
                    ->join('outbound_transactions', 'outbound_details.Outbound_ID', '=', 'outbound_transactions.Outbound_ID')
                    ->whereDate('outbound_transactions.Tanggal', $date)
                    ->sum('outbound_details.Qty');
            }

            return ['labels' => $labels, 'inbound' => $inboundData, 'outbound' => $outboundData];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'stats' => [
                    'total_sku'        => $stats['total_sku'],
                    'total_stok'       => $stats['total_stok'],
                    'nilai_gudang'     => $stats['nilai_gudang'],
                    'low_stock_count'  => $stats['low_stock_count'],
                    'inbound_today'    => $inboundToday,
                    'outbound_today'   => $outboundToday,
                    'pending_picking'  => $pendingCount,
                ],
                'low_stock_items' => $stats['low_stock_items'],
                'picking_queue'   => $pickingQueue,
                'chart_data'      => $chartData,
            ],
        ]);
    }
}
