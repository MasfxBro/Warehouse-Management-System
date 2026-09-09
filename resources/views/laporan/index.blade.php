@extends('layouts.app')

@section('title', 'Laporan & Export')
@section('page_heading', 'Laporan & Export Data Gudang')

@section('content')
<div class="space-y-5">

    <div class="wms-card p-5">
        <h2 class="wms-card-title flex items-center gap-2">
            <i class="fa-solid fa-chart-bar text-[#0058be]"></i> Laporan & Export Excel
        </h2>
        <p class="page-subtitle">Ekspor data inventori, inbound, dan outbound ke format .xlsx untuk keperluan dokumentasi dan audit.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        {{-- Inventori --}}
        <div class="wms-card p-6 flex flex-col gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-boxes-stacked text-[#0058be] text-lg"></i>
                </div>
                <div>
                    <h3 class="wms-card-title">Laporan Inventori</h3>
                    <p class="text-[11px] text-slate-400">Semua barang + stok + nilai aset</p>
                </div>
            </div>
            <p class="text-xs text-slate-500 flex-1">
                Export seluruh data barang beserta stok saat ini, nilai aset, lokasi rak, dan status (Aman/Reorder).
            </p>
            <a href="{{ route('laporan.inventori.export') }}"
               class="btn btn-primary gap-2 justify-center">
                <i class="fa-solid fa-file-excel"></i> Export Inventori (.xlsx)
            </a>
        </div>

        {{-- Inbound --}}
        <div class="wms-card p-6 flex flex-col gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-arrow-down text-[#10b981] text-lg"></i>
                </div>
                <div>
                    <h3 class="wms-card-title">Laporan Inbound</h3>
                    <p class="text-[11px] text-slate-400">Transaksi penerimaan barang</p>
                </div>
            </div>
            <form action="{{ route('laporan.inbound.export') }}" method="GET" class="flex flex-col gap-3 flex-1">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="wms-label text-[10px]">Dari Tanggal</label>
                        <input type="date" name="from" class="wms-input text-xs">
                    </div>
                    <div>
                        <label class="wms-label text-[10px]">Sampai</label>
                        <input type="date" name="to" class="wms-input text-xs">
                    </div>
                </div>
                <button type="submit" class="btn btn-success gap-2 justify-center mt-auto">
                    <i class="fa-solid fa-file-excel"></i> Export Inbound (.xlsx)
                </button>
            </form>
        </div>

        {{-- Outbound --}}
        <div class="wms-card p-6 flex flex-col gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-arrow-up-from-bracket text-[#93000a] text-lg"></i>
                </div>
                <div>
                    <h3 class="wms-card-title">Laporan Outbound</h3>
                    <p class="text-[11px] text-slate-400">Transaksi pengiriman barang</p>
                </div>
            </div>
            <form action="{{ route('laporan.outbound.export') }}" method="GET" class="flex flex-col gap-3 flex-1">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="wms-label text-[10px]">Dari Tanggal</label>
                        <input type="date" name="from" class="wms-input text-xs">
                    </div>
                    <div>
                        <label class="wms-label text-[10px]">Sampai</label>
                        <input type="date" name="to" class="wms-input text-xs">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary gap-2 justify-center mt-auto">
                    <i class="fa-solid fa-file-excel"></i> Export Outbound (.xlsx)
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
