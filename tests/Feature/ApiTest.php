<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MasterBarang;
use App\Models\InboundTransaction;
use App\Models\OutboundTransaction;
use App\Models\RackLocation;
use App\Models\Supplier;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_login_and_read_flutter_api(): void
    {
        $login = $this->postJson('/api/v1/login', ['login' => 'admin', 'password' => 'password'])
            ->assertOk()->assertJsonPath('success', true)->assertJsonPath('user.role', 'admin');
        $token = $login->json('token');

        $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.user.role', 'admin');
        $this->withToken($token)->getJson('/api/v1/dashboard')->assertOk()->assertJsonStructure(['data' => ['stats', 'low_stock_items', 'picking_queue']]);
        $this->withToken($token)->getJson('/api/v1/barang')->assertOk()->assertJsonStructure(['data', 'meta']);
        $this->withToken($token)->getJson('/api/v1/inventory/kartu-stok')->assertOk();
    }

    public function test_student_identity_is_required_for_flutter_mutations(): void
    {
        $token = $this->postJson('/api/v1/login', ['login' => 'siswa', 'password' => 'password'])->assertOk()->json('token');
        $this->withToken($token)->postJson('/api/v1/stock-opname', [])->assertForbidden()->assertJsonPath('code', 'STUDENT_IDENTITY_REQUIRED');

        $this->withToken($token)->postJson('/api/v1/student-identity', [
            'name' => 'Budi Santoso', 'class' => 'X Logistik 1', 'nis' => '12345',
        ])->assertOk();
        $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.student_identity.nis', '12345');
    }

    public function test_invalid_or_missing_api_token_is_rejected(): void
    {
        $this->getJson('/api/v1/dashboard')->assertUnauthorized();
        $this->withToken('invalid')->getJson('/api/v1/dashboard')->assertUnauthorized();
    }

    public function test_admin_can_create_inbound_and_outbound_through_flutter_api(): void
    {
        $token = $this->postJson('/api/v1/login', ['login' => 'admin', 'password' => 'password'])->json('token');
        $supplier = Supplier::firstOrFail();
        $rack = RackLocation::withSum('inboundDetails as in_qty', 'Qty')->withSum('completedOutboundDetails as out_qty', 'Qty')
            ->get()->first(fn ($row) => $row->Kapasitas - (($row->in_qty ?? 0) - ($row->out_qty ?? 0)) >= 1);
        $item = MasterBarang::firstOrFail();

        $this->withToken($token)->postJson('/api/v1/inbound', [
            'Tanggal' => today()->format('Y-m-d'), 'Supplier_ID' => $supplier->Supplier_ID,
            'items' => [['jenis' => 'lama', 'SKU_lama' => $item->SKU, 'Rack_ID_lama' => $rack->Rack_ID, 'Qty' => 1, 'tanpa_resi' => true]],
        ])->assertCreated()->assertJsonPath('success', true);

        $customer = Customer::firstOrFail();
        $this->withToken($token)->postJson('/api/v1/outbound', [
            'Tanggal' => today()->format('Y-m-d'), 'Customer_ID' => $customer->Customer_ID, 'Nama_Penerima' => 'Penerima API',
            'items' => [['SKU' => $item->SKU, 'Qty' => 1]],
        ])->assertCreated()->assertJsonPath('success', true);
    }

    public function test_flutter_api_supports_quick_master_data_and_documents(): void
    {
        $token = $this->postJson('/api/v1/login', ['login' => 'admin', 'password' => 'password'])->json('token');

        $this->withToken($token)->postJson('/api/v1/suppliers', [
            'Nama' => 'Supplier Aplikasi', 'No_Kontak' => '08123456789', 'Email' => 'supplier@app.test',
        ])->assertCreated()->assertJsonPath('data.nama', 'Supplier Aplikasi');
        $this->withToken($token)->postJson('/api/v1/customers', [
            'Nama' => 'Customer Aplikasi', 'No_Kontak' => '08987654321', 'Email' => 'customer@app.test',
        ])->assertCreated()->assertJsonPath('data.nama', 'Customer Aplikasi');
        $this->withToken($token)->postJson('/api/v1/units', ['Nama' => 'karton'])
            ->assertCreated()->assertJsonPath('data.nama', 'Karton');

        $item = MasterBarang::firstOrFail();
        $label = $this->withToken($token)->getJson('/api/v1/barang/'.urlencode($item->SKU).'/label-link')
            ->assertOk()->assertJsonPath('success', true)->json('data.url');
        $this->get($label)->assertOk()->assertHeader('content-type', 'application/pdf');

        $rack = RackLocation::firstOrFail();
        $this->withToken($token)->getJson('/api/v1/rack-locations/'.$rack->Rack_ID)
            ->assertOk()->assertJsonStructure(['data' => ['rack_id', 'items', 'other_racks']]);
    }

    public function test_flutter_api_validates_receipt_periods_nis_and_rack_photo(): void
    {
        $adminToken = $this->postJson('/api/v1/login', ['login' => 'admin', 'password' => 'password'])->json('token');
        $supplier = Supplier::firstOrFail();
        $rack = RackLocation::firstOrFail();
        $item = MasterBarang::firstOrFail();

        $this->withToken($adminToken)->postJson('/api/v1/inbound', [
            'Tanggal' => today()->format('Y-m-d'), 'Supplier_ID' => $supplier->Supplier_ID,
            'items' => [['jenis' => 'lama', 'SKU_lama' => $item->SKU, 'Rack_ID_lama' => $rack->Rack_ID, 'Qty' => 1, 'tanpa_resi' => false]],
        ])->assertUnprocessable()->assertJsonValidationErrors('items.0.No_Resi_Supplier');

        $this->withToken($adminToken)->getJson('/api/v1/dashboard?period_trx=semua&period=setahun')
            ->assertOk()->assertJsonPath('data.stats.transaction_period', 'semua')
            ->assertJsonPath('data.chart.period', 'setahun')->assertJsonCount(12, 'data.chart.labels');

        $studentToken = $this->postJson('/api/v1/login', ['login' => 'siswa', 'password' => 'password'])->json('token');
        $longNis = str_repeat('7', 75);
        $this->withToken($studentToken)->postJson('/api/v1/student-identity', [
            'name' => 'Siswa Penguji', 'class' => 'X Logistik', 'nis' => $longNis,
        ])->assertOk();

        Storage::fake('public');
        $this->withToken($adminToken)->post('/api/v1/rack-locations/'.$rack->Rack_ID.'/photo', [
            'foto' => UploadedFile::fake()->image('rak.jpg'),
        ], ['Accept' => 'application/json'])->assertOk()->assertJsonPath('success', true);
        $rack->refresh();
        Storage::disk('public')->assertExists($rack->foto_path);
        $this->withToken($adminToken)->deleteJson('/api/v1/rack-locations/'.$rack->Rack_ID.'/photo')->assertOk();
        $this->assertNull($rack->fresh()->foto_path);
    }

    public function test_flutter_transaction_search_matches_web_fields_and_inbound_total(): void
    {
        $token = $this->postJson('/api/v1/login', ['login' => 'admin', 'password' => 'password'])->json('token');
        $inbound = InboundTransaction::with('supplier')->firstOrFail();
        $outbound = OutboundTransaction::with('customer')->firstOrFail();

        $this->withToken($token)->getJson('/api/v1/inbound?search='.urlencode(strtolower($inbound->supplier->Nama)))
            ->assertOk()->assertJsonFragment(['inbound_id' => $inbound->Inbound_ID]);
        $this->withToken($token)->getJson('/api/v1/inbound/'.$inbound->Inbound_ID)
            ->assertOk()->assertJsonPath('data.total_nilai', $inbound->total_nilai);
        $this->withToken($token)->getJson('/api/v1/outbound?status=not_complete&search='.urlencode(strtolower($outbound->No_Shipping)))
            ->assertOk();
        $this->withToken($token)->getJson('/api/v1/outbound?status=complete&search=tidak-akan-ditemukan')
            ->assertOk()->assertJsonCount(0, 'data');
    }
}
