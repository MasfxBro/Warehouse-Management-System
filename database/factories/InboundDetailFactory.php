<?php

namespace Database\Factories;

use App\Models\InboundDetail;
use App\Models\InboundTransaction;
use App\Models\MasterBarang;
use App\Models\RackLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory: InboundDetailFactory
 *
 * Menghasilkan data dummy untuk tabel inbound_details.
 * Nomor Batch dibuat dengan format: BCH-YYYY-NNNN.
 * Qty dibuat dalam rentang realistis untuk operasional gudang.
 */
class InboundDetailFactory extends Factory
{
    protected $model = InboundDetail::class;

    public function definition(): array
    {
        return [
            // Inbound_ID, SKU, dan Rack_ID diisi oleh seeder
            'Inbound_ID' => InboundTransaction::inRandomOrder()->value('Inbound_ID'),
            'SKU'        => MasterBarang::inRandomOrder()->value('SKU'),
            'Rack_ID'    => RackLocation::inRandomOrder()->value('Rack_ID'),
            'Qty'        => $this->faker->numberBetween(1, 200),
            // Batch bersifat opsional — 30% record tidak memiliki batch
            'Batch'      => $this->faker->optional(0.7)->bothify('BCH-' . date('Y') . '-####'),
        ];
    }

    /**
     * State: detail dengan header, SKU, dan rak yang ditentukan secara eksplisit.
     */
    public function forTransaction(int $inboundId, string $sku, int $rackId): static
    {
        return $this->state(fn (array $attributes) => [
            'Inbound_ID' => $inboundId,
            'SKU'        => $sku,
            'Rack_ID'    => $rackId,
        ]);
    }
}
