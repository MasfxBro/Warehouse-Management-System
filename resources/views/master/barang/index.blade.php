@extends('layouts.app')

@section('title', 'Master Data Barang')
@section('page_heading', 'Master Data — Inventori Barang')

@section('content')
<div class="space-y-5">

    {{-- Info Banner --}}
    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-xs flex items-start gap-3">
        <i class="fa-solid fa-circle-info text-[#0058be] text-base mt-0.5 shrink-0"></i>
        <div>
            <strong class="font-bold text-slate-900 text-sm">Halaman Read-Only — Data Otomatis</strong>
            <p class="text-slate-600 mt-0.5">
                Inventori ini diakumulasi otomatis dari transaksi <strong>Inbound</strong>.
                Stok dihitung secara real-time (inbound − outbound). Data tidak dapat diedit langsung.
            </p>
        </div>
    </div>

    {{-- Header + Filter --}}
    <div class="wms-card p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="wms-card-title flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-[#0058be]"></i>
                Daftar Barang & Produk Gudang
            </h2>
            <p class="page-subtitle">Ringkasan stok, nilai aset, batas minimum, dan lokasi rak default.</p>
        </div>
        <form action="{{ route('master.barang.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <select name="kategori" class="wms-select w-auto text-xs" style="height:2.25rem;padding-top:0;padding-bottom:0;">
                <option value="">Semua Kategori</option>
                @foreach($kategoriList as $kat)
                    <option value="{{ $kat }}" {{ $kategori == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                @endforeach
            </select>
            <div class="search-group">
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari SKU / nama barang..."
                       class="search-input w-52">
                <button type="submit" class="search-btn">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari
                </button>
            </div>
            @if($search || $kategori)
                <a href="{{ route('master.barang.index') }}"
                   class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1">
                    <i class="fa-solid fa-xmark text-[10px]"></i> Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="wms-card overflow-hidden">
        <div class="overflow-x-auto">
            @if($items->count() > 0)
                <table class="wms-table">
                    <thead>
                        <tr>
                            <th>Kode SKU</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th class="text-right">Total Stok</th>
                            <th class="text-right">Min. Stok</th>
                            <th class="text-right">Harga Satuan</th>
                            <th class="text-right">Total Value</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            @php $stok = $item->stok; $isSafe = $stok > $item->Min_Stok; @endphp
                            <tr>
                                <td class="font-mono font-semibold text-[#0058be]">{{ $item->SKU }}</td>
                                <td class="font-medium text-slate-900">{{ $item->Nama }}</td>
                                <td><span class="badge badge-neutral">{{ $item->Kategori }}</span></td>
                                <td class="text-right font-mono font-bold {{ $isSafe ? 'text-slate-900' : 'text-[#93000a]' }}">
                                    {{ number_format($stok) }}
                                </td>
                                <td class="text-right font-mono text-slate-500">{{ number_format($item->Min_Stok) }}</td>
                                <td class="text-right font-mono text-slate-700">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                <td class="text-right font-mono font-bold text-slate-900">Rp {{ number_format($item->nilai_barang, 0, ',', '.') }}</td>
                                <td>
                                    @if($isSafe)
                                        <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Aman</span>
                                    @else
                                        <span class="badge badge-warning"><i class="fa-solid fa-triangle-exclamation"></i> Reorder</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('master.barang.show', $item->SKU) }}" class="btn btn-outline btn-sm gap-1">
                                        <i class="fa-solid fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="py-14 text-center text-slate-400 text-xs space-y-2">
                    <i class="fa-solid fa-box-open text-3xl block"></i>
                    <p>Belum ada data barang yang terdaftar.</p>
                </div>
            @endif
        </div>
        @if($items->hasPages())
            <div class="p-4 border-t border-[#e2e8f0] bg-[#f7f9fb]">{{ $items->links() }}</div>
        @endif
    </div>

</div>
@endsection
