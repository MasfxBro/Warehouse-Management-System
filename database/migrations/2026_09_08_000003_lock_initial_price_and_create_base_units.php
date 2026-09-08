<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            $table->renameColumn('Harga_Rata_Rata', 'Harga_Dasar');
        });

        Schema::create('base_units', function (Blueprint $table) {
            $table->uuid('Unit_ID')->primary();
            $table->string('Nama', 50)->unique();
            $table->timestamps();
        });

        $now = now();
        DB::table('master_barang')
            ->select(['SKU', 'Satuan'])
            ->orderBy('SKU')
            ->get()
            ->each(function (object $item) use ($now): void {
                DB::table('base_units')->insertOrIgnore([
                    'Unit_ID' => (string) Str::uuid(),
                    'Nama' => $item->Satuan,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $firstPrice = DB::table('inbound_details')
                    ->join('inbound_transactions', 'inbound_details.Inbound_ID', '=', 'inbound_transactions.Inbound_ID')
                    ->where('inbound_details.SKU', $item->SKU)
                    ->orderBy('inbound_transactions.Tanggal')
                    ->orderBy('inbound_details.created_at')
                    ->value('inbound_details.Harga_Satuan');

                if ($firstPrice !== null) {
                    DB::table('master_barang')->where('SKU', $item->SKU)->update([
                        'Harga_Dasar' => $firstPrice,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('base_units');

        Schema::table('master_barang', function (Blueprint $table) {
            $table->renameColumn('Harga_Dasar', 'Harga_Rata_Rata');
        });
    }
};
