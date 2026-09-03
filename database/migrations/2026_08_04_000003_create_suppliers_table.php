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
            $table->uuid('Supplier_ID')->primary()->comment('UUID primary key');
            $table->string('Nama', 255)->comment('Nama supplier / perusahaan');
            $table->string('Kontak', 255)->nullable()->comment('Kontak gabungan / ringkas');
            $table->string('No_Kontak', 100)->nullable()->comment('Nomor telepon kontak');
            $table->string('Email', 150)->nullable()->comment('Email perusahaan/kontak');
            $table->text('Alamat')->nullable()->comment('Alamat lengkap perusahaan supplier');
            $table->timestamps();

            $table->index('Nama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
