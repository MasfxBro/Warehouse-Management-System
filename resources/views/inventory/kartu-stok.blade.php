@extends('layouts.app')

@section('title', 'Kartu Stok')
@section('page_heading', 'Inventory - Kartu Stok Barang')

@section('content')
<div class="space-y-5">

    <div class="wms-card p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h2 class="wms-card-title flex items-center gap-2">
                <i class="fa-solid fa-rectangle-list text-[#0058be]"></i> Kartu Stok Seluruh Barang
            </h2>
            <p class="page-subtitle">Pantau stok real-time. Klik "Detail" untuk melihat riwayat mutasi per barang.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <form action="{{ route('inventory.kartu-stok.index') }}" method="GET" class="flex items-center gap-2">
                <div class="search-group">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                           placeholder="Cari SKU / nama / kategori..."
                           class="search-input" style="width:16rem;">
                    <button type="submit" class="search-btn">Cari</button>
                </div>
                @if(!empty($search))
                    <a href="{{ route('inventory.kartu-stok.index') }}"
                       class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1">
                        <i class="fa-solid fa-xmark text-[10px]"></i> Reset
                    </a>
                @endif
            </form>
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
                            @php
                                $stok = max(0, (int)($item->inbound_qty ?? 0) - (int)($item->outbound_qty ?? 0));
                                $aman = $stok > $item->Min_Stok;
                            @endphp
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
                                    @if($stok == 0)
                                        <span class="badge badge-danger">
                                            <i class="fa-solid fa-circle-xmark text-[9px]"></i> Habis
                                        </span>
                                    @elseif($aman)
                                        <span class="badge badge-success">
                                            <i class="fa-solid fa-circle-check text-[9px]"></i> Aman
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            <i class="fa-solid fa-arrow-down text-[9px]"></i> Reorder
                                        </span>
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
        @if($items->hasPages())
            <div class="p-4 border-t border-[#e2e8f0] bg-surface">{{ $items->links() }}</div>
        @endif
    </div>

</div>
@endsection
