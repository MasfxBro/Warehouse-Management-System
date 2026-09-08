<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Koreksi data lama: Qty outbound dialokasikan ulang ke rak yang memiliki
     * inbound SKU tersebut. Total Qty dan referensi transaksi tidak berubah.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $skus = DB::table('inbound_details')
                ->whereNull('deleted_at')
                ->distinct()
                ->pluck('SKU');

            foreach ($skus as $sku) {
                $rackBalances = DB::table('inbound_details')
                    ->where('SKU', $sku)
                    ->whereNull('deleted_at')
                    ->selectRaw('"Rack_ID", SUM("Qty") as total_qty')
                    ->groupBy('Rack_ID')
                    ->orderBy('Rack_ID')
                    ->pluck('total_qty', 'Rack_ID')
                    ->map(fn ($qty) => (int) $qty)
                    ->all();

                if ($rackBalances === []) {
                    continue;
                }

                $details = DB::table('outbound_details')
                    ->where('SKU', $sku)
                    ->whereNull('deleted_at')
                    ->orderBy('created_at')
                    ->orderBy('Detail_ID')
                    ->get();

                $lastRackId = (string) array_key_last($rackBalances);

                foreach ($details as $detail) {
                    $remaining = (int) $detail->Qty;
                    $allocations = [];

                    foreach ($rackBalances as $rackId => $available) {
                        if ($remaining <= 0) {
                            break;
                        }
                        if ($available <= 0) {
                            continue;
                        }

                        $allocated = min($remaining, $available);
                        $allocations[] = ['rack_id' => (string) $rackId, 'qty' => $allocated];
                        $rackBalances[$rackId] -= $allocated;
                        $remaining -= $allocated;
                    }

                    // Data seeder lama dapat memiliki outbound melebihi seluruh
                    // inbound. Tempelkan ke rak terakhir agar saldo total dan
                    // saldo per-rak tetap sama-sama nol, tanpa menghapus Qty.
                    if ($remaining > 0) {
                        $last = array_key_last($allocations);
                        if ($last !== null && $allocations[$last]['rack_id'] === $lastRackId) {
                            $allocations[$last]['qty'] += $remaining;
                        } else {
                            $allocations[] = ['rack_id' => $lastRackId, 'qty' => $remaining];
                        }
                        $rackBalances[$lastRackId] -= $remaining;
                    }

                    $first = array_shift($allocations);
                    DB::table('outbound_details')
                        ->where('Detail_ID', $detail->Detail_ID)
                        ->update([
                            'Rack_ID' => $first['rack_id'],
                            'Qty' => $first['qty'],
                            'updated_at' => now(),
                        ]);

                    foreach ($allocations as $allocation) {
                        DB::table('outbound_details')->insert([
                            'Detail_ID' => (string) Str::orderedUuid(),
                            'Outbound_ID' => $detail->Outbound_ID,
                            'SKU' => $detail->SKU,
                            'Rack_ID' => $allocation['rack_id'],
                            'Qty' => $allocation['qty'],
                            'created_at' => $detail->created_at,
                            'updated_at' => now(),
                            'deleted_at' => null,
                        ]);
                    }
                }
            }
        });
    }

    public function down(): void
    {
        // Data historis tidak dapat dikembalikan ke Rack_ID yang salah.
    }
};
