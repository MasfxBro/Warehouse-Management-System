<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory: CustomerFactory
 *
 * Menghasilkan data dummy untuk tabel customers.
 * Nama customer dan alamat menggunakan data palsu yang realistis.
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'Nama'   => $this->faker->company(),
            'Alamat' => $this->faker->address(),
        ];
    }
}
