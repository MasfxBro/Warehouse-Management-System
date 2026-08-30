@extends('layouts.app')

@section('title', 'Detail Inbound - ' . $inbound->No_Receiving)
@section('page_heading', 'Detail Inbound - ' . $inbound->No_Receiving)

@section('content')
<div class="space-y-5">

    {{-- Breadcrumb --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('inbound.index') }}" class="btn btn-ghost btn-sm gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Inbound
        </a>
        <span class="text-xs text-slate-400">Dicatat: {{ $inbound->created_at->format('d/m/Y H:i') }} WIB</span>
    </div>

    {{-- Header Card --}}
    <div class="wms-card p-6">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div class="space-y-2">
                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Diterima</span>
                <h2 class="text-xl font-black font-mono text-[#0058be] tracking-tight">{{ $inbound->No_Receiving }}</h2>
                <div class="flex items-center gap-4 text-sm text-slate-600 flex-wrap">
                    <span><i class="fa-regular fa-calendar text-slate-400 mr-1"></i>{{ $inbound->Tanggal->format('d F Y') }}</span>
                    <span><i class="fa-solid fa-building text-slate-400 mr-1"></i>{{ $inbound->supplier->Nama ?? '-' }}</span>
                    <span><i class="fa-solid fa-user text-slate-400 mr-1"></i>{{ $inbound->user->name ?? '-' }}</span>
                </div>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Jenis Barang</p>
                <p class="text-4xl font-black text-slate-900 font-mono">{{ $inbound->inboundDetails->count() }}</p>
                <p class="text-xs text-slate-400">jenis diterima</p>
            </div>
        </div>
        @if($inbound->Catatan)
            <div class="mt-4 pt-4 border-t border-[#f2f4f6]">
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Catatan</p>
                <p class="text-sm text-slate-700 bg-[#f7f9fb] rounded-lg px-4 py-2.5">{{ $inbound->Catatan }}</p>
            </div>
        @endif
    </div>

    {{-- Info Supplier --}}
    <div class="wms-card p-5">
        <h3 class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-3 flex items-center gap-1.5">
            <i class="fa-solid fa-building text-slate-400"></i> Informasi Supplier
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
            @foreach([
                ['Nama Perusahaan',  $inbound->supplier->Nama ?? '-',                                        false],
                ['No. Kontak',       $inbound->supplier->No_Kontak ?? ($inbound->supplier->Kontak ?? '-'),   true],
                ['Email',            $inbound->supplier->Email ?? '-',                                       true],
                ['Alamat',           $inbound->supplier->Alamat ?? '-',                                      false],
            ] as [$lbl, $val, $mono])
                <div>
                    <p class="text-[10px] text-slate-400 mb-0.5">{{ $lbl }}</p>
                    <p class="font-semibold text-slate-800 {{ $mono ? 'font-mono' : '' }}">{{ $val }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Detail Barang --}}
    <div class="wms-card overflow-hidden">
        <div class="wms-card-header">
            <h3 class="wms-card-title flex items-center gap-2">
                <i class="fa-solid fa-list-check text-[#10b981]"></i> Detail Barang Diterima
            </h3>
            <span class="text-xs text-slate-400">{{ $inbound->inboundDetails->count() }} baris</span>
        </div>
        <div class="overflow-x-auto">
            @if($inbound->inboundDetails->count() > 0)
                <table class="wms-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>SKU</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Lokasi Rak</th>
                            <th class="text-right">Qty Diterima</th>
                            <th>No. Resi Supplier</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inbound->inboundDetails as $i => $detail)
                            <tr>
                                <td class="font-mono text-slate-400">{{ $i + 1 }}</td>
                                <td class="font-mono font-semibold text-[#0058be]">
                                    <a href="{{ route('master.barang.show', $detail->SKU) }}" class="hover:underline">
                                        {{ $detail->SKU }}
                                    </a>
                                </td>
                                <td class="font-medium text-slate-900">{{ $detail->masterBarang->Nama ?? '-' }}</td>
                                <td><span class="badge badge-neutral">{{ $detail->masterBarang->Kategori ?? '-' }}</span></td>
                                <td class="font-mono text-slate-600">{{ $detail->rackLocation->Kode_Rak ?? '-' }}</td>
                                <td class="text-right">
                                    <span class="font-mono font-bold text-[#10b981]">+{{ number_format($detail->Qty) }}</span>
                                </td>
                                <td class="font-mono text-slate-500 text-xs">
                                    {{ $detail->No_Resi_Supplier ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-[#f7f9fb] border-t border-[#e2e8f0]">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-xs font-bold text-slate-500 text-right uppercase tracking-wider">
                                Total Qty Diterima
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-black text-slate-900">
                                +{{ number_format($inbound->inboundDetails->sum('Qty')) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>

</div>
@endsection
