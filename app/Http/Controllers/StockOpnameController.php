<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\MasterBarang;
use App\Models\InboundDetail;
use App\Models\OutboundDetail;
use Illuminate\Http\Request;

class StockOpnameController extends Controller
{
    public function index()
    {
        $opnames = StockOpname::with(['masterBarang', 'user'])
            ->latest('tanggal_opname')
            ->latest('opname_id')
            ->paginate(20);
        
        return view('inventory.stock-opname.index', compact('opnames'));
    }

    public function create()
    {
        $barangs = MasterBarang::withSum('inboundDetails as total_masuk', 'Qty')
            ->withSum('outboundDetails as total_keluar', 'Qty')
            ->get()
            ->map(function ($barang) {
                $masuk = (int) ($barang->total_masuk ?? 0);
                $keluar = (int) ($barang->total_keluar ?? 0);
                $barang->stok_sistem = $masuk - $keluar;
                return $barang;
            });

        return view('inventory.stock-opname.create', compact('barangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'SKU' => 'required|exists:master_barang,SKU',
            'tanggal_opname' => 'required|date',
            'stok_fisik' => 'required|integer|min:0',
            'action_taken' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $totalMasuk = (int) InboundDetail::where('SKU', $validated['SKU'])->sum('Qty');
        $totalKeluar = (int) OutboundDetail::where('SKU', $validated['SKU'])->sum('Qty');
        $stokSistem = $totalMasuk - $totalKeluar;
        $stokFisik = (int) $validated['stok_fisik'];
        $variance = $stokFisik - $stokSistem;
        $status = ($variance == 0) ? 'MATCH' : 'SELISIH';

        StockOpname::create([
            'SKU' => $validated['SKU'],
            'tanggal_opname' => $validated['tanggal_opname'],
            'stok_sistem' => $stokSistem,
            'stok_fisik' => $stokFisik,
            'variance' => $variance,
            'status' => $status,
            'action_taken' => $validated['action_taken'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('inventory.stock-opname.index')
            ->with('success', 'Pencatatan Stock Opname berhasil disimpan.');
    }

    public function exportExcel()
    {
        $opnames = StockOpname::with(['masterBarang', 'user'])
            ->latest('tanggal_opname')
            ->latest('opname_id')
            ->get();

        $filename = 'Stock-Opname-' . date('Ymd-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($opnames) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['Tanggal', 'SKU', 'Nama Barang', 'Stok Sistem', 'Stok Fisik', 'Selisih (Variance)', 'Status', 'Tindakan / Catatan', 'Petugas']);

            foreach ($opnames as $op) {
                $statusText = ($op->variance == 0) ? 'SESUAI' : (($op->variance < 0) ? 'KURANG' : 'LEBIH');
                $catatan = trim(($op->action_taken ?? '') . ' ' . ($op->notes ?? ''));

                fputcsv($file, [
                    \Carbon\Carbon::parse($op->tanggal_opname)->format('Y-m-d'),
                    $op->SKU,
                    $op->masterBarang->Nama ?? '-',
                    $op->stok_sistem,
                    $op->stok_fisik,
                    $op->variance,
                    $statusText,
                    $catatan ?: '-',
                    $op->user->name ?? 'System',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        $opnames = StockOpname::with(['masterBarang', 'user'])
            ->latest('tanggal_opname')
            ->latest('opname_id')
            ->get();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.stock-opname-pdf', compact('opnames'));
            return $pdf->download('Stock-Opname-' . date('Ymd-His') . '.pdf');
        }

        return view('exports.stock-opname-pdf', compact('opnames'));
    }
}
