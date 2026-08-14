<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tambah kolom kapasitas_terisi ke rack_locations
 * 
 * UNTUK: Tracking kapasitas rak yang sudah terpakai, validasi maksimum
 * AMAN: Tidak menghapus data, hanya menambah kolom dengan default 0
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rack_locations', function (Blueprint $table) {
            $table->integer('kapasitas_terisi')->default(0)->after('Kapasitas')
                  ->comment('Jumlah unit barang yang saat ini ada di rak');
        });
    }

    public function down(): void
    {
        Schema::table('rack_locations', function (Blueprint $table) {
            $table->dropColumn('kapasitas_terisi');
        });
    }
};
