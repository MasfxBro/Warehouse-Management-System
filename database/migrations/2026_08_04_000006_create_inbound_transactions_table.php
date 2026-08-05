<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: inbound_transactions
 *
 * Tabel header transaksi penerimaan barang (inbound) dari supplier.
 * Setiap record merepresentasikan satu dokumen receiving.
 * Menggunakan SoftDelete agar data historis tidak hilang permanen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbound_transactions', function (Blueprint $table) {
            // Primary Key — auto increment
            $table->id('Inbound_ID');

            // Nomor dokumen receiving (misal: RCV-2026-0001) — harus unik
            $table->string('No_Receiving', 100)->unique()->comment('Nomor dokumen penerimaan barang, harus unik');

            // Tanggal penerimaan barang
            $table->date('Tanggal')->comment('Tanggal penerimaan barang dari supplier');

            // FK ke suppliers
            $table->unsignedBigInteger('Supplier_ID')->comment('Supplier yang mengirimkan barang');
            $table->foreign('Supplier_ID')
                  ->references('Supplier_ID')
                  ->on('suppliers')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            // FK ke users — operator yang memproses penerimaan
            $table->unsignedBigInteger('User_ID')->comment('User/operator yang memproses penerimaan');
            $table->foreign('User_ID')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->timestamps();

            // Soft delete — data tidak dihapus permanen, hanya ditandai terhapus
            $table->softDeletes();

            // Index untuk query laporan berdasarkan tanggal dan supplier
            $table->index('Tanggal');
            $table->index('Supplier_ID');
            $table->index('User_ID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbound_transactions');
    }
};
