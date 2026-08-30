@extends('layouts.app')

@section('title', 'Transaksi Outbound')
@section('page_heading', 'Outbound - Pengiriman Barang Keluar')

@section('content')
<div class="space-y-5">

    {{-- Toolbar --}}
    <div class="wms-card p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h2 class="wms-card-title flex items-center gap-2">
                <i class="fa-solid fa-arrow-up-from-bracket text-[#0058be]"></i>
                Manajemen Outbound & Picking List
            </h2>
            <p class="page-subtitle">Kelola pengiriman barang, selesaikan picking list, unduh Surat Jalan.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <form action="{{ route('outbound.index') }}" method="GET" id="filter-form" class="flex items-center gap-2">
                <select name="customer_id" onchange="document.getElementById('filter-form').submit()"
                        class="wms-select w-auto text-xs">
                    <option value="">Semua Customer</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->Customer_ID }}" {{ request('customer_id') == $c->Customer_ID ? 'selected' : '' }}>
                            {{ $c->Nama }}
                        </option>
                    @endforeach
                </select>
                @if(request('customer_id'))
                    <a href="{{ route('outbound.index') }}" class="text-xs text-slate-400 hover:underline">Reset</a>
                @endif
            </form>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[11px] pointer-events-none"></i>
                <input type="text" id="live-search" placeholder="Cari No. SJ / Customer..."
                       class="wms-input w-52 text-xs" style="height:2.25rem;padding-left:2rem">
            </div>
            <a href="{{ route('outbound.create') }}" class="btn btn-primary btn-sm gap-1.5">
                <i class="fa-solid fa-plus"></i> Buat Outbound
            </a>
        </div>
    </div>

    {{-- TABEL 1: PICKING QUEUE --}}
    <div>
        <div class="flex items-center gap-2 mb-3">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-clipboard-list text-amber-500"></i> Antrian Picking List
            </h3>
            <span class="badge badge-warning">{{ $pickingQueue->count() }} pending</span>
        </div>
        <div class="wms-card overflow-hidden">
            <div class="overflow-x-auto">
                @if($pickingQueue->count() > 0)
                    <table class="wms-table">
                        <thead><tr>
                            <th>No. Outbound</th><th>Tanggal</th><th>Customer</th>
                            <th>Total Item</th><th>Prioritas</th><th class="text-right">Aksi</th>
                        </tr></thead>
                        <tbody>
                            @foreach($pickingQueue as $trx)
                                <tr class="outbound-row"
                                    data-search="{{ strtolower($trx->No_Shipping . ' ' . ($trx->customer->Nama ?? '')) }}">
                                    <td class="font-mono font-semibold text-[#0058be]">{{ $trx->No_Shipping }}</td>
                                    <td class="font-mono text-slate-600">{{ $trx->Tanggal->format('d/m/Y') }}</td>
                                    <td class="font-medium text-slate-900">{{ $trx->customer->Nama ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            <i class="fa-solid fa-box"></i>
                                            {{ $trx->outboundDetails->count() }} jenis
                                            ({{ number_format($trx->outboundDetails->sum('Qty')) }} unit)
                                        </span>
                                    </td>
                                    <td><span class="badge badge-{{ $trx->priority }}">{{ $trx->priorityLabel() }}</span></td>
                                    <td class="text-right">
                                        <a href="{{ route('outbound.picking-list', $trx->Outbound_ID) }}"
                                           class="btn btn-outline btn-sm gap-1">
                                            <i class="fa-solid fa-clipboard-check"></i> Picking List
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="py-10 text-center text-slate-400 text-xs space-y-2">
                        <i class="fa-solid fa-circle-check text-3xl text-emerald-400 block"></i>
                        <p>Tidak ada picking list yang pending.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- TABEL 2: RIWAYAT --}}
    <div>
        <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-slate-400"></i> Riwayat Outbound (Selesai)
        </h3>
        <div class="wms-card overflow-hidden">
            <div class="overflow-x-auto">
                @if($riwayat->count() > 0)
                    <table class="wms-table">
                        <thead><tr>
                            <th>No. Shipping</th><th>Tanggal</th><th>Customer</th>
                            <th>Status PL</th><th class="text-right">Aksi</th>
                        </tr></thead>
                        <tbody>
                            @foreach($riwayat as $trx)
                                <tr class="outbound-row"
                                    data-search="{{ strtolower($trx->No_Shipping . ' ' . ($trx->customer->Nama ?? '')) }}">
                                    <td class="font-mono font-semibold text-[#0058be]">{{ $trx->No_Shipping }}</td>
                                    <td class="font-mono text-slate-600">{{ $trx->Tanggal->format('d/m/Y') }}</td>
                                    <td class="font-medium text-slate-900">{{ $trx->customer->Nama ?? '-' }}</td>
                                    <td><span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Complete</span></td>
                                    <td class="text-right">
                                        <a href="{{ route('outbound.show', $trx->Outbound_ID) }}"
                                           class="btn btn-outline btn-sm gap-1">
                                            <i class="fa-solid fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="py-10 text-center text-slate-400 text-xs space-y-2">
                        <i class="fa-solid fa-box-open text-3xl block"></i>
                        <p>Belum ada transaksi outbound yang selesai.</p>
                    </div>
                @endif
            </div>
            @if($riwayat->hasPages())
                <div class="p-4 border-t border-[#e2e8f0] bg-[#f7f9fb]">{{ $riwayat->links() }}</div>
            @endif
        </div>
    </div>

</div>

<script>
document.getElementById('live-search')?.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.outbound-row').forEach(r => {
        r.style.display = (r.dataset.search || '').includes(q) ? '' : 'none';
    });
});
</script>
@endsection
