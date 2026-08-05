<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder: UserSeeder
 *
 * Mengisi tabel users dengan akun-akun dummy WMS.
 * - 1 Admin utama (akun tetap dengan kredensial yang diketahui)
 * - 2 Manager
 * - 5 Operator
 *
 * Semua akun menggunakan password: "password" (untuk development only).
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------------------------------
        // Admin utama — akun tetap untuk development/testing
        // -------------------------------------------------------
        User::create([
            'name'              => 'Administrator',
            'email'             => 'admin@wms.local',
            'password'          => Hash::make('password'),
            'role'              => UserRole::Admin->value,
            'email_verified_at' => now(),
        ]);

        // -------------------------------------------------------
        // Manager
        // -------------------------------------------------------
        $managers = [
            ['name' => 'Budi Santoso',   'email' => 'budi.manager@wms.local'],
            ['name' => 'Siti Rahayu',    'email' => 'siti.manager@wms.local'],
        ];

        foreach ($managers as $manager) {
            User::create([
                'name'              => $manager['name'],
                'email'             => $manager['email'],
                'password'          => Hash::make('password'),
                'role'              => UserRole::Manager->value,
                'email_verified_at' => now(),
            ]);
        }

        // -------------------------------------------------------
        // Operator
        // -------------------------------------------------------
        $operators = [
            ['name' => 'Agus Prasetyo',  'email' => 'agus.op@wms.local'],
            ['name' => 'Dewi Kurniawati','email' => 'dewi.op@wms.local'],
            ['name' => 'Eko Wahyudi',    'email' => 'eko.op@wms.local'],
            ['name' => 'Fitri Handayani','email' => 'fitri.op@wms.local'],
            ['name' => 'Hendra Gunawan', 'email' => 'hendra.op@wms.local'],
        ];

        foreach ($operators as $operator) {
            User::create([
                'name'              => $operator['name'],
                'email'             => $operator['email'],
                'password'          => Hash::make('password'),
                'role'              => UserRole::Operator->value,
                'email_verified_at' => now(),
            ]);
        }

        $this->command->info('  UserSeeder: 8 user (1 admin, 2 manager, 5 operator) berhasil dibuat.');
    }
}
