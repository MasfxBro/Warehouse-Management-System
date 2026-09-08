<?php

namespace App\Http\Controllers;

use App\Exports\InboundExport;
use App\Exports\InventoriExport;
use App\Exports\OutboundExport;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    // =========================================================
    // INDEX — Halaman Laporan (3 Tab)
    // =========================================================

    public function index()
    {
        return view('laporan.index');
    }

    // =========================================================
    // EXPORT INVENTORI
    // =========================================================

    public function exportInventori()
    {
        ActivityLog::record('Laporan Inventori diekspor.');

        $path = (new InventoriExport)->download();

        return response()->download($path, 'Laporan_Inventori_'.now()->format('Ymd').'.xlsx')
            ->deleteFileAfterSend();
    }

    // =========================================================
    // EXPORT INBOUND
    // =========================================================

    public function exportInbound(Request $request)
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        ActivityLog::record('Laporan Inbound diekspor.');

        $path = (new InboundExport($validated['from'] ?? null, $validated['to'] ?? null))->download();

        return response()->download($path, 'Laporan_Inbound_'.now()->format('Ymd').'.xlsx')
            ->deleteFileAfterSend();
    }

    // =========================================================
    // EXPORT OUTBOUND
    // =========================================================

    public function exportOutbound(Request $request)
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        ActivityLog::record('Laporan Outbound diekspor.');

        $path = (new OutboundExport($validated['from'] ?? null, $validated['to'] ?? null))->download();

        return response()->download($path, 'Laporan_Outbound_'.now()->format('Ymd').'.xlsx')
            ->deleteFileAfterSend();
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

}
