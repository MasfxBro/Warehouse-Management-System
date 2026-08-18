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
            'Kontak' => '021-5551001 / purchasing@citraniaga.co.id',
        ],
        [
            'Nama'   => 'CV Berkah Perdagangan',
            'Alamat' => 'Jl. Raya Darmo No. 12, Surabaya, Jawa Timur 60264',
            'Kontak' => '031-5551002 / berkah.perdagangan@gmail.com',
        ],
        [
            'Nama'   => 'PT Nusantara Distribusi',
            'Alamat' => 'Jl. Asia Afrika No. 78, Bandung, Jawa Barat 40112',
            'Kontak' => '022-5551003 / order@nusantaradist.com',
        ],
        [
            'Nama'   => 'UD Maju Bersama',
            'Alamat' => 'Jl. Pemuda No. 33, Semarang, Jawa Tengah 50132',
            'Kontak' => '024-5551004 / majubersama@yahoo.com',
        ],
        [
            'Nama'   => 'PT Sinar Harapan Tbk',
            'Alamat' => 'Jl. Gatot Subroto KM 5, Medan, Sumatera Utara 20112',
            'Kontak' => '061-5551005 / corporate@sinarharapan.co.id',
        ],
        [
            'Nama'   => 'CV Duta Niaga Sejati',
            'Alamat' => 'Jl. Diponegoro No. 99, Yogyakarta, DIY 55232',
            'Kontak' => '0274-5551006 / dutaniaga@outlook.com',
        ],
        [
            'Nama'   => 'PT Karya Utama Mandiri',
            'Alamat' => 'Jl. A. Yani No. 55, Makassar, Sulawesi Selatan 90221',
            'Kontak' => '0411-5551007 / info@karyautama.co.id',
        ],
        [
            'Nama'   => 'Toko Teknik Jaya',
            'Alamat' => 'Jl. Merdeka No. 17, Malang, Jawa Timur 65119',
            'Kontak' => '0341-5551008 / tokoteknikjaya@gmail.com',
        ],
        [
            'Nama'   => 'PT Wira Logistik Indonesia',
            'Alamat' => 'Jl. Khatib Sulaiman No. 22, Padang, Sumatera Barat 25137',
            'Kontak' => '0751-5551009 / wiralogistik@indo.net.id',
        ],
        [
            'Nama'   => 'CV Surya Kencana',
            'Alamat' => 'Jl. Imam Bonjol No. 44, Denpasar, Bali 80232',
            'Kontak' => '0361-5551010 / suryakencana@bali.co.id',
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
