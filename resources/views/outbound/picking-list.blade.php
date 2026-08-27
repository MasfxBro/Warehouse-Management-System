@extends('layouts.app')

@section('title', 'Picking List — ' . $outbound->No_Shipping)
@section('page_heading', 'Picking List — ' . $outbound->No_Shipping)

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
                <h2 class="text-lg font-black font-mono text-[#0058be]">{{ $outbound->No_Shipping }}</h2>
                <p class="text-xs text-slate-500">
                    Customer: <strong class="text-slate-800">{{ $outbound->customer->Nama ?? '-' }}</strong>
                    &nbsp;·&nbsp; Penerima: <strong class="text-slate-800">{{ $outbound->Nama_Penerima ?? '-' }}</strong>
                    &nbsp;·&nbsp; <i class="fa-regular fa-calendar mr-1"></i>{{ $outbound->Tanggal->format('d/m/Y') }}
                </p>
            </div>

            @if(!$outbound->isComplete())
                <form action="{{ route('outbound.picking-complete', $outbound->Outbound_ID) }}" method="POST"
                      onsubmit="return confirm('Tandai picking list ini SELESAI? Stok barang akan dikurangi.')">
                    @csrf
                    <button type="submit" class="btn btn-success btn-lg gap-2 cursor-pointer">
                        <i class="fa-solid fa-circle-check"></i> Mark as Complete
                    </button>
                </form>
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
            <i class="fa-solid fa-triangle-exclamation text-amber-500 text-base flex-shrink-0 mt-0.5"></i>
            <div>
                <strong class="font-bold text-slate-900">Petunjuk Pengambilan Barang</strong>
                <p class="text-slate-600 mt-0.5">
                    Ambil barang dari rak sesuai daftar. Centang setiap baris setelah diambil.
                    Klik <strong>Mark as Complete</strong> setelah semua barang terkumpul untuk mengaktifkan Surat Jalan PDF.
                </p>
            </div>
        </div>
    @endif

    {{-- Tabel Picking --}}
    <div class="wms-card overflow-hidden">
        <div class="wms-card-header">
            <h3 class="wms-card-title flex items-center gap-2">
                <i class="fa-solid fa-list-check text-[#0058be]"></i> Daftar Barang yang Diambil
            </h3>
            <span class="text-xs text-slate-400">{{ $outbound->outboundDetails->count() }} baris</span>
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
                            <tr id="row-{{ $i }}">
                                <td class="font-mono text-slate-400">{{ $i + 1 }}</td>
                                <td class="font-mono font-semibold text-[#0058be]">{{ $detail->SKU }}</td>
                                <td class="font-medium text-slate-900">{{ $detail->masterBarang->Nama ?? '-' }}</td>
                                <td>
                                    <span class="font-mono font-bold text-slate-800 bg-[#f2f4f6] border border-[#e2e8f0] px-2.5 py-1 rounded text-xs">
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
                                        <input type="checkbox" id="check-{{ $i }}" onchange="markRow({{ $i }})"
                                               class="w-4 h-4 rounded text-[#10b981] cursor-pointer">
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-[#f7f9fb] border-t border-[#e2e8f0]">
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

<script>
function markRow(idx){
    const row=document.getElementById(`row-${idx}`);
    const cb=document.getElementById(`check-${idx}`);
    row.classList.toggle('opacity-40',cb.checked);
    row.classList.toggle('line-through',cb.checked);
}
</script>
@endsection
