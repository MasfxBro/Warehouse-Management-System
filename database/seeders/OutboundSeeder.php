<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\MasterBarang;
use App\Models\OutboundDetail;
use App\Models\OutboundTransaction;
use App\Models\RackLocation;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder: OutboundSeeder
 *
 * Mengisi tabel outbound_transactions dan outbound_details dengan data dummy
 * transaksi pengiriman barang yang realistis.
 *
 * Strategi:
 * - 12 transaksi outbound, masing-masing memiliki 1–4 baris detail.
 * - FK diambil dari data yang sudah di-seed.
 * - No_Shipping dan No_Surat_Jalan dibuat unik dan berurutan.
 * - ~80% transaksi sudah memiliki No_Surat_Jalan (sisanya pending).
 */
class OutboundSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil data yang sudah ada di database
        $customerIds = Customer::pluck('Customer_ID')->toArray();
        $userIds     = User::pluck('id')->toArray();
        $skus        = MasterBarang::pluck('SKU')->toArray();
        $rackIds     = RackLocation::pluck('Rack_ID')->toArray();

        $transactionCount = 12;
        $detailSeq        = 1;

        $namaKurir = ['Budi Santoso', 'Andi Wijaya', 'Citra Lestari', 'Dian Permana'];

        for ($i = 1; $i <= $transactionCount; $i++) {
            // 70% transaksi sudah complete (picking selesai)
            $isComplete = $i <= (int) ($transactionCount * 0.7);
            $totalQty   = rand(5, 60);
            $priority   = $totalQty > 50 ? 'high' : ($totalQty > 10 ? 'normal' : 'decent');
            $tanggal    = now()->subDays($transactionCount - $i + 1)->format('Y-m-d');
            $dateKey    = date('Ymd', strtotime($tanggal));

            // Buat header transaksi outbound
            $transaction = OutboundTransaction::create([
                'No_Shipping'    => sprintf('SJ-%s-%04d', $dateKey, $i),
                'Tanggal'        => $tanggal,
                'Customer_ID'    => $customerIds[($i - 1) % count($customerIds)],
                'User_ID'        => $userIds[($i % count($userIds))],
                'picking_status' => $isComplete ? 'complete' : 'not_complete',
                'priority'       => $priority,
                'Nama_Penerima'  => $namaKurir[$i % count($namaKurir)],
                'Catatan'        => $i % 3 === 0 ? 'Pengiriman reguler sesuai PO' : null,
            ]);

            // Setiap transaksi memiliki 1–4 detail baris barang
            $detailCount  = rand(1, 4);
            $selectedSkus = array_slice($skus, ($i * 5) % count($skus), $detailCount);
            if (count($selectedSkus) < $detailCount) {
                $selectedSkus = array_merge($selectedSkus, array_slice($skus, 0, $detailCount - count($selectedSkus)));
            }

            foreach ($selectedSkus as $sku) {
                OutboundDetail::create([
                    'Outbound_ID' => $transaction->Outbound_ID,
                    'SKU'         => $sku,
                    'Rack_ID'     => $rackIds[($detailSeq - 1) % count($rackIds)],
                    'Qty'         => rand(1, 80),
                ]);

                $detailSeq++;
            }
        }

        $this->command->info(sprintf(
            '  OutboundSeeder: %d transaksi dan %d detail outbound berhasil dibuat.',
            $transactionCount,
            $detailSeq - 1
        ));
    }
}
