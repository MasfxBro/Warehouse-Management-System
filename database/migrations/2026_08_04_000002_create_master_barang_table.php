<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: master_barang
 *
 * Tabel master data barang/produk yang dikelola di gudang.
 * Primary Key menggunakan SKU (string) karena SKU adalah kode unik bisnis yang sudah baku.
 * Mereferensikan rack_locations untuk lokasi default penyimpanan barang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_barang', function (Blueprint $table) {
            // Primary Key — SKU sebagai string, karena SKU adalah identifier bisnis
            $table->string('SKU', 50)->primary()->comment('Stock Keeping Unit — kode unik barang');

            // Nama lengkap barang
            $table->string('Nama', 255)->comment('Nama lengkap barang');

            // Kategori barang (misal: Elektronik, Furnitur, dll)
            $table->string('Kategori', 100)->comment('Kategori/jenis barang');

            // Stok minimum — jika stok <= Min_Stok maka perlu reorder
            $table->unsignedInteger('Min_Stok')->default(0)->comment('Batas minimum stok sebelum reorder');

            // Barcode untuk scanning fisik
            $table->string('Barcode_ID', 100)->nullable()->unique()->comment('ID barcode untuk scanning fisik');

            // FK ke rack_locations — lokasi default penyimpanan barang
            $table->unsignedBigInteger('Rack_ID')->nullable()->comment('Lokasi default rak penyimpanan');
            $table->foreign('Rack_ID')
                  ->references('Rack_ID')
                  ->on('rack_locations')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->timestamps();

            // Index untuk pencarian yang sering dilakukan
            $table->index('Kategori');
            $table->index('Rack_ID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_barang');
    }
};
