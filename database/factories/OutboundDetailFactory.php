<?php

namespace Database\Factories;

use App\Models\MasterBarang;
use App\Models\OutboundDetail;
use App\Models\OutboundTransaction;
use App\Models\RackLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory: OutboundDetailFactory
 *
 * Menghasilkan data dummy untuk tabel outbound_details.
 * Qty dibuat dalam rentang realistis untuk operasional pengiriman gudang.
 */
class OutboundDetailFactory extends Factory
{
    protected $model = OutboundDetail::class;

    public function definition(): array
    {
        return [
            // Outbound_ID, SKU, dan Rack_ID diisi oleh seeder
            'Outbound_ID' => OutboundTransaction::inRandomOrder()->value('Outbound_ID'),
            'SKU'         => MasterBarang::inRandomOrder()->value('SKU'),
            'Rack_ID'     => RackLocation::inRandomOrder()->value('Rack_ID'),
            'Qty'         => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * State: detail dengan header, SKU, dan rak yang ditentukan secara eksplisit.
     */
    public function forTransaction(int $outboundId, string $sku, int $rackId): static
    {
        return $this->state(fn (array $attributes) => [
            'Outbound_ID' => $outboundId,
            'SKU'         => $sku,
            'Rack_ID'     => $rackId,
        ]);
    }
}
