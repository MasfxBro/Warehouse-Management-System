<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: outbound_details
 *
 * Tabel detail per-baris dari transaksi outbound.
 * Setiap record merepresentasikan satu SKU dalam satu transaksi pengiriman,
 * dengan informasi rak sumber pengambilan barang.
 * Menggunakan SoftDelete agar konsisten dengan tabel header.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbound_details', function (Blueprint $table) {
            $table->uuid('Detail_ID')->primary()->comment('UUID primary key');

            $table->uuid('Outbound_ID')->comment('Referensi ke transaksi outbound header');
            $table->foreign('Outbound_ID')
                  ->references('Outbound_ID')
                  ->on('outbound_transactions')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->string('SKU', 50)->comment('Kode barang yang dikirim');
            $table->foreign('SKU')
                  ->references('SKU')
                  ->on('master_barang')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->uuid('Rack_ID')->comment('Rak sumber pengambilan barang untuk dikirim');
            $table->foreign('Rack_ID')
                  ->references('Rack_ID')
                  ->on('rack_locations')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            // Jumlah unit barang yang dikirim
            $table->unsignedInteger('Qty')->comment('Jumlah unit barang yang dikirim');

            $table->timestamps();

            // Soft delete — konsisten dengan tabel header
            $table->softDeletes();

            // Index untuk query detail berdasarkan transaksi, SKU, dan rak
            $table->index('Outbound_ID');
            $table->index('SKU');
            $table->index('Rack_ID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_details');
    }
};
