<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rack_locations', function (Blueprint $table) {
            $table->string('foto_path', 500)->nullable()->after('Kapasitas')
                ->comment('Path foto rak di storage/app/public/rak-foto/');
        });
    }

    public function down(): void
    {
        Schema::table('rack_locations', function (Blueprint $table) {
            $table->dropColumn('foto_path');
        });
    }
};
