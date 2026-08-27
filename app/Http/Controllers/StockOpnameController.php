<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\MasterBarang;
use App\Models\StockOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockOpnameController extends Controller
{
    // =========================================================
    // INDEX — Daftar Catatan Stock Opname
    // =========================================================

    public function index(Request $request)
    {
        $query = StockOpname::with(['masterBarang', 'user'])
            ->orderBy('Tanggal', 'desc')
            ->orderBy('Opname_ID', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('SKU', 'ILIKE', "%{$search}%")
                  ->orWhereHas('masterBarang', fn ($q2) => $q2->where('Nama', 'ILIKE', "%{$search}%"));
            });
        }

        $opnames = $query->paginate(15)->withQueryString();

        return view('inventory.stock-opname', compact('opnames'));
    }

    // =========================================================
    // CREATE — Form Tambah Opname
    // =========================================================

    public function create()
    {
        $barangs = MasterBarang::orderBy('Nama')->get();
        return view('inventory.stock-opname-create', compact('barangs'));
    }

    // =========================================================
    // STORE — Simpan Catatan Opname Baru
    // =========================================================

    public function store(Request $request)
    {
        $request->validate([
            'SKU'     => ['required', 'exists:master_barang,SKU'],
            'Tanggal' => ['required', 'date'],
            'Kondisi' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'Kondisi.min'     => 'Deskripsi kondisi minimal 5 karakter.',
            'Kondisi.required' => 'Deskripsi kondisi fisik wajib diisi.',
        ]);

        $opname = StockOpname::create([
            'SKU'     => $request->SKU,
            'User_ID' => Auth::id(),
            'Tanggal' => $request->Tanggal,
            'Kondisi' => trim($request->Kondisi),
        ]);

        $barang = MasterBarang::find($request->SKU);
        ActivityLog::record("Stock Opname baru dibuat untuk [{$barang->Nama}] ({$request->SKU}) pada [{$request->Tanggal}] oleh [{$this->operatorLabel()}].");

        return redirect()->route('inventory.stock-opname.index')
            ->with('success', "Catatan Stock Opname untuk {$barang->Nama} berhasil disimpan.");
    }

    // =========================================================
    // EDIT — Form Edit Opname
    // =========================================================

    public function edit(int $id)
    {
        $opname  = StockOpname::findOrFail($id);
        $barangs = MasterBarang::orderBy('Nama')->get();
        return view('inventory.stock-opname-edit', compact('opname', 'barangs'));
    }

    // =========================================================
    // UPDATE — Simpan Perubahan Opname
    // =========================================================

    public function update(Request $request, int $id)
    {
        $opname = StockOpname::findOrFail($id);

        $request->validate([
            'SKU'     => ['required', 'exists:master_barang,SKU'],
            'Tanggal' => ['required', 'date'],
            'Kondisi' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $opname->update([
            'SKU'     => $request->SKU,
            'Tanggal' => $request->Tanggal,
            'Kondisi' => trim($request->Kondisi),
        ]);

        $barang = MasterBarang::find($request->SKU);
        ActivityLog::record("Stock Opname [{$opname->Opname_ID}] untuk [{$barang->Nama}] diperbarui oleh [{$this->operatorLabel()}].");

        return redirect()->route('inventory.stock-opname.index')
            ->with('success', "Catatan Stock Opname berhasil diperbarui.");
    }

    // =========================================================
    // DESTROY — Hapus Catatan Opname
    // =========================================================

    public function destroy(int $id)
    {
        $opname = StockOpname::findOrFail($id);
        $sku    = $opname->SKU;
        $opname->delete();

        ActivityLog::record("Stock Opname [{$id}] untuk SKU [{$sku}] dihapus oleh [{$this->operatorLabel()}].");

        return redirect()->route('inventory.stock-opname.index')
            ->with('success', 'Catatan Stock Opname berhasil dihapus.');
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
