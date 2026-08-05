<?php

namespace Database\Factories;

use App\Models\RackLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory: RackLocationFactory
 *
 * Menghasilkan data dummy untuk tabel rack_locations.
 * Kode rak dibuat dari kombinasi Aisle + Level + nomor urut agar unik dan realistis.
 */
class RackLocationFactory extends Factory
{
    protected $model = RackLocation::class;

    public function definition(): array
    {
        // Aisle berupa huruf kapital A-F, Level berupa angka 1-5
        $aisle = $this->faker->randomElement(['A', 'B', 'C', 'D', 'E', 'F']);
        $level = $this->faker->numberBetween(1, 5);
        $seq   = $this->faker->unique()->numberBetween(1, 99);

        return [
            // Format Kode_Rak: R-A1-01
            'Kode_Rak'  => sprintf('R-%s%d-%02d', $aisle, $level, $seq),
            'Aisle'     => $aisle,
            'Level'     => (string) $level,
            'Kapasitas' => $this->faker->numberBetween(50, 500),
        ];
    }
}
