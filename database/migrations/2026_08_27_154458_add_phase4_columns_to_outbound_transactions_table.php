<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tambah kolom Phase 4 ke outbound_transactions.
 *
 * Menambahkan kolom:
 * - picking_status : Status picking list (not_complete / complete)
 * - priority       : Prioritas pengambilan (high / normal / decent)
 * - Nama_Penerima  : Nama kurir / orang yang menerima barang
 * - Catatan        : Catatan opsional untuk outbound
 *
 * Kolom No_Shipping sudah ada dari migration awal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outbound_transactions', function (Blueprint $table) {
            // Status picking list
            $table->string('picking_status', 20)->default('not_complete')
                  ->after('User_ID')
                  ->comment('Status picking: not_complete | complete');

            // Prioritas berdasarkan total qty
            $table->string('priority', 10)->default('decent')
                  ->after('picking_status')
                  ->comment('Prioritas: high (>50) | normal (11-50) | decent (1-10)');

            // Nama penerima / kurir
            $table->string('Nama_Penerima', 255)->nullable()
                  ->after('priority')
                  ->comment('Nama kurir atau penerima barang');

            // Catatan outbound
            $table->text('Catatan')->nullable()
                  ->after('Nama_Penerima')
                  ->comment('Catatan tambahan untuk transaksi outbound');
        });
    }

    public function down(): void
    {
        Schema::table('outbound_transactions', function (Blueprint $table) {
            $table->dropColumn(['picking_status', 'priority', 'Nama_Penerima', 'Catatan']);
        });
    }
};
