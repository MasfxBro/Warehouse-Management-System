<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder — Titik masuk utama untuk semua seeder WMS.
 *
 * Urutan pemanggilan sangat penting karena adanya foreign key constraint:
 *
 *  1. RackLocationSeeder   — tidak bergantung pada tabel lain
 *  2. MasterBarangSeeder   — bergantung pada rack_locations
 *  3. SupplierSeeder       — tidak bergantung pada tabel lain
 *  4. CustomerSeeder       — tidak bergantung pada tabel lain
 *  5. UserSeeder           — tidak bergantung pada tabel WMS lain
 *  6. InboundSeeder        — bergantung pada suppliers, users, master_barang, rack_locations
 *  7. OutboundSeeder       — bergantung pada customers, users, master_barang, rack_locations
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info(' WMS Database Seeder — Mulai...');
        $this->command->info('========================================');

        $this->call([
            RackLocationSeeder::class,  // 1. Master lokasi rak
            MasterBarangSeeder::class,  // 2. Master barang (FK → rack_locations)
            SupplierSeeder::class,      // 3. Master supplier
            CustomerSeeder::class,      // 4. Master customer
            UserSeeder::class,          // 5. User dengan role
            InboundSeeder::class,       // 6. Transaksi inbound + detail
            OutboundSeeder::class,      // 7. Transaksi outbound + detail
        ]);

        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info(' Seeder selesai. Database siap digunakan.');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info(' Akun default:');
        $this->command->info('   Guru (Admin) : admin@wms.local (atau username "admin") / password');
        $this->command->info('   Siswa (User) : siswa@wms.local (atau username "siswa") / password');
        $this->command->info('');
    }
}
