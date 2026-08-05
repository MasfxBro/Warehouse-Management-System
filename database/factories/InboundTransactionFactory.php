<?php

namespace Database\Factories;

use App\Models\InboundTransaction;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory: InboundTransactionFactory
 *
 * Menghasilkan data dummy untuk tabel inbound_transactions.
 * No_Receiving dibuat unik dengan format: RCV-YYYY-NNNN.
 * Tanggal dibuat dalam rentang 1 tahun terakhir agar data terlihat historis.
 */
class InboundTransactionFactory extends Factory
{
    protected $model = InboundTransaction::class;

    public function definition(): array
    {
        return [
            // Format nomor receiving: RCV-2026-0001
            'No_Receiving' => 'RCV-' . date('Y') . '-' . $this->faker->unique()->numerify('####'),
            'Tanggal'      => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            // Supplier_ID dan User_ID diisi oleh seeder menggunakan record yang sudah ada
            'Supplier_ID'  => Supplier::inRandomOrder()->value('Supplier_ID'),
            'User_ID'      => User::inRandomOrder()->value('id'),
        ];
    }

    /**
     * State: transaksi dengan supplier dan user yang ditentukan secara eksplisit.
     */
    public function forSupplierAndUser(int $supplierId, int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'Supplier_ID' => $supplierId,
            'User_ID'     => $userId,
        ]);
    }
}
