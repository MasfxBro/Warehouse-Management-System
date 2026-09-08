<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            $table->string('Satuan', 50)->default('PCS')->after('Kategori');
            $table->unsignedBigInteger('Harga_Rata_Rata')->default(50000)->after('Satuan');
        });

        Schema::table('inbound_details', function (Blueprint $table) {
            $table->unsignedBigInteger('Harga_Satuan')->default(50000)->after('Qty');
        });

        // Data lama berasal dari dataset demo yang sebelumnya memakai accessor harga
        // tetap Rp50.000. Beri nilai dan satuan yang bervariasi agar dataset lama tetap
        // realistis setelah fitur harga transaksi tersedia.
        $demoValues = [
            'ELK-00001' => ['Unit', 8500000], 'ELK-00002' => ['Unit', 2200000],
            'ELK-00003' => ['Unit', 550000], 'ELK-00004' => ['Unit', 180000],
            'ELK-00005' => ['Unit', 900000], 'FRN-00001' => ['Unit', 1250000],
            'FRN-00002' => ['Unit', 1100000], 'FRN-00003' => ['Unit', 1750000],
            'FRN-00004' => ['Unit', 1450000], 'FRN-00005' => ['Unit', 2300000],
            'PER-00001' => ['Unit', 6500000], 'PER-00002' => ['Unit', 4800000],
            'PER-00003' => ['Unit', 2100000], 'PER-00004' => ['Unit', 1850000],
            'PER-00005' => ['Unit', 425000], 'KNS-00001' => ['Pack', 48000],
            'KNS-00002' => ['Roll', 135000], 'KNS-00003' => ['Lusin', 165000],
            'KNS-00004' => ['Roll', 28000], 'KNS-00005' => ['Pack', 72000],
            'BBK-00001' => ['Kaleng', 120000], 'BBK-00002' => ['Pail', 175000],
            'BBK-00003' => ['KG', 95000], 'BBK-00004' => ['Lembar', 85000],
            'BBK-00005' => ['Set', 65000], 'SPR-00001' => ['PCS', 45000],
            'SPR-00002' => ['PCS', 78000], 'SPR-00003' => ['PCS', 125000],
            'ATS-00001' => ['Box', 145000], 'ATS-00002' => ['Rim', 68000],
        ];

        foreach ($demoValues as $sku => [$unit, $price]) {
            DB::table('master_barang')->where('SKU', $sku)->update([
                'Satuan' => $unit,
                'Harga_Rata_Rata' => $price,
            ]);
            DB::table('inbound_details')->where('SKU', $sku)->update([
                'Harga_Satuan' => $price,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('inbound_details', function (Blueprint $table) {
            $table->dropColumn('Harga_Satuan');
        });

        Schema::table('master_barang', function (Blueprint $table) {
            $table->dropColumn(['Satuan', 'Harga_Rata_Rata']);
        });
    }
};
