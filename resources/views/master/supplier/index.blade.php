@extends('layouts.app')

@section('title', 'Data Supplier')
@section('page_heading', 'Master Data — Directory Supplier')

@section('content')
<div class="space-y-6">

    {{-- Info Banner --}}
    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-xs flex items-start gap-3 shadow-xs">
        <i class="fa-solid fa-circle-info text-[#0058be] text-base mt-0.5 shrink-0"></i>
        <div>
            <strong class="font-bold text-slate-900 text-sm">Ketentuan Master Data Supplier (Read-Only Directory)</strong>
            <p class="text-slate-600 mt-0.5">
                Halaman ini bertindak murni sebagai Direktori Rekapitulasi Data Supplier. Data supplier bertambah secara
                otomatis ketika operator/siswa melakukan pengisian pada <strong>Formulir Transaksi Inbound</strong>.
            </p>
        </div>
    </div>

    {{-- Header + Search --}}
    <div class="wms-card p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-building text-[#0058be]"></i>
                Daftar Pemasok Barang (Supplier)
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Menampilkan seluruh entitas perusahaan supplier yang telah tercatat dari transaksi inbound.</p>
        </div>

        <form action="{{ route('master.supplier.index') }}" method="GET" class="flex items-center gap-2">
            <div class="search-group">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Cari nama / kontak / alamat..."
                       class="search-input" style="width:16rem;">
                <button type="submit" class="search-btn">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari
                </button>
            </div>
            @if($search)
                <a href="{{ route('master.supplier.index') }}"
                   class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1">
                    <i class="fa-solid fa-xmark text-[10px]"></i> Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Supplier Table --}}
    <div class="wms-card overflow-hidden">
        <div class="overflow-x-auto">
            @if($suppliers->count() > 0)
                <table class="wms-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Supplier</th>
                            <th>No. Telepon Kontak</th>
                            <th>Email Perusahaan</th>
                            <th>Alamat Perusahaan</th>
                            <th>Total Inbound</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliers as $index => $supplier)
                            <tr>
                                <td class="font-mono text-slate-400">{{ $suppliers->firstItem() + $index }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-building text-slate-400"></i>
                                        <span class="font-bold text-slate-900">{{ $supplier->Nama }}</span>
                                    </div>
                                </td>
                                <td class="font-mono text-slate-700">{{ $supplier->No_Kontak ?? $supplier->Kontak ?? '-' }}</td>
                                <td class="font-mono text-slate-600">{{ $supplier->Email ?? '-' }}</td>
                                <td class="text-slate-700 max-w-xs truncate">{{ $supplier->Alamat ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-info">
                                        <i class="fa-solid fa-arrow-down-to-bracket"></i>
                                        {{ $supplier->inbound_transactions_count }} transaksi
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-12 text-center text-slate-400 text-xs">
                    <i class="fa-solid fa-building text-3xl mb-3 block"></i>
                    Belum ada data supplier yang tercatat dari proses Inbound.
                </div>
            @endif
        </div>

        @if($suppliers->hasPages())
            <div class="p-4 border-t border-[#e2e8f0] bg-slate-50">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
