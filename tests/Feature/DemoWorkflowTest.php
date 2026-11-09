<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\InboundDetail;
use App\Models\InboundTransaction;
use App\Models\MasterBarang;
use App\Models\OutboundDetail;
use App\Models\OutboundTransaction;
use App\Models\PracticeSession;
use App\Models\RackLocation;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Supplier $supplier;

    private Customer $customer;

    private RackLocation $rack;

    private MasterBarang $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->supplier = Supplier::create(['Nama' => 'Supplier Workflow']);
        $this->customer = Customer::create(['Nama' => 'Customer Workflow']);
        $this->rack = RackLocation::create([
            'Kode_Rak' => 'WF-A1',
            'Aisle' => 'A',
            'Level' => '1',
            'Kapasitas' => 500,
        ]);
        $this->item = MasterBarang::create([
            'SKU' => 'WF-00001',
            'Nama' => 'Barang Workflow',
            'Kategori' => 'Pengujian',
            'Satuan' => 'PCS',
            'Harga_Dasar' => 25000,
            'Rack_ID' => $this->rack->Rack_ID,
            'Min_Stok' => 5,
        ]);

        $inbound = InboundTransaction::create([
            'No_Receiving' => 'RSI-WORKFLOW-0001',
            'Tanggal' => today(),
            'Supplier_ID' => $this->supplier->Supplier_ID,
            'User_ID' => $this->admin->id,
            'Practice_Session_ID' => PracticeSession::current()?->Practice_Session_ID,
        ]);
        InboundDetail::create([
            'Inbound_ID' => $inbound->Inbound_ID,
            'SKU' => $this->item->SKU,
            'Rack_ID' => $this->rack->Rack_ID,
            'Qty' => 50,
            'Harga_Satuan' => 25000,
        ]);
    }

    public function test_outbound_reserves_stock_then_reduces_physical_stock_after_picking(): void
    {
        $response = $this->actingAs($this->admin)->post(route('outbound.store'), [
            'Tanggal' => today()->toDateString(),
            'Customer_ID' => $this->customer->Customer_ID,
            'Nama_Penerima' => 'Penerima Workflow',
            'items' => [['SKU' => $this->item->SKU, 'Qty' => 20]],
        ]);

        $outbound = OutboundTransaction::where('No_Shipping', 'like', 'SJ-%')->latest('created_at')->firstOrFail();
        $response->assertRedirect(route('outbound.show', $outbound->Outbound_ID));
        $this->assertSame(50, $this->item->fresh()->stok_fisik);
        $this->assertSame(20, $this->item->fresh()->stok_direservasi);
        $this->assertSame(30, $this->item->fresh()->stok);

        $this->actingAs($this->admin)
            ->post(route('outbound.picking-complete', $outbound->Outbound_ID))
            ->assertRedirect(route('outbound.show', $outbound->Outbound_ID));

        $this->assertSame(30, $this->item->fresh()->stok_fisik);
        $this->assertSame(0, $this->item->fresh()->stok_direservasi);
        $this->assertSame(30, $this->item->fresh()->stok);
    }

    public function test_cancelling_pending_outbound_releases_its_reservation_and_keeps_audit_details(): void
    {
        $this->actingAs($this->admin)->post(route('outbound.store'), [
            'Tanggal' => today()->toDateString(),
            'Customer_ID' => $this->customer->Customer_ID,
            'Nama_Penerima' => 'Penerima Batal',
            'items' => [['SKU' => $this->item->SKU, 'Qty' => 15]],
        ]);
        $outbound = OutboundTransaction::where('No_Shipping', 'like', 'SJ-%')->latest('created_at')->firstOrFail();

        $this->actingAs($this->admin)->post(route('outbound.cancel', $outbound->Outbound_ID), [
            'reason' => 'Kesalahan simulasi pada dokumen outbound.',
        ])->assertSessionHas('success');

        $this->assertTrue($outbound->fresh()->isCancelled());
        $this->assertSame(0, OutboundDetail::where('Outbound_ID', $outbound->Outbound_ID)->count());
        $this->assertSame(1, OutboundDetail::withTrashed()->where('Outbound_ID', $outbound->Outbound_ID)->count());
        $this->assertSame(50, $this->item->fresh()->stok_fisik);
        $this->assertSame(0, $this->item->fresh()->stok_direservasi);
        $this->assertSame(50, $this->item->fresh()->stok);
    }

    public function test_inbound_cannot_be_cancelled_until_dependent_outbound_is_cancelled(): void
    {
        $sourceInbound = InboundTransaction::where('No_Receiving', 'RSI-WORKFLOW-0001')->firstOrFail();
        $this->actingAs($this->admin)->post(route('outbound.store'), [
            'Tanggal' => today()->toDateString(),
            'Customer_ID' => $this->customer->Customer_ID,
            'Nama_Penerima' => 'Penerima Ketergantungan',
            'items' => [['SKU' => $this->item->SKU, 'Qty' => 10]],
        ]);
        $outbound = OutboundTransaction::where('No_Shipping', 'like', 'SJ-%')->latest('created_at')->firstOrFail();

        $this->actingAs($this->admin)->post(route('inbound.cancel', $sourceInbound->Inbound_ID), [
            'reason' => 'Menguji perlindungan transaksi yang saling terkait.',
        ])->assertSessionHas('error');
        $this->assertFalse($sourceInbound->fresh()->isCancelled());

        $this->actingAs($this->admin)->post(route('outbound.cancel', $outbound->Outbound_ID), [
            'reason' => 'Outbound dibatalkan sebelum membatalkan inbound.',
        ])->assertSessionHas('success');
        $this->actingAs($this->admin)->post(route('inbound.cancel', $sourceInbound->Inbound_ID), [
            'reason' => 'Inbound latihan tidak lagi dibutuhkan setelah koreksi.',
        ])->assertSessionHas('success');

        $this->assertTrue($sourceInbound->fresh()->isCancelled());
        $this->assertSame(0, $this->item->fresh()->stok);
        $this->assertSame(1, InboundDetail::withTrashed()->where('Inbound_ID', $sourceInbound->Inbound_ID)->count());
    }

    public function test_closed_practice_session_blocks_new_transactions_until_admin_opens_another(): void
    {
        $session = PracticeSession::current();
        $this->actingAs($this->admin)->post(route('practice-sessions.close', $session->Practice_Session_ID), [
            'confirmation' => 'TUTUP SESI',
        ])->assertSessionHas('success');

        $before = InboundTransaction::count();
        $this->actingAs($this->admin)->post(route('inbound.store'), [
            'Tanggal' => today()->toDateString(),
            'Supplier_ID' => $this->supplier->Supplier_ID,
            'items' => [[
                'jenis' => 'lama',
                'SKU_lama' => $this->item->SKU,
                'Rack_ID_lama' => $this->rack->Rack_ID,
                'Qty' => 5,
                'Harga_Satuan' => 99999,
            ]],
        ])->assertSessionHas('error');
        $this->assertSame($before, InboundTransaction::count());

        $this->actingAs($this->admin)->post(route('practice-sessions.store'), [
            'Nama' => 'Praktikum Kelas XI',
            'Kelas' => 'XI Logistik 1',
            'Tanggal' => today()->toDateString(),
        ])->assertSessionHas('success');
        $this->assertSame('Praktikum Kelas XI', PracticeSession::current()?->Nama);
    }

    public function test_document_numbers_are_sequential_per_type_and_date(): void
    {
        foreach ([1, 2] as $qty) {
            $this->actingAs($this->admin)->post(route('inbound.store'), [
                'Tanggal' => today()->toDateString(),
                'Supplier_ID' => $this->supplier->Supplier_ID,
                'items' => [[
                    'jenis' => 'lama',
                    'SKU_lama' => $this->item->SKU,
                    'Rack_ID_lama' => $this->rack->Rack_ID,
                    'Qty' => $qty,
                ]],
            ])->assertRedirect(route('inbound.index'));
        }

        $numbers = InboundTransaction::where('No_Receiving', 'like', 'RSI-'.today()->format('Ymd').'-%')
            ->orderBy('No_Receiving')
            ->pluck('No_Receiving');
        $this->assertSame([
            'RSI-'.today()->format('Ymd').'-0001',
            'RSI-'.today()->format('Ymd').'-0002',
        ], $numbers->all());
    }

    public function test_admin_dashboard_reports_physical_and_reserved_totals(): void
    {
        $this->actingAs($this->admin)->post(route('outbound.store'), [
            'Tanggal' => today()->toDateString(),
            'Customer_ID' => $this->customer->Customer_ID,
            'Nama_Penerima' => 'Penerima Dashboard',
            'items' => [['SKU' => $this->item->SKU, 'Qty' => 12]],
        ]);

        $this->actingAs($this->admin)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('totalStok', 50)
            ->assertViewHas('totalReserved', 12)
            ->assertViewHas('pendingCount', 1);
    }

    public function test_practice_session_cannot_close_while_picking_is_pending(): void
    {
        $this->actingAs($this->admin)->post(route('outbound.store'), [
            'Tanggal' => today()->toDateString(),
            'Customer_ID' => $this->customer->Customer_ID,
            'Nama_Penerima' => 'Penerima Belum Picking',
            'items' => [['SKU' => $this->item->SKU, 'Qty' => 5]],
        ]);

        $session = PracticeSession::current();
        $this->actingAs($this->admin)->post(route('practice-sessions.close', $session->Practice_Session_ID), [
            'confirmation' => 'TUTUP SESI',
        ])->assertSessionHasErrors('confirmation');

        $this->assertSame('active', $session->fresh()->Status);
    }

    public function test_reserved_units_cannot_be_relocated_as_available_stock(): void
    {
        $target = RackLocation::create([
            'Kode_Rak' => 'WF-B1',
            'Aisle' => 'B',
            'Level' => '1',
            'Kapasitas' => 500,
        ]);
        $this->actingAs($this->admin)->post(route('outbound.store'), [
            'Tanggal' => today()->toDateString(),
            'Customer_ID' => $this->customer->Customer_ID,
            'Nama_Penerima' => 'Penerima Reservasi',
            'items' => [['SKU' => $this->item->SKU, 'Qty' => 20]],
        ]);

        $this->actingAs($this->admin)->post(route('master.rak.pindah-barang', $this->rack->Rack_ID), [
            'sku' => $this->item->SKU,
            'new_rack_id' => $target->Rack_ID,
            'qty' => 31,
        ])->assertSessionHas('error');

        $this->actingAs($this->admin)->post(route('master.rak.pindah-barang', $this->rack->Rack_ID), [
            'sku' => $this->item->SKU,
            'new_rack_id' => $target->Rack_ID,
            'qty' => 30,
        ])->assertSessionHas('success');

        $this->assertSame(30, InboundDetail::where('SKU', $this->item->SKU)->where('Rack_ID', $target->Rack_ID)->sum('Qty'));
        $this->assertSame($this->rack->Rack_ID, $this->item->fresh()->Rack_ID, 'Rak default tetap di asal selama stok reservasi masih berada di sana.');
        $this->assertSame(50, $this->item->fresh()->stok_fisik);
        $this->assertSame(20, $this->item->fresh()->stok_direservasi);
        $this->assertSame(30, $this->item->fresh()->stok);
    }
}
