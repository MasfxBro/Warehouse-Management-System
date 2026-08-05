<?php

namespace Database\Seeders;

use App\Models\MasterBarang;
use App\Models\RackLocation;
use Illuminate\Database\Seeder;

/**
 * Seeder: MasterBarangSeeder
 *
 * Mengisi tabel master_barang dengan 30 barang dummy yang realistis
 * sesuai konteks operasional gudang. Setiap barang diberikan lokasi
 * rak default dari data rack_locations yang sudah ada.
 */
class MasterBarangSeeder extends Seeder
{
    /**
     * Data barang statis yang representatif untuk WMS.
     * Format: [SKU, Nama, Kategori, Min_Stok, Barcode_ID]
     */
    private array $items = [
        ['ELK-00001', 'Laptop Asus VivoBook 14',         'Elektronik',  5,  '8901234567890'],
        ['ELK-00002', 'Monitor LG 24 Inch FHD',          'Elektronik',  3,  '8901234567891'],
        ['ELK-00003', 'Keyboard Logitech K380',           'Elektronik', 10,  '8901234567892'],
        ['ELK-00004', 'Mouse Wireless Logitech M185',     'Elektronik', 10,  '8901234567893'],
        ['ELK-00005', 'UPS APC 650VA',                   'Elektronik',  2,  '8901234567894'],
        ['FRN-00001', 'Kursi Kerja Ergonomis Meja Putar', 'Furnitur',    3,  '8902345678901'],
        ['FRN-00002', 'Meja Kantor 120x60 cm',            'Furnitur',    2,  '8902345678902'],
        ['FRN-00003', 'Lemari Arsip 4 Laci',              'Furnitur',    1,  '8902345678903'],
        ['FRN-00004', 'Rak Besi 5 Susun',                 'Furnitur',    2,  '8902345678904'],
        ['FRN-00005', 'Loker Karyawan 6 Pintu',           'Furnitur',    1,  '8902345678905'],
        ['PER-00001', 'Forklift Manual 2 Ton',            'Peralatan',   1,  '8903456789012'],
        ['PER-00002', 'Hand Pallet Jack 2 Ton',           'Peralatan',   2,  '8903456789013'],
        ['PER-00003', 'Timbangan Digital 300 kg',         'Peralatan',   2,  '8903456789014'],
        ['PER-00004', 'Barcode Scanner Zebra DS2208',     'Peralatan',   3,  '8903456789015'],
        ['PER-00005', 'Tape Dispenser Otomatis',          'Peralatan',  10,  '8903456789016'],
        ['KNS-00001', 'Baterai AA Alkaline (Pack 10)',    'Konsumable', 50,  '8904567890123'],
        ['KNS-00002', 'Bubble Wrap Roll 50m',             'Konsumable', 20,  '8904567890124'],
        ['KNS-00003', 'Lakban Coklat 2 Inch (Lusin)',     'Konsumable', 30,  '8904567890125'],
        ['KNS-00004', 'Tali Rafia Roll 1 kg',             'Konsumable', 20,  '8904567890126'],
        ['KNS-00005', 'Kantong Plastik PE 40x60',         'Konsumable', 100, '8904567890127'],
        ['BBK-00001', 'Resin Epoxy 1 kg',                 'Bahan Baku', 10,  '8905678901234'],
        ['BBK-00002', 'Cat Tembok Putih 5 kg',            'Bahan Baku', 15,  '8905678901235'],
        ['BBK-00003', 'Serbuk Logam Aluminium 1 kg',      'Bahan Baku', 10,  '8905678901236'],
        ['BBK-00004', 'Fiber Glass Sheet 2mm',            'Bahan Baku',  5,  '8905678901237'],
        ['BBK-00005', 'Lem Epoxy 2 Komponen 500ml',       'Bahan Baku', 10,  '8905678901238'],
        ['SPR-00001', 'Bearing 6205 ZZ',                  'Spare Part', 20,  '8906789012345'],
        ['SPR-00002', 'V-Belt A45',                       'Spare Part', 15,  '8906789012346'],
        ['SPR-00003', 'Filter Udara Kompresor',           'Spare Part',  5,  '8906789012347'],
        ['ATS-00001', 'Ballpoint Pilot G2 (Box 12)',      'Alat Tulis', 20,  '8907890123456'],
        ['ATS-00002', 'Kertas HVS A4 80gr (Rim)',         'Alat Tulis', 10,  '8907890123457'],
    ];

    public function run(): void
    {
        // Ambil semua Rack_ID yang sudah ada
        $rackIds = RackLocation::pluck('Rack_ID')->toArray();

        foreach ($this->items as $index => $item) {
            MasterBarang::create([
                'SKU'        => $item[0],
                'Nama'       => $item[1],
                'Kategori'   => $item[2],
                'Min_Stok'   => $item[3],
                'Barcode_ID' => $item[4],
                // Distribusikan barang ke rak secara round-robin
                'Rack_ID'    => $rackIds[$index % count($rackIds)],
            ]);
        }

        $this->command->info('  MasterBarangSeeder: 30 barang berhasil dibuat.');
    }
}
