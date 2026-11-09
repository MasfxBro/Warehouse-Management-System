<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: customers
 *
 * Tabel master data customer/pelanggan penerima barang dari gudang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('Customer_ID')->primary()->comment('UUID primary key');
            $table->string('Nama', 255)->comment('Nama customer / perusahaan');
            $table->string('Kontak', 255)->nullable()->comment('Kontak gabungan / ringkas');
            $table->string('No_Kontak', 100)->nullable()->comment('Nomor telepon kontak');
            $table->string('Email', 150)->nullable()->comment('Email perusahaan/kontak');
            $table->text('Alamat')->nullable()->comment('Alamat lengkap customer');
            $table->timestamps();

            $table->index('Nama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
