<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: inbound_details
 *
 * Tabel detail per-baris dari transaksi inbound.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbound_details', function (Blueprint $table) {
            $table->id('Detail_ID');

            $table->unsignedBigInteger('Inbound_ID')->comment('Referensi ke transaksi inbound header');
            $table->foreign('Inbound_ID')
                  ->references('Inbound_ID')
                  ->on('inbound_transactions')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->string('SKU', 50)->comment('Kode barang yang diterima');
            $table->foreign('SKU')
                  ->references('SKU')
                  ->on('master_barang')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->unsignedBigInteger('Rack_ID')->comment('Rak tujuan penempatan barang');
            $table->foreign('Rack_ID')
                  ->references('Rack_ID')
                  ->on('rack_locations')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->unsignedInteger('Qty')->comment('Jumlah unit barang yang diterima');
            $table->string('No_Resi_Supplier', 150)->nullable()->comment('Nomor resi dari supplier');
            $table->string('Batch', 100)->nullable()->comment('Nomor batch/lot barang');

            $table->timestamps();
            $table->softDeletes();

            $table->index('Inbound_ID');
            $table->index('SKU');
            $table->index('Rack_ID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbound_details');
    }
};
