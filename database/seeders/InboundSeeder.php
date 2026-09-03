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
 * transaksi penerimaan barang yang realistis.
 *
 * Strategi:
 * - 15 transaksi inbound, masing-masing memiliki 2–5 baris detail.
 * - FK diambil dari data yang sudah di-seed (bukan random query).
 * - No_Receiving dibuat unik dan berurutan.
 * - Batch diisi untuk ~70% detail.
 */
class InboundSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil data yang sudah ada di database
        $supplierIds = Supplier::pluck('Supplier_ID')->toArray();
        $userIds     = User::pluck('id')->toArray();
        $skus        = MasterBarang::pluck('SKU')->toArray();
        $rackIds     = RackLocation::pluck('Rack_ID')->toArray();

        $transactionCount = 15;
        $detailSeq        = 1;

        for ($i = 1; $i <= $transactionCount; $i++) {
            // Tanggal dibuat mundur agar tidak bentrok dengan tanggal hari ini
            $tanggal = now()->subDays($transactionCount - $i + 1)->format('Y-m-d');
            $dateKey = date('Ymd', strtotime($tanggal));

            $transaction = InboundTransaction::create([
                'No_Receiving' => sprintf('RSI-%s-%04d', $dateKey, $i),
                'Tanggal'      => $tanggal,
                'Supplier_ID'  => $supplierIds[($i - 1) % count($supplierIds)],
                'User_ID'      => $userIds[($i % (count($userIds) - 1)) + 1],
            ]);

            // Setiap transaksi memiliki 2–5 detail baris barang
            $detailCount = rand(2, 5);
            // Acak SKU dan hindari duplikasi SKU dalam satu transaksi
            $selectedSkus = array_slice($skus, ($i * 3) % count($skus), $detailCount);
            if (count($selectedSkus) < $detailCount) {
                $selectedSkus = array_merge($selectedSkus, array_slice($skus, 0, $detailCount - count($selectedSkus)));
            }

            foreach ($selectedSkus as $skuIndex => $sku) {
                $hasBatch = ($detailSeq % 10) !== 0; // ~90% memiliki batch

                InboundDetail::create([
                    'Inbound_ID' => $transaction->Inbound_ID,
                    'SKU'        => $sku,
                    'Rack_ID'    => $rackIds[($detailSeq - 1) % count($rackIds)],
                    'Qty'        => rand(10, 150),
                    'Batch'      => $hasBatch
                                    ? sprintf('BCH-2026-%04d', $detailSeq)
                                    : null,
                ]);

                $detailSeq++;
            }
        }

        $this->command->info(sprintf(
            '  InboundSeeder: %d transaksi dan %d detail inbound berhasil dibuat.',
            $transactionCount,
            $detailSeq - 1
        ));
    }
}
