<?php

namespace App\Http\Controllers;

use App\Models\MasterBarang;
use App\Models\InboundTransaction;
use App\Models\OutboundTransaction;
use App\Models\InboundDetail;
use App\Models\OutboundDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Tampilkan Halaman Utama Dashboard.
     */
    public function index(Request $request)
    {
        // --- Stat Cards (cache 2 menit) ---
        $stats = Cache::remember('dashboard_stats', 120, function () {
            // withSum menghindari N+1 — 3 query total, bukan 2×N query
            $items = MasterBarang::withSum('inboundDetails as inbound_qty', 'Qty')
                ->withSum('outboundDetails as outbound_qty', 'Qty')
                ->get()
                ->map(function ($item) {
                    $item->computed_stok = max(0, (int)($item->inbound_qty ?? 0) - (int)($item->outbound_qty ?? 0));
                    return $item;
                });

            return [
                'totalSku'      => $items->count(),
                'totalStok'     => $items->sum('computed_stok'),
                'nilaiGudang'   => $items->sum(fn($item) => $item->computed_stok * $item->harga),
                'lowStockItems' => $items->filter(fn($item) => $item->computed_stok <= $item->Min_Stok)->values(),
            ];
        });

        $today = now()->toDateString();

        $inboundTodayCount  = Cache::remember('inbound_today_' . $today, 120, fn() =>
            InboundTransaction::whereDate('Tanggal', $today)->count()
        );
        $outboundTodayCount = Cache::remember('outbound_today_' . $today, 120, fn() =>
            OutboundTransaction::whereDate('Tanggal', $today)->count()
        );

        // --- Picking Queue (cache 60 detik) ---
        $pendingOutbounds = Cache::remember('picking_queue', 60, function () {
            return OutboundTransaction::with('customer')
                ->where('picking_status', 'not_complete')
                ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'normal' THEN 2 ELSE 3 END")
                ->limit(6)
                ->get();
        });
        $pendingCount = Cache::remember('picking_count', 60, fn() =>
            OutboundTransaction::where('picking_status', 'not_complete')->count()
        );

        // --- Chart Data (cache 5 menit per period) ---
        $period    = $request->query('period', 'seminggu_ini');
        $chartData = Cache::remember('chart_' . $period, 300, fn() => $this->getChartData($period));

        return view('dashboard', [
            'totalSku'          => $stats['totalSku'],
            'totalStok'         => $stats['totalStok'],
            'nilaiGudang'       => $stats['nilaiGudang'],
            'lowStockItems'     => $stats['lowStockItems'],
            'lowStockCount'     => $stats['lowStockItems']->count(),
            'inboundTodayCount' => $inboundTodayCount,
            'outboundTodayCount'=> $outboundTodayCount,
            'pendingOutbounds'  => $pendingOutbounds,
            'pendingCount'      => $pendingCount,
            'chartData'         => $chartData,
            'period'            => $period,
        ]);
    }

    /**
     * Hitung dataset grafik Inbound vs Outbound berdasarkan periode.
     * Menggunakan JOIN langsung ke inbound_transactions / outbound_transactions
     * (lebih efisien dari whereHas subquery).
     */
    private function getChartData(string $period): array
    {
        $labels       = [];
        $inboundData  = [];
        $outboundData = [];

        if ($period === 'seminggu' || $period === '7days') {
            // 7 Hari Terakhir
            for ($i = 6; $i >= 0; $i--) {
                $date    = now()->subDays($i)->toDateString();
                $labels[] = now()->subDays($i)->format('d M');

                $inboundData[]  = $this->sumDetailByDate('inbound_details', 'inbound_transactions', $date, $date);
                $outboundData[] = $this->sumDetailByDate('outbound_details', 'outbound_transactions', $date, $date);
            }
        } elseif ($period === 'sebulan' || $period === 'this_month') {
            // Sebulan (per 5 hari)
            $daysInMonth = now()->daysInMonth;
            for ($day = 1; $day <= $daysInMonth; $day += 5) {
                $start = now()->startOfMonth()->addDays($day - 1);
                $end   = (clone $start)->addDays(4);
                if ($end->month !== now()->month) {
                    $end = now()->endOfMonth();
                }
                $labels[]       = $start->format('d') . '-' . $end->format('d M');
                $inboundData[]  = $this->sumDetailByDate('inbound_details', 'inbound_transactions', $start->toDateString(), $end->toDateString());
                $outboundData[] = $this->sumDetailByDate('outbound_details', 'outbound_transactions', $start->toDateString(), $end->toDateString());
            }
        } elseif ($period === 'setahun' || $period === 'this_year') {
            // Setahun (Jan–Des)
            for ($m = 1; $m <= 12; $m++) {
                $start = Carbon::create(now()->year, $m, 1)->startOfMonth()->toDateString();
                $end   = Carbon::create(now()->year, $m, 1)->endOfMonth()->toDateString();
                $labels[]       = Carbon::create(now()->year, $m, 1)->format('M');
                $inboundData[]  = $this->sumDetailByDate('inbound_details', 'inbound_transactions', $start, $end);
                $outboundData[] = $this->sumDetailByDate('outbound_details', 'outbound_transactions', $start, $end);
            }
        } else {
            // Default: Seminggu Ini (Senin–Minggu)
            $startOfWeek = now()->startOfWeek();
            for ($i = 0; $i < 7; $i++) {
                $date     = (clone $startOfWeek)->addDays($i)->toDateString();
                $labels[] = (clone $startOfWeek)->addDays($i)->format('D, d M');

                $inboundData[]  = $this->sumDetailByDate('inbound_details', 'inbound_transactions', $date, $date);
                $outboundData[] = $this->sumDetailByDate('outbound_details', 'outbound_transactions', $date, $date);
            }
        }

        return [
            'labels'   => $labels,
            'inbound'  => $inboundData,
            'outbound' => $outboundData,
        ];
    }

    /**
     * Helper: SUM(Qty) dari detail table via JOIN ke transaction table,
     * filter by Tanggal range. Jauh lebih efisien dari whereHas subquery.
     */
    private function sumDetailByDate(
        string $detailTable,
        string $transactionTable,
        string $dateFrom,
        string $dateTo
    ): int {
        // Tentukan foreign key dan transaction PK sesuai tabel
        if ($detailTable === 'inbound_details') {
            $fk = 'inbound_details.Inbound_ID';
            $pk = 'inbound_transactions.Inbound_ID';
        } else {
            $fk = 'outbound_details.Outbound_ID';
            $pk = 'outbound_transactions.Outbound_ID';
        }

        return (int) DB::table($detailTable)
            ->join($transactionTable, $fk, '=', $pk)
            ->whereBetween("{$transactionTable}.Tanggal", [$dateFrom, $dateTo])
            ->sum("{$detailTable}.Qty");
    }
}
