@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_heading', 'Dashboard Overview')

@section('content')
<div class="space-y-5">

    <!-- Student Banner -->
    @if(auth()->user()->isUser() && session()->has('student_identity'))
        @php $student = session('student_identity'); @endphp
        <div class="flex items-center gap-4 bg-slate-900 text-white px-5 py-3.5 rounded-xl shadow-sm">
            <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-graduation-cap text-sm"></i>
            </div>
            <div class="flex items-center gap-6 text-[12px] flex-wrap">
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-0.5">Nama Operator</p>
                    <p class="font-bold text-white">{{ $student['name'] }}</p>
                </div>
                <div class="w-px h-8 bg-white/10 hidden sm:block"></div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-0.5">Kelas</p>
                    <p class="font-bold text-white">{{ $student['class'] }}</p>
                </div>
                <div class="w-px h-8 bg-white/10 hidden sm:block"></div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-0.5">NIS</p>
                    <p class="font-bold font-mono text-slate-200">{{ $student['nis'] }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================================
         STAT CARDS — 5 kolom
         ============================================================ -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

        <!-- Total SKU -->
        <div class="stat-card">
            <div>
                <p class="stat-card-label">Total SKU</p>
                <p class="stat-card-value">{{ number_format($totalSku) }}</p>
                <p class="stat-card-sub">Jenis barang</p>
            </div>
            <div class="stat-card-icon bg-slate-100 text-slate-600">
                <i class="fa-solid fa-boxes-stacked text-base"></i>
            </div>
        </div>

        <!-- Total Stok -->
        <div class="stat-card">
            <div>
                <p class="stat-card-label">Total Stok</p>
                <p class="stat-card-value">{{ number_format($totalStok) }}</p>
                <p class="stat-card-sub text-[#10b981] font-medium">unit di gudang</p>
            </div>
            <div class="stat-card-icon bg-emerald-50 text-[#10b981]">
                <i class="fa-solid fa-cubes text-base"></i>
            </div>
        </div>

        <!-- Nilai Gudang -->
        <div class="stat-card">
            <div>
                <p class="stat-card-label">Nilai Gudang</p>
                <p class="text-lg font-bold text-slate-900 mt-1 font-mono leading-none">
                    Rp {{ number_format($nilaiGudang / 1000000, 1) }}Jt
                </p>
                <p class="stat-card-sub">Total aset ({{ 'Rp ' . number_format($nilaiGudang, 0, ',', '.') }})</p>
            </div>
            <div class="stat-card-icon bg-indigo-50 text-indigo-600">
                <i class="fa-solid fa-sack-dollar text-base"></i>
            </div>
        </div>

        <!-- Inbound & Outbound dengan filter periode — span 2 kolom -->
        <div class="stat-card lg:col-span-2 flex-col gap-3 items-stretch">
            {{-- Header: label + filter periode --}}
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <p class="stat-card-label">Transaksi Masuk & Keluar</p>
                {{-- Filter Periode — konsep sama dengan chart period selector --}}
                <div class="inline-flex rounded-lg border border-[#e2e8f0] bg-[#f7f9fb] p-0.5 gap-0.5 flex-wrap">
                    @foreach([
                        'hari_ini' => 'Hari Ini',
                        '7_hari'   => '7 Hari',
                        '1_bulan'  => '1 Bulan',
                        '1_tahun'  => '1 Tahun',
                        'semua'    => 'Semua',
                    ] as $key => $label)
                        <a href="{{ route('dashboard', array_merge(request()->query(), ['period_trx' => $key, 'period' => $period])) }}"
                           class="px-2.5 py-1 rounded-md text-[11px] font-medium transition-colors
                                  {{ $periodTrx === $key ? 'bg-white text-secondary font-semibold shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
            {{-- Dua angka stat side by side --}}
            <div class="grid grid-cols-2 gap-4 pt-1">
                {{-- Inbound --}}
                <div class="flex items-center justify-between gap-3 bg-emerald-50 rounded-xl px-4 py-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 mb-0.5">Inbound</p>
                        <p class="text-2xl font-extrabold text-emerald-700 font-mono leading-none">{{ number_format($inboundTodayCount) }}</p>
                        <p class="text-[11px] text-emerald-600 mt-1">
                            {{ match($periodTrx) {
                                '7_hari'  => '7 hari terakhir',
                                '1_bulan' => '30 hari terakhir',
                                '1_tahun' => '1 tahun terakhir',
                                'semua'   => 'Seluruh data',
                                default   => 'Hari ini',
                            } }}
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-truck-ramp-box text-base"></i>
                    </div>
                </div>
                {{-- Outbound --}}
                <div class="flex items-center justify-between gap-3 bg-blue-50 rounded-xl px-4 py-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-secondary mb-0.5">Outbound</p>
                        <p class="text-2xl font-extrabold text-secondary font-mono leading-none">{{ number_format($outboundTodayCount) }}</p>
                        <p class="text-[11px] text-secondary mt-1">
                            {{ match($periodTrx) {
                                '7_hari'  => '7 hari terakhir',
                                '1_bulan' => '30 hari terakhir',
                                '1_tahun' => '1 tahun terakhir',
                                'semua'   => 'Seluruh data',
                                default   => 'Hari ini',
                            } }}
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-secondary flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-arrow-up-from-bracket text-base"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ============================================================
         ROW: CHART + RECENT ACTIVITY
         ============================================================ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Chart — 2/3 width -->
        <div class="lg:col-span-2 wms-card">
            <div class="wms-card-header">
                <div>
                    <h3 class="wms-card-title">Warehouse Activity</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Perbandingan barang masuk & keluar</p>
                </div>
                <div class="inline-flex rounded-lg border border-[#e2e8f0] bg-surface p-0.5 gap-0.5">
                    @foreach([
                        'seminggu_ini' => 'Minggu Ini',
                        'seminggu'     => '7 Hari',
                        'sebulan'      => '30 Hari',
                        'setahun'      => 'Setahun',
                    ] as $key => $label)
                        <a href="{{ route('dashboard', ['period' => $key]) }}"
                           class="px-2.5 py-1 rounded-md text-[11px] font-medium transition-colors
                                  {{ $period === $key ? 'bg-white text-secondary font-semibold shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="p-5">
                <div class="h-60">
                    <canvas id="inboundOutboundChart"></canvas>
                </div>
                <!-- Legend -->
                <div class="flex items-center gap-5 mt-3">
                    <div class="flex items-center gap-1.5 text-[11px] text-slate-500">
                        <span class="w-3 h-3 rounded-sm bg-[#10b981] inline-block"></span>
                        Inbound (Masuk)
                    </div>
                    <div class="flex items-center gap-1.5 text-[11px] text-slate-500">
                        <span class="w-3 h-3 rounded-sm bg-secondary inline-block"></span>
                        Outbound (Keluar)
                    </div>
                </div>
            </div>
        </div>

        <!-- Picking Queue — 1/3 width -->
        <div class="wms-card flex flex-col">
            <div class="wms-card-header">
                <div class="flex items-center gap-2">
                    <h3 class="wms-card-title">Antrian Picking</h3>
                    @if($pendingCount > 0)
                        <span class="badge badge-warning">{{ $pendingCount }} pending</span>
                    @else
                        <span class="badge badge-success">Kosong</span>
                    @endif
                </div>
                <a href="{{ route('outbound.index') }}" class="text-[11px] text-secondary hover:underline">
                    Lihat semua
                </a>
            </div>
            <div class="flex-1 overflow-y-auto divide-y divide-surface-low">
                @forelse($pendingOutbounds as $trx)
                    <a href="{{ route('outbound.picking-list', $trx->Outbound_ID) }}"
                       class="flex items-start gap-3 px-4 py-3 hover:bg-surface transition-colors group">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5
                            {{ $trx->priority === 'high' ? 'bg-error-container text-on-error-container' : ($trx->priority === 'normal' ? 'bg-[#fef3c7] text-[#92400e]' : 'bg-[#d1fae5] text-[#065f46]') }}">
                            <i class="fa-solid fa-clipboard-list text-[11px]"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[12px] font-semibold text-slate-800 font-mono truncate">{{ $trx->No_Shipping }}</p>
                            <p class="text-[11px] text-slate-500 truncate">{{ $trx->customer->Nama ?? '-' }}</p>
                        </div>
                        <span class="badge badge-{{ $trx->priority }} shrink-0 mt-0.5">{{ $trx->priorityLabel() }}</span>
                    </a>
                @empty
                    <div class="px-4 py-8 text-center text-[11px] text-slate-400">
                        <i class="fa-solid fa-circle-check text-2xl text-emerald-400 mb-2 block"></i>
                        Semua picking selesai
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ============================================================
         CRITICAL STOCK ALERTS
         ============================================================ -->
    <div class="wms-card overflow-hidden">
        <div class="wms-card-header">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-error-container flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation text-on-error-container text-[11px]"></i>
                </div>
                <h3 class="wms-card-title">Critical Stock Alerts</h3>
                <span class="badge badge-danger font-mono">{{ $lowStockCount }} item</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            @if($lowStockItems->count() > 0)
                <table class="wms-table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th class="text-right">Stok Saat Ini</th>
                            <th class="text-right">Min. Stok</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockItems as $item)
                            @php $stok = $item->computed_stok; @endphp
                            <tr>
                                <td class="font-mono font-semibold text-secondary">
                                    {{ $item->SKU }}
                                </td>
                                <td class="font-medium text-slate-800">{{ $item->Nama }}</td>
                                <td><span class="badge badge-neutral">{{ $item->Kategori }}</span></td>
                                <td class="text-right font-mono font-bold text-on-error-container">{{ number_format($stok) }}</td>
                                <td class="text-right font-mono text-slate-500">{{ number_format($item->Min_Stok) }}</td>
                                <td>
                                    @if($stok == 0)
                                        <span class="badge badge-danger">
                                            <i class="fa-solid fa-circle-xmark text-[9px]"></i>
                                            Habis
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            <i class="fa-solid fa-arrow-down text-[9px]"></i>
                                            Reorder
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Pagination — muncul hanya jika data > 10 --}}
                @if($lowStockItems->hasPages())
                    <div class="px-5 py-3 border-t border-[#e2e8f0] bg-surface-low flex items-center justify-between gap-4 flex-wrap">
                        <p class="text-[11px] text-slate-400">
                            Menampilkan {{ $lowStockItems->firstItem() }}–{{ $lowStockItems->lastItem() }}
                            dari <span class="font-semibold text-slate-600">{{ $lowStockItems->total() }}</span> item kritis
                        </p>
                        <div class="flex items-center gap-1">
                            {{-- Prev --}}
                            @if($lowStockItems->onFirstPage())
                                <span class="px-2.5 py-1 rounded-md text-[11px] text-slate-300 border border-[#e2e8f0] cursor-not-allowed bg-white">
                                    <i class="fa-solid fa-chevron-left text-[9px]"></i>
                                </span>
                            @else
                                <a href="{{ $lowStockItems->previousPageUrl() }}"
                                   class="px-2.5 py-1 rounded-md text-[11px] text-slate-600 border border-[#e2e8f0] hover:bg-white hover:border-secondary hover:text-secondary transition-colors">
                                    <i class="fa-solid fa-chevron-left text-[9px]"></i>
                                </a>
                            @endif

                            {{-- Nomor halaman --}}
                            @foreach($lowStockItems->getUrlRange(1, $lowStockItems->lastPage()) as $page => $url)
                                @if($page == $lowStockItems->currentPage())
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-secondary text-white border border-secondary">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}"
                                       class="px-2.5 py-1 rounded-md text-[11px] text-slate-600 border border-[#e2e8f0] hover:bg-white hover:border-secondary hover:text-secondary transition-colors">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach

                            {{-- Next --}}
                            @if($lowStockItems->hasMorePages())
                                <a href="{{ $lowStockItems->nextPageUrl() }}"
                                   class="px-2.5 py-1 rounded-md text-[11px] text-slate-600 border border-[#e2e8f0] hover:bg-white hover:border-secondary hover:text-secondary transition-colors">
                                    <i class="fa-solid fa-chevron-right text-[9px]"></i>
                                </a>
                            @else
                                <span class="px-2.5 py-1 rounded-md text-[11px] text-slate-300 border border-[#e2e8f0] cursor-not-allowed bg-white">
                                    <i class="fa-solid fa-chevron-right text-[9px]"></i>
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

            @else
                <div class="py-10 text-center text-[11px] text-slate-400 space-y-2">
                    <i class="fa-solid fa-shield-halved text-3xl text-emerald-400 block"></i>
                    <p class="font-medium">Semua stok dalam kondisi aman di atas batas minimum.</p>
                </div>
            @endif
        </div>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const ctx  = document.getElementById('inboundOutboundChart')?.getContext('2d');
    if (!ctx) return;
    const data = @json($chartData);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Inbound',
                    data: data.inbound,
                    backgroundColor: '#10b981',
                    borderRadius: 4,
                    barPercentage: 0.55,
                    categoryPercentage: 0.7,
                },
                {
                    label: 'Outbound',
                    data: data.outbound,
                    backgroundColor: '#0058be',
                    borderRadius: 4,
                    barPercentage: 0.55,
                    categoryPercentage: 0.7,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#191c1e',
                    titleFont: { family: 'Inter', size: 11, weight: '600' },
                    bodyFont:  { family: 'JetBrains Mono', size: 11 },
                    padding: 10,
                    cornerRadius: 6,
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { font: { family: 'Inter', size: 11 }, color: '#76777d' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#eceef0', drawBorder: false },
                    border: { display: false, dash: [4, 4] },
                    ticks: { font: { family: 'JetBrains Mono', size: 10 }, color: '#76777d' }
                }
            }
        }
    });
});
</script>
@endsection
