<?php

namespace App\Exports;

use App\Models\InboundTransaction;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Export: InboundExport
 *
 * Mengekspor data transaksi inbound + detail supplier & barang ke .xlsx.
 */
class InboundExport
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
        $query = InboundTransaction::with(['supplier', 'inboundDetails.masterBarang', 'user'])
            ->orderBy('Tanggal')
            ->orderBy('Inbound_ID');

        if ($this->from) $query->whereDate('Tanggal', '>=', $this->from);
        if ($this->to)   $query->whereDate('Tanggal', '<=', $this->to);

        $transactions = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Inbound');

        $headers = ['No. Resi', 'Tanggal', 'Supplier', 'SKU', 'Nama Barang', 'Qty Masuk', 'No. Resi Supplier', 'Dicatat Oleh'];
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

        foreach ($headers as $i => $h) {
            $cell = $cols[$i] . '1';
            $sheet->setCellValue($cell, $h);
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '10B981']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getColumnDimension($cols[$i])->setAutoSize(true);
        }

        $row = 2;
        foreach ($transactions as $trx) {
            foreach ($trx->inboundDetails as $detail) {
                $sheet->setCellValue("A{$row}", $trx->No_Receiving);
                $sheet->setCellValue("B{$row}", $trx->Tanggal->format('d/m/Y'));
                $sheet->setCellValue("C{$row}", $trx->supplier->Nama ?? '-');
                $sheet->setCellValue("D{$row}", $detail->SKU);
                $sheet->setCellValue("E{$row}", $detail->masterBarang->Nama ?? '-');
                $sheet->setCellValue("F{$row}", $detail->Qty);
                $sheet->setCellValue("G{$row}", $detail->No_Resi_Supplier ?? '-');
                $sheet->setCellValue("H{$row}", $trx->user->name ?? '-');
                $row++;
            }
        }

        $tmpPath = sys_get_temp_dir() . '/inbound_' . now()->format('Ymd_His') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);
        return $tmpPath;
    }
}
