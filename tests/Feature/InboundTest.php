<?php

namespace Tests\Feature;

use App\Models\InboundDetail;
use App\Models\InboundTransaction;
use App\Models\MasterBarang;
use App\Models\RackLocation;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InboundTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    // ============================================================
    // Helper: Login sebagai Admin
    // ============================================================
    private function loginAsAdmin(): User
    {
        $admin = User::where('email', 'admin@wms.local')->first();
        $this->actingAs($admin);
        return $admin;
    }

    // ============================================================
    // TEST 1: Format RSI harus benar
    // ============================================================

    public function test_generate_rsi_format_correctly(): void
    {
        $admin    = $this->loginAsAdmin();
        $supplier = Supplier::first();
        $rack     = RackLocation::first();

        // Buat barang lama dulu
        $barang = MasterBarang::create([
            'SKU'      => 'TST-00001',
            'Nama'     => 'Barang Test RSI',
            'Kategori' => 'Testing',
            'Rack_ID'  => $rack->Rack_ID,
            'Min_Stok' => 5,
        ]);

        // Buat inbound pertama hari ini
        $response = $this->post(route('inbound.store'), [
            'Tanggal'     => now()->format('Y-m-d'),
            'Supplier_ID' => $supplier->Supplier_ID,
            'Catatan'     => null,
            'items'       => [
                [
                    'jenis'    => 'lama',
                    'SKU_lama' => 'TST-00001',
                    'Qty'      => 10,
                ],
            ],
        ]);

        $response->assertRedirect(route('inbound.index'));

        $trx = InboundTransaction::latest('Inbound_ID')->first();
        $this->assertNotNull($trx);

        // Format: RSI-YYYYMMDD-XXXX
        $today = now()->format('Ymd');
        $this->assertMatchesRegularExpression(
            '/^RSI-' . $today . '-\d{4}$/',
            $trx->No_Receiving,
            "Format No_Receiving harus RSI-YYYYMMDD-XXXX, dapat: {$trx->No_Receiving}"
        );
    }

    // ============================================================
    // TEST 2: Barang baru otomatis masuk ke master_barang
    // ============================================================

    public function test_inbound_with_new_item_creates_master_barang(): void
    {
        $this->loginAsAdmin();
        $supplier = Supplier::first();
        $rack     = RackLocation::first();

        $countBefore = MasterBarang::count();

        $response = $this->post(route('inbound.store'), [
            'Tanggal'     => now()->format('Y-m-d'),
            'Supplier_ID' => $supplier->Supplier_ID,
            'items'       => [
                [
                    'jenis'         => 'baru',
                    'Nama_baru'     => 'Laptop Gaming Test',
                    'Kategori_baru' => 'Elektronik',
                    'Rack_ID_baru'  => $rack->Rack_ID,
                    'Min_Stok_baru' => 3,
                    'Qty'           => 5,
                ],
            ],
        ]);

        $response->assertRedirect(route('inbound.index'));

        // Master barang harus bertambah 1
        $this->assertEquals($countBefore + 1, MasterBarang::count());

        // SKU harus dihasilkan dengan prefix dari "Elektronik" → konsonan ELK
        $newBarang = MasterBarang::where('Nama', 'Laptop Gaming Test')->first();
        $this->assertNotNull($newBarang, 'Barang baru seharusnya ada di master_barang.');
        $this->assertEquals('Elektronik', $newBarang->Kategori);
        $this->assertStringStartsWith('LKT', $newBarang->SKU,
            "SKU dari kategori 'Elektronik' harus dimulai dengan prefix konsonan (LKT untuk 'Lptk...' dst).");

        // Stok harus = 5 via accessor
        $this->assertEquals(5, $newBarang->stok);
    }

    // ============================================================
    // TEST 3: Barang lama — stok bertambah setelah inbound
    // ============================================================

    public function test_inbound_with_existing_item_auto_fills_correctly(): void
    {
        $this->loginAsAdmin();
        $supplier = Supplier::first();
        $rack     = RackLocation::first();

        // Buat barang lama dengan stok awal via inbound pertama
        $barang = MasterBarang::create([
            'SKU'      => 'EXS-00001',
            'Nama'     => 'Barang Existing Test',
            'Kategori' => 'Spare Part',
            'Rack_ID'  => $rack->Rack_ID,
            'Min_Stok' => 5,
        ]);

        // Simulasi stok awal (inbound pertama langsung ke DB)
        $inbound1 = InboundTransaction::create([
            'No_Receiving' => 'RSI-TEST-0001',
            'Tanggal'      => now()->subDay()->format('Y-m-d'),
            'Supplier_ID'  => $supplier->Supplier_ID,
            'User_ID'      => auth()->id() ?? User::where('email', 'admin@wms.local')->value('id'),
        ]);
        InboundDetail::create([
            'Inbound_ID' => $inbound1->Inbound_ID,
            'SKU'        => 'EXS-00001',
            'Rack_ID'    => $rack->Rack_ID,
            'Qty'        => 20,
        ]);

        $stokSebelum = $barang->fresh()->stok;
        $this->assertEquals(20, $stokSebelum);

        // Inbound kedua via form
        $response = $this->post(route('inbound.store'), [
            'Tanggal'     => now()->format('Y-m-d'),
            'Supplier_ID' => $supplier->Supplier_ID,
            'items'       => [
                [
                    'jenis'    => 'lama',
                    'SKU_lama' => 'EXS-00001',
                    'Qty'      => 10,
                ],
            ],
        ]);

        $response->assertRedirect(route('inbound.index'));

        // Stok sekarang harus 20 + 10 = 30
        $this->assertEquals(30, $barang->fresh()->stok);
    }

    // ============================================================
    // TEST 4: Title Case Engine pada Supplier AJAX
    // ============================================================

    public function test_new_supplier_ajax_stores_with_title_case(): void
    {
        $this->loginAsAdmin();

        $response = $this->postJson(route('inbound.supplier.ajax'), [
            'Nama'      => 'pt maju jaya tbk',
            'No_Kontak' => '082100000000',
            'Email'     => 'info@majujaya.com',
            'Alamat'    => 'jl. raya industri nomor 5',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $supplier = Supplier::latest('Supplier_ID')->first();
        $this->assertNotNull($supplier);

        // Nama harus Title Case
        $this->assertEquals('Pt Maju Jaya Tbk', $supplier->Nama,
            'Title Case Engine harus mengubah nama supplier menjadi Title Case.');

        // Alamat harus Title Case
        $this->assertEquals('Jl. Raya Industri Nomor 5', $supplier->Alamat,
            'Title Case Engine harus mengubah alamat menjadi Title Case.');
    }
}
