<?php

namespace Database\Seeders;

use App\Models\RackLocation;
use Illuminate\Database\Seeder;

/**
 * Seeder: RackLocationSeeder
 *
 * Mengisi tabel rack_locations dengan data dummy lokasi rak gudang.
 * Dibuat lebih awal karena direferensikan oleh master_barang dan detail transaksi.
 * Total: 20 rak (4 aisle × 5 level).
 */
class RackLocationSeeder extends Seeder
{
    public function run(): void
    {
        $aisles = ['A', 'B', 'C', 'D'];
        $levels = ['1', '2', '3', '4', '5'];
        $seq    = 1;

        foreach ($aisles as $aisle) {
            foreach ($levels as $level) {
                RackLocation::create([
                    'Kode_Rak'  => sprintf('R-%s%s-%02d', $aisle, $level, $seq),
                    'Aisle'     => $aisle,
                    'Level'     => $level,
                    'Kapasitas' => rand(100, 300),
                ]);
                $seq++;
            }
        }

        $this->command->info('  RackLocationSeeder: 20 rak berhasil dibuat.');
    }
}
