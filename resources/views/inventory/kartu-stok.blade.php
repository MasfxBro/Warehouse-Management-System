@extends('layouts.app')

@section('title', 'Kartu Stok')
@section('page_heading', 'Inventory — Kartu Stok Barang')

@section('content')
<div class="space-y-5">

    <div class="wms-card p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h2 class="wms-card-title flex items-center gap-2">
                <i class="fa-solid fa-rectangle-list text-[#0058be]"></i> Kartu Stok Seluruh Barang
            </h2>
            <p class="page-subtitle">Pantau stok real-time. Klik "Detail" untuk melihat riwayat mutasi per barang.</p>
        </div>
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[11px] pointer-events-none"></i>
            <input type="text" id="live-search" placeholder="Cari SKU / nama / kategori..."
                   class="wms-input w-72 text-xs pl-8" style="height:2.25rem;">
        </div>
    </div>

    <div class="wms-card overflow-hidden">
        <div class="overflow-x-auto">
            @if($items->count() > 0)
                <table class="wms-table">
                    <thead><tr>
                        <th>SKU</th><th>Nama Barang</th><th>Kategori</th>
                        <th>Lokasi Rak</th><th class="text-right">Total Stok</th>
                        <th>Status</th><th class="text-right">Aksi</th>
                    </tr></thead>
                    <tbody>
                        @foreach($items as $item)
                            @php $stok = $item->stok; $aman = $stok > $item->Min_Stok; @endphp
                            <tr class="stok-row"
                                data-search="{{ strtolower($item->SKU . ' ' . $item->Nama . ' ' . $item->Kategori) }}">
                                <td class="font-mono font-semibold text-[#0058be]">{{ $item->SKU }}</td>
                                <td class="font-medium text-slate-900">{{ $item->Nama }}</td>
                                <td><span class="badge badge-neutral">{{ $item->Kategori }}</span></td>
                                <td class="font-mono text-slate-600">{{ $item->rackLocation->Kode_Rak ?? '-' }}</td>
                                <td class="text-right font-mono font-bold {{ $aman ? 'text-slate-900' : 'text-[#93000a]' }}">
                                    {{ number_format($stok) }}
                                </td>
                                <td>
                                    @if($aman)
                                        <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Aman</span>
                                    @else
                                        <span class="badge badge-warning"><i class="fa-solid fa-triangle-exclamation"></i> Reorder</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('inventory.kartu-stok.detail', $item->SKU) }}"
                                       class="btn btn-outline btn-sm gap-1">
                                        <i class="fa-solid fa-timeline"></i> Timeline
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="py-14 text-center text-slate-400 text-xs space-y-2">
                    <i class="fa-solid fa-box-open text-3xl block"></i>
                    <p>Belum ada data barang.</p>
                </div>
            @endif
        </div>
    </div>

</div>
<script>
document.getElementById('live-search')?.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.stok-row').forEach(r => {
        r.style.display = (r.dataset.search||'').includes(q) ? '' : 'none';
    });
});
</script>
@endsection
