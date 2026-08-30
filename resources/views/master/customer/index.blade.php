@extends('layouts.app')

@section('title', 'Data Customer')
@section('page_heading', 'Master Data - Directory Customer')

@section('content')
<div class="space-y-6">

    {{-- Info Banner --}}
    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-xs flex items-start gap-3 shadow-xs">
        <i class="fa-solid fa-circle-info text-[#0058be] text-base mt-0.5 shrink-0"></i>
        <div>
            <strong class="font-bold text-slate-900 text-sm">Ketentuan Master Data Customer (Read-Only Directory)</strong>
            <p class="text-slate-600 mt-0.5">
                Halaman ini bertindak murni sebagai Direktori Rekapitulasi Data Customer/Pelanggan. Data customer bertambah
                secara otomatis ketika operator/siswa melakukan pengisian pada <strong>Formulir Transaksi Outbound</strong>.
            </p>
        </div>
    </div>

    {{-- Header + Search --}}
    <div class="wms-card p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-users text-[#0058be]"></i>
                Daftar Pelanggan (Customer)
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Menampilkan seluruh entitas customer pemesan barang yang telah tercatat dari transaksi outbound.</p>
        </div>

        <form action="{{ route('master.customer.index') }}" method="GET" class="flex items-center gap-2">
            <div class="search-group">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Cari nama / kontak / alamat..."
                       class="search-input" style="width:16rem;">
                <button type="submit" class="search-btn">Cari</button>
            </div>
            @if($search)
                <a href="{{ route('master.customer.index') }}"
                   class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1">
                    <i class="fa-solid fa-xmark text-[10px]"></i> Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Customer Table --}}
    <div class="wms-card overflow-hidden">
        <div class="overflow-x-auto">
            @if($customers->count() > 0)
                <table class="wms-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Customer</th>
                            <th>No. Telepon Kontak</th>
                            <th>Email Perusahaan</th>
                            <th>Alamat Perusahaan</th>
                            <th>Total Outbound</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $index => $customer)
                            <tr>
                                <td class="font-mono text-slate-400">{{ $customers->firstItem() + $index }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-store text-slate-400"></i>
                                        <span class="font-bold text-slate-900">{{ $customer->Nama }}</span>
                                    </div>
                                </td>
                                <td class="font-mono text-slate-700">{{ $customer->No_Kontak ?? '-' }}</td>
                                <td class="font-mono text-slate-600">{{ $customer->Email ?? '-' }}</td>
                                <td class="text-slate-700 max-w-xs truncate">{{ $customer->Alamat ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-warning">
                                        <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                        {{ $customer->outbound_transactions_count }} transaksi
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-12 text-center text-slate-400 text-xs">
                    <i class="fa-solid fa-users text-3xl mb-3 block"></i>
                    Belum ada data customer yang tercatat dari proses Outbound.
                </div>
            @endif
        </div>

        @if($customers->hasPages())
            <div class="p-4 border-t border-[#e2e8f0] bg-slate-50">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
