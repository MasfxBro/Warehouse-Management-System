<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\OutboundTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory: OutboundTransactionFactory
 *
 * Menghasilkan data dummy untuk tabel outbound_transactions.
 * No_Shipping dibuat unik dengan format: SHP-YYYY-NNNN.
 * No_Surat_Jalan dibuat unik dengan format: SJ-YYYY-NNNN.
 */
class OutboundTransactionFactory extends Factory
{
    protected $model = OutboundTransaction::class;

    public function definition(): array
    {
        return [
            // Format nomor shipping: SHP-2026-0001
            'No_Shipping'    => 'SHP-' . date('Y') . '-' . $this->faker->unique()->numerify('####'),
            'Tanggal'        => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            // Customer_ID dan User_ID diisi oleh seeder menggunakan record yang sudah ada
            'Customer_ID'    => Customer::inRandomOrder()->value('Customer_ID'),
            'User_ID'        => User::inRandomOrder()->value('id'),
            // 80% transaksi sudah memiliki surat jalan, 20% belum diterbitkan
            'No_Surat_Jalan' => $this->faker->optional(0.8)->bothify('SJ-' . date('Y') . '-####'),
        ];
    }

    /**
     * State: transaksi dengan customer dan user yang ditentukan secara eksplisit.
     */
    public function forCustomerAndUser(int $customerId, int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'Customer_ID' => $customerId,
            'User_ID'     => $userId,
        ]);
    }
}
