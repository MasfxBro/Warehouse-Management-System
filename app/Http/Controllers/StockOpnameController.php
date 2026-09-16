<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\MasterBarang;
use App\Models\PracticeSession;
use App\Models\StockOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $query = StockOpname::with(['masterBarang', 'user'])
            ->orderBy('Tanggal', 'desc')
            ->orderBy('Opname_ID', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $needle = '%'.strtolower($search).'%';
                $q->whereRaw('LOWER("SKU") LIKE ?', [$needle])
                    ->orWhereHas('masterBarang', fn ($q2) => $q2->whereRaw('LOWER("Nama") LIKE ?', [$needle]));
            });
        }

        $opnames = $query->paginate(15)->withQueryString();

        return view('inventory.stock-opname', compact('opnames'));
    }

    public function create()
    {
        $barangs = MasterBarang::orderBy('Nama')->get();

        return view('inventory.stock-opname-create', compact('barangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'SKU' => ['required', 'exists:master_barang,SKU'],
            'Tanggal' => ['required', 'date', 'before_or_equal:today'],
            'Kondisi' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'Kondisi.min' => 'Deskripsi kondisi minimal 5 karakter.',
            'Kondisi.required' => 'Deskripsi kondisi fisik wajib diisi.',
        ]);

        $practiceSession = PracticeSession::current();
        if (! $practiceSession) {
            return back()->withInput()->with('error', 'Belum ada sesi praktikum aktif. Minta Guru/Admin membuka sesi terlebih dahulu.');
        }

        $opname = StockOpname::create([
            'SKU' => $request->SKU,
            'User_ID' => Auth::id(),
            'Tanggal' => $request->Tanggal,
            'Kondisi' => trim($request->Kondisi),
            'Practice_Session_ID' => $practiceSession->Practice_Session_ID,
        ]);

        $barang = MasterBarang::find($request->SKU);
        ActivityLog::record("Stock Opname baru dibuat untuk [{$barang->Nama}] ({$request->SKU}) pada [{$request->Tanggal}].");
        session()->save();

        return redirect()->route('inventory.stock-opname.index')
            ->with('success', "Catatan Stock Opname untuk {$barang->Nama} berhasil disimpan.");
    }
}
