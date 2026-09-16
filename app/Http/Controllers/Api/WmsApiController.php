<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BaseUnit;
use App\Models\Customer;
use App\Models\InboundDetail;
use App\Models\InboundTransaction;
use App\Models\MasterBarang;
use App\Models\OutboundDetail;
use App\Models\OutboundTransaction;
use App\Models\PracticeSession;
use App\Models\RackLocation;
use App\Models\StockOpname;
use App\Models\Supplier;
use App\Services\DocumentNumberService;
use App\Services\StockAllocationService;
use App\Services\SkuNumberService;
use App\Support\UnitNormalizer;
use App\Support\WarehouseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class WmsApiController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $periodTrx = in_array($request->query('period_trx'), ['hari_ini', '7_hari', '1_bulan', '1_tahun', 'semua'], true)
            ? $request->query('period_trx') : 'hari_ini';
        $period = in_array($request->query('period'), ['seminggu_ini', 'seminggu', 'sebulan', 'setahun'], true)
            ? $request->query('period') : 'seminggu_ini';
        $items = $this->stockQuery()->get();
        $low = $items->map(fn ($item) => $this->itemPayload($item))
            ->filter(fn ($item) => $item['stok'] <= $item['min_stok'])->sortBy('stok')->take(10)->values();
        $queue = OutboundTransaction::with('customer')->withSum('outboundDetails as total_qty', 'Qty')
            ->where('transaction_status', 'active')->where('picking_status', 'not_complete')
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'normal' THEN 2 ELSE 3 END")->take(6)->get();

        return response()->json(['success' => true, 'data' => [
            'stats' => [
                'total_sku' => $items->count(),
                'total_stok' => $items->sum(fn ($item) => $this->physical($item)),
                'total_reserved' => $items->sum(fn ($item) => (int) ($item->reserved_qty ?? 0)),
                'nilai_gudang' => $items->sum(fn ($item) => $this->physical($item) * (int) $item->Harga_Dasar),
                'pending_picking' => OutboundTransaction::where('transaction_status', 'active')->where('picking_status', 'not_complete')->count(),
                'inbound_today' => $this->transactionCount(InboundTransaction::query(), $periodTrx),
                'outbound_today' => $this->transactionCount(OutboundTransaction::query(), $periodTrx),
                'transaction_period' => $periodTrx,
            ],
            'low_stock_items' => $low,
            'picking_queue' => $queue->map(fn ($row) => [
                'outbound_id' => $row->Outbound_ID, 'no_shipping' => $row->No_Shipping,
                'customer_nama' => $row->customer?->Nama ?? '-', 'priority' => $row->priority,
                'priority_label' => $row->priorityLabel(), 'status' => $row->picking_status,
            ]),
            'chart' => $this->chart($period),
        ]]);
    }

    public function categories(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => MasterBarang::query()->distinct()->orderBy('Kategori')->pluck('Kategori')->filter()->values()]);
    }

    public function items(Request $request): JsonResponse
    {
        $query = $this->stockQuery()->with('rackLocation')->orderBy('Nama');
        if ($request->filled('search')) {
            $needle = '%'.strtolower($request->string('search')->toString()).'%';
            $query->where(fn ($q) => $q->whereRaw('LOWER("SKU") LIKE ?', [$needle])->orWhereRaw('LOWER("Nama") LIKE ?', [$needle]));
        }
        if ($request->filled('kategori')) $query->where('Kategori', $request->string('kategori'));
        $page = $query->paginate($this->perPage($request));
        return $this->paginated($page, fn ($item) => $this->itemPayload($item));
    }

    public function item(string $sku): JsonResponse
    {
        $item = $this->stockQuery()->with('rackLocation')->findOrFail($sku);
        $data = $this->itemPayload($item);
        $data['nilai_barang'] = $data['stok_fisik'] * $data['harga_dasar'];
        $data['inbound_history'] = InboundDetail::with('inboundTransaction.supplier')->where('SKU', $sku)->latest()->take(10)->get()->map(fn ($d) => [
            'tanggal' => $d->inboundTransaction?->Tanggal?->format('Y-m-d'), 'no_receiving' => $d->inboundTransaction?->No_Receiving,
            'supplier' => $d->inboundTransaction?->supplier?->Nama ?? '-', 'qty' => $d->Qty,
        ]);
        $data['outbound_history'] = OutboundDetail::with('outboundTransaction.customer')->where('SKU', $sku)->latest()->take(10)->get()->map(fn ($d) => [
            'tanggal' => $d->outboundTransaction?->Tanggal?->format('Y-m-d'), 'no_shipping' => $d->outboundTransaction?->No_Shipping,
            'customer' => $d->outboundTransaction?->customer?->Nama ?? '-', 'qty' => $d->Qty,
        ]);
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function itemLabelLink(Request $request, string $sku): JsonResponse
    {
        MasterBarang::findOrFail($sku);

        return response()->json(['success' => true, 'data' => [
            'url' => $this->signedUrl($request, 'api.files.item-label', ['sku' => $sku]),
        ]]);
    }

    public function suppliers(Request $request): JsonResponse
    {
        $query = Supplier::withCount('inboundTransactions')->orderBy('Nama');
        $this->searchParty($query, $request);
        return $this->paginated($query->paginate($this->perPage($request)), fn ($row) => $this->partyPayload($row, 'Supplier_ID', $row->inbound_transactions_count));
    }

    public function customers(Request $request): JsonResponse
    {
        $query = Customer::withCount('outboundTransactions')->orderBy('Nama');
        $this->searchParty($query, $request);
        return $this->paginated($query->paginate($this->perPage($request)), fn ($row) => $this->partyPayload($row, 'Customer_ID', $row->outbound_transactions_count));
    }

    public function storeSupplier(Request $request): JsonResponse
    {
        $data = $request->validate([
            'Nama' => ['required', 'string', 'max:255'],
            'No_Kontak' => ['nullable', 'regex:/^\d*$/', 'max:20'],
            'Email' => ['nullable', 'email', 'max:255'],
            'Alamat' => ['nullable', 'string', 'max:500'],
        ]);
        $supplier = Supplier::create([
            'Nama' => trim($data['Nama']),
            'No_Kontak' => $data['No_Kontak'] ?? null,
            'Kontak' => $data['No_Kontak'] ?? null,
            'Email' => $data['Email'] ?? null,
            'Alamat' => $data['Alamat'] ?? null,
        ]);
        ActivityLog::record("Supplier [{$supplier->Nama}] ditambahkan melalui aplikasi Flutter.");

        return response()->json(['success' => true, 'message' => 'Supplier berhasil ditambahkan.', 'data' => [
            'id' => $supplier->Supplier_ID, 'nama' => $supplier->Nama,
        ]], 201);
    }

    public function storeCustomer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'Nama' => ['required', 'string', 'max:255'],
            'No_Kontak' => ['nullable', 'regex:/^\d*$/', 'max:20'],
            'Email' => ['nullable', 'email', 'max:255'],
            'Alamat' => ['nullable', 'string', 'max:500'],
        ]);
        $customer = Customer::create([
            'Nama' => trim($data['Nama']),
            'No_Kontak' => $data['No_Kontak'] ?? null,
            'Kontak' => $data['No_Kontak'] ?? null,
            'Email' => $data['Email'] ?? null,
            'Alamat' => $data['Alamat'] ?? null,
        ]);
        ActivityLog::record("Customer [{$customer->Nama}] ditambahkan melalui aplikasi Flutter.");

        return response()->json(['success' => true, 'message' => 'Customer berhasil ditambahkan.', 'data' => [
            'id' => $customer->Customer_ID, 'nama' => $customer->Nama,
        ]], 201);
    }

    public function storeUnit(Request $request): JsonResponse
    {
        $data = $request->validate(['Nama' => ['required', 'string', 'max:50']]);
        $name = UnitNormalizer::normalize($data['Nama']);
        $unit = BaseUnit::firstOrCreate(['Nama' => $name]);

        return response()->json(['success' => true, 'message' => 'Satuan siap digunakan.', 'data' => ['nama' => $unit->Nama]], 201);
    }

    public function updateSupplier(Request $request, string $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);
        $data = $request->validate([
            'Nama' => ['required', 'string', 'max:255'], 'No_Kontak' => ['nullable', 'regex:/^\d*$/', 'max:20'],
            'Email' => ['nullable', 'email', 'max:255'], 'Alamat' => ['nullable', 'string', 'max:500'],
        ]);
        $supplier->update(['Nama' => trim($data['Nama']), 'No_Kontak' => $data['No_Kontak'] ?? null,
            'Kontak' => $data['No_Kontak'] ?? null, 'Email' => $data['Email'] ?? null, 'Alamat' => $data['Alamat'] ?? null]);
        ActivityLog::record("Admin memperbarui Supplier [{$supplier->Nama}] melalui aplikasi Flutter.");

        return response()->json(['success' => true, 'message' => 'Data supplier berhasil diperbarui.']);
    }

    public function updateCustomer(Request $request, string $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $data = $request->validate([
            'Nama' => ['required', 'string', 'max:255'], 'No_Kontak' => ['nullable', 'regex:/^\d*$/', 'max:20'],
            'Email' => ['nullable', 'email', 'max:255'], 'Alamat' => ['nullable', 'string', 'max:500'],
        ]);
        $customer->update(['Nama' => trim($data['Nama']), 'No_Kontak' => $data['No_Kontak'] ?? null,
            'Kontak' => $data['No_Kontak'] ?? null, 'Email' => $data['Email'] ?? null, 'Alamat' => $data['Alamat'] ?? null]);
        ActivityLog::record("Admin memperbarui Customer [{$customer->Nama}] melalui aplikasi Flutter.");

        return response()->json(['success' => true, 'message' => 'Data customer berhasil diperbarui.']);
    }

    public function storeRack(Request $request): JsonResponse
    {
        $data = $request->validate(['Kode_Rak' => ['required', 'string', 'max:50', 'unique:rack_locations,Kode_Rak'],
            'Aisle' => ['required', 'string', 'max:20'], 'Level' => ['required', 'string', 'max:20'], 'Kapasitas' => ['required', 'integer', 'min:1', 'max:2147483647']]);
        $rack = RackLocation::create($data);
        ActivityLog::record("Admin membuat Lokasi Rak [{$rack->Kode_Rak}] melalui aplikasi Flutter.");

        return response()->json(['success' => true, 'message' => 'Lokasi rak berhasil ditambahkan.', 'data' => ['id' => $rack->Rack_ID]], 201);
    }

    public function updateRack(Request $request, string $id): JsonResponse
    {
        $rack = RackLocation::findOrFail($id);
        $used = max(0, InboundDetail::where('Rack_ID', $id)->sum('Qty') - OutboundDetail::where('Rack_ID', $id)
            ->whereHas('outboundTransaction', fn ($q) => $q->where('transaction_status', 'active')->where('picking_status', 'complete'))->sum('Qty'));
        $data = $request->validate(['Kode_Rak' => ['required', 'string', 'max:50', 'unique:rack_locations,Kode_Rak,'.$id.',Rack_ID'],
            'Aisle' => ['required', 'string', 'max:20'], 'Level' => ['required', 'string', 'max:20'], 'Kapasitas' => ['required', 'integer', 'min:'.$used, 'max:2147483647']]);
        $rack->update($data);
        ActivityLog::record("Admin memperbarui Lokasi Rak [{$rack->Kode_Rak}] melalui aplikasi Flutter.");

        return response()->json(['success' => true, 'message' => 'Lokasi rak berhasil diperbarui.']);
    }

    public function deleteRack(string $id): JsonResponse
    {
        $rack = RackLocation::findOrFail($id);
        $inboundHistory = InboundDetail::withTrashed()->where('Rack_ID', $id)->count();
        $outboundHistory = OutboundDetail::withTrashed()->where('Rack_ID', $id)->count();
        $registeredItems = MasterBarang::where('Rack_ID', $id)->count();
        abort_if($inboundHistory > 0 || $outboundHistory > 0 || $registeredItems > 0, 422,
            "Rak {$rack->Kode_Rak} tidak dapat dihapus karena masih terhubung dengan {$registeredItems} barang, {$inboundHistory} detail inbound, dan {$outboundHistory} detail outbound. Riwayat transaksi yang dibatalkan tetap dihitung untuk menjaga audit.");
        $code = $rack->Kode_Rak; $rack->delete();
        ActivityLog::record("Admin menghapus Lokasi Rak [{$code}] melalui aplikasi Flutter.");

        return response()->json(['success' => true, 'message' => 'Lokasi rak berhasil dihapus.']);
    }

    public function racks(Request $request): JsonResponse
    {
        $query = RackLocation::withSum('inboundDetails as inbound_qty', 'Qty')->withSum('completedOutboundDetails as outbound_qty', 'Qty')->orderBy('Kode_Rak');
        if ($request->filled('search')) {
            $needle = '%'.strtolower($request->string('search')->toString()).'%';
            $query->where(fn ($q) => $q->whereRaw('LOWER("Kode_Rak") LIKE ?', [$needle])->orWhereRaw('LOWER("Aisle") LIKE ?', [$needle]));
        }
        return response()->json(['success' => true, 'data' => $query->get()->map(function ($rack) {
            $used = max(0, (int) ($rack->inbound_qty ?? 0) - (int) ($rack->outbound_qty ?? 0));
            $ratio = $rack->Kapasitas > 0 ? $used / $rack->Kapasitas : 0;
            return ['rack_id' => $rack->Rack_ID, 'kode_rak' => $rack->Kode_Rak, 'aisle' => $rack->Aisle, 'level' => $rack->Level,
                'kapasitas' => $rack->Kapasitas, 'kapasitas_terpakai' => $used,
                'status_kapasitas' => $ratio >= 1 ? 'Penuh' : ($ratio >= .8 ? 'Hampir Penuh' : 'Tersedia')];
        })]);
    }

    public function rack(Request $request, string $id): JsonResponse
    {
        $rack = RackLocation::findOrFail($id);
        $inbound = InboundDetail::where('Rack_ID', $id)->selectRaw('"SKU", SUM("Qty") as qty')->groupBy('SKU')->pluck('qty', 'SKU');
        $completed = OutboundDetail::where('Rack_ID', $id)->whereHas('outboundTransaction', fn ($q) => $q
            ->where('transaction_status', 'active')->where('picking_status', 'complete'))
            ->selectRaw('"SKU", SUM("Qty") as qty')->groupBy('SKU')->pluck('qty', 'SKU');
        $reserved = OutboundDetail::where('Rack_ID', $id)->whereHas('outboundTransaction', fn ($q) => $q
            ->where('transaction_status', 'active')->where('picking_status', 'not_complete'))
            ->selectRaw('"SKU", SUM("Qty") as qty')->groupBy('SKU')->pluck('qty', 'SKU');
        $items = MasterBarang::whereIn('SKU', $inbound->keys())->orderBy('Nama')->get()->map(function ($item) use ($inbound, $completed, $reserved) {
            $physical = max(0, (int) $inbound->get($item->SKU, 0) - (int) $completed->get($item->SKU, 0));
            $held = (int) $reserved->get($item->SKU, 0);
            return ['sku' => $item->SKU, 'nama' => $item->Nama, 'kategori' => $item->Kategori, 'satuan' => $item->Satuan,
                'stok_fisik' => $physical, 'stok_direservasi' => $held, 'stok_tersedia' => max(0, $physical - $held)];
        })->filter(fn ($item) => $item['stok_fisik'] > 0)->values();
        $used = $items->sum('stok_fisik');
        $others = RackLocation::whereKeyNot($id)->withSum('inboundDetails as in_qty', 'Qty')
            ->withSum('completedOutboundDetails as out_qty', 'Qty')->orderBy('Kode_Rak')->get()->map(function ($row) {
                $used = max(0, (int) ($row->in_qty ?? 0) - (int) ($row->out_qty ?? 0));
                return ['id' => $row->Rack_ID, 'kode_rak' => $row->Kode_Rak, 'sisa' => max(0, $row->Kapasitas - $used)];
            })->filter(fn ($row) => $row['sisa'] > 0)->values();

        return response()->json(['success' => true, 'data' => [
            'rack_id' => $rack->Rack_ID, 'kode_rak' => $rack->Kode_Rak, 'aisle' => $rack->Aisle, 'level' => $rack->Level,
            'kapasitas' => (int) $rack->Kapasitas, 'kapasitas_terpakai' => $used,
            'has_photo' => filled($rack->foto_path),
            'foto_url' => $rack->foto_path ? rtrim($request->root(), '/').Storage::url($rack->foto_path) : null,
            'items' => $items, 'other_racks' => $others,
        ]]);
    }

    public function rackPhoto(string $id)
    {
        $rack = RackLocation::findOrFail($id);
        abort_unless(
            filled($rack->foto_path) && Storage::disk('public')->exists($rack->foto_path),
            404,
            'Foto rak tidak ditemukan atau file sudah dihapus.'
        );

        return Storage::disk('public')->response($rack->foto_path, basename($rack->foto_path), [
            'Cache-Control' => 'private, no-cache, must-revalidate',
            'Content-Disposition' => 'inline',
        ]);
    }

    public function uploadRackPhoto(Request $request, string $id): JsonResponse
    {
        $rack = RackLocation::findOrFail($id);
        $request->validate(['foto' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048']]);
        if ($rack->foto_path) Storage::disk('public')->delete($rack->foto_path);
        $rack->update(['foto_path' => $request->file('foto')->store('rak-foto', 'public')]);
        ActivityLog::record("Admin mengupload foto Rak {$rack->Kode_Rak} melalui aplikasi Flutter.");
        return response()->json(['success' => true, 'message' => 'Foto rak berhasil disimpan.', 'data' => [
            'foto_url' => rtrim($request->root(), '/').Storage::url($rack->foto_path),
        ]]);
    }

    public function deleteRackPhoto(string $id): JsonResponse
    {
        $rack = RackLocation::findOrFail($id);
        if ($rack->foto_path) Storage::disk('public')->delete($rack->foto_path);
        $rack->update(['foto_path' => null]);
        ActivityLog::record("Admin menghapus foto Rak {$rack->Kode_Rak} melalui aplikasi Flutter.");
        return response()->json(['success' => true, 'message' => 'Foto rak berhasil dihapus.']);
    }

    public function moveRackItem(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'sku' => ['required', 'exists:master_barang,SKU'],
            'new_rack_id' => ['required', 'exists:rack_locations,Rack_ID'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);
        abort_if($data['new_rack_id'] === $id, 422, 'Rak tujuan tidak boleh sama dengan rak asal.');
        $moved = DB::transaction(function () use ($data, $id) {
            $item = MasterBarang::whereKey($data['sku'])->lockForUpdate()->firstOrFail();
            $racks = RackLocation::whereIn('Rack_ID', [$id, $data['new_rack_id']])->orderBy('Rack_ID')->lockForUpdate()->get()->keyBy('Rack_ID');
            $source = $racks->get($id) ?? abort(404);
            $target = $racks->get($data['new_rack_id']) ?? abort(404);
            $physical = InboundDetail::where('SKU', $item->SKU)->where('Rack_ID', $id)->sum('Qty')
                - OutboundDetail::where('SKU', $item->SKU)->where('Rack_ID', $id)->whereHas('outboundTransaction', fn ($q) => $q
                    ->where('transaction_status', 'active')->where('picking_status', 'complete'))->sum('Qty');
            $reserved = OutboundDetail::where('SKU', $item->SKU)->where('Rack_ID', $id)->whereHas('outboundTransaction', fn ($q) => $q
                ->where('transaction_status', 'active')->where('picking_status', 'not_complete'))->sum('Qty');
            $available = max(0, $physical - $reserved);
            abort_if($data['qty'] > $available, 422, "Qty melebihi stok tersedia di rak {$source->Kode_Rak} ({$available} {$item->Satuan}).");
            $targetUsed = InboundDetail::where('Rack_ID', $target->Rack_ID)->sum('Qty')
                - OutboundDetail::where('Rack_ID', $target->Rack_ID)->whereHas('outboundTransaction', fn ($q) => $q
                    ->where('transaction_status', 'active')->where('picking_status', 'complete'))->sum('Qty');
            abort_if($data['qty'] > max(0, $target->Kapasitas - $targetUsed), 422, "Kapasitas rak {$target->Kode_Rak} tidak mencukupi.");
            $remaining = (int) $data['qty'];
            foreach (InboundDetail::where('SKU', $item->SKU)->where('Rack_ID', $id)->orderBy('created_at')->get() as $detail) {
                if ($remaining <= 0) break;
                if ($detail->Qty <= $remaining) {
                    $remaining -= $detail->Qty; $detail->update(['Rack_ID' => $target->Rack_ID]);
                } else {
                    $detail->update(['Qty' => $detail->Qty - $remaining]);
                    InboundDetail::create(['Inbound_ID' => $detail->Inbound_ID, 'SKU' => $detail->SKU, 'Rack_ID' => $target->Rack_ID,
                        'Qty' => $remaining, 'Harga_Satuan' => $detail->Harga_Satuan, 'No_Resi_Supplier' => $detail->No_Resi_Supplier, 'Batch' => $detail->Batch]);
                    $remaining = 0;
                }
            }
            abort_if($remaining > 0, 422, 'Detail stok tidak cukup untuk relokasi.');
            if ($physical - $data['qty'] <= 0) $item->update(['Rack_ID' => $target->Rack_ID]);
            ActivityLog::record("Barang [{$item->SKU} - {$item->Nama}] dipindah {$data['qty']} {$item->Satuan} dari rak [{$source->Kode_Rak}] ke [{$target->Kode_Rak}] melalui aplikasi Flutter.");
            return ['qty' => (int) $data['qty'], 'item' => $item->Nama, 'target' => $target->Kode_Rak];
        });
        WarehouseCache::clearDashboard();

        return response()->json(['success' => true, 'message' => "{$moved['qty']} unit {$moved['item']} berhasil dipindahkan ke {$moved['target']}."]);
    }

    public function inbounds(Request $request): JsonResponse
    {
        $query = InboundTransaction::with('supplier')->withSum('allInboundDetails as total_qty', 'Qty')->latest('Tanggal')->latest('Inbound_ID');
        if ($request->filled('search')) {
            $needle = '%'.strtolower(trim($request->string('search')->toString())).'%';
            $query->where(fn ($q) => $q->whereRaw('LOWER("No_Receiving") LIKE ?', [$needle])
                ->orWhereHas('supplier', fn ($supplier) => $supplier->whereRaw('LOWER("Nama") LIKE ?', [$needle])));
        }
        return $this->paginated($query->paginate(15), fn ($row) => [
            'inbound_id' => $row->Inbound_ID, 'no_receiving' => $row->No_Receiving, 'tanggal' => $row->Tanggal?->format('Y-m-d'),
            'supplier' => $row->supplier?->Nama ?? '-', 'total_qty' => (int) ($row->total_qty ?? 0), 'transaction_status' => $row->transaction_status,
        ]);
    }

    public function inboundOptions(): JsonResponse
    {
        $racks = RackLocation::withSum('inboundDetails as in_qty', 'Qty')->withSum('completedOutboundDetails as out_qty', 'Qty')->orderBy('Kode_Rak')->get()->map(function ($rack) {
            $used = max(0, (int) ($rack->in_qty ?? 0) - (int) ($rack->out_qty ?? 0));
            return ['id' => $rack->Rack_ID, 'kode_rak' => $rack->Kode_Rak, 'sisa' => max(0, $rack->Kapasitas - $used)];
        })->filter(fn ($rack) => $rack['sisa'] > 0)->values();
        return response()->json(['success' => true, 'data' => [
            'suppliers' => Supplier::orderBy('Nama')->get()->map(fn ($row) => ['id' => $row->Supplier_ID, 'nama' => $row->Nama]),
            'items' => $this->stockQuery()->orderBy('Nama')->get()->map(fn ($item) => $this->itemPayload($item)),
            'racks' => $racks, 'categories' => MasterBarang::distinct()->orderBy('Kategori')->pluck('Kategori')->filter()->values(),
            'units' => BaseUnit::orderBy('Nama')->pluck('Nama')->values(),
            'practice_session' => PracticeSession::current()?->only(['Practice_Session_ID', 'Nama', 'Kelas', 'Status']),
        ]]);
    }

    public function storeInbound(Request $request, DocumentNumberService $numbers, SkuNumberService $skuNumbers): JsonResponse
    {
        $data = $request->validate([
            'Tanggal' => ['required', 'date', 'before_or_equal:today'], 'Supplier_ID' => ['required', 'exists:suppliers,Supplier_ID'],
            'Catatan' => ['nullable', 'string'], 'items' => ['required', 'array', 'min:1'],
            'items.*.jenis' => ['required', 'in:lama,baru'], 'items.*.Qty' => ['required', 'integer', 'min:1'],
            'items.*.SKU_lama' => ['exclude_unless:items.*.jenis,lama', 'required', 'exists:master_barang,SKU'],
            'items.*.Rack_ID_lama' => ['exclude_unless:items.*.jenis,lama', 'required', 'exists:rack_locations,Rack_ID'],
            'items.*.Nama_baru' => ['exclude_unless:items.*.jenis,baru', 'required', 'string', 'max:255'],
            'items.*.Kategori_baru' => ['exclude_unless:items.*.jenis,baru', 'required', 'string', 'max:100'],
            'items.*.Satuan_baru' => ['exclude_unless:items.*.jenis,baru', 'required', 'string', 'max:50'],
            'items.*.Rack_ID_baru' => ['exclude_unless:items.*.jenis,baru', 'required', 'exists:rack_locations,Rack_ID'],
            'items.*.Min_Stok_baru' => ['exclude_unless:items.*.jenis,baru', 'required', 'integer', 'min:0'],
            'items.*.Harga_Satuan' => ['exclude_unless:items.*.jenis,baru', 'required', 'integer', 'min:0'],
            'items.*.No_Resi_Supplier' => ['nullable', 'string', 'max:100'],
            'items.*.tanpa_resi' => ['required', 'boolean'],
        ]);
        foreach ($data['items'] as $index => $item) {
            if (! $item['tanpa_resi'] && blank($item['No_Resi_Supplier'] ?? null)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "items.$index.No_Resi_Supplier" => 'Nomor resi wajib diisi atau pilih Tidak ada resi.',
                ]);
            }
        }
        $session = PracticeSession::current();
        abort_unless($session, 422, 'Belum ada sesi praktikum aktif.');
        $byRack = collect($data['items'])->groupBy(fn ($item) => $item['jenis'] === 'lama' ? $item['Rack_ID_lama'] : $item['Rack_ID_baru'])->map(fn ($rows) => (int) $rows->sum('Qty'));
        foreach ($byRack as $rackId => $qty) {
            $rack = RackLocation::withSum('inboundDetails as in_qty', 'Qty')->withSum('completedOutboundDetails as out_qty', 'Qty')->findOrFail($rackId);
            $remaining = max(0, $rack->Kapasitas - max(0, (int) ($rack->in_qty ?? 0) - (int) ($rack->out_qty ?? 0)));
            abort_if($qty > $remaining, 422, "Total Qty ke rak {$rack->Kode_Rak} melebihi sisa kapasitas {$remaining}.");
        }
        $newNames = collect($data['items'])->where('jenis', 'baru')->pluck('Nama_baru')->map(fn ($name) => strtolower(trim($name)));
        abort_if($newNames->duplicates()->isNotEmpty(), 422, 'Nama barang baru tidak boleh duplikat dalam satu transaksi.');
        foreach ($newNames as $name) abort_if(MasterBarang::whereRaw('LOWER("Nama") = ?', [$name])->exists(), 422, "Barang {$name} sudah terdaftar. Gunakan Barang Lama.");

        $inbound = DB::transaction(function () use ($request, $data, $session, $numbers, $skuNumbers) {
            $row = InboundTransaction::create(['No_Receiving' => $numbers->next('RSI', $data['Tanggal']), 'Tanggal' => $data['Tanggal'],
                'Supplier_ID' => $data['Supplier_ID'], 'User_ID' => $request->user()->id, 'Catatan' => filled($data['Catatan'] ?? null) ? trim($data['Catatan']) : null,
                'Practice_Session_ID' => $session->Practice_Session_ID]);
            foreach ($data['items'] as $item) {
                if ($item['jenis'] === 'lama') {
                    $barang = MasterBarang::lockForUpdate()->findOrFail($item['SKU_lama']);
                    [$sku, $rackId, $price] = [$barang->SKU, $item['Rack_ID_lama'], $barang->Harga_Dasar];
                } else {
                    $unit = UnitNormalizer::normalize($item['Satuan_baru']); BaseUnit::firstOrCreate(['Nama' => $unit]);
                    $prefix = str_pad(strtoupper(substr(preg_replace('/[aeiou\s]/i', '', $item['Kategori_baru']), 0, 3)), 3, 'X');
                    $sku = $skuNumbers->next($prefix); $rackId = $item['Rack_ID_baru']; $price = (int) $item['Harga_Satuan'];
                    MasterBarang::create(['SKU' => $sku, 'Nama' => trim($item['Nama_baru']), 'Kategori' => trim($item['Kategori_baru']),
                        'Satuan' => $unit, 'Harga_Dasar' => $price, 'Rack_ID' => $rackId, 'Min_Stok' => (int) $item['Min_Stok_baru'],
                        'Created_From_Inbound_ID' => $row->Inbound_ID]);
                }
                InboundDetail::create(['Inbound_ID' => $row->Inbound_ID, 'SKU' => $sku, 'Rack_ID' => $rackId, 'Qty' => (int) $item['Qty'],
                    'Harga_Satuan' => $price, 'No_Resi_Supplier' => $item['tanpa_resi'] ? null : trim($item['No_Resi_Supplier'])]);
            }
            return $row;
        });
        WarehouseCache::clearDashboard();
        ActivityLog::record("Transaksi Inbound [{$inbound->No_Receiving}] dibuat melalui aplikasi Flutter.");
        return response()->json(['success' => true, 'message' => "Inbound {$inbound->No_Receiving} berhasil dibuat.", 'data' => ['inbound_id' => $inbound->Inbound_ID]], 201);
    }

    public function inbound(string $id): JsonResponse
    {
        $row = InboundTransaction::with(['supplier', 'user', 'allInboundDetails.masterBarang', 'allInboundDetails.rackLocation'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => [
            'inbound_id' => $row->Inbound_ID, 'no_receiving' => $row->No_Receiving, 'tanggal' => $row->Tanggal?->format('Y-m-d'),
            'supplier' => ['nama' => $row->supplier?->Nama ?? '-', 'no_kontak' => $row->supplier?->No_Kontak],
            'operator' => $row->user?->name ?? '-', 'catatan' => $row->Catatan, 'transaction_status' => $row->transaction_status,
            'total_nilai' => (int) $row->total_nilai,
            'details' => $row->allInboundDetails->map(fn ($d) => [
                'sku' => $d->SKU, 'nama_barang' => $d->masterBarang?->Nama ?? '-', 'rack' => $d->rackLocation?->Kode_Rak ?? '-',
                'qty' => $d->Qty, 'satuan' => $d->masterBarang?->Satuan ?? 'PCS', 'harga_satuan' => $d->Harga_Satuan, 'subtotal' => $d->subtotal,
            ]),
        ]]);
    }

    public function outbounds(Request $request): JsonResponse
    {
        $query = OutboundTransaction::with('customer')->withSum('allOutboundDetails as total_qty', 'Qty')->latest('Tanggal')->latest('Outbound_ID');
        if ($request->filled('search')) {
            $needle = '%'.strtolower(trim($request->string('search')->toString())).'%';
            $query->where(fn ($q) => $q->whereRaw('LOWER("No_Shipping") LIKE ?', [$needle])
                ->orWhereHas('customer', fn ($customer) => $customer->whereRaw('LOWER("Nama") LIKE ?', [$needle])));
        }
        if ($request->input('status') === 'not_complete') $query->where('transaction_status', 'active')->where('picking_status', 'not_complete');
        if ($request->input('status') === 'complete') $query->where(fn ($q) => $q->where('picking_status', 'complete')->orWhere('transaction_status', 'cancelled'));
        return $this->paginated($query->paginate(15), fn ($row) => [
            'outbound_id' => $row->Outbound_ID, 'no_shipping' => $row->No_Shipping, 'tanggal' => $row->Tanggal?->format('Y-m-d'),
            'customer' => $row->customer?->Nama ?? '-', 'total_qty' => (int) ($row->total_qty ?? 0),
            'priority' => $row->priority, 'priority_label' => $row->priorityLabel(), 'picking_status' => $row->picking_status,
            'transaction_status' => $row->transaction_status,
        ]);
    }

    public function outboundOptions(): JsonResponse
    {
        $items = $this->stockQuery()->with('rackLocation')->orderBy('Nama')->get()
            ->map(fn ($item) => $this->itemPayload($item))->filter(fn ($item) => $item['stok'] > 0)->values();
        return response()->json(['success' => true, 'data' => [
            'customers' => Customer::orderBy('Nama')->get()->map(fn ($row) => ['id' => $row->Customer_ID, 'nama' => $row->Nama]),
            'items' => $items,
            'practice_session' => PracticeSession::current()?->only(['Practice_Session_ID', 'Nama', 'Kelas', 'Status']),
        ]]);
    }

    public function storeOutbound(Request $request, StockAllocationService $allocation, DocumentNumberService $numbers): JsonResponse
    {
        $data = $request->validate([
            'Tanggal' => ['required', 'date', 'before_or_equal:today'],
            'Customer_ID' => ['required', 'exists:customers,Customer_ID'],
            'Nama_Penerima' => ['required', 'string', 'max:255'],
            'Catatan' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.SKU' => ['required', 'exists:master_barang,SKU'],
            'items.*.Qty' => ['required', 'integer', 'min:1'],
        ]);
        $session = PracticeSession::current();
        abort_unless($session, 422, 'Belum ada sesi praktikum aktif.');
        $requested = collect($data['items'])->groupBy('SKU')->map(fn ($rows) => (int) $rows->sum('Qty'));

        $outbound = DB::transaction(function () use ($request, $data, $requested, $session, $allocation, $numbers) {
            $allocations = [];
            foreach ($requested as $sku => $qty) {
                MasterBarang::whereKey($sku)->lockForUpdate()->firstOrFail();
                $allocations[$sku] = $allocation->allocate($sku, $qty);
            }
            $total = $requested->sum();
            $row = OutboundTransaction::create([
                'No_Shipping' => $numbers->next('SJ', $data['Tanggal']), 'Tanggal' => $data['Tanggal'],
                'Customer_ID' => $data['Customer_ID'], 'User_ID' => $request->user()->id,
                'picking_status' => 'not_complete', 'priority' => $total > 50 ? 'high' : ($total > 10 ? 'normal' : 'decent'),
                'Nama_Penerima' => trim($data['Nama_Penerima']), 'Catatan' => filled($data['Catatan'] ?? null) ? trim($data['Catatan']) : null,
                'Practice_Session_ID' => $session->Practice_Session_ID,
            ]);
            foreach ($allocations as $sku => $parts) foreach ($parts as $part) {
                OutboundDetail::create(['Outbound_ID' => $row->Outbound_ID, 'SKU' => $sku, 'Rack_ID' => $part['rack_id'], 'Qty' => $part['qty']]);
            }
            return $row;
        });
        WarehouseCache::clearDashboard();
        ActivityLog::record("Transaksi Outbound baru dibuat dengan No. [{$outbound->No_Shipping}] melalui aplikasi Flutter.");
        return response()->json(['success' => true, 'message' => "Outbound {$outbound->No_Shipping} berhasil dibuat.", 'data' => ['outbound_id' => $outbound->Outbound_ID]], 201);
    }

    public function outbound(string $id): JsonResponse
    {
        $row = OutboundTransaction::with(['customer', 'user', 'allOutboundDetails.masterBarang', 'allOutboundDetails.rackLocation'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => [
            'outbound_id' => $row->Outbound_ID, 'no_shipping' => $row->No_Shipping, 'tanggal' => $row->Tanggal?->format('Y-m-d'),
            'customer' => ['nama' => $row->customer?->Nama ?? '-', 'no_kontak' => $row->customer?->No_Kontak],
            'operator' => $row->user?->name ?? '-', 'nama_penerima' => $row->Nama_Penerima, 'catatan' => $row->Catatan,
            'priority' => $row->priority, 'priority_label' => $row->priorityLabel(), 'is_complete' => $row->isComplete(),
            'transaction_status' => $row->transaction_status,
            'details' => $row->allOutboundDetails->map(fn ($d) => [
                'detail_id' => $d->Detail_ID, 'sku' => $d->SKU, 'nama_barang' => $d->masterBarang?->Nama ?? '-', 'rack' => $d->rackLocation?->Kode_Rak ?? '-',
                'qty' => $d->Qty, 'satuan' => $d->masterBarang?->Satuan ?? 'PCS',
            ]),
        ]]);
    }

    public function outboundDocumentLink(Request $request, string $id): JsonResponse
    {
        $outbound = OutboundTransaction::findOrFail($id);
        abort_unless($outbound->isComplete(), 422, 'Surat jalan tersedia setelah picking selesai.');

        return response()->json(['success' => true, 'data' => [
            'url' => $this->signedUrl($request, 'api.files.delivery-note', ['id' => $id]),
        ]]);
    }

    public function completePicking(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'confirmed_detail_ids' => ['required', 'array', 'min:1'],
            'confirmed_detail_ids.*' => ['required', 'uuid', 'distinct'],
        ]);
        $row = DB::transaction(function () use ($data, $id) {
            $outbound = OutboundTransaction::with('outboundDetails')->lockForUpdate()->findOrFail($id);
            abort_if($outbound->isCancelled(), 422, 'Transaksi sudah dibatalkan.');
            $expected = $outbound->outboundDetails->pluck('Detail_ID')->map(fn ($value) => (string) $value)->sort()->values();
            $confirmed = collect($data['confirmed_detail_ids'])->map(fn ($value) => (string) $value)->sort()->values();
            abort_unless(
                $expected->isNotEmpty() && $expected->all() === $confirmed->all(),
                422,
                'Centang seluruh barang pada picking list sebelum menyelesaikan picking.'
            );
            if (! $outbound->isComplete()) $outbound->update(['picking_status' => 'complete']);
            return $outbound;
        });
        WarehouseCache::clearDashboard();
        ActivityLog::record("Picking List untuk Outbound [{$row->No_Shipping}] ditandai selesai melalui aplikasi Flutter.");
        return response()->json(['success' => true, 'message' => 'Picking berhasil diselesaikan.']);
    }

    public function cancelInbound(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:500']]);
        $row = DB::transaction(function () use ($request, $data, $id) {
            $inbound = InboundTransaction::with('inboundDetails')->lockForUpdate()->findOrFail($id);
            abort_if($inbound->isCancelled(), 422, 'Transaksi sudah dibatalkan.');
            $requested = $inbound->inboundDetails->groupBy(fn ($detail) => $detail->SKU.'|'.$detail->Rack_ID)->map(fn ($rows) => (int) $rows->sum('Qty'));
            MasterBarang::whereIn('SKU', $inbound->inboundDetails->pluck('SKU')->unique())->lockForUpdate()->get();
            foreach ($requested as $key => $qty) {
                [$sku, $rackId] = explode('|', $key, 2);
                $used = OutboundDetail::where('SKU', $sku)->where('Rack_ID', $rackId)->sum('Qty');
                $received = InboundDetail::where('SKU', $sku)->where('Rack_ID', $rackId)->sum('Qty');
                abort_if($received - $used - $qty < 0, 422, "Inbound tidak dapat dibatalkan karena stok {$sku} sudah digunakan.");
            }
            $affectedSkus = $inbound->inboundDetails->pluck('SKU')->unique()->values();
            $inbound->inboundDetails->each->delete();
            $inbound->update(['transaction_status' => 'cancelled', 'Cancelled_At' => now(), 'Cancelled_By' => $request->user()->id, 'Cancellation_Reason' => trim($data['reason'])]);
            MasterBarang::whereIn('SKU', $affectedSkus)
                ->where('Created_From_Inbound_ID', $inbound->Inbound_ID)
                ->whereDoesntHave('inboundDetails')
                ->whereDoesntHave('outboundDetails')
                ->whereDoesntHave('stockOpnames')
                ->get()
                ->each
                ->delete();
            return $inbound;
        });
        WarehouseCache::clearDashboard(); ActivityLog::record("Transaksi Inbound [{$row->No_Receiving}] dibatalkan melalui aplikasi Flutter.");
        return response()->json(['success' => true, 'message' => 'Inbound berhasil dibatalkan.']);
    }

    public function cancelOutbound(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:500']]);
        $row = DB::transaction(function () use ($request, $data, $id) {
            $outbound = OutboundTransaction::with('outboundDetails')->lockForUpdate()->findOrFail($id);
            abort_if($outbound->isCancelled(), 422, 'Transaksi sudah dibatalkan.');
            $outbound->outboundDetails->each->delete();
            $outbound->update(['transaction_status' => 'cancelled', 'Cancelled_At' => now(), 'Cancelled_By' => $request->user()->id, 'Cancellation_Reason' => trim($data['reason'])]);
            return $outbound;
        });
        WarehouseCache::clearDashboard(); ActivityLog::record("Transaksi Outbound [{$row->No_Shipping}] dibatalkan melalui aplikasi Flutter.");
        return response()->json(['success' => true, 'message' => 'Outbound berhasil dibatalkan.']);
    }

    public function activityLogs(Request $request): JsonResponse
    {
        $query = ActivityLog::with('user')->latest();
        return $this->paginated($query->paginate(30), fn ($row) => ['id' => $row->id, 'operator' => $row->operator_name,
            'action' => $row->action, 'created_at' => $row->created_at?->toIso8601String()]);
    }

    public function reportLinks(Request $request): JsonResponse
    {
        $expires = now()->addMinutes(10);
        $link = fn (string $route) => $request->root().URL::temporarySignedRoute($route, $expires, [], false);
        return response()->json(['success' => true, 'data' => [
            'inventory' => $link('api.reports.inventory'),
            'inbound' => $link('api.reports.inbound'),
            'outbound' => $link('api.reports.outbound'),
            'expires_at' => $expires->toIso8601String(),
        ]]);
    }

    public function practiceSessions(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => PracticeSession::latest('Tanggal')->get()->map(fn ($row) => [
            'id' => $row->Practice_Session_ID, 'name' => $row->Nama, 'class' => $row->Kelas,
            'date' => $row->Tanggal?->format('Y-m-d'), 'status' => $row->Status,
        ])]);
    }

    public function storePracticeSession(Request $request): JsonResponse
    {
        abort_if(PracticeSession::current(), 422, 'Masih ada sesi praktikum aktif.');
        $data = $request->validate(['name' => ['required', 'string', 'max:150'], 'class' => ['nullable', 'string', 'max:100'], 'date' => ['required', 'date']]);
        $row = PracticeSession::create(['Nama' => trim($data['name']), 'Kelas' => filled($data['class'] ?? null) ? trim($data['class']) : null,
            'Tanggal' => $data['date'], 'Status' => 'active', 'Created_By' => $request->user()->id, 'Opened_At' => now()]);
        ActivityLog::record("Sesi praktikum [{$row->Nama}] dibuka melalui aplikasi Flutter.");
        return response()->json(['success' => true, 'message' => 'Sesi praktikum berhasil dibuka.', 'data' => ['id' => $row->Practice_Session_ID]], 201);
    }

    public function closePracticeSession(string $id): JsonResponse
    {
        $row = PracticeSession::findOrFail($id);
        abort_if(OutboundTransaction::where('Practice_Session_ID', $id)->where('transaction_status', 'active')->where('picking_status', 'not_complete')->exists(), 422, 'Selesaikan atau batalkan seluruh picking sebelum menutup sesi.');
        $row->update(['Status' => 'closed', 'Closed_At' => now()]);
        ActivityLog::record("Sesi praktikum [{$row->Nama}] ditutup melalui aplikasi Flutter.");
        return response()->json(['success' => true, 'message' => 'Sesi praktikum berhasil ditutup.']);
    }

    public function inventory(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->stockQuery()->with('rackLocation')->orderBy('Nama')->get()->map(fn ($item) => $this->itemPayload($item))]);
    }

    public function inventoryDetail(string $sku): JsonResponse
    {
        $item = $this->stockQuery()->with('rackLocation')->findOrFail($sku);
        $mutations = collect();
        InboundDetail::with('inboundTransaction.user')->where('SKU', $sku)->get()->each(function ($d) use ($mutations) {
            $mutations->push(['tanggal' => $d->inboundTransaction->Tanggal, 'jenis' => 'Inbound', 'no_ref' => $d->inboundTransaction->No_Receiving,
                'qty_in' => $d->Qty, 'qty_out' => 0, 'operator' => $d->inboundTransaction->user?->name ?? '-']);
        });
        OutboundDetail::with('outboundTransaction.user')->where('SKU', $sku)->get()->each(function ($d) use ($mutations) {
            $mutations->push(['tanggal' => $d->outboundTransaction->Tanggal, 'jenis' => 'Outbound', 'no_ref' => $d->outboundTransaction->No_Shipping,
                'qty_in' => 0, 'qty_out' => $d->Qty, 'operator' => $d->outboundTransaction->user?->name ?? '-']);
        });
        $saldo = 0;
        $mutations = $mutations->sortBy('tanggal')->map(function ($m) use (&$saldo) {
            $saldo += $m['qty_in'] - $m['qty_out'];
            $m['tanggal'] = $m['tanggal']?->format('Y-m-d'); $m['saldo'] = $saldo; return $m;
        })->reverse()->values();
        return response()->json(['success' => true, 'data' => ['barang' => $this->itemPayload($item), 'mutations' => $mutations]]);
    }

    public function stockOpnames(Request $request): JsonResponse
    {
        $query = StockOpname::with(['masterBarang', 'user'])->latest('Tanggal')->latest('Opname_ID');
        return $this->paginated($query->paginate(15), fn ($row) => [
            'opname_id' => $row->Opname_ID, 'sku' => $row->SKU, 'nama_barang' => $row->masterBarang?->Nama ?? '-',
            'tanggal' => $row->Tanggal?->format('Y-m-d'), 'kondisi' => $row->Kondisi, 'operator' => $row->user?->name ?? '-',
        ]);
    }

    public function storeStockOpname(Request $request): JsonResponse
    {
        $data = $request->validate(['SKU' => ['required', 'exists:master_barang,SKU'], 'Tanggal' => ['required', 'date', 'before_or_equal:today'], 'Kondisi' => ['required', 'string', 'min:5', 'max:2000']]);
        $session = PracticeSession::current();
        abort_unless($session, 422, 'Belum ada sesi praktikum aktif.');
        $row = StockOpname::create(['SKU' => $data['SKU'], 'Tanggal' => $data['Tanggal'], 'Kondisi' => trim($data['Kondisi']),
            'User_ID' => $request->user()->id, 'Practice_Session_ID' => $session->Practice_Session_ID]);
        ActivityLog::record("Stock Opname baru dibuat untuk [{$row->SKU}] melalui aplikasi Flutter.");
        return response()->json(['success' => true, 'message' => 'Catatan stock opname berhasil disimpan.', 'data' => ['opname_id' => $row->Opname_ID]], 201);
    }

    private function stockQuery(): Builder
    {
        return MasterBarang::query()->withSum('inboundDetails as inbound_qty', 'Qty')->withSum('outboundDetails as outbound_qty', 'Qty')
            ->withSum('completedOutboundDetails as completed_outbound_qty', 'Qty')->withSum('reservedOutboundDetails as reserved_qty', 'Qty');
    }

    private function transactionCount(Builder $query, string $period): int
    {
        $query->where('transaction_status', 'active');
        return match ($period) {
            '7_hari' => $query->whereDate('Tanggal', '>=', now()->subDays(6))->count(),
            '1_bulan' => $query->whereBetween('Tanggal', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            '1_tahun' => $query->whereYear('Tanggal', now()->year)->count(),
            'semua' => $query->count(),
            default => $query->whereDate('Tanggal', today())->count(),
        };
    }

    private function chart(string $period): array
    {
        $labels = []; $inbound = []; $outbound = [];
        $ranges = [];
        if ($period === 'setahun') {
            for ($month = 1; $month <= 12; $month++) {
                $start = Carbon::create(now()->year, $month)->startOfMonth();
                $ranges[] = [$start, $start->copy()->endOfMonth(), $start->format('M')];
            }
        } elseif ($period === 'sebulan') {
            for ($day = 1; $day <= now()->daysInMonth; $day += 5) {
                $start = now()->startOfMonth()->addDays($day - 1);
                $end = $start->copy()->addDays(4)->min(now()->endOfMonth());
                $ranges[] = [$start, $end, $start->format('d').'-'.$end->format('d M')];
            }
        } else {
            $start = $period === 'seminggu_ini' ? now()->startOfWeek() : now()->subDays(6)->startOfDay();
            for ($i = 0; $i < 7; $i++) {
                $date = $start->copy()->addDays($i);
                $ranges[] = [$date, $date, $date->format('d M')];
            }
        }
        foreach ($ranges as [$start, $end, $label]) {
            $labels[] = $label;
            $inbound[] = $this->sumChart('inbound_details', 'inbound_transactions', 'Inbound_ID', $start, $end);
            $outbound[] = $this->sumChart('outbound_details', 'outbound_transactions', 'Outbound_ID', $start, $end);
        }
        return compact('period', 'labels', 'inbound', 'outbound');
    }

    private function sumChart(string $details, string $transactions, string $key, Carbon $start, Carbon $end): int
    {
        return (int) DB::table($details)->join($transactions, "$details.$key", '=', "$transactions.$key")
            ->whereNull("$details.deleted_at")->where("$transactions.transaction_status", 'active')
            ->whereBetween("$transactions.Tanggal", [$start->toDateString(), $end->toDateString()])->sum("$details.Qty");
    }

    private function signedUrl(Request $request, string $route, array $parameters): string
    {
        $relative = URL::temporarySignedRoute($route, now()->addMinutes(15), $parameters, false);

        return rtrim($request->root(), '/').$relative;
    }

    private function itemPayload(MasterBarang $item): array
    {
        $available = max(0, (int) ($item->inbound_qty ?? 0) - (int) ($item->outbound_qty ?? 0));
        $physical = $this->physical($item);
        return ['sku' => $item->SKU, 'nama' => $item->Nama, 'kategori' => $item->Kategori, 'satuan' => $item->Satuan,
            'harga_dasar' => (int) $item->Harga_Dasar, 'min_stok' => (int) $item->Min_Stok, 'rack' => $item->rackLocation?->Kode_Rak ?? '-',
            'kode_rak' => $item->rackLocation?->Kode_Rak ?? '-', 'stok' => $available, 'stok_fisik' => $physical,
            'stok_direservasi' => (int) ($item->reserved_qty ?? 0),
            'status_stok' => $available <= 0 ? 'Habis' : ($available <= $item->Min_Stok ? 'Reorder' : 'Aman')];
    }

    private function physical(MasterBarang $item): int
    {
        return max(0, (int) ($item->inbound_qty ?? 0) - (int) ($item->completed_outbound_qty ?? 0));
    }

    private function perPage(Request $request): int
    {
        return min(100, max(1, $request->integer('per_page', 20)));
    }

    private function paginated(LengthAwarePaginator $page, callable $map): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $page->getCollection()->map($map)->values(), 'meta' => [
            'current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'per_page' => $page->perPage(), 'total' => $page->total(),
        ]]);
    }

    private function searchParty(Builder $query, Request $request): void
    {
        if (! $request->filled('search')) return;
        $needle = '%'.strtolower($request->string('search')->toString()).'%';
        $query->where(fn ($q) => $q->whereRaw('LOWER("Nama") LIKE ?', [$needle])->orWhereRaw('LOWER("Email") LIKE ?', [$needle]));
    }

    private function partyPayload($row, string $idField, int $count): array
    {
        return ['id' => $row->{$idField}, 'nama' => $row->Nama, 'no_kontak' => $row->No_Kontak ?? $row->Kontak,
            'email' => $row->Email, 'alamat' => $row->Alamat, 'total_transaksi' => $count];
    }
}
