@extends('layouts.app')

@section('title', 'Detail Outbound — ' . $outbound->No_Shipping)
@section('page_heading', 'Detail Outbound — ' . $outbound->No_Shipping)

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <a href="{{ route('outbound.index') }}" class="btn btn-ghost btn-sm gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
        <span class="text-xs text-slate-400">Dibuat: {{ $outbound->created_at->format('d/m/Y H:i') }} WIB</span>
    </div>

    {{-- Header --}}
    <div class="wms-card p-6">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div class="space-y-2">
                <div class="flex items-center gap-2 flex-wrap">
                    @if($outbound->isComplete())
                        <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Picking Selesai</span>
                    @else
                        <span class="badge badge-warning"><i class="fa-solid fa-clock"></i> Menunggu Picking</span>
                    @endif
                    <span class="badge badge-{{ $outbound->priority }}">{{ $outbound->priorityLabel() }}</span>
                </div>
                <h2 class="text-xl font-black font-mono text-[#0058be] tracking-tight">{{ $outbound->No_Shipping }}</h2>
                <div class="text-sm text-slate-600 flex items-center gap-4 flex-wrap">
                    <span><i class="fa-regular fa-calendar text-slate-400 mr-1"></i>{{ $outbound->Tanggal->format('d F Y') }}</span>
                    <span><i class="fa-solid fa-user text-slate-400 mr-1"></i>{{ $outbound->customer->Nama ?? '-' }}</span>
                    @if($outbound->Nama_Penerima)
                        <span><i class="fa-solid fa-truck text-slate-400 mr-1"></i>{{ $outbound->Nama_Penerima }}</span>
                    @endif
                </div>
            </div>

            {{-- CTA --}}
            <div class="flex flex-col gap-2 flex-shrink-0">
                @if(!$outbound->isComplete())
                    <a href="{{ route('outbound.picking-list', $outbound->Outbound_ID) }}"
                       class="btn btn-outline btn-lg gap-2 border-amber-300 text-amber-700 hover:bg-amber-50">
                        <i class="fa-solid fa-clipboard-list"></i> Lihat Picking List
                    </a>
                @endif
                @if($outbound->isComplete())
                    <a href="{{ route('outbound.surat-jalan', $outbound->Outbound_ID) }}"
                       class="btn btn-primary btn-lg gap-2">
                        <i class="fa-solid fa-file-pdf"></i> Download Surat Jalan PDF
                    </a>
                @else
                    <button disabled class="btn btn-outline btn-lg gap-2 opacity-40 cursor-not-allowed"
                            title="Selesaikan Picking List terlebih dahulu">
                        <i class="fa-solid fa-lock"></i> Surat Jalan (Terkunci)
                    </button>
                @endif
            </div>
        </div>
        @if($outbound->Catatan)
            <div class="mt-4 pt-4 border-t border-[#f2f4f6]">
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Catatan</p>
                <p class="text-sm text-slate-700 bg-[#f7f9fb] rounded-lg px-4 py-2.5">{{ $outbound->Catatan }}</p>
            </div>
        @endif
    </div>

    {{-- Info Customer --}}
    <div class="wms-card p-5">
        <h3 class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-3 flex items-center gap-1.5">
            <i class="fa-solid fa-users text-slate-400"></i> Informasi Customer
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
            @foreach([
                ['Nama Customer',  $outbound->customer->Nama ?? '-',                                               false],
                ['No. Kontak',     $outbound->customer->No_Kontak ?? ($outbound->customer->Kontak ?? '-'),         true],
                ['Email',          $outbound->customer->Email ?? '-',                                               true],
                ['Alamat',         $outbound->customer->Alamat ?? '-',                                              false],
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
                <i class="fa-solid fa-list text-[#0058be]"></i> Detail Barang Dikirim
            </h3>
            <span class="text-xs text-slate-400">{{ $outbound->outboundDetails->count() }} baris</span>
        </div>
        <div class="overflow-x-auto">
            @if($outbound->outboundDetails->count() > 0)
                <table class="wms-table">
                    <thead><tr>
                        <th>No</th><th>SKU</th><th>Nama Barang</th>
                        <th>Lokasi Rak</th><th class="text-right">Qty Keluar</th>
                    </tr></thead>
                    <tbody>
                        @foreach($outbound->outboundDetails as $i => $detail)
                            <tr>
                                <td class="font-mono text-slate-400">{{ $i + 1 }}</td>
                                <td class="font-mono font-semibold text-[#0058be]">
                                    <a href="{{ route('master.barang.show', $detail->SKU) }}" class="hover:underline">
                                        {{ $detail->SKU }}
                                    </a>
                                </td>
                                <td class="font-medium text-slate-900">{{ $detail->masterBarang->Nama ?? '-' }}</td>
                                <td class="font-mono text-slate-600">{{ $detail->rackLocation->Kode_Rak ?? '-' }}</td>
                                <td class="text-right font-mono font-bold text-[#93000a]">
                                    -{{ number_format($detail->Qty) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-[#f7f9fb] border-t border-[#e2e8f0]">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Total Qty Keluar</td>
                            <td class="px-4 py-3 text-right font-mono font-black text-slate-900">
                                -{{ number_format($outbound->outboundDetails->sum('Qty')) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>

</div>
@endsection
