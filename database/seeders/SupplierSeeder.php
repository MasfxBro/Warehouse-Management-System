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
        ['Nama' => 'PT Maju Jaya Elektronik',       'Kontak' => '021-5550101 / purchasing@majujaya.co.id'],
        ['Nama' => 'CV Sumber Makmur Furnitur',      'Kontak' => '022-5550202 / order@sumbermakmur.com'],
        ['Nama' => 'PT Global Teknindo',             'Kontak' => '031-5550303 / sales@globalteknindo.co.id'],
        ['Nama' => 'UD Prima Bahan Baku',            'Kontak' => '024-5550404 / primabb@gmail.com'],
        ['Nama' => 'PT Artha Logistik Nusantara',   'Kontak' => '021-5550505 / info@arthalogistik.com'],
        ['Nama' => 'CV Karya Mandiri Sejahtera',     'Kontak' => '0812-1111-2222 / karyamandiri@yahoo.com'],
        ['Nama' => 'PT Surya Abadi Parts',           'Kontak' => '021-5550606 / parts@suryaabadi.co.id'],
        ['Nama' => 'UD Berkah Konsumable',           'Kontak' => '031-5550707 / berkahkons@gmail.com'],
        ['Nama' => 'PT Indo Kemasan Utama',          'Kontak' => '022-5550808 / sales@indokemasan.co.id'],
        ['Nama' => 'CV Mitra Gudang Persada',        'Kontak' => '024-5550909 / mitragudang@outlook.com'],
    ];

    public function run(): void
    {
        foreach ($this->suppliers as $supplier) {
            Supplier::create($supplier);
        }

        $this->command->info('  SupplierSeeder: 10 supplier berhasil dibuat.');
    }
}
