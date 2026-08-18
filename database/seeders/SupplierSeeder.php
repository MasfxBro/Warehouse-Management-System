<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

/**
 * Seeder: SupplierSeeder
 *
 * Mengisi tabel suppliers dengan 10 supplier dummy yang realistis
 * sesuai konteks distribusi dan manufaktur.
 */
class SupplierSeeder extends Seeder
{
    private array $suppliers = [
        ['Nama' => 'PT Maju Jaya Elektronik',       'Kontak' => '021-5550101 / purchasing@majujaya.co.id', 'Alamat' => 'Jl. Mangga Dua Raya No. 25, Jakarta Utara 14430'],
        ['Nama' => 'CV Sumber Makmur Furnitur',      'Kontak' => '022-5550202 / order@sumbermakmur.com', 'Alamat' => 'Jl. Cihampelas No. 88, Bandung 40131'],
        ['Nama' => 'PT Global Teknindo',             'Kontak' => '031-5550303 / sales@globalteknindo.co.id', 'Alamat' => 'Jl. Raya Darmo No. 155, Surabaya 60264'],
        ['Nama' => 'UD Prima Bahan Baku',            'Kontak' => '024-5550404 / primabb@gmail.com', 'Alamat' => 'Jl. Pemuda No. 98, Semarang 50132'],
        ['Nama' => 'PT Artha Logistik Nusantara',   'Kontak' => '021-5550505 / info@arthalogistik.com', 'Alamat' => 'Jl. TB Simatupang Kav. 12, Jakarta Selatan 12430'],
        ['Nama' => 'CV Karya Mandiri Sejahtera',     'Kontak' => '0812-1111-2222 / karyamandiri@yahoo.com', 'Alamat' => 'Jl. Diponegoro No. 77, Yogyakarta 55233'],
        ['Nama' => 'PT Surya Abadi Parts',           'Kontak' => '021-5550606 / parts@suryaabadi.co.id', 'Alamat' => 'Jl. Raya Bekasi KM 28, Bekasi 17530'],
        ['Nama' => 'UD Berkah Konsumable',           'Kontak' => '031-5550707 / berkahkons@gmail.com', 'Alamat' => 'Jl. Kenjeran No. 123, Surabaya 60127'],
        ['Nama' => 'PT Indo Kemasan Utama',          'Kontak' => '022-5550808 / sales@indokemasan.co.id', 'Alamat' => 'Jl. Soekarno Hatta No. 300, Bandung 40286'],
        ['Nama' => 'CV Mitra Gudang Persada',        'Kontak' => '024-5550909 / mitragudang@outlook.com', 'Alamat' => 'Jl. Majapahit No. 45, Semarang 50135'],
    ];

    public function run(): void
    {
        foreach ($this->suppliers as $supplier) {
            Supplier::create($supplier);
        }

        $this->command->info('  SupplierSeeder: 10 supplier berhasil dibuat.');
    }
}
