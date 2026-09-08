<?php

namespace Tests\Feature;

use App\Models\MasterBarang;
use App\Models\StockOpname;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_login_and_access_dashboard(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Guru (Admin)');
    }

    public function test_siswa_can_login_and_fill_identity(): void
    {
        $response = $this->post('/login', [
            'login' => 'siswa',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        // Siswa goes to dashboard, modal identity is required
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Form Identitas Siswa');

        // Submit student identity
        $identityResponse = $this->post('/student-identity', [
            'name' => 'Test Siswa',
            'class' => 'XII RPL 1',
            'nis' => '123456',
        ]);

        $identityResponse->assertRedirect(route('dashboard'));
        $this->assertEquals('Test Siswa', session('student_identity.name'));
    }

    public function test_siswa_cannot_change_data_before_filling_identity(): void
    {
        $siswa = User::where('email', 'siswa@wms.local')->firstOrFail();
        $sku = MasterBarang::value('SKU');

        $response = $this->actingAs($siswa)->post(route('inventory.stock-opname.store'), [
            'SKU' => $sku,
            'Tanggal' => now()->toDateString(),
            'Kondisi' => 'Kondisi barang baik.',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
        $this->assertSame(0, StockOpname::count());
    }
}
