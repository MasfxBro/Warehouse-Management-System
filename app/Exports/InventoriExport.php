<?php

namespace App\Exports;

use App\Models\MasterBarang;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Export: InventoriExport
 *
 * Mengekspor seluruh data inventori barang ke format .xlsx.
 * Kolom: SKU, Nama Barang, Kategori, Lokasi Rak, Total Stok, Nilai Aset, Status.
 */
class InventoriExport
{
    public function download(): string
    {
        // withSum menghindari N+1 query (1 query, bukan 2×N)
        $items = MasterBarang::with('rackLocation')
            ->withSum('inboundDetails as inbound_qty', 'Qty')
            ->withSum('outboundDetails as outbound_qty', 'Qty')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventori');

        // --- Header ---
        $headers = ['SKU', 'Nama Barang', 'Kategori', 'Lokasi Rak', 'Total Stok', 'Nilai Aset (Rp)', 'Status'];
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

        foreach ($headers as $i => $h) {
            $cell = $cols[$i] . '1';
            $sheet->setCellValue($cell, $h);
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0058BE']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }

        // Auto-size header columns
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // --- Data Rows ---
        $row = 2;
        foreach ($items as $item) {
            $stok   = max(0, (int)($item->inbound_qty ?? 0) - (int)($item->outbound_qty ?? 0));
            $nilai  = $stok * $item->harga;
            $status = $stok > $item->Min_Stok ? 'Aman' : 'Reorder';

            $sheet->setCellValue("A{$row}", $item->SKU);
            $sheet->setCellValue("B{$row}", $item->Nama);
            $sheet->setCellValue("C{$row}", $item->Kategori);
            $sheet->setCellValue("D{$row}", $item->rackLocation->Kode_Rak ?? '-');
            $sheet->setCellValue("E{$row}", $stok);
            $sheet->setCellValue("F{$row}", $nilai);
            $sheet->setCellValue("G{$row}", $status);

            // Color status
            $statusColor = $status === 'Aman' ? 'D1FAE5' : 'FEF3C7';
            $sheet->getStyle("G{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $statusColor]],
            ]);

            $row++;
        }

        $tmpPath = sys_get_temp_dir() . '/inventori_' . now()->format('Ymd_His') . '.xlsx';
        $writer  = new Xlsx($spreadsheet);
        $writer->save($tmpPath);

        return $tmpPath;
    }
}
