<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /** Harus dipanggil di dalam transaksi database. */
    public function next(string $type, CarbonInterface|string $date): string
    {
        $date = $date instanceof CarbonInterface ? $date->toDateString() : $date;
        $type = strtoupper($type);

        DB::table('document_counters')->insertOrIgnore([
            'Document_Type' => $type,
            'Document_Date' => $date,
            'Last_Number' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $counter = DB::table('document_counters')
            ->where('Document_Type', $type)
            ->whereDate('Document_Date', $date)
            ->lockForUpdate()
            ->first();

        $next = ((int) $counter->Last_Number) + 1;
        DB::table('document_counters')->where('id', $counter->id)->update([
            'Last_Number' => $next,
            'updated_at' => now(),
        ]);

        return sprintf('%s-%s-%04d', $type, str_replace('-', '', $date), $next);
    }
}
