<?php

namespace Database\Seeders;

use App\Models\InboundDetail;
use App\Models\InboundTransaction;
use App\Models\MasterBarang;
use App\Models\RackLocation;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder: InboundSeeder
 *
 * Mengisi tabel inbound_transactions dan inbound_details dengan data dummy
 * yang TIDAK melebihi kapasitas maksimal tiap rak.
 *
 * Strategi kapasitas:
 * - Setiap rak dibagi: ~40% Penuh, ~30% Hampir Penuh (70-85%), ~30% Tersedia (<50%)
 * - Qty per detail dihitung agar total per Rack_ID tidak melebihi Kapasitas
 */
class InboundSeeder extends Seeder
{
    public function run(): void
    {
        $supplierIds = Supplier::pluck('Supplier_ID')->toArray();
        $userIds     = User::pluck('id')->toArray();
        $barangs     = MasterBarang::all();
        $racks       = RackLocation::all();

        if ($barangs->isEmpty() || $racks->isEmpty() || empty($supplierIds)) {
            $this->command->warn('  InboundSeeder: Data master tidak lengkap, lewati.');
            return;
        }

        // Hitung target qty per rak sesuai skenario kapasitas
        $rackTargets = [];
        $rackCount   = $racks->count();
        $i = 0;
        foreach ($racks as $rack) {
            $pct = $i % 10; // siklus 10 rak: 4 penuh, 3 hampir penuh, 3 tersedia
            if ($pct < 4) {
                // Penuh: tepat 100% kapasitas
                $target = $rack->Kapasitas;
            } elseif ($pct < 7) {
                // Hampir Penuh: 75–90% kapasitas
                $target = (int)round($rack->Kapasitas * (rand(75, 90) / 100));
            } else {
                // Tersedia: 10–45% kapasitas
                $target = (int)round($rack->Kapasitas * (rand(10, 45) / 100));
            }
            $rackTargets[$rack->Rack_ID] = max(0, $target);
            $i++;
        }

        // Distribute qty ke barang-barang yang ada di masing-masing rak
        $rackBarangs = [];
        foreach ($barangs as $barang) {
            if ($barang->Rack_ID) {
                $rackBarangs[$barang->Rack_ID][] = $barang->SKU;
            }
        }

        // Buat inbound transactions dan detail
        $tanggalBase   = now()->subDays(60);
        $trxCount      = 0;
        $detailCount   = 0;

        // Untuk setiap rak, buat inbound detail yang totalnya = target
        foreach ($racks as $rack) {
            $target  = $rackTargets[$rack->Rack_ID] ?? 0;
            if ($target <= 0) continue;

            $skusInRak = $rackBarangs[$rack->Rack_ID] ?? [];
            if (empty($skusInRak)) {
                // Pakai SKU random dari semua barang jika rak ini tidak punya barang default
                $skusInRak = $barangs->pluck('SKU')->take(3)->toArray();
            }

            // Bagi target qty ke beberapa transaksi (1-3 transaksi per rak)
            $numTrx    = min(3, count($skusInRak));
            $remaining = $target;
            $dayOffset = 0;

            for ($t = 0; $t < $numTrx && $remaining > 0; $t++) {
                $qtyTrx   = ($t === $numTrx - 1) ? $remaining : (int)($remaining / ($numTrx - $t));
                $remaining -= $qtyTrx;
                if ($qtyTrx <= 0) continue;

                $tanggal = $tanggalBase->copy()->addDays($dayOffset)->format('Y-m-d');
                $dayOffset += rand(3, 10);

                $trxSeq = $trxCount + 1;
                $dateKey = str_replace('-', '', $tanggal);

                $transaction = InboundTransaction::create([
                    'No_Receiving' => sprintf('RSI-%s-%04d', $dateKey, $trxSeq),
                    'Tanggal'      => $tanggal,
                    'Supplier_ID'  => $supplierIds[$trxCount % count($supplierIds)],
                    'User_ID'      => $userIds[$trxCount % count($userIds)],
                    'Catatan'      => null,
                ]);
                $trxCount++;

                // Detail: pakai SKU dari rak ini
                $sku = $skusInRak[$t % count($skusInRak)];

                InboundDetail::create([
                    'Inbound_ID'       => $transaction->Inbound_ID,
                    'SKU'              => $sku,
                    'Rack_ID'          => $rack->Rack_ID,
                    'Qty'              => $qtyTrx,
                    'No_Resi_Supplier' => null,
                    'Batch'            => sprintf('BCH-2026-%04d', $detailCount + 1),
                ]);
                $detailCount++;
            }
        }

        $this->command->info(sprintf(
            '  InboundSeeder: %d transaksi dan %d detail inbound dibuat (kapasitas rak terjaga).',
            $trxCount,
            $detailCount
        ));
    }
}
