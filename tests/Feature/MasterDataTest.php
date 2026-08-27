<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MasterBarang;
use App\Models\RackLocation;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_supplier_and_customer_title_case_mutator(): void
    {
        $supplier = Supplier::create([
            'Nama'   => 'pt. indo jaya sejahtera',
            'Alamat' => 'jl. pahlawan raya no 12',
        ]);

        $this->assertEquals('Pt. Indo Jaya Sejahtera', $supplier->Nama);
        $this->assertEquals('Jl. Pahlawan Raya No 12', $supplier->Alamat);

        $customer = Customer::create([
            'Nama'   => 'toko makmur abadi',
            'Alamat' => 'jl. gajah mada no 45',
        ]);

        $this->assertEquals('Toko Makmur Abadi', $customer->Nama);
        $this->assertEquals('Jl. Gajah Mada No 45', $customer->Alamat);
    }

    public function test_admin_can_crud_rack_locations_and_siswa_is_read_only(): void
    {
        $admin = User::where('email', 'admin@wms.local')->first();
        $siswa = User::where('email', 'siswa@wms.local')->first();

        // Admin create rack location
        $responseAdmin = $this->actingAs($admin)->post(route('master.rak.store'), [
            'Kode_Rak'  => 'R-TEST-01',
            'Aisle'     => 'T1',
            'Level'     => '01',
            'Kapasitas' => 100,
        ]);
        $responseAdmin->assertRedirect(route('master.rak.index'));
        $this->assertDatabaseHas('rack_locations', ['Kode_Rak' => 'R-TEST-01']);

        // Siswa try create rack location (should be blocked by role middleware)
        $responseSiswa = $this->actingAs($siswa)->post(route('master.rak.store'), [
            'Kode_Rak'  => 'R-SISWA-01',
            'Aisle'     => 'S1',
            'Level'     => '01',
            'Kapasitas' => 100,
        ]);
        $responseSiswa->assertRedirect(route('dashboard'));
        $this->assertDatabaseMissing('rack_locations', ['Kode_Rak' => 'R-SISWA-01']);
    }

    public function test_master_barang_show_detail_page(): void
    {
        $admin = User::where('email', 'admin@wms.local')->first();
        $item  = MasterBarang::first();

        $response = $this->actingAs($admin)->get(route('master.barang.show', $item->SKU));
        $response->assertStatus(200);
        $response->assertSee($item->SKU);
        $response->assertSee('QR Barcode');
    }
}
