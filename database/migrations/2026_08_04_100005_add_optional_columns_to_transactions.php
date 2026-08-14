<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tambah kolom status & notes ke tabel transaksi
 * 
 * OPTIONAL: Untuk tracking status transaksi dan catatan
 * Bisa di-skip jika tidak diperlukan untuk MVP
 */
return new class extends Migration
{
    public function up(): void
    {
        // Inbound transactions
        Schema::table('inbound_transactions', function (Blueprint $table) {
            $table->enum('status', ['draft', 'completed', 'cancelled'])
                  ->default('completed')
                  ->after('User_ID')
                  ->comment('Status transaksi inbound');
            
            $table->text('notes')->nullable()->after('status')
                  ->comment('Catatan tambahan transaksi');
        });
        
        // Outbound transactions
        Schema::table('outbound_transactions', function (Blueprint $table) {
            $table->enum('status', ['draft', 'completed', 'cancelled'])
                  ->default('completed')
                  ->after('User_ID')
                  ->comment('Status transaksi outbound');
            
            $table->text('notes')->nullable()->after('status')
                  ->comment('Catatan tambahan transaksi');
        });
    }

    public function down(): void
    {
        Schema::table('inbound_transactions', function (Blueprint $table) {
            $table->dropColumn(['status', 'notes']);
        });
        
        Schema::table('outbound_transactions', function (Blueprint $table) {
            $table->dropColumn(['status', 'notes']);
        });
    }
};
