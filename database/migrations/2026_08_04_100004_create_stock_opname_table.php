<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Buat tabel stock_opname untuk audit fisik inventory
 * 
 * UNTUK: OPSI E - Stock Opname (audit fisik vs sistem)
 * Mencatat hasil perhitungan fisik stok dan variance dengan sistem
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opname', function (Blueprint $table) {
            $table->id('opname_id');
            
            // FK ke master barang
            $table->string('SKU', 50);
            $table->foreign('SKU')
                  ->references('SKU')
                  ->on('master_barang')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            // Tanggal audit
            $table->date('tanggal_opname')->comment('Tanggal pelaksanaan stock opname');
            
            // Data stok
            $table->integer('stok_sistem')->comment('Stok menurut sistem');
            $table->integer('stok_fisik')->comment('Stok hasil perhitungan fisik');
            $table->integer('variance')->comment('Selisih (stok_fisik - stok_sistem)');
            
            // Status & tindakan
            $table->enum('status', ['MATCH', 'SELISIH'])->default('SELISIH')
                  ->comment('MATCH jika variance = 0, SELISIH jika ada perbedaan');
            
            $table->text('action_taken')->nullable()
                  ->comment('Tindakan koreksi yang diambil');
            
            $table->text('notes')->nullable()
                  ->comment('Catatan tambahan');
            
            // FK ke user yang melakukan opname
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            $table->timestamps();
            
            // Index untuk query cepat
            $table->index('SKU');
            $table->index('tanggal_opname');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname');
    }
};
