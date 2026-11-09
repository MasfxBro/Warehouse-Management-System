<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class WarehouseCache
{
    public static function clearDashboard(): void
    {
        foreach (['dashboard_stats', 'picking_queue', 'picking_count'] as $key) {
            Cache::forget($key);
        }

        foreach (['hari_ini', '7_hari', '1_bulan', '1_tahun', 'semua'] as $period) {
            Cache::forget('trx_count_'.$period.'_'.today()->toDateString());
        }

        foreach (['seminggu_ini', 'seminggu', 'sebulan', 'setahun'] as $period) {
            Cache::forget('chart_'.$period);
        }
    }
}
