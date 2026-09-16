<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\InboundDetail;
use App\Models\InboundTransaction;
use App\Models\MasterBarang;
use App\Models\OutboundDetail;
use App\Models\PracticeSession;
use App\Models\RackLocation;
use App\Models\Supplier;
use App\Models\User;
use App\Support\WarehouseCache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemoDataRepairService
{
    public function __construct(private readonly DocumentNumberService $documentNumbers) {}

    /** @return array{adjusted_units: int, moved_units: int, adjustment_number: ?string} */
    public function repair(): array
    {
        return DB::transaction(function (): array {
            MasterBarang::query()->lockForUpdate()->get();
            RackLocation::query()->lockForUpdate()->get();

            $deficits = $this->rackDeficits();
            $adjustedUnits = 0;
            $adjustment = null;

            if ($deficits->isNotEmpty()) {
                $supplier = Supplier::firstOrFail();
                $user = User::where('role', 'admin')->first() ?? User::firstOrFail();
                $session = PracticeSession::current();
                if (! $session) {
                    throw new RuntimeException('Tidak ada sesi praktikum aktif untuk mencatat penyesuaian.');
                }

                $number = $this->documentNumbers->next('RSI', today());
                $adjustment = InboundTransaction::create([
                    'No_Receiving' => $number,
                    'Tanggal' => today(),
                    'Supplier_ID' => $supplier->Supplier_ID,
                    'User_ID' => $user->id,
                    'Catatan' => 'Penyesuaian otomatis untuk memperbaiki saldo negatif data demo lama.',
                    'Practice_Session_ID' => $session->Practice_Session_ID,
                ]);

                foreach ($deficits as $deficit) {
                    $item = MasterBarang::findOrFail($deficit['sku']);
                    InboundDetail::create([
                        'Inbound_ID' => $adjustment->Inbound_ID,
                        'SKU' => $item->SKU,
                        'Rack_ID' => $deficit['rack_id'],
                        'Qty' => $deficit['qty'],
                        'Harga_Satuan' => $item->Harga_Dasar,
                        'Batch' => 'ADJ-DEMO-LEGACY',
                    ]);
                    $adjustedUnits += $deficit['qty'];
                }
            }

            $movedUnits = $this->relocateRackOverflow();

            WarehouseCache::clearDashboard();

            if ($adjustedUnits > 0 || $movedUnits > 0) {
                ActivityLog::record("Perbaikan integritas data demo: {$adjustedUnits} unit penyesuaian stok dan {$movedUnits} unit relokasi kapasitas rak.");
            }

            return [
                'adjusted_units' => $adjustedUnits,
                'moved_units' => $movedUnits,
                'adjustment_number' => $adjustment?->No_Receiving,
            ];
        }, 3);
    }

    private function rackDeficits()
    {
        $inbound = InboundDetail::query()
            ->selectRaw('"SKU", "Rack_ID", SUM("Qty") as qty')
            ->groupBy('SKU', 'Rack_ID')
            ->get()
            ->keyBy(fn ($row) => $row->SKU.'|'.$row->Rack_ID);

        return OutboundDetail::query()
            ->selectRaw('"SKU", "Rack_ID", SUM("Qty") as qty')
            ->groupBy('SKU', 'Rack_ID')
            ->get()
            ->map(function ($row) use ($inbound) {
                $received = (int) ($inbound->get($row->SKU.'|'.$row->Rack_ID)?->qty ?? 0);
                $qty = (int) $row->qty - $received;

                return $qty > 0 ? ['sku' => $row->SKU, 'rack_id' => $row->Rack_ID, 'qty' => $qty] : null;
            })
            ->filter()
            ->values();
    }

    private function relocateRackOverflow(): int
    {
        $moved = 0;

        foreach (RackLocation::orderBy('Kode_Rak')->get() as $source) {
            $overflow = max(0, $this->physicalUsed($source->Rack_ID) - $source->Kapasitas);
            if ($overflow === 0) {
                continue;
            }

            foreach ($this->availableBySku($source->Rack_ID) as $sku => $available) {
                while ($overflow > 0 && $available > 0) {
                    $target = RackLocation::where('Rack_ID', '!=', $source->Rack_ID)
                        ->orderBy('Kode_Rak')
                        ->get()
                        ->map(function (RackLocation $rack) {
                            $rack->free_capacity = max(0, $rack->Kapasitas - $this->physicalUsed($rack->Rack_ID));

                            return $rack;
                        })
                        ->filter(fn (RackLocation $rack) => $rack->free_capacity > 0)
                        ->sortByDesc('free_capacity')
                        ->first();

                    if (! $target) {
                        throw new RuntimeException("Tidak ada kapasitas rak tujuan untuk mengosongkan {$source->Kode_Rak}.");
                    }

                    $qty = min($overflow, $available, $target->free_capacity);
                    $this->moveInbound($sku, $source->Rack_ID, $target->Rack_ID, $qty);
                    $overflow -= $qty;
                    $available -= $qty;
                    $moved += $qty;
                }

                if ($overflow === 0) {
                    break;
                }
            }

            if ($overflow > 0) {
                throw new RuntimeException("Rak {$source->Kode_Rak} masih melebihi kapasitas {$overflow} unit dan tidak memiliki stok tersedia yang dapat dipindah.");
            }
        }

        return $moved;
    }

    private function physicalUsed(string $rackId): int
    {
        $inbound = InboundDetail::where('Rack_ID', $rackId)->sum('Qty');
        $completed = OutboundDetail::where('Rack_ID', $rackId)
            ->whereHas('outboundTransaction', fn ($query) => $query
                ->where('transaction_status', 'active')
                ->where('picking_status', 'complete'))
            ->sum('Qty');

        return max(0, $inbound - $completed);
    }

    private function availableBySku(string $rackId)
    {
        $inbound = InboundDetail::where('Rack_ID', $rackId)
            ->selectRaw('"SKU", SUM("Qty") as qty')
            ->groupBy('SKU')
            ->pluck('qty', 'SKU');
        $outbound = OutboundDetail::where('Rack_ID', $rackId)
            ->selectRaw('"SKU", SUM("Qty") as qty')
            ->groupBy('SKU')
            ->pluck('qty', 'SKU');

        return $inbound->map(fn ($qty, $sku) => max(0, (int) $qty - (int) $outbound->get($sku, 0)))
            ->filter()
            ->sortDesc();
    }

    private function moveInbound(string $sku, string $sourceRackId, string $targetRackId, int $qty): void
    {
        $remaining = $qty;
        $details = InboundDetail::where('SKU', $sku)
            ->where('Rack_ID', $sourceRackId)
            ->latest('created_at')
            ->get();

        foreach ($details as $detail) {
            if ($remaining === 0) {
                break;
            }

            if ($detail->Qty <= $remaining) {
                $detail->update(['Rack_ID' => $targetRackId]);
                $remaining -= $detail->Qty;

                continue;
            }

            $detail->decrement('Qty', $remaining);
            InboundDetail::create([
                'Inbound_ID' => $detail->Inbound_ID,
                'SKU' => $detail->SKU,
                'Rack_ID' => $targetRackId,
                'Qty' => $remaining,
                'Harga_Satuan' => $detail->Harga_Satuan,
                'No_Resi_Supplier' => $detail->No_Resi_Supplier,
                'Batch' => $detail->Batch,
            ]);
            $remaining = 0;
        }

        if ($remaining > 0) {
            throw new RuntimeException("Detail inbound {$sku} tidak cukup untuk relokasi {$qty} unit.");
        }

        $physicalAtSource = InboundDetail::where('SKU', $sku)->where('Rack_ID', $sourceRackId)->sum('Qty')
            - OutboundDetail::where('SKU', $sku)->where('Rack_ID', $sourceRackId)
                ->whereHas('outboundTransaction', fn ($query) => $query
                    ->where('transaction_status', 'active')
                    ->where('picking_status', 'complete'))
                ->sum('Qty');
        if ($physicalAtSource <= 0) {
            MasterBarang::whereKey($sku)->where('Rack_ID', $sourceRackId)->update(['Rack_ID' => $targetRackId]);
        }
    }
}
