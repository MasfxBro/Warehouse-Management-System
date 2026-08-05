<?php

namespace Database\Factories;

use App\Models\MasterBarang;
use App\Models\RackLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory: MasterBarangFactory
 *
 * Menghasilkan data dummy untuk tabel master_barang.
 * SKU dibuat unik dengan format: [KATEGORI]-[angka 5 digit].
 * Barcode_ID menggunakan format EAN-13 simulasi.
 */
class MasterBarangFactory extends Factory
{
    protected $model = MasterBarang::class;

    /**
     * Daftar kategori barang gudang yang realistis.
     */
    private array $categories = [
        'Elektronik',
        'Furnitur',
        'Peralatan',
        'Konsumable',
        'Bahan Baku',
        'Spare Part',
        'Kemasan',
        'Alat Tulis',
    ];

    public function definition(): array
    {
        $kategori = $this->faker->randomElement($this->categories);

        // SKU format: 3 huruf kapital kategori + dash + 5 digit angka, misal: ELK-00123
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $kategori), 0, 3));
        $sku    = $prefix . '-' . $this->faker->unique()->numerify('#####');

        return [
            'SKU'        => $sku,
            'Nama'       => $this->faker->words(3, true) . ' ' . $this->faker->word(),
            'Kategori'   => $kategori,
            'Min_Stok'   => $this->faker->numberBetween(5, 100),
            'Barcode_ID' => $this->faker->unique()->ean13(),
            // Rack_ID diisi oleh seeder agar menggunakan ID yang sudah ada
            'Rack_ID'    => null,
        ];
    }

    /**
     * State: barang dengan rack location yang sudah ditentukan.
     */
    public function withRack(int $rackId): static
    {
        return $this->state(fn (array $attributes) => [
            'Rack_ID' => $rackId,
        ]);
    }
}
