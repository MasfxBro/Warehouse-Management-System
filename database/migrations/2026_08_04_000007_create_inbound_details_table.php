<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: inbound_details
 *
 * Tabel detail per-baris dari transaksi inbound.
 * Setiap record merepresentasikan satu SKU dalam satu transaksi receiving,
 * dengan informasi rak tujuan dan nomor batch.
 * Menggunakan SoftDelete agar konsisten dengan tabel header.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbound_details', function (Blueprint $table) {
            // Primary Key — auto increment
            $table->id('Detail_ID');

            // FK ke inbound_transactions (header)
            $table->unsignedBigInteger('Inbound_ID')->comment('Referensi ke transaksi inbound header');
            $table->foreign('Inbound_ID')
                  ->references('Inbound_ID')
                  ->on('inbound_transactions')
                  ->onDelete('cascade')   // detail ikut terhapus jika header dihapus
                  ->onUpdate('cascade');

            // FK ke master_barang — SKU adalah string PK
            $table->string('SKU', 50)->comment('Kode barang yang diterima');
            $table->foreign('SKU')
                  ->references('SKU')
                  ->on('master_barang')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            // FK ke rack_locations — rak tujuan penempatan barang
            $table->unsignedBigInteger('Rack_ID')->comment('Rak tujuan penempatan barang yang diterima');
            $table->foreign('Rack_ID')
                  ->references('Rack_ID')
                  ->on('rack_locations')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            // Jumlah unit barang yang diterima
            $table->unsignedInteger('Qty')->comment('Jumlah unit barang yang diterima');

            // Nomor batch/lot untuk traceability (nullable — tidak semua barang pakai batch)
            $table->string('Batch', 100)->nullable()->comment('Nomor batch/lot barang untuk traceability');

            $table->timestamps();

            // Soft delete — konsisten dengan tabel header
            $table->softDeletes();

            // Index untuk query detail berdasarkan transaksi, SKU, dan rak
            $table->index('Inbound_ID');
            $table->index('SKU');
            $table->index('Rack_ID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbound_details');
    }
};
