<?php

namespace App\Services;

use App\Models\InboundDetail;
use App\Models\MasterBarang;
use App\Models\OutboundDetail;
use Illuminate\Support\Collection;
use RuntimeException;

class StockAllocationService
{
    /**
     * Saldo positif sebuah SKU pada setiap rak.
     *
     * @return Collection<string, int> Rack_ID => stok tersedia
     */
    public function stockByRack(string $sku): Collection
    {
        $inbound = InboundDetail::query()
            ->where('SKU', $sku)
            ->selectRaw('"Rack_ID", SUM("Qty") as total_qty')
            ->groupBy('Rack_ID')
            ->pluck('total_qty', 'Rack_ID');

        $outbound = OutboundDetail::query()
            ->where('SKU', $sku)
            ->selectRaw('"Rack_ID", SUM("Qty") as total_qty')
            ->groupBy('Rack_ID')
            ->pluck('total_qty', 'Rack_ID');

        $defaultRackId = MasterBarang::whereKey($sku)->value('Rack_ID');

        return $inbound
            ->map(fn ($qty, $rackId) => max(0, (int) $qty - (int) $outbound->get($rackId, 0)))
            ->filter(fn (int $qty) => $qty > 0)
            ->sortBy(fn (int $qty, string $rackId) => $rackId === $defaultRackId ? 0 : 1);
    }

    /**
     * Membagi Qty outbound ke rak-rak yang benar-benar memiliki stok SKU.
     *
     * @return array<int, array{rack_id: string, qty: int}>
     */
    public function allocate(string $sku, int $requestedQty): array
    {
        $remaining = $requestedQty;
        $allocations = [];

        foreach ($this->stockByRack($sku) as $rackId => $availableQty) {
            if ($remaining <= 0) {
                break;
            }

            $allocatedQty = min($remaining, $availableQty);
            $allocations[] = [
                'rack_id' => (string) $rackId,
                'qty' => $allocatedQty,
            ];
            $remaining -= $allocatedQty;
        }

        if ($remaining > 0) {
            $available = $requestedQty - $remaining;
            throw new RuntimeException(
                "Stok SKU {$sku} tidak mencukupi. Tersedia {$available}, diminta {$requestedQty}."
            );
        }

        return $allocations;
    }
}
