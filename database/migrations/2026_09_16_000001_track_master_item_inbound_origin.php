<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            $table->uuid('Created_From_Inbound_ID')->nullable()->index();
            $table->softDeletes();

            $table->foreign('Created_From_Inbound_ID')
                ->references('Inbound_ID')
                ->on('inbound_transactions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            $table->dropForeign(['Created_From_Inbound_ID']);
            $table->dropColumn(['Created_From_Inbound_ID', 'deleted_at']);
        });
    }
};
