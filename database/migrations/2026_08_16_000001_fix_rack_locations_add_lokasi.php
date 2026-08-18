<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rack_locations', function (Blueprint $table) {
            // Add Lokasi column as nullable first
            $table->string('Lokasi', 255)->nullable()->after('Kode_Rak')->comment('Lokasi lengkap rak, contoh: Gudang A - Lorong 1 - Rak 3');
        });
        
        // Update existing records: combine Aisle + Level into Lokasi
        DB::statement("UPDATE rack_locations SET \"Lokasi\" = CONCAT(\"Aisle\", ' - Level ', \"Level\") WHERE \"Lokasi\" IS NULL");
        
        // Make Lokasi NOT NULL after data is populated
        Schema::table('rack_locations', function (Blueprint $table) {
            $table->string('Lokasi', 255)->nullable(false)->change();
        });
        
        // Drop old columns if they exist
        Schema::table('rack_locations', function (Blueprint $table) {
            if (Schema::hasColumn('rack_locations', 'Aisle')) {
                $table->dropColumn('Aisle');
            }
            if (Schema::hasColumn('rack_locations', 'Level')) {
                $table->dropColumn('Level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rack_locations', function (Blueprint $table) {
            $table->dropColumn('Lokasi');
            $table->string('Aisle', 20)->comment('Lorong tempat rak berada');
            $table->string('Level', 20)->comment('Tingkat/level rak');
        });
    }
};
