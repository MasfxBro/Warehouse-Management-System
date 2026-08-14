<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tambah kolom stok_real dan harga ke master_barang
 * 
 * UNTUK: Dashboard analytics, inventory control, nilai persediaan
 * AMAN: Tidak menghapus data, hanya menambah kolom dengan default value
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            // Stok real-time — akan di-update setiap transaksi inbound/outbound
            $table->integer('stok_real')->default(0)->after('Min_Stok')
                  ->comment('Stok real-time saat ini di gudang');
            
            // Harga per unit — untuk kalkulasi nilai persediaan
            $table->decimal('harga', 15, 2)->default(0)->after('stok_real')
                  ->comment('Harga per unit barang (untuk nilai persediaan)');
            
            // Kolom opsional (nice to have)
            $table->string('satuan', 20)->default('PCS')->after('harga')
                  ->comment('Satuan barang: PCS, BOX, KG, dll');
        });
    }

    public function down(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            $table->dropColumn(['stok_real', 'harga', 'satuan']);
        });
    }
};
