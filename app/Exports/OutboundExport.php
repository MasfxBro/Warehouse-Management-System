<?php

namespace App\Exports;

use App\Models\OutboundTransaction;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Export: OutboundExport
 *
 * Mengekspor data transaksi outbound + detail customer & barang ke .xlsx.
 */
class OutboundExport
{
    private ?string $from;
    private ?string $to;

    public function __construct(?string $from = null, ?string $to = null)
    {
        $this->from = $from;
        $this->to   = $to;
    }

    public function download(): string
    {
        $query = OutboundTransaction::with(['customer', 'outboundDetails.masterBarang', 'user'])
            ->orderBy('Tanggal')
            ->orderBy('Outbound_ID');

        if ($this->from) $query->whereDate('Tanggal', '>=', $this->from);
        if ($this->to)   $query->whereDate('Tanggal', '<=', $this->to);

        $transactions = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Outbound');

        $headers = ['No. Shipping', 'Tanggal', 'Customer', 'Nama Penerima', 'SKU', 'Nama Barang', 'Qty Keluar', 'Status Picking', 'Dicatat Oleh'];
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

        foreach ($headers as $i => $h) {
            $cell = $cols[$i] . '1';
            $sheet->setCellValue($cell, $h);
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0058BE']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getColumnDimension($cols[$i])->setAutoSize(true);
        }

        $row = 2;
        foreach ($transactions as $trx) {
            foreach ($trx->outboundDetails as $detail) {
                $sheet->setCellValue("A{$row}", $trx->No_Shipping);
                $sheet->setCellValue("B{$row}", $trx->Tanggal->format('d/m/Y'));
                $sheet->setCellValue("C{$row}", $trx->customer->Nama ?? '-');
                $sheet->setCellValue("D{$row}", $trx->Nama_Penerima ?? '-');
                $sheet->setCellValue("E{$row}", $detail->SKU);
                $sheet->setCellValue("F{$row}", $detail->masterBarang->Nama ?? '-');
                $sheet->setCellValue("G{$row}", $detail->Qty);
                $sheet->setCellValue("H{$row}", $trx->picking_status === 'complete' ? 'Complete' : 'Not Complete');
                $sheet->setCellValue("I{$row}", $trx->user->name ?? '-');
                $row++;
            }
        }

        $tmpPath = sys_get_temp_dir() . '/outbound_' . now()->format('Ymd_His') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);
        return $tmpPath;
    }
}
