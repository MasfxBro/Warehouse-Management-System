<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SkuNumberService
{
    /** Harus dipanggil di dalam transaksi database. */
    public function next(string $prefix): string
    {
        $prefix = strtoupper($prefix);
        DB::table('sku_counters')->insertOrIgnore([
            'Prefix' => $prefix,
            'Last_Number' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $counter = DB::table('sku_counters')->where('Prefix', $prefix)->lockForUpdate()->first();
        $next = ((int) $counter->Last_Number) + 1;
        DB::table('sku_counters')->where('Prefix', $prefix)->update([
            'Last_Number' => $next,
            'updated_at' => now(),
        ]);

        return sprintf('%s-%05d', $prefix, $next);
    }
}
