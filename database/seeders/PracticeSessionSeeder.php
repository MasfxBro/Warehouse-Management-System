<?php

namespace Database\Seeders;

use App\Models\PracticeSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class PracticeSessionSeeder extends Seeder
{
    public function run(): void
    {
        if (PracticeSession::active()->exists()) {
            return;
        }

        PracticeSession::create([
            'Nama' => 'Sesi Demo Utama',
            'Kelas' => 'Demo WMS',
            'Tanggal' => today(),
            'Status' => 'active',
            'Created_By' => User::where('role', 'admin')->value('id'),
            'Opened_At' => now(),
        ]);

        $this->command->info('  PracticeSessionSeeder: sesi demo aktif dibuat.');
    }
}
