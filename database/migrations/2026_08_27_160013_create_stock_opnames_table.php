<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: stock_opnames
 *
 * Tabel pencatatan kondisi fisik barang hasil pemeriksaan lapangan.
 * BUKAN mengubah stok — hanya mencatat deskripsi kondisi fisik.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id('Opname_ID');

            // FK ke master_barang (SKU)
            $table->string('SKU', 50);
            $table->foreign('SKU')
                  ->references('SKU')
                  ->on('master_barang')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // FK ke users (pemeriksa)
            $table->unsignedBigInteger('User_ID');
            $table->foreign('User_ID')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            // Tanggal pemeriksaan
            $table->date('Tanggal');

            // Deskripsi kondisi fisik barang
            $table->text('Kondisi');

            $table->timestamps();

            $table->index(['SKU', 'Tanggal']);
            $table->index('User_ID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
