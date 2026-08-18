<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('Kontak', 100)->nullable()->after('Nama')->comment('Nomor kontak customer');
        });
        
        // Set default value for existing records
        DB::table('customers')->whereNull('Kontak')->update(['Kontak' => '-']);
        
        // Make NOT NULL after populating
        Schema::table('customers', function (Blueprint $table) {
            $table->string('Kontak', 100)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('Kontak');
        });
    }
};
