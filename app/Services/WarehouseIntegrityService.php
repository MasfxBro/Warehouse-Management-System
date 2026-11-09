<?php

namespace App\Services;

use App\Models\InboundDetail;
use App\Models\MasterBarang;
use App\Models\OutboundDetail;
use App\Models\RackLocation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WarehouseIntegrityService
{
    /** @return array{metrics: array<string, int>, issues: array<int, array{type: string, reference: string, detail: string}>} */
    public function audit(): array
    {
        $issues = [];
        $items = MasterBarang::withSum('inboundDetails as inbound_qty', 'Qty')
            ->withSum('outboundDetails as outbound_qty', 'Qty')
            ->get();

        foreach ($items as $item) {
            $balance = (int) ($item->inbound_qty ?? 0) - (int) ($item->outbound_qty ?? 0);
            if ($balance < 0) {
                $issues[] = [
                    'type' => 'STOK_NEGATIF',
                    'reference' => $item->SKU,
                    'detail' => "Saldo tersedia {$balance}",
                ];
            }
        }

        $inboundByLocation = InboundDetail::query()
            ->selectRaw('"SKU", "Rack_ID", SUM("Qty") as qty')
            ->groupBy('SKU', 'Rack_ID')
            ->get()
            ->keyBy(fn ($row) => $row->SKU.'|'.$row->Rack_ID);
        $outboundByLocation = OutboundDetail::query()
            ->selectRaw('"SKU", "Rack_ID", SUM("Qty") as qty')
            ->groupBy('SKU', 'Rack_ID')
            ->get();

        foreach ($outboundByLocation as $row) {
            $inbound = (int) ($inboundByLocation->get($row->SKU.'|'.$row->Rack_ID)?->qty ?? 0);
            $outbound = (int) $row->qty;
            if ($outbound > $inbound) {
                $issues[] = [
                    'type' => 'STOK_RAK_NEGATIF',
                    'reference' => $row->SKU,
                    'detail' => "Rak {$row->Rack_ID}: inbound {$inbound}, outbound/reservasi {$outbound}",
                ];
            }
        }

        $racks = RackLocation::withSum('inboundDetails as inbound_qty', 'Qty')
            ->withSum('completedOutboundDetails as completed_outbound_qty', 'Qty')
            ->get();
        foreach ($racks as $rack) {
            $physical = max(0, (int) ($rack->inbound_qty ?? 0) - (int) ($rack->completed_outbound_qty ?? 0));
            if ($physical > $rack->Kapasitas) {
                $issues[] = [
                    'type' => 'RAK_MELEBIHI_KAPASITAS',
                    'reference' => $rack->Kode_Rak,
                    'detail' => "Fisik {$physical}, kapasitas {$rack->Kapasitas}",
                ];
            }
        }

        $priceMismatches = InboundDetail::query()
            ->join('master_barang', 'master_barang.SKU', '=', 'inbound_details.SKU')
            ->whereColumn('inbound_details.Harga_Satuan', '!=', 'master_barang.Harga_Dasar')
            ->count();
        if ($priceMismatches > 0) {
            $issues[] = [
                'type' => 'HARGA_TIDAK_KONSISTEN',
                'reference' => 'inbound_details',
                'detail' => "{$priceMismatches} detail tidak sama dengan harga dasar barang",
            ];
        }

        $this->auditDocumentNumbers($issues, 'RSI', 'inbound_transactions', 'No_Receiving');
        $this->auditDocumentNumbers($issues, 'SJ', 'outbound_transactions', 'No_Shipping');

        foreach (['inbound_transactions', 'outbound_transactions'] as $table) {
            $withoutSession = DB::table($table)->whereNull('Practice_Session_ID')->count();
            if ($withoutSession > 0) {
                $issues[] = [
                    'type' => 'SESI_TRANSAKSI_KOSONG',
                    'reference' => $table,
                    'detail' => "{$withoutSession} transaksi tidak terhubung ke sesi praktikum",
                ];
            }
        }

        return [
            'metrics' => [
                'sku' => $items->count(),
                'rack' => $racks->count(),
                'inbound_details' => InboundDetail::count(),
                'outbound_details' => OutboundDetail::count(),
            ],
            'issues' => $issues,
        ];
    }

    /** @param array<int, array{type: string, reference: string, detail: string}> $issues */
    private function auditDocumentNumbers(array &$issues, string $type, string $table, string $column): void
    {
        $duplicates = DB::table($table)
            ->select($column)
            ->groupBy($column)
            ->havingRaw('COUNT(*) > 1')
            ->pluck($column);
        foreach ($duplicates as $number) {
            $issues[] = [
                'type' => 'NOMOR_DOKUMEN_DUPLIKAT',
                'reference' => $number,
                'detail' => "Nomor {$type} digunakan lebih dari sekali",
            ];
        }

        DB::table($table)->select(['Tanggal', $column])->get()->groupBy('Tanggal')
            ->each(function ($rows, $date) use (&$issues, $type, $column): void {
                $date = Carbon::parse($date)->toDateString();
                $maxNumber = $rows->map(function ($row) use ($column): int {
                    preg_match('/(\d+)$/', $row->{$column}, $matches);

                    return (int) ($matches[1] ?? 0);
                })->max();
                $counter = (int) (DB::table('document_counters')
                    ->where('Document_Type', $type)
                    ->where('Document_Date', $date)
                    ->value('Last_Number') ?? 0);

                if ($counter < $maxNumber) {
                    $issues[] = [
                        'type' => 'COUNTER_DOKUMEN_TERTINGGAL',
                        'reference' => "{$type}-{$date}",
                        'detail' => "Counter {$counter}, nomor terbesar {$maxNumber}",
                    ];
                }
            });
    }
}
