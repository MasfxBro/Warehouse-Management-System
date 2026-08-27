<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder: UserSeeder
 *
 * Mengisi tabel users dengan 2 role utama WMS Prototipe 2:
 * 1. Admin (Guru): `admin@wms.local` / `password`
 * 2. Shared Siswa (User/Operator): `siswa@wms.local` / `password`
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate/delete existing users if necessary or use updateOrCreate
        User::updateOrCreate(
            ['email' => 'admin@wms.local'],
            [
                'name'              => 'Guru Administrator',
                'password'          => Hash::make('password'),
                'role'              => UserRole::Admin->value,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'siswa@wms.local'],
            [
                'name'              => 'Operator Siswa (Shared)',
                'password'          => Hash::make('password'),
                'role'              => UserRole::User->value,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('  UserSeeder: Akun Admin (Guru) & Shared Siswa berhasil dibuat.');
    }
}
