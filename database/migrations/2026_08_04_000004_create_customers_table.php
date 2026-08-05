<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: customers
 *
 * Tabel master data pelanggan yang menerima pengiriman barang dari gudang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            // Primary Key — auto increment
            $table->id('Customer_ID');

            // Nama perusahaan atau individu customer
            $table->string('Nama', 255)->comment('Nama pelanggan');

            // Alamat pengiriman
            $table->text('Alamat')->nullable()->comment('Alamat lengkap pelanggan');

            $table->timestamps();

            // Index untuk pencarian berdasarkan nama
            $table->index('Nama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
