@extends('layouts.app')

@section('title', 'Stock Opname')
@section('page_heading', 'Inventory - Stock Opname')

@section('content')
<div class="space-y-5">

    {{-- Info Banner --}}
    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-xs flex items-start gap-3">
        <i class="fa-solid fa-circle-info text-secondary text-base mt-0.5 shrink-0"></i>
        <div>
            <strong class="font-bold text-slate-900 text-sm">Konsep Stock Opname</strong>
            <p class="text-slate-600 mt-0.5">
                Stock Opname di sini adalah pencatatan <strong>kondisi fisik barang</strong> hasil pemeriksaan lapangan.
                Catatan ini <strong>tidak mengubah angka stok</strong> — hanya mendokumentasikan temuan lapangan.
            </p>
        </div>
    </div>

    {{-- Header --}}
    <div class="wms-card p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h2 class="wms-card-title flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check text-secondary"></i> Riwayat Catatan Stock Opname
            </h2>
            <p class="page-subtitle">Total {{ $opnames->total() }} catatan kondisi fisik tersimpan.</p>
        </div>
        <a href="{{ route('inventory.stock-opname.create') }}" class="btn btn-primary btn-sm gap-1.5">
            <i class="fa-solid fa-plus"></i> Tambah Catatan
        </a>
    </div>

    {{-- Table --}}
    <div class="wms-card overflow-hidden">
        <div class="overflow-x-auto">
            @if($opnames->count() > 0)
                <table class="wms-table">
                    <thead><tr>
                        <th>Tanggal</th><th>SKU</th><th>Nama Barang</th>
                        <th>Kondisi Fisik</th><th>Pemeriksa</th><th class="text-right">Aksi</th>
                    </tr></thead>
                    <tbody>
                        @foreach($opnames as $op)
                            <tr>
                                <td class="font-mono text-slate-700">{{ $op->Tanggal->format('d/m/Y') }}</td>
                                <td class="font-mono font-semibold text-secondary">{{ $op->SKU }}</td>
                                <td class="font-medium text-slate-900">{{ $op->masterBarang->Nama ?? '-' }}</td>
                                <td class="text-slate-700 max-w-xs text-xs leading-relaxed">
                                    {{ Str::limit($op->Kondisi, 50) }}
                                </td>
                                <td class="text-slate-600 text-xs">{{ $op->user->name ?? '-' }}</td>
                                <td class="text-right">
                                    <button type="button"
                                            onclick="showOpnameDetail(
                                                '{{ $op->Opname_ID }}',
                                                {{ json_encode($op->SKU) }},
                                                {{ json_encode($op->masterBarang->Nama ?? '-') }},
                                                '{{ $op->Tanggal->format('d/m/Y') }}',
                                                {{ json_encode($op->user->name ?? '-') }},
                                                {{ json_encode($op->Kondisi) }}
                                            )"
                                            class="btn btn-outline btn-sm gap-1">
                                        <i class="fa-solid fa-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="py-14 text-center text-slate-400 text-xs space-y-2">
                    <i class="fa-solid fa-clipboard-check text-3xl block"></i>
                    <p>Belum ada catatan stock opname.</p>
                </div>
            @endif
        </div>
        @if($opnames->hasPages())
            <div class="p-4 border-t border-[#e2e8f0] bg-surface">{{ $opnames->links() }}</div>
        @endif
    </div>

</div>

{{-- Modal Detail Stock Opname --}}
<div id="modal-opname-detail" class="fixed inset-0 bg-black/50 backdrop-blur-sm items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full border border-[#e2e8f0] overflow-hidden">
        <div class="bg-slate-900 px-6 py-4 text-white flex items-center justify-between">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check"></i>
                Detail Stock Opname
            </h3>
            <button type="button" onclick="closeOpnameDetail()"
                    class="text-slate-400 hover:text-white transition-colors cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-1">SKU</p>
                    <p id="detail-sku" class="font-mono font-bold text-secondary"></p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-1">Nama Barang</p>
                    <p id="detail-nama" class="font-medium text-slate-900"></p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-1">Tanggal Pemeriksaan</p>
                    <p id="detail-tanggal" class="font-mono text-slate-700"></p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-1">Pemeriksa</p>
                    <p id="detail-pemeriksa" class="text-slate-700"></p>
                </div>
            </div>
            <div>
                <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-2">Kondisi Fisik Barang</p>
                <div id="detail-kondisi"
                     class="bg-surface border border-[#e2e8f0] rounded-lg p-4 text-sm text-slate-800 leading-relaxed whitespace-pre-wrap">
                </div>
            </div>
            <div class="flex justify-end pt-2">
                <button type="button" onclick="closeOpnameDetail()" class="btn btn-outline">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showOpnameDetail(id, sku, nama, tanggal, pemeriksa, kondisi) {
    document.getElementById('detail-sku').textContent       = sku;
    document.getElementById('detail-nama').textContent      = nama;
    document.getElementById('detail-tanggal').textContent   = tanggal;
    document.getElementById('detail-pemeriksa').textContent = pemeriksa;
    document.getElementById('detail-kondisi').textContent   = kondisi;

    const m = document.getElementById('modal-opname-detail');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function closeOpnameDetail() {
    const m = document.getElementById('modal-opname-detail');
    m.classList.add('hidden');
    m.classList.remove('flex');
}
document.getElementById('modal-opname-detail')?.addEventListener('click', function(e) {
    if (e.target === this) closeOpnameDetail();
});
</script>
@endsection
