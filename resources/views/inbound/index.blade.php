@extends('layouts.app')

@section('title', 'Transaksi Inbound')
@section('page_heading', 'Inbound - Penerimaan Barang Masuk')

@section('content')
<div class="space-y-5">

    {{-- Toolbar --}}
    <div class="wms-card p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h2 class="wms-card-title flex items-center gap-2">
                <i class="fa-solid fa-arrow-down text-[#10b981]"></i>
                Riwayat Transaksi Inbound
            </h2>
            <p class="page-subtitle">Seluruh penerimaan barang dari supplier tercatat di sini.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Filter Supplier --}}
            <form action="{{ route('inbound.index') }}" method="GET" id="filter-form" class="flex items-center gap-2">
                <select name="supplier_id" onchange="document.getElementById('filter-form').submit()"
                        class="wms-select w-auto text-xs" style="height:2.25rem;padding-top:0;padding-bottom:0;">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->Supplier_ID }}" {{ request('supplier_id') == $s->Supplier_ID ? 'selected' : '' }}>
                            {{ $s->Nama }}
                        </option>
                    @endforeach
                </select>
                @if(request('supplier_id'))
                    <a href="{{ route('inbound.index') }}" class="text-xs text-slate-400 hover:underline">Reset</a>
                @endif
            </form>
            {{-- Live Search --}}
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[11px] pointer-events-none"></i>
                <input type="text" id="live-search" placeholder="Cari No. RSI / Supplier..."
                       class="wms-input w-52 text-xs" style="height:2.25rem;padding-left:2rem">
            </div>
            {{-- Tambah --}}
            <a href="{{ route('inbound.create') }}" class="btn btn-primary btn-sm gap-1.5">
                <i class="fa-solid fa-plus"></i> Tambah Inbound
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="wms-card overflow-hidden">
        <div class="overflow-x-auto">
            @if($transactions->count() > 0)
                <table class="wms-table">
                    <thead>
                        <tr>
                            <th>No. RSI</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Total Jenis</th>
                            <th>Catatan</th>
                            <th>Dicatat Oleh</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="inbound-tbody">
                        @foreach($transactions as $trx)
                            <tr class="inbound-row"
                                data-search="{{ strtolower($trx->No_Receiving . ' ' . ($trx->supplier->Nama ?? '')) }}">
                                <td class="font-mono font-semibold text-[#0058be]">{{ $trx->No_Receiving }}</td>
                                <td class="font-mono text-slate-600">{{ $trx->Tanggal->format('d/m/Y') }}</td>
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-building text-slate-300 text-xs"></i>
                                        <span class="font-medium text-slate-800">{{ $trx->supplier->Nama ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fa-solid fa-box"></i>
                                        {{ $trx->inboundDetails->count() }} jenis
                                    </span>
                                </td>
                                <td class="text-slate-500 max-w-xs truncate text-xs">{{ $trx->Catatan ?? '—' }}</td>
                                <td class="text-slate-600 text-xs">{{ $trx->user->name ?? '-' }}</td>
                                <td class="text-right">
                                    <a href="{{ route('inbound.show', $trx->Inbound_ID) }}"
                                       class="btn btn-outline btn-sm gap-1">
                                        <i class="fa-solid fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="py-14 text-center text-slate-400 text-xs space-y-2">
                    <i class="fa-solid fa-inbox text-3xl block"></i>
                    <p>Belum ada transaksi inbound yang tercatat.</p>
                </div>
            @endif
        </div>
        @if($transactions->hasPages())
            <div class="p-4 border-t border-[#e2e8f0] bg-[#f7f9fb]">{{ $transactions->links() }}</div>
        @endif
    </div>

</div>

<script>
document.getElementById('live-search')?.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.inbound-row').forEach(r => {
        r.style.display = (r.dataset.search || '').includes(q) ? '' : 'none';
    });
});
</script>
@endsection
