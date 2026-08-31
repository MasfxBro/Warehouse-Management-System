<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MasterBarang;
use App\Models\StockOpname;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * API Controller: Stock Opname
 *
 * GET    /api/stock-opname           → Daftar
 * POST   /api/stock-opname           → Tambah
 * PUT    /api/stock-opname/{id}      → Edit
 * DELETE /api/stock-opname/{id}      → Hapus
 */
class StockOpnameApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockOpname::with(['masterBarang', 'user'])
            ->orderBy('Tanggal', 'desc')
            ->orderBy('Opname_ID', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('SKU', 'ILIKE', "%{$s}%")
                ->orWhereHas('masterBarang', fn($q2) => $q2->where('Nama', 'ILIKE', "%{$s}%"))
            );
        }

        $perPage   = (int) $request->query('per_page', 15);
        $paginator = $query->paginate($perPage);

        $data = collect($paginator->items())->map(fn($op) => [
            'opname_id'   => $op->Opname_ID,
            'sku'         => $op->SKU,
            'nama_barang' => $op->masterBarang->Nama ?? '-',
            'tanggal'     => $op->Tanggal->format('d/m/Y'),
            'tanggal_raw' => $op->Tanggal->format('Y-m-d'),
            'kondisi'     => $op->Kondisi,
            'operator'    => $op->user->name ?? '-',
        ]);

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'SKU'     => ['required', 'exists:master_barang,SKU'],
            'Tanggal' => ['required', 'date'],
            'Kondisi' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $opname = StockOpname::create([
            'SKU'     => $request->SKU,
            'User_ID' => Auth::id(),
            'Tanggal' => $request->Tanggal,
            'Kondisi' => trim($request->Kondisi),
        ]);

        $barang = MasterBarang::find($request->SKU);
        ActivityLog::record("Stock Opname [{$barang->Nama}] dibuat via Flutter oleh [{$this->operatorLabel()}].");

        return response()->json([
            'success'   => true,
            'message'   => 'Stock Opname berhasil disimpan.',
            'opname_id' => $opname->Opname_ID,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
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

        return response()->json(['success' => true, 'message' => 'Stock Opname berhasil diperbarui.']);
    }

    public function destroy(int $id): JsonResponse
    {
        $opname = StockOpname::findOrFail($id);
        $opname->delete();

        return response()->json(['success' => true, 'message' => 'Stock Opname berhasil dihapus.']);
    }

    private function operatorLabel(): string
    {
        $user = Auth::user();
        if (!$user) return 'Flutter/API';
        return $user->isAdmin() ? 'Guru: ' . $user->name : 'Siswa: ' . $user->name;
    }
}
