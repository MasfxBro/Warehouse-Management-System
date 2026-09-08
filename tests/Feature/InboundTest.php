<?php

namespace Tests\Feature;

use App\Models\BaseUnit;
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
        $admin = $this->loginAsAdmin();
        $supplier = Supplier::first();
        $rack = RackLocation::create([
            'Kode_Rak' => 'TEST-RSI', 'Aisle' => 'T', 'Level' => '1', 'Kapasitas' => 1000,
        ]);

        // Buat barang lama dulu
        $barang = MasterBarang::create([
            'SKU' => 'TST-00001',
            'Nama' => 'Barang Test RSI',
            'Kategori' => 'Testing',
            'Rack_ID' => $rack->Rack_ID,
            'Min_Stok' => 5,
        ]);

        // Buat inbound pertama hari ini
        $response = $this->post(route('inbound.store'), [
            'Tanggal' => now()->format('Y-m-d'),
            'Supplier_ID' => $supplier->Supplier_ID,
            'Catatan' => null,
            'items' => [
                [
                    'jenis' => 'lama',
                    'SKU_lama' => 'TST-00001',
                    'Rack_ID_lama' => $rack->Rack_ID,
                    'Qty' => 10,
                    'Harga_Satuan' => 75000,
                ],
            ],
        ]);

        $response->assertRedirect(route('inbound.index'));

        $trx = InboundTransaction::latest('Inbound_ID')->first();
        $this->assertNotNull($trx);

        // Format: RSI-YYYYMMDD-XXXX
        $today = now()->format('Ymd');
        $this->assertMatchesRegularExpression(
            '/^RSI-'.$today.'-\d{4}$/',
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
        $rack = RackLocation::create([
            'Kode_Rak' => 'TEST-NEW', 'Aisle' => 'T', 'Level' => '2', 'Kapasitas' => 1000,
        ]);

        $countBefore = MasterBarang::count();

        $response = $this->post(route('inbound.store'), [
            'Tanggal' => now()->format('Y-m-d'),
            'Supplier_ID' => $supplier->Supplier_ID,
            'items' => [
                [
                    'jenis' => 'baru',
                    'Nama_baru' => 'Laptop Gaming Test',
                    'Kategori_baru' => 'Elektronik',
                    'Rack_ID_baru' => $rack->Rack_ID,
                    'Min_Stok_baru' => 3,
                    'Satuan_baru' => 'kaleng plastik',
                    'Qty' => 5,
                    'Harga_Satuan' => 125000,
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
        $this->assertEquals('Kaleng Plastik', $newBarang->Satuan);
        $this->assertEquals(125000, $newBarang->Harga_Dasar);
        $this->assertDatabaseHas('base_units', ['Nama' => 'Kaleng Plastik']);
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
        $rack = RackLocation::create([
            'Kode_Rak' => 'TEST-OLD', 'Aisle' => 'T', 'Level' => '3', 'Kapasitas' => 1000,
        ]);

        // Buat barang lama dengan stok awal via inbound pertama
        $barang = MasterBarang::create([
            'SKU' => 'EXS-00001',
            'Nama' => 'Barang Existing Test',
            'Kategori' => 'Spare Part',
            'Rack_ID' => $rack->Rack_ID,
            'Min_Stok' => 5,
        ]);

        // Simulasi stok awal (inbound pertama langsung ke DB)
        $inbound1 = InboundTransaction::create([
            'No_Receiving' => 'RSI-TEST-0001',
            'Tanggal' => now()->subDay()->format('Y-m-d'),
            'Supplier_ID' => $supplier->Supplier_ID,
            'User_ID' => auth()->id() ?? User::where('email', 'admin@wms.local')->value('id'),
        ]);
        InboundDetail::create([
            'Inbound_ID' => $inbound1->Inbound_ID,
            'SKU' => 'EXS-00001',
            'Rack_ID' => $rack->Rack_ID,
            'Qty' => 20,
        ]);

        $stokSebelum = $barang->fresh()->stok;
        $this->assertEquals(20, $stokSebelum);

        // Inbound kedua via form
        $response = $this->post(route('inbound.store'), [
            'Tanggal' => now()->format('Y-m-d'),
            'Supplier_ID' => $supplier->Supplier_ID,
            'items' => [
                [
                    'jenis' => 'lama',
                    'SKU_lama' => 'EXS-00001',
                    'Rack_ID_lama' => $rack->Rack_ID,
                    'Qty' => 10,
                    'Harga_Satuan' => 200000,
                ],
            ],
        ]);

        $response->assertRedirect(route('inbound.index'));

        // Stok sekarang harus 20 + 10 = 30
        $this->assertEquals(30, $barang->fresh()->stok);

        $transaksiTerbaru = InboundTransaction::where('No_Receiving', 'like', 'RSI-'.now()->format('Ymd').'-%')
            ->latest('created_at')
            ->first();
        $detailTerbaru = InboundDetail::where('Inbound_ID', $transaksiTerbaru?->Inbound_ID)->first();
        $this->assertNotNull($detailTerbaru);
        $this->assertEquals(10, $detailTerbaru->Qty,
            'Qty detail inbound harus sama persis dengan nominal yang dikirim pengguna.');
    }

    // ============================================================
    // TEST 4: Title Case Engine pada Supplier AJAX
    // ============================================================

    public function test_new_supplier_ajax_stores_with_title_case(): void
    {
        $this->loginAsAdmin();

        $response = $this->postJson(route('inbound.supplier.ajax'), [
            'Nama' => 'pt maju jaya tbk',
            'No_Kontak' => '082100000000',
            'Email' => 'info@majujaya.com',
            'Alamat' => 'jl. raya industri nomor 5',
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

    public function test_total_items_cannot_exceed_the_same_rack_capacity(): void
    {
        $this->loginAsAdmin();
        $supplier = Supplier::firstOrFail();
        $rack = RackLocation::create([
            'Kode_Rak' => 'TEST-CAP', 'Aisle' => 'T', 'Level' => '4', 'Kapasitas' => 100,
        ]);

        foreach (['CAP-00001', 'CAP-00002'] as $sku) {
            MasterBarang::create([
                'SKU' => $sku,
                'Nama' => "Barang {$sku}",
                'Kategori' => 'Testing',
                'Rack_ID' => $rack->Rack_ID,
                'Min_Stok' => 1,
            ]);
        }

        $countBefore = InboundTransaction::count();
        $response = $this->post(route('inbound.store'), [
            'Tanggal' => now()->toDateString(),
            'Supplier_ID' => $supplier->Supplier_ID,
            'items' => [
                ['jenis' => 'lama', 'SKU_lama' => 'CAP-00001', 'Rack_ID_lama' => $rack->Rack_ID, 'Qty' => 60, 'Harga_Satuan' => 10000],
                ['jenis' => 'lama', 'SKU_lama' => 'CAP-00002', 'Rack_ID_lama' => $rack->Rack_ID, 'Qty' => 60, 'Harga_Satuan' => 20000],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertSame($countBefore, InboundTransaction::count());
    }

    public function test_existing_item_uses_locked_initial_price_and_ignores_submitted_price(): void
    {
        $this->loginAsAdmin();
        $supplier = Supplier::firstOrFail();
        $rack = RackLocation::create([
            'Kode_Rak' => 'TEST-PRICE', 'Aisle' => 'T', 'Level' => '5', 'Kapasitas' => 1000,
        ]);
        $barang = MasterBarang::create([
            'SKU' => 'PRC-00001',
            'Nama' => 'Barang Harga Test',
            'Kategori' => 'Testing',
            'Satuan' => 'pcs',
            'Harga_Dasar' => 10000,
            'Rack_ID' => $rack->Rack_ID,
            'Min_Stok' => 1,
        ]);
        $awal = InboundTransaction::create([
            'No_Receiving' => 'RSI-PRICE-0001',
            'Tanggal' => now()->subDay(),
            'Supplier_ID' => $supplier->Supplier_ID,
            'User_ID' => auth()->id(),
        ]);
        InboundDetail::create([
            'Inbound_ID' => $awal->Inbound_ID,
            'SKU' => $barang->SKU,
            'Rack_ID' => $rack->Rack_ID,
            'Qty' => 10,
            'Harga_Satuan' => 10000,
        ]);

        $response = $this->post(route('inbound.store'), [
            'Tanggal' => now()->toDateString(),
            'Supplier_ID' => $supplier->Supplier_ID,
            'items' => [[
                'jenis' => 'lama',
                'SKU_lama' => $barang->SKU,
                'Rack_ID_lama' => $rack->Rack_ID,
                'Qty' => 30,
                'Harga_Satuan' => 20000,
            ]],
        ]);

        $response->assertRedirect(route('inbound.index'));
        $detail = InboundDetail::where('SKU', $barang->SKU)
            ->where('Inbound_ID', '!=', $awal->Inbound_ID)
            ->firstOrFail();
        $this->assertSame(10000, $detail->Harga_Satuan);
        $this->assertSame(300000, $detail->subtotal);
        $this->assertSame('PCS', $barang->fresh()->Satuan);
        $this->assertSame(10000, $barang->fresh()->Harga_Dasar);
    }

    public function test_user_can_add_a_normalized_base_unit_without_duplicates(): void
    {
        $this->loginAsAdmin();

        $first = $this->postJson(route('inbound.unit.ajax'), ['Nama' => 'karung besar']);
        $first->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('created', true)
            ->assertJsonPath('unit.nama', 'Karung Besar');

        $second = $this->postJson(route('inbound.unit.ajax'), ['Nama' => 'KARUNG BESAR']);
        $second->assertOk()
            ->assertJsonPath('created', false)
            ->assertJsonPath('unit.nama', 'Karung Besar');

        $this->assertSame(1, BaseUnit::where('Nama', 'Karung Besar')->count());
    }
}
