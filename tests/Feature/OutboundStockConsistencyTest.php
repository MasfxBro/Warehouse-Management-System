<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\InboundDetail;
use App\Models\InboundTransaction;
use App\Models\MasterBarang;
use App\Models\OutboundDetail;
use App\Models\OutboundTransaction;
use App\Models\RackLocation;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutboundStockConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_outbound_is_allocated_from_actual_stock_racks(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $supplier = Supplier::create(['Nama' => 'Supplier Test']);
        $customer = Customer::create(['Nama' => 'Customer Test']);
        $rackA = RackLocation::create([
            'Kode_Rak' => 'TEST-A', 'Aisle' => 'A', 'Level' => '1', 'Kapasitas' => 100,
        ]);
        $rackB = RackLocation::create([
            'Kode_Rak' => 'TEST-B', 'Aisle' => 'B', 'Level' => '1', 'Kapasitas' => 100,
        ]);
        $barang = MasterBarang::create([
            'SKU' => 'TST-RACK-01', 'Nama' => 'Barang Multi Rak',
            'Kategori' => 'Testing', 'Rack_ID' => $rackA->Rack_ID, 'Min_Stok' => 5,
        ]);
        $inbound = InboundTransaction::create([
            'No_Receiving' => 'RSI-TEST-RACK', 'Tanggal' => now()->toDateString(),
            'Supplier_ID' => $supplier->Supplier_ID, 'User_ID' => $admin->id,
        ]);

        InboundDetail::create([
            'Inbound_ID' => $inbound->Inbound_ID, 'SKU' => $barang->SKU,
            'Rack_ID' => $rackA->Rack_ID, 'Qty' => 30,
        ]);
        InboundDetail::create([
            'Inbound_ID' => $inbound->Inbound_ID, 'SKU' => $barang->SKU,
            'Rack_ID' => $rackB->Rack_ID, 'Qty' => 20,
        ]);

        $response = $this->actingAs($admin)->post(route('outbound.store'), [
            'Tanggal' => now()->toDateString(),
            'Customer_ID' => $customer->Customer_ID,
            'Nama_Penerima' => 'Penerima Test',
            'items' => [
                ['SKU' => $barang->SKU, 'Qty' => 40],
            ],
        ]);

        $outbound = OutboundTransaction::latest('created_at')->first();
        $response->assertRedirect(route('outbound.show', $outbound->Outbound_ID));

        $this->assertEquals(40, OutboundDetail::where('Outbound_ID', $outbound->Outbound_ID)->sum('Qty'));
        $this->assertEquals(30, OutboundDetail::where('Outbound_ID', $outbound->Outbound_ID)
            ->where('Rack_ID', $rackA->Rack_ID)->sum('Qty'));
        $this->assertEquals(10, OutboundDetail::where('Outbound_ID', $outbound->Outbound_ID)
            ->where('Rack_ID', $rackB->Rack_ID)->sum('Qty'));

        $globalStock = $barang->fresh()->stok;
        $stockAcrossRacks = collect([$rackA, $rackB])->sum(function (RackLocation $rack) use ($barang) {
            $in = InboundDetail::where('SKU', $barang->SKU)->where('Rack_ID', $rack->Rack_ID)->sum('Qty');
            $out = OutboundDetail::where('SKU', $barang->SKU)->where('Rack_ID', $rack->Rack_ID)->sum('Qty');

            return max(0, $in - $out);
        });

        $this->assertEquals(10, $globalStock);
        $this->assertEquals($globalStock, $stockAcrossRacks);
    }
}
