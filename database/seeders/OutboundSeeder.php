<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\MasterBarang;
use App\Models\OutboundDetail;
use App\Models\OutboundTransaction;
use App\Models\PracticeSession;
use App\Models\User;
use App\Services\StockAllocationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        $userIds = User::pluck('id')->toArray();
        $stockAllocation = app(StockAllocationService::class);
        $practiceSessionId = PracticeSession::current()?->Practice_Session_ID;

        $transactionCount = 12;
        $detailSeq = 1;
        $createdTransactions = 0;

        $namaKurir = ['Budi Santoso', 'Andi Wijaya', 'Citra Lestari', 'Dian Permana'];

        for ($i = 1; $i <= $transactionCount; $i++) {
            // 70% transaksi sudah complete (picking selesai)
            $isComplete = $i <= (int) ($transactionCount * 0.7);
            $availableBarangs = MasterBarang::all()
                ->filter(fn (MasterBarang $barang) => $barang->stok > 0)
                ->sortBy('SKU')
                ->values();

            if ($availableBarangs->isNotEmpty()) {
                $offset = ($i - 1) % $availableBarangs->count();
                $availableBarangs = $availableBarangs
                    ->slice($offset)
                    ->concat($availableBarangs->slice(0, $offset))
                    ->take(1 + (($i - 1) % 4))
                    ->values();
            }

            if ($availableBarangs->isEmpty()) {
                break;
            }

            $plannedItems = $availableBarangs->map(function (MasterBarang $barang, int $index) use ($i) {
                return [
                    'sku' => $barang->SKU,
                    'qty' => min(20, $barang->stok, 1 + (($i * 7 + $index * 5) % 20)),
                ];
            });
            $totalQty = $plannedItems->sum('qty');
            $priority = $totalQty > 50 ? 'high' : ($totalQty > 10 ? 'normal' : 'decent');
            $tanggal = now()->subDays($transactionCount - $i + 1)->format('Y-m-d');
            $dateKey = date('Ymd', strtotime($tanggal));

            // Buat header transaksi outbound
            $transaction = OutboundTransaction::create([
                'No_Shipping' => sprintf('SJ-%s-%04d', $dateKey, $i),
                'Tanggal' => $tanggal,
                'Customer_ID' => $customerIds[($i - 1) % count($customerIds)],
                'User_ID' => $userIds[($i % count($userIds))],
                'picking_status' => $isComplete ? 'complete' : 'not_complete',
                'priority' => $priority,
                'Nama_Penerima' => $namaKurir[$i % count($namaKurir)],
                'Catatan' => $i % 3 === 0 ? 'Pengiriman reguler sesuai PO' : null,
                'Practice_Session_ID' => $practiceSessionId,
            ]);
            $createdTransactions++;

            DB::table('document_counters')->updateOrInsert(
                ['Document_Type' => 'SJ', 'Document_Date' => $tanggal],
                ['Last_Number' => $i, 'created_at' => now(), 'updated_at' => now()]
            );

            foreach ($plannedItems as $item) {
                foreach ($stockAllocation->allocate($item['sku'], $item['qty']) as $allocation) {
                    OutboundDetail::create([
                        'Outbound_ID' => $transaction->Outbound_ID,
                        'SKU' => $item['sku'],
                        'Rack_ID' => $allocation['rack_id'],
                        'Qty' => $allocation['qty'],
                    ]);
                    $detailSeq++;
                }
            }
        }

        $this->command->info(sprintf(
            '  OutboundSeeder: %d transaksi dan %d detail outbound berhasil dibuat.',
            $createdTransactions,
            $detailSeq - 1
        ));
    }
}
