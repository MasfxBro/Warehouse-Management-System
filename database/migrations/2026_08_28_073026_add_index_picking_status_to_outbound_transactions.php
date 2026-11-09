<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outbound_transactions', function (Blueprint $table) {
            // Index untuk filter WHERE picking_status = 'not_complete'
            $table->index('picking_status', 'idx_outbound_picking_status');

            // Index untuk ORDER BY priority (CASE expression)
            $table->index('priority', 'idx_outbound_priority');
        });
    }

    public function down(): void
    {
        Schema::table('outbound_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_outbound_picking_status');
            $table->dropIndex('idx_outbound_priority');
        });
    }
};
