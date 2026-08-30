@extends('layouts.app')

@section('title', 'Picking List - ' . $outbound->No_Shipping)
@section('page_heading', 'Picking List - ' . $outbound->No_Shipping)

@section('content')
<div class="space-y-5">

    <a href="{{ route('outbound.show', $outbound->Outbound_ID) }}" class="btn btn-ghost btn-sm gap-1.5">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Detail Outbound
    </a>

    {{-- Status Bar --}}
    <div class="wms-card p-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-2">
                <div class="flex items-center gap-2 flex-wrap">
                    @if($outbound->isComplete())
                        <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Picking Selesai</span>
                    @else
                        <span class="badge badge-warning"><i class="fa-solid fa-hourglass-half"></i> Belum Selesai</span>
                    @endif
                    <span class="badge badge-{{ $outbound->priority }}">{{ $outbound->priorityLabel() }}</span>
                </div>
                <h2 class="text-lg font-black font-mono text-secondary">{{ $outbound->No_Shipping }}</h2>
                <p class="text-xs text-slate-500">
                    Customer: <strong class="text-slate-800">{{ $outbound->customer->Nama ?? '-' }}</strong>
                    &nbsp;·&nbsp; Penerima: <strong class="text-slate-800">{{ $outbound->Nama_Penerima ?? '-' }}</strong>
                    &nbsp;·&nbsp; <i class="fa-regular fa-calendar mr-1"></i>{{ $outbound->Tanggal->format('d/m/Y') }}
                </p>
            </div>

            @if(!$outbound->isComplete())
                {{-- Tombol Mark as Complete (disabled hingga semua checkbox dicentang) --}}
                <button type="button" id="btn-complete" disabled
                        onclick="openCompleteModal()"
                        class="btn btn-success btn-lg gap-2 cursor-not-allowed opacity-40 transition-opacity">
                    <i class="fa-solid fa-circle-check"></i> Mark as Complete
                </button>
            @else
                <a href="{{ route('outbound.surat-jalan', $outbound->Outbound_ID) }}"
                   class="btn btn-primary btn-lg gap-2">
                    <i class="fa-solid fa-file-pdf"></i> Download Surat Jalan PDF
                </a>
            @endif
        </div>
    </div>

    {{-- Instruksi --}}
    @if(!$outbound->isComplete())
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-amber-500 text-base shrink-0 mt-0.5"></i>
            <div>
                <strong class="font-bold text-slate-900">Petunjuk Pengambilan Barang</strong>
                <p class="text-slate-600 mt-0.5">
                    Ambil barang dari rak sesuai daftar. <strong>Centang semua baris</strong> setelah diambil.
                    Tombol <strong>Mark as Complete</strong> akan aktif otomatis setelah semua baris dicentang.
                </p>
            </div>
        </div>
    @endif

    {{-- Tabel Picking --}}
    <div class="wms-card overflow-hidden">
        <div class="wms-card-header">
            <h3 class="wms-card-title flex items-center gap-2">
                <i class="fa-solid fa-list-check text-secondary"></i> Daftar Barang yang Diambil
            </h3>
            @if(!$outbound->isComplete())
                <span class="text-xs text-slate-400">
                    <span id="checked-count">0</span> / {{ $outbound->outboundDetails->count() }} dicentang
                </span>
            @else
                <span class="text-xs text-slate-400">{{ $outbound->outboundDetails->count() }} baris</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            @if($outbound->outboundDetails->count() > 0)
                <table class="wms-table">
                    <thead><tr>
                        <th>No</th><th>SKU</th><th>Nama Barang</th>
                        <th>Lokasi Rak</th><th class="text-right">Qty Ambil</th>
                        @if(!$outbound->isComplete())<th class="text-center w-16">Cek</th>@endif
                    </tr></thead>
                    <tbody>
                        @foreach($outbound->outboundDetails as $i => $detail)
                            <tr id="row-{{ $i }}" class="transition-opacity">
                                <td class="font-mono text-slate-400">{{ $i + 1 }}</td>
                                <td class="font-mono font-semibold text-secondary">{{ $detail->SKU }}</td>
                                <td class="font-medium text-slate-900">{{ $detail->masterBarang->Nama ?? '-' }}</td>
                                <td>
                                    <span class="font-mono font-bold text-slate-800 bg-surface-low border border-[#e2e8f0] px-2.5 py-1 rounded text-xs">
                                        {{ $detail->rackLocation->Kode_Rak ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <span class="font-mono font-bold {{ $outbound->isComplete() ? 'text-[#10b981]' : 'text-amber-700' }}">
                                        {{ number_format($detail->Qty) }} unit
                                    </span>
                                </td>
                                @if(!$outbound->isComplete())
                                    <td class="text-center">
                                        <input type="checkbox" id="check-{{ $i }}"
                                               onchange="markRow({{ $i }})"
                                               class="picking-cb w-4 h-4 rounded text-[#10b981] cursor-pointer">
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-surface border-t border-[#e2e8f0]">
                        <tr>
                            <td colspan="{{ $outbound->isComplete() ? 4 : 5 }}"
                                class="px-4 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">
                                Total Qty
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-black">
                                {{ number_format($outbound->outboundDetails->sum('Qty')) }} unit
                            </td>
                            @if(!$outbound->isComplete())<td></td>@endif
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>

</div>

{{-- Custom Confirm Modal --}}
@if(!$outbound->isComplete())
<div id="modal-complete" class="fixed inset-0 bg-black/50 backdrop-blur-sm items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full border border-[#e2e8f0] overflow-hidden">
        <div class="bg-emerald-600 px-6 py-5 text-white text-center">
            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-circle-check text-2xl"></i>
            </div>
            <h3 class="text-base font-bold">Konfirmasi Selesai Picking</h3>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-sm text-slate-700 text-center">
                Semua barang sudah diambil dan diperiksa?<br>
                Setelah ini, <strong>Surat Jalan PDF</strong> akan dapat diunduh.
            </p>
            <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-xs text-amber-800 flex items-start gap-2">
                <i class="fa-solid fa-triangle-exclamation shrink-0 mt-0.5"></i>
                <span>Aksi ini tidak dapat dibatalkan. Pastikan semua barang sudah benar-benar diambil dari rak.</span>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeCompleteModal()"
                        class="btn btn-outline flex-1">
                    Batal, Periksa Lagi
                </button>
                <form action="{{ route('outbound.picking-complete', $outbound->Outbound_ID) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="btn btn-success w-full gap-2">
                        <i class="fa-solid fa-circle-check"></i> Ya, Selesaikan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const totalRows = {{ $outbound->outboundDetails->count() }};

function markRow(idx) {
    const row = document.getElementById(`row-${idx}`);
    const cb  = document.getElementById(`check-${idx}`);
    if (cb.checked) {
        row.classList.add('opacity-50');
        row.querySelector('td:last-child')?.previousElementSibling
            ?.querySelector('span')?.classList.replace('text-amber-700', 'text-[#10b981]');
    } else {
        row.classList.remove('opacity-50');
    }
    updateCompleteButton();
}

function updateCompleteButton() {
    const cbs     = document.querySelectorAll('.picking-cb');
    const checked = [...cbs].filter(c => c.checked).length;
    const btn     = document.getElementById('btn-complete');
    const counter = document.getElementById('checked-count');

    if (counter) counter.textContent = checked;

    if (checked === totalRows && totalRows > 0) {
        btn.disabled = false;
        btn.classList.remove('cursor-not-allowed', 'opacity-40');
        btn.classList.add('cursor-pointer');
    } else {
        btn.disabled = true;
        btn.classList.add('cursor-not-allowed', 'opacity-40');
        btn.classList.remove('cursor-pointer');
    }
}

function openCompleteModal() {
    const m = document.getElementById('modal-complete');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function closeCompleteModal() {
    const m = document.getElementById('modal-complete');
    m.classList.add('hidden');
    m.classList.remove('flex');
}

// Close on backdrop click
document.getElementById('modal-complete')?.addEventListener('click', function (e) {
    if (e.target === this) closeCompleteModal();
});</script>
@endif
@endsection