<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

/**
 * Seeder: CustomerSeeder
 *
 * Mengisi tabel customers dengan 10 pelanggan dummy yang realistis.
 */
class CustomerSeeder extends Seeder
{
    private array $customers = [
        [
            'Nama'   => 'PT Citra Niaga Abadi',
            'Alamat' => 'Jl. Sudirman No. 45, Jakarta Pusat, DKI Jakarta 10220',
        ],
        [
            'Nama'   => 'CV Berkah Perdagangan',
            'Alamat' => 'Jl. Raya Darmo No. 12, Surabaya, Jawa Timur 60264',
        ],
        [
            'Nama'   => 'PT Nusantara Distribusi',
            'Alamat' => 'Jl. Asia Afrika No. 78, Bandung, Jawa Barat 40112',
        ],
        [
            'Nama'   => 'UD Maju Bersama',
            'Alamat' => 'Jl. Pemuda No. 33, Semarang, Jawa Tengah 50132',
        ],
        [
            'Nama'   => 'PT Sinar Harapan Tbk',
            'Alamat' => 'Jl. Gatot Subroto KM 5, Medan, Sumatera Utara 20112',
        ],
        [
            'Nama'   => 'CV Duta Niaga Sejati',
            'Alamat' => 'Jl. Diponegoro No. 99, Yogyakarta, DIY 55232',
        ],
        [
            'Nama'   => 'PT Karya Utama Mandiri',
            'Alamat' => 'Jl. A. Yani No. 55, Makassar, Sulawesi Selatan 90221',
        ],
        [
            'Nama'   => 'Toko Teknik Jaya',
            'Alamat' => 'Jl. Merdeka No. 17, Malang, Jawa Timur 65119',
        ],
        [
            'Nama'   => 'PT Wira Logistik Indonesia',
            'Alamat' => 'Jl. Khatib Sulaiman No. 22, Padang, Sumatera Barat 25137',
        ],
        [
            'Nama'   => 'CV Surya Kencana',
            'Alamat' => 'Jl. Imam Bonjol No. 44, Denpasar, Bali 80232',
        ],
    ];

    public function run(): void
    {
        foreach ($this->customers as $customer) {
            Customer::create($customer);
        }

        $this->command->info('  CustomerSeeder: 10 customer berhasil dibuat.');
    }
}
