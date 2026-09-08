<?php

namespace Tests\Feature;

use App\Models\InboundDetail;
use App\Models\MasterBarang;
use App\Models\OutboundDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SeederStockIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_stock_never_goes_negative_and_matches_all_racks(): void
    {
        $this->seed();

        foreach (MasterBarang::all() as $barang) {
            $inbound = InboundDetail::where('SKU', $barang->SKU)->sum('Qty');
            $outbound = OutboundDetail::where('SKU', $barang->SKU)->sum('Qty');
            $this->assertGreaterThanOrEqual(
                $outbound,
                $inbound,
                "Seeder membuat stok negatif untuk SKU {$barang->SKU}."
            );

            $inboundByRack = InboundDetail::where('SKU', $barang->SKU)
                ->selectRaw('"Rack_ID", SUM("Qty") as total_qty')
                ->groupBy('Rack_ID')
                ->pluck('total_qty', 'Rack_ID');
            $outboundByRack = OutboundDetail::where('SKU', $barang->SKU)
                ->selectRaw('"Rack_ID", SUM("Qty") as total_qty')
                ->groupBy('Rack_ID')
                ->pluck('total_qty', 'Rack_ID');

            $stockAcrossRacks = $inboundByRack->keys()
                ->merge($outboundByRack->keys())
                ->unique()
                ->sum(fn ($rackId) => max(
                    0,
                    (int) $inboundByRack->get($rackId, 0) - (int) $outboundByRack->get($rackId, 0)
                ));

            $this->assertSame($barang->stok, $stockAcrossRacks, "Stok rak tidak sinkron untuk SKU {$barang->SKU}.");
        }

        $this->assertSame(0, Artisan::call('wms:audit'), Artisan::output());
    }
}
