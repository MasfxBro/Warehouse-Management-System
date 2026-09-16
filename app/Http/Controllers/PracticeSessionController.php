<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\OutboundTransaction;
use App\Models\PracticeSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PracticeSessionController extends Controller
{
    public function index()
    {
        $current = PracticeSession::current();
        $sessions = PracticeSession::with('creator')->latest('Tanggal')->latest('created_at')->paginate(15);

        return view('practice-sessions.index', compact('current', 'sessions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Nama' => ['required', 'string', 'max:150'],
            'Kelas' => ['nullable', 'string', 'max:100'],
            'Tanggal' => ['required', 'date', 'before_or_equal:today'],
        ]);

        if (PracticeSession::active()->exists()) {
            return back()->withInput()->with('error', 'Tutup sesi praktikum aktif sebelum membuka sesi baru.');
        }

        $session = PracticeSession::create([
            'Nama' => trim($request->Nama),
            'Kelas' => $request->filled('Kelas') ? trim($request->Kelas) : null,
            'Tanggal' => $request->Tanggal,
            'Status' => 'active',
            'Created_By' => Auth::id(),
            'Opened_At' => now(),
        ]);

        ActivityLog::record("Sesi praktikum [{$session->Nama}] dibuka.");

        return back()->with('success', "Sesi {$session->Nama} berhasil dibuka.");
    }

    public function close(Request $request, string $id)
    {
        $request->validate([
            'confirmation' => ['required', 'in:TUTUP SESI'],
        ], [
            'confirmation.in' => 'Ketik TUTUP SESI untuk mengonfirmasi.',
        ]);

        DB::transaction(function () use ($id): void {
            $session = PracticeSession::lockForUpdate()->findOrFail($id);
            if ($session->Status === 'closed') {
                return;
            }

            $pending = OutboundTransaction::where('Practice_Session_ID', $session->Practice_Session_ID)
                ->where('transaction_status', 'active')
                ->where('picking_status', 'not_complete')
                ->count();
            if ($pending > 0) {
                throw ValidationException::withMessages([
                    'confirmation' => "Masih ada {$pending} outbound yang belum selesai. Selesaikan atau batalkan sebelum menutup sesi.",
                ]);
            }

            ActivityLog::record("Sesi praktikum [{$session->Nama}] ditutup.");
            $session->update(['Status' => 'closed', 'Closed_At' => now()]);
        });

        return back()->with('success', 'Sesi praktikum berhasil ditutup. Transaksi baru dikunci sampai sesi berikutnya dibuka.');
    }
}
