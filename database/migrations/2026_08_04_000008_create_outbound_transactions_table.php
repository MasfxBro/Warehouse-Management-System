<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: outbound_transactions
 *
 * Tabel header transaksi pengiriman barang (outbound) ke customer.
 * Setiap record merepresentasikan satu dokumen shipping/pengiriman.
 * No_Surat_Jalan adalah nomor surat jalan fisik yang menyertai pengiriman.
 * Menggunakan SoftDelete agar data historis tidak hilang permanen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbound_transactions', function (Blueprint $table) {
            $table->uuid('Outbound_ID')->primary()->comment('UUID primary key');
            $table->string('No_Shipping', 100)->unique()->comment('Nomor dokumen pengiriman barang, harus unik');
            $table->date('Tanggal')->comment('Tanggal pengiriman barang ke customer');
            $table->uuid('Customer_ID')->comment('Customer penerima barang');
            $table->foreign('Customer_ID')
                  ->references('Customer_ID')
                  ->on('customers')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->string('No_Surat_Jalan', 100)->nullable()->comment('Nomor surat jalan fisik yang menyertai pengiriman');
            $table->unsignedBigInteger('User_ID')->comment('User/operator yang memproses pengiriman');
            $table->foreign('User_ID')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->timestamps();

            // Soft delete — data tidak dihapus permanen
            $table->softDeletes();

            // Index untuk query laporan
            $table->index('Tanggal');
            $table->index('Customer_ID');
            $table->index('User_ID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_transactions');
    }
};
