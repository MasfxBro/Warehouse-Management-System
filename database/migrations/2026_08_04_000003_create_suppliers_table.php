<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: suppliers
 *
 * Tabel master data supplier/pemasok barang ke gudang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            // Primary Key — auto increment
            $table->id('Supplier_ID');

            // Nama perusahaan atau individu supplier
            $table->string('Nama', 255)->comment('Nama supplier');

            // Informasi kontak (nomor telepon, email, atau nama PIC)
            $table->string('Kontak', 255)->nullable()->comment('Informasi kontak supplier');

            $table->timestamps();

            // Index untuk pencarian berdasarkan nama
            $table->index('Nama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
