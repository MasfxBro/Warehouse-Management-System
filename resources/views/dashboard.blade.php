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

        <!-- Inbound Hari Ini -->
        <div class="stat-card">
            <div>
                <p class="stat-card-label">Inbound Hari Ini</p>
                <p class="stat-card-value text-[#10b981]">{{ number_format($inboundTodayCount) }}</p>
                <p class="stat-card-sub">Transaksi masuk</p>
            </div>
            <div class="stat-card-icon bg-emerald-50 text-[#10b981]">
                <i class="fa-solid fa-truck-ramp-box text-base"></i>
            </div>
        </div>

        <!-- Outbound Hari Ini -->
        <div class="stat-card">
            <div>
                <p class="stat-card-label">Outbound Hari Ini</p>
                <p class="stat-card-value text-secondary">{{ number_format($outboundTodayCount) }}</p>
                <p class="stat-card-sub">Transaksi keluar</p>
            </div>
            <div class="stat-card-icon bg-blue-50 text-secondary">
                <i class="fa-solid fa-arrow-up-from-bracket text-base"></i>
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
            <a href="{{ auth()->user()->isAdmin() ? route('inventory.kartu-stok.index') : route('inventory.stock-opname.index') }}"
               class="text-[11px] text-secondary hover:underline flex items-center gap-1">
                View All Inventory
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
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
                            <th>Aksi</th>
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
                                <td>
                                    <a href="{{ route('inbound.create') }}"
                                       class="text-[11px] text-secondary hover:underline font-medium">
                                        + Inbound
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
