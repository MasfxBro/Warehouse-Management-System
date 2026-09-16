<?php

namespace App\Http\Controllers;

use App\Models\InboundTransaction;
use App\Models\MasterBarang;
use App\Models\OutboundTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Tampilkan Halaman Utama Dashboard.
     */
    public function index(Request $request)
    {
        // --- Stat Cards (cache 2 menit) ---
        $stats = Cache::remember('dashboard_stats', 120, function () {
            $items = MasterBarang::withSum('inboundDetails as inbound_qty', 'Qty')
                ->withSum('outboundDetails as outbound_qty', 'Qty')
                ->withSum('completedOutboundDetails as completed_outbound_qty', 'Qty')
                ->withSum('reservedOutboundDetails as reserved_qty', 'Qty')
                ->get()
                ->map(function ($item) {
                    $item->computed_stok = max(0, (int) ($item->inbound_qty ?? 0) - (int) ($item->outbound_qty ?? 0));
                    $item->physical_stock = max(0, (int) ($item->inbound_qty ?? 0) - (int) ($item->completed_outbound_qty ?? 0));

                    return $item;
                });

            return [
                'totalSku' => $items->count(),
                'totalStok' => $items->sum('physical_stock'),
                'totalReserved' => $items->sum(fn ($item) => (int) ($item->reserved_qty ?? 0)),
                'nilaiGudang' => $items->sum(fn ($item) => $item->physical_stock * $item->harga),
            ];
        });

        // --- Critical Stock — paginated 10 per halaman (tidak di-cache karena butuh page param) ---
        $lowStockPage = max(1, (int) $request->query('low_page', 1));
        $lowStockPerPage = 10;

        $allLowStock = MasterBarang::withSum('inboundDetails as inbound_qty', 'Qty')
            ->withSum('outboundDetails as outbound_qty', 'Qty')
            ->get()
            ->map(function ($item) {
                $item->computed_stok = max(0, (int) ($item->inbound_qty ?? 0) - (int) ($item->outbound_qty ?? 0));

                return $item;
            })
            ->filter(fn ($item) => $item->computed_stok <= $item->Min_Stok)
            ->sortBy('computed_stok')
            ->values();

        $lowStockItems = new LengthAwarePaginator(
            $allLowStock->forPage($lowStockPage, $lowStockPerPage),
            $allLowStock->count(),
            $lowStockPerPage,
            $lowStockPage,
            ['path' => route('dashboard'), 'query' => array_merge($request->query(), []), 'pageName' => 'low_page']
        );
        $lowStockCount = $allLowStock->count();

        $today = now()->toDateString();

        // --- Periode filter untuk stat card transaksi ---
        $periodTrx = $request->query('period_trx', 'hari_ini');
        if (! in_array($periodTrx, ['hari_ini', '7_hari', '1_bulan', '1_tahun', 'semua'], true)) {
            $periodTrx = 'hari_ini';
        }
        $cacheKeyTrx = 'trx_count_'.$periodTrx.'_'.$today;

        [$inboundCount, $outboundCount] = Cache::remember($cacheKeyTrx, 120, function () use ($periodTrx) {
            $inboundQuery = InboundTransaction::where('transaction_status', 'active');
            $outboundQuery = OutboundTransaction::where('transaction_status', 'active');

            match ($periodTrx) {
                '7_hari' => [$inboundQuery->whereDate('Tanggal', '>=', now()->subDays(6)->toDateString()),
                    $outboundQuery->whereDate('Tanggal', '>=', now()->subDays(6)->toDateString())],
                '1_bulan' => [$inboundQuery->whereDate('Tanggal', '>=', now()->subDays(29)->toDateString()),
                    $outboundQuery->whereDate('Tanggal', '>=', now()->subDays(29)->toDateString())],
                '1_tahun' => [$inboundQuery->whereDate('Tanggal', '>=', now()->subYear()->toDateString()),
                    $outboundQuery->whereDate('Tanggal', '>=', now()->subYear()->toDateString())],
                'semua' => [$inboundQuery, $outboundQuery],
                default => [$inboundQuery->whereDate('Tanggal', now()->toDateString()),
                    $outboundQuery->whereDate('Tanggal', now()->toDateString())],
            };

            return [$inboundQuery->count(), $outboundQuery->count()];
        });

        // --- Picking Queue (cache 60 detik) ---
        $pendingOutbounds = Cache::remember('picking_queue', 60, function () {
            return OutboundTransaction::with('customer')
                ->where('transaction_status', 'active')
                ->where('picking_status', 'not_complete')
                ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'normal' THEN 2 ELSE 3 END")
                ->limit(6)
                ->get();
        });
        $pendingCount = Cache::remember('picking_count', 60, fn () => OutboundTransaction::where('transaction_status', 'active')
            ->where('picking_status', 'not_complete')
            ->count()
        );

        // --- Chart Data (cache 5 menit per period) ---
        $period = $request->query('period', 'seminggu_ini');
        if (! in_array($period, ['seminggu_ini', 'seminggu', 'sebulan', 'setahun'], true)) {
            $period = 'seminggu_ini';
        }
        $chartData = Cache::remember('chart_'.$period, 300, fn () => $this->getChartData($period));

        return view('dashboard', [
            'totalSku' => $stats['totalSku'],
            'totalStok' => $stats['totalStok'],
            'totalReserved' => $stats['totalReserved'],
            'nilaiGudang' => $stats['nilaiGudang'],
            'lowStockItems' => $lowStockItems,
            'lowStockCount' => $lowStockCount,
            'inboundTodayCount' => $inboundCount,
            'outboundTodayCount' => $outboundCount,
            'periodTrx' => $periodTrx,
            'pendingOutbounds' => $pendingOutbounds,
            'pendingCount' => $pendingCount,
            'chartData' => $chartData,
            'period' => $period,
        ]);
    }

    /**
     * Hitung dataset grafik Inbound vs Outbound berdasarkan periode.
     * Menggunakan JOIN langsung ke inbound_transactions / outbound_transactions
     * (lebih efisien dari whereHas subquery).
     */
    private function getChartData(string $period): array
    {
        $labels = [];
        $inboundData = [];
        $outboundData = [];

        if ($period === 'seminggu' || $period === '7days') {
            // 7 Hari Terakhir
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();
                $labels[] = now()->subDays($i)->format('d M');

                $inboundData[] = $this->sumDetailByDate('inbound_details', 'inbound_transactions', $date, $date);
                $outboundData[] = $this->sumDetailByDate('outbound_details', 'outbound_transactions', $date, $date);
            }
        } elseif ($period === 'sebulan' || $period === 'this_month') {
            // Sebulan (per 5 hari)
            $daysInMonth = now()->daysInMonth;
            for ($day = 1; $day <= $daysInMonth; $day += 5) {
                $start = now()->startOfMonth()->addDays($day - 1);
                $end = (clone $start)->addDays(4);
                if ($end->month !== now()->month) {
                    $end = now()->endOfMonth();
                }
                $labels[] = $start->format('d').'-'.$end->format('d M');
                $inboundData[] = $this->sumDetailByDate('inbound_details', 'inbound_transactions', $start->toDateString(), $end->toDateString());
                $outboundData[] = $this->sumDetailByDate('outbound_details', 'outbound_transactions', $start->toDateString(), $end->toDateString());
            }
        } elseif ($period === 'setahun' || $period === 'this_year') {
            // Setahun (Jan–Des)
            for ($m = 1; $m <= 12; $m++) {
                $start = Carbon::create(now()->year, $m, 1)->startOfMonth()->toDateString();
                $end = Carbon::create(now()->year, $m, 1)->endOfMonth()->toDateString();
                $labels[] = Carbon::create(now()->year, $m, 1)->format('M');
                $inboundData[] = $this->sumDetailByDate('inbound_details', 'inbound_transactions', $start, $end);
                $outboundData[] = $this->sumDetailByDate('outbound_details', 'outbound_transactions', $start, $end);
            }
        } else {
            // Default: Seminggu Ini (Senin–Minggu)
            $startOfWeek = now()->startOfWeek();
            for ($i = 0; $i < 7; $i++) {
                $date = (clone $startOfWeek)->addDays($i)->toDateString();
                $labels[] = (clone $startOfWeek)->addDays($i)->format('D, d M');

                $inboundData[] = $this->sumDetailByDate('inbound_details', 'inbound_transactions', $date, $date);
                $outboundData[] = $this->sumDetailByDate('outbound_details', 'outbound_transactions', $date, $date);
            }
        }

        return [
            'labels' => $labels,
            'inbound' => $inboundData,
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
            ->whereNull("{$detailTable}.deleted_at")
            ->where("{$transactionTable}.transaction_status", 'active')
            ->whereBetween("{$transactionTable}.Tanggal", [$dateFrom, $dateTo])
            ->sum("{$detailTable}.Qty");
    }
}
