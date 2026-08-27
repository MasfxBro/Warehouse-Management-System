<?php

namespace App\Http\Controllers;

use App\Models\MasterBarang;
use App\Models\InboundTransaction;
use App\Models\OutboundTransaction;
use App\Models\InboundDetail;
use App\Models\OutboundDetail;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Tampilkan Halaman Utama Dashboard.
     */
    public function index(Request $request)
    {
        $items = MasterBarang::with(['inboundDetails', 'outboundDetails'])->get();

        $totalSku    = $items->count();
        $totalStok   = $items->sum(fn($item) => $item->stok);
        $nilaiGudang = $items->sum(fn($item) => $item->nilai_barang);

        $today = now()->format('Y-m-d');
        $inboundTodayCount  = InboundTransaction::whereDate('Tanggal', $today)->count();
        $outboundTodayCount = OutboundTransaction::whereDate('Tanggal', $today)->count();

        $lowStockItems = $items->filter(fn($item) => $item->stok <= $item->Min_Stok)->values();
        $lowStockCount = $lowStockItems->count();

        // Periode chart filter (default: seminggu_ini)
        $period = $request->query('period', 'seminggu_ini');
        $chartData = $this->getChartData($period);

        return view('dashboard', compact(
            'totalSku',
            'totalStok',
            'nilaiGudang',
            'inboundTodayCount',
            'outboundTodayCount',
            'lowStockItems',
            'lowStockCount',
            'chartData',
            'period'
        ));
    }

    /**
     * Hitung dataset grafik Inbound vs Outbound berdasarkan periode.
     */
    private function getChartData(string $period): array
    {
        $labels = [];
        $inboundData = [];
        $outboundData = [];

        if ($period === 'seminggu' || $period === '7days') {
            // 7 Hari Terakhir
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dateStr = $date->format('Y-m-d');
                $labels[] = $date->format('d M');

                $inboundData[]  = InboundDetail::whereHas('inboundTransaction', fn($q) => $q->whereDate('Tanggal', $dateStr))->sum('Qty');
                $outboundData[] = OutboundDetail::whereHas('outboundTransaction', fn($q) => $q->whereDate('Tanggal', $dateStr))->sum('Qty');
            }
        } elseif ($period === 'sebulan' || $period === 'this_month') {
            // Sebulan (per 5 hari / mingguan dalam bulan ini)
            $startOfMonth = now()->startOfMonth();
            $daysInMonth  = now()->daysInMonth;
            
            for ($day = 1; $day <= $daysInMonth; $day += 5) {
                $start = now()->setDate(now()->year, now()->month, $day);
                $end   = (clone $start)->addDays(4);
                if ($end->month !== now()->month) {
                    $end = now()->endOfMonth();
                }

                $labels[] = $start->format('d') . '-' . $end->format('d M');
                $inboundData[]  = InboundDetail::whereHas('inboundTransaction', fn($q) => $q->whereBetween('Tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')]))->sum('Qty');
                $outboundData[] = OutboundDetail::whereHas('outboundTransaction', fn($q) => $q->whereBetween('Tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')]))->sum('Qty');
            }
        } elseif ($period === 'setahun' || $period === 'this_year') {
            // Setahun (Jan - Des)
            for ($m = 1; $m <= 12; $m++) {
                $monthDate = now()->setDate(now()->year, $m, 1);
                $labels[] = $monthDate->format('M');

                $inboundData[]  = InboundDetail::whereHas('inboundTransaction', fn($q) => $q->whereYear('Tanggal', now()->year)->whereMonth('Tanggal', $m))->sum('Qty');
                $outboundData[] = OutboundDetail::whereHas('outboundTransaction', fn($q) => $q->whereYear('Tanggal', now()->year)->whereMonth('Tanggal', $m))->sum('Qty');
            }
        } else {
            // Default: Seminggu Ini (Senin - Minggu minggu berjalan)
            $startOfWeek = now()->startOfWeek();
            for ($i = 0; $i < 7; $i++) {
                $date = (clone $startOfWeek)->addDays($i);
                $dateStr = $date->format('Y-m-d');
                $labels[] = $date->format('D, d M');

                $inboundData[]  = InboundDetail::whereHas('inboundTransaction', fn($q) => $q->whereDate('Tanggal', $dateStr))->sum('Qty');
                $outboundData[] = OutboundDetail::whereHas('outboundTransaction', fn($q) => $q->whereDate('Tanggal', $dateStr))->sum('Qty');
            }
        }

        return [
            'labels'   => $labels,
            'inbound'  => $inboundData,
            'outbound' => $outboundData,
        ];
    }
}
