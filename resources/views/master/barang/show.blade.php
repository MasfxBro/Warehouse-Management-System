@extends('layouts.app')

@section('title', 'Detail Barang — ' . $item->SKU)
@section('page_heading', 'Detail Barang: ' . $item->SKU)

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <a href="{{ route('master.barang.index') }}"
           class="btn btn-ghost btn-sm gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Master Barang
        </a>
        <span class="text-[11px] text-slate-400 font-mono">SKU: {{ $item->SKU }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- LEFT: Detail + Riwayat --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Overview Card --}}
            <div class="wms-card p-6 space-y-4">
                <div class="flex items-start justify-between border-b border-[#f2f4f6] pb-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 font-mono">{{ $item->SKU }}</p>
                        <h2 class="text-xl font-bold text-slate-900 mt-1">{{ $item->Nama }}</h2>
                    </div>
                    @php $isSafe = $item->stok > $item->Min_Stok; @endphp
                    @if($isSafe)
                        <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Aman</span>
                    @else
                        <span class="badge badge-warning"><i class="fa-solid fa-triangle-exclamation"></i> Reorder</span>
                    @endif
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach([
                        ['Kategori',        $item->Kategori,                                         false],
                        ['Total Stok',      number_format($item->stok) . ' pcs',                    true],
                        ['Min. Stok',       number_format($item->Min_Stok) . ' pcs',                true],
                        ['Harga Satuan',    'Rp ' . number_format($item->harga, 0, ',', '.'),        true],
                        ['Total Nilai',     'Rp ' . number_format($item->nilai_barang, 0, ',', '.'), true],
                        ['Lokasi Rak',      $rackName,                                               true],
                    ] as [$label, $val, $mono])
                        <div class="p-3 rounded-lg bg-[#f7f9fb] border border-[#eceef0]">
                            <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-1">{{ $label }}</p>
                            <p class="text-sm font-bold text-slate-900 {{ $mono ? 'font-mono' : '' }}">{{ $val }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Riwayat Mutasi --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Inbound --}}
                <div class="wms-card p-5">
                    <h3 class="text-[11px] font-bold uppercase tracking-widest text-slate-500 mb-3 flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-down-to-bracket text-[#10b981]"></i> Riwayat Inbound
                    </h3>
                    @if($inboundHistory->count() > 0)
                        <div class="space-y-2">
                            @foreach($inboundHistory as $hist)
                                <div class="flex items-center justify-between p-2.5 rounded-lg bg-[#f7f9fb] border border-[#eceef0] text-xs">
                                    <div>
                                        <p class="font-mono font-semibold text-slate-800">{{ $hist->inboundTransaction->No_Resi ?? '-' }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $hist->inboundTransaction->Tanggal->format('d M Y') }}</p>
                                    </div>
                                    <span class="font-mono font-bold text-[#10b981]">+{{ number_format($hist->Qty) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-6 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-inbox block text-2xl mb-2"></i>
                            Belum ada riwayat inbound
                        </div>
                    @endif
                </div>

                {{-- Outbound --}}
                <div class="wms-card p-5">
                    <h3 class="text-[11px] font-bold uppercase tracking-widest text-slate-500 mb-3 flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-up-from-bracket text-[#0058be]"></i> Riwayat Outbound
                    </h3>
                    @if($outboundHistory->count() > 0)
                        <div class="space-y-2">
                            @foreach($outboundHistory as $hist)
                                <div class="flex items-center justify-between p-2.5 rounded-lg bg-[#f7f9fb] border border-[#eceef0] text-xs">
                                    <div>
                                        <p class="font-mono font-semibold text-slate-800">{{ $hist->outboundTransaction->No_Shipping ?? '-' }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $hist->outboundTransaction->Tanggal->format('d M Y') }}</p>
                                    </div>
                                    <span class="font-mono font-bold text-[#0058be]">-{{ number_format($hist->Qty) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-6 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-inbox block text-2xl mb-2"></i>
                            Belum ada riwayat outbound
                        </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- RIGHT: QR Code --}}
        <div>
            @if(auth()->user()->isAdmin())
                <div id="qrPrintArea" class="wms-card p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-[#f2f4f6] pb-3">
                        <h3 class="text-[11px] font-bold uppercase tracking-widest text-slate-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-qrcode text-[#0058be]"></i> QR Barcode
                        </h3>
                        <span class="text-[10px] bg-slate-900 text-white font-medium px-2 py-0.5 rounded">Admin Only</span>
                    </div>
                    <p class="text-xs text-slate-500">Scan untuk identifikasi fisik barang di gudang.</p>
                    <div class="p-4 bg-[#f7f9fb] rounded-xl border border-[#eceef0] flex items-center justify-center">
                        <div id="qrcode"></div>
                    </div>
                    <div class="bg-[#f2f4f6] p-3 rounded-lg text-[11px] font-mono text-slate-600 break-all">
                        {{ $qrString }}
                    </div>
                    <button type="button" onclick="window.print()"
                            class="btn btn-primary w-full justify-center gap-2">
                        <i class="fa-solid fa-print"></i> Cetak Label QR
                    </button>
                </div>
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        new QRCode(document.getElementById("qrcode"), {
                            text: @json($qrString),
                            width: 160, height: 160,
                            colorDark: "#000000", colorLight: "#ffffff",
                            correctLevel: QRCode.CorrectLevel.H
                        });
                    });
                </script>
            @else
                <div class="wms-card p-6 text-center space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mx-auto">
                        <i class="fa-solid fa-lock text-slate-400 text-lg"></i>
                    </div>
                    <h3 class="text-xs font-bold text-slate-700">QR Barcode Restricted</h3>
                    <p class="text-[11px] text-slate-400">
                        Fitur ini hanya tersedia untuk akun <strong>Guru (Admin)</strong>.
                    </p>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
