<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: alter users — tambah kolom role
 *
 * Menambahkan kolom `role` ke tabel users bawaan Laravel.
 * Menggunakan PHP Enum `UserRole` untuk nilai yang diizinkan.
 * Default role adalah 'operator' agar user baru memiliki akses paling terbatas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kolom role menggunakan tipe string dengan nilai enum yang divalidasi di aplikasi
            // Default 'operator' — prinsip least privilege
            $table->string('role', 20)
                  ->default(UserRole::Operator->value)
                  ->after('password')
                  ->comment('Peran pengguna: admin, manager, operator');

            // Index untuk filter berdasarkan role
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }
};
