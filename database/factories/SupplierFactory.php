<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory: SupplierFactory
 *
 * Menghasilkan data dummy untuk tabel suppliers.
 * Nama supplier menggunakan nama perusahaan palsu yang realistis.
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'Nama'   => $this->faker->company(),
            'Kontak' => $this->faker->optional()->phoneNumber() . ' / ' . $this->faker->optional()->email(),
        ];
    }
}
