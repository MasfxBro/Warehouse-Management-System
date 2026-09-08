<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sku_counters', function (Blueprint $table) {
            $table->string('Prefix', 10)->primary();
            $table->unsignedInteger('Last_Number')->default(0);
            $table->timestamps();
        });

        DB::table('master_barang')->orderBy('SKU')->pluck('SKU')->each(function (string $sku): void {
            if (! preg_match('/^([A-Z0-9]+)-(\d+)$/', $sku, $matches)) {
                return;
            }

            $prefix = $matches[1];
            $number = (int) $matches[2];
            $current = (int) (DB::table('sku_counters')->where('Prefix', $prefix)->value('Last_Number') ?? 0);
            DB::table('sku_counters')->updateOrInsert(
                ['Prefix' => $prefix],
                ['Last_Number' => max($number, $current), 'created_at' => now(), 'updated_at' => now()]
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sku_counters');
    }
};
