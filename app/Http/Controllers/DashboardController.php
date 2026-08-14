<?php

namespace App\Http\Controllers;

use App\Models\MasterBarang;
use App\Models\InboundTransaction;
use App\Models\OutboundTransaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Controller: DashboardController
 * 
 * OPSI A: Dashboard Analytics
 * Menampilkan statistik real-time dari database:
 * - Total SKU
 * - Total Stok
 * - Nilai Persediaan
 * - Alert Reorder
 * - Chart arus barang 7 hari
 * - Low stock alert table
 */
class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard utama.
     */
    public function index()
    {
        // 1. Total SKU (jumlah jenis barang)
        $totalSKU = MasterBarang::count();

        // 2. Total Stok Real-time (sum semua stok_real)
        $totalStok = MasterBarang::sum('stok_real');

        // 3. Total Nilai Persediaan (sum stok_real * harga)
        $nilaiPersediaan = MasterBarang::selectRaw('SUM(stok_real * harga) as total')
            ->value('total') ?? 0;

        // 4. Alert Reorder (barang yang stok < min_stok)
        $alertReorder = MasterBarang::whereColumn('stok_real', '<', 'Min_Stok')
            ->count();

        // 5. Data untuk chart arus barang (7 hari terakhir)
        $chartData = $this->getChartData();

        // 6. Low Stock Items (barang yang perlu reorder)
        $lowStockItems = MasterBarang::whereColumn('stok_real', '<', 'Min_Stok')
            ->orderBy('stok_real', 'asc')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'totalSKU',
            'totalStok',
            'nilaiPersediaan',
            'alertReorder',
            'chartData',
            'lowStockItems'
        ));
    }

    /**
     * Generate data untuk chart arus barang 7 hari terakhir.
     * 
     * @return array
     */
    private function getChartData(): array
    {
        $labels = [];
        $inboundData = [];
        $outboundData = [];

        // Loop 7 hari terakhir
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dayName = Carbon::parse($date)->locale('id')->isoFormat('ddd'); // Sen, Sel, Rab

            $labels[] = $dayName;

            // Total qty inbound pada tanggal ini
            $inboundQty = InboundTransaction::whereDate('Tanggal', $date)
                ->join('inbound_details', 'inbound_transactions.Inbound_ID', '=', 'inbound_details.Inbound_ID')
                ->whereNull('inbound_details.deleted_at')
                ->sum('inbound_details.Qty');

            $inboundData[] = (int) $inboundQty;

            // Total qty outbound pada tanggal ini
            $outboundQty = OutboundTransaction::whereDate('Tanggal', $date)
                ->join('outbound_details', 'outbound_transactions.Outbound_ID', '=', 'outbound_details.Outbound_ID')
                ->whereNull('outbound_details.deleted_at')
                ->sum('outbound_details.Qty');

            $outboundData[] = (int) $outboundQty;
        }

        return [
            'labels' => $labels,
            'inbound' => $inboundData,
            'outbound' => $outboundData,
        ];
    }
}
