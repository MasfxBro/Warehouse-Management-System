<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tambah kolom expired_date ke inbound_details
 * 
 * UNTUK: FIFO picking (First In First Out) berdasarkan tanggal kadaluarsa
 * AMAN: Nullable, tidak wajib diisi untuk barang non-perishable
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inbound_details', function (Blueprint $table) {
            $table->date('expired_date')->nullable()->after('Batch')
                  ->comment('Tanggal kadaluarsa barang untuk FIFO picking');
        });
    }

    public function down(): void
    {
        Schema::table('inbound_details', function (Blueprint $table) {
            $table->dropColumn('expired_date');
        });
    }
};
