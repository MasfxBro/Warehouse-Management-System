<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: rack_locations
 *
 * Tabel master lokasi rak di gudang.
 * Dibuat lebih awal karena direferensikan oleh master_barang, inbound_details, dan outbound_details.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rack_locations', function (Blueprint $table) {
            // Primary Key — UUID
            $table->uuid('Rack_ID')->primary()->comment('UUID primary key');

            // Kode unik untuk identifikasi rak (misal: R-A1-01)
            $table->string('Kode_Rak', 50)->unique()->comment('Kode unik rak, contoh: R-A1-01');

            // Aisle / lorong tempat rak berada
            $table->string('Aisle', 20)->comment('Lorong tempat rak berada');

            // Level / tingkat rak (misal: 1, 2, 3 atau A, B, C)
            $table->string('Level', 20)->comment('Tingkat/level rak');

            // Kapasitas maksimal barang yang bisa ditampung di rak ini
            $table->unsignedInteger('Kapasitas')->comment('Kapasitas maksimal unit barang');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rack_locations');
    }
};
