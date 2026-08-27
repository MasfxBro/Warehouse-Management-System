<?php

namespace App\Http\Controllers;

use App\Exports\InboundExport;
use App\Exports\InventoriExport;
use App\Exports\OutboundExport;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        ActivityLog::record("Laporan Inventori di-export oleh [{$this->operatorLabel()}].");

        $path = (new InventoriExport())->download();

        return response()->download($path, 'Laporan_Inventori_' . now()->format('Ymd') . '.xlsx')
            ->deleteFileAfterSend();
    }

    // =========================================================
    // EXPORT INBOUND
    // =========================================================

    public function exportInbound(Request $request)
    {
        ActivityLog::record("Laporan Inbound di-export oleh [{$this->operatorLabel()}].");

        $path = (new InboundExport($request->from, $request->to))->download();

        return response()->download($path, 'Laporan_Inbound_' . now()->format('Ymd') . '.xlsx')
            ->deleteFileAfterSend();
    }

    // =========================================================
    // EXPORT OUTBOUND
    // =========================================================

    public function exportOutbound(Request $request)
    {
        ActivityLog::record("Laporan Outbound di-export oleh [{$this->operatorLabel()}].");

        $path = (new OutboundExport($request->from, $request->to))->download();

        return response()->download($path, 'Laporan_Outbound_' . now()->format('Ymd') . '.xlsx')
            ->deleteFileAfterSend();
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    private function operatorLabel(): string
    {
        $user = Auth::user();
        if (!$user) return 'Sistem';
        if ($user->isAdmin()) return 'Guru: ' . $user->name;

        $identity = session('student_identity');
        if ($identity && !empty($identity['name'])) {
            return "Operator: {$identity['name']} | {$identity['class']}";
        }
        return 'Siswa: ' . $user->name;
    }
}
