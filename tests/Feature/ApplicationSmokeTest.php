<?php

namespace Tests\Feature;

use App\Models\MasterBarang;
use App\Models\OutboundTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_open_all_primary_pages(): void
    {
        $admin = User::where('email', 'admin@wms.local')->firstOrFail();

        $routes = [
            'dashboard',
            'master.barang.index',
            'master.rak.index',
            'master.supplier.index',
            'master.customer.index',
            'inbound.index',
            'inbound.create',
            'outbound.index',
            'outbound.create',
            'inventory.kartu-stok.index',
            'inventory.stock-opname.index',
            'inventory.stock-opname.create',
            'laporan.index',
            'logs.index',
        ];

        foreach ($routes as $routeName) {
            $this->actingAs($admin)->get(route($routeName))->assertOk();
        }
    }

    public function test_pdf_and_excel_outputs_can_be_generated(): void
    {
        $admin = User::where('email', 'admin@wms.local')->firstOrFail();
        $sku = MasterBarang::value('SKU');
        $completeOutbound = OutboundTransaction::where('picking_status', 'complete')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('master.barang.label-pdf', $sku))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($admin)
            ->get(route('outbound.surat-jalan', $completeOutbound->Outbound_ID))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        foreach (['laporan.inventori.export', 'laporan.inbound.export', 'laporan.outbound.export'] as $routeName) {
            $response = $this->actingAs($admin)->get(route($routeName));
            $response->assertOk();
            $this->assertStringContainsString(
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                (string) $response->headers->get('content-type')
            );
        }
    }
}
