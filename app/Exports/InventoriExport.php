<?php

namespace App\Exports;

use App\Models\MasterBarang;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Export: InventoriExport
 *
 * Mengekspor seluruh data inventori barang ke format .xlsx.
 * Memisahkan stok fisik, reservasi picking, dan stok tersedia agar tidak ambigu.
 */
class InventoriExport
{
    public function download(): string
    {
        // withSum menghindari N+1 query (1 query, bukan 2×N)
        $items = MasterBarang::with('rackLocation')
            ->withSum('inboundDetails as inbound_qty', 'Qty')
            ->withSum('outboundDetails as outbound_qty', 'Qty')
            ->withSum('completedOutboundDetails as completed_outbound_qty', 'Qty')
            ->withSum('reservedOutboundDetails as reserved_qty', 'Qty')
            ->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventori');

        // --- Header ---
        $headers = ['SKU', 'Nama Barang', 'Kategori', 'Satuan', 'Lokasi Rak', 'Stok Fisik', 'Direservasi', 'Tersedia', 'Harga Dasar (Rp)', 'Nilai Aset Fisik (Rp)', 'Status'];
        $cols = range('A', 'K');

        foreach ($headers as $i => $h) {
            $cell = $cols[$i].'1';
            $sheet->setCellValue($cell, $h);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0058BE']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }

        // Auto-size header columns
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // --- Data Rows ---
        $row = 2;
        foreach ($items as $item) {
            $stokTersedia = max(0, (int) ($item->inbound_qty ?? 0) - (int) ($item->outbound_qty ?? 0));
            $stokFisik = max(0, (int) ($item->inbound_qty ?? 0) - (int) ($item->completed_outbound_qty ?? 0));
            $reservasi = (int) ($item->reserved_qty ?? 0);
            $nilai = $stokFisik * $item->harga;
            $status = $stokTersedia > $item->Min_Stok ? 'Aman' : 'Reorder';

            $sheet->setCellValue("A{$row}", $item->SKU);
            $sheet->setCellValue("B{$row}", $item->Nama);
            $sheet->setCellValue("C{$row}", $item->Kategori);
            $sheet->setCellValue("D{$row}", $item->Satuan);
            $sheet->setCellValue("E{$row}", $item->rackLocation->Kode_Rak ?? '-');
            $sheet->setCellValue("F{$row}", $stokFisik);
            $sheet->setCellValue("G{$row}", $reservasi);
            $sheet->setCellValue("H{$row}", $stokTersedia);
            $sheet->setCellValue("I{$row}", $item->harga);
            $sheet->setCellValue("J{$row}", $nilai);
            $sheet->setCellValue("K{$row}", $status);

            // Color status
            $statusColor = $status === 'Aman' ? 'D1FAE5' : 'FEF3C7';
            $sheet->getStyle("K{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $statusColor]],
            ]);

            $row++;
        }

        $tmpPath = sys_get_temp_dir().'/inventori_'.now()->format('Ymd_His').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpPath);

        return $tmpPath;
    }
}
