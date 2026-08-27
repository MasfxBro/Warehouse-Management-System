<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: inbound_transactions
 *
 * Tabel header transaksi penerimaan barang (inbound) dari supplier.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbound_transactions', function (Blueprint $table) {
            $table->id('Inbound_ID');
            $table->string('No_Receiving', 100)->unique()->comment('Nomor Resi Sistem Inbound (RSI-YYYYMMDD-XXXX)');
            $table->date('Tanggal')->comment('Tanggal penerimaan barang');
            $table->unsignedBigInteger('Supplier_ID')->comment('Supplier penyuplai');
            $table->foreign('Supplier_ID')
                  ->references('Supplier_ID')
                  ->on('suppliers')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->unsignedBigInteger('User_ID')->comment('Operator yang memproses');
            $table->foreign('User_ID')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->text('Catatan')->nullable()->comment('Catatan opsional transaksi inbound');

            $table->timestamps();
            $table->softDeletes();

            $table->index('No_Receiving');
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
