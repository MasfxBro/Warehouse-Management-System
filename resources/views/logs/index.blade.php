@extends('layouts.app')

@section('title', 'Log Activity')
@section('page_heading', 'System Activity Log')

@section('content')
<div class="space-y-6">

    {{-- Header + Search + Filter --}}
    <div class="wms-card p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-secondary"></i>
                Riwayat Aktivitas Sistem
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Mencatat seluruh aksi Guru (Admin) dan Siswa (Operator). Read-only.</p>
        </div>

        <form action="{{ route('logs.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
            {{-- Date Range Dropdown --}}
            <div class="relative">
                <select name="date_range" onchange="this.form.submit()"
                        class="wms-select text-xs pr-8 appearance-none" style="min-width:130px;">
                    <option value="all"   {{ $dateRange === 'all'   ? 'selected' : '' }}>Semua Data</option>
                    <option value="today" {{ $dateRange === 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="week"  {{ $dateRange === 'week'  ? 'selected' : '' }}>Minggu Ini</option>
                    <option value="month" {{ $dateRange === 'month' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="year"  {{ $dateRange === 'year'  ? 'selected' : '' }}>Tahun Ini</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
                    <svg class="w-3 h-3 text-slate-400" viewBox="0 0 12 12" fill="none">
                        <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>

            {{-- Search --}}
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[11px] pointer-events-none"></i>
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Cari operator / aksi..."
                       class="wms-input text-xs" style="width:14rem;padding-left:2rem">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                Cari
            </button>
            @if($search || $dateRange !== 'all')
                <a href="{{ route('logs.index') }}"
                   class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1">
                    <i class="fa-solid fa-xmark text-[10px]"></i> Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Logs Table --}}
    <div class="wms-card overflow-hidden">
        <div class="overflow-x-auto">
            @if($logs->count() > 0)
                <table class="wms-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Waktu (Timestamp)</th>
                            <th>Akun User</th>
                            <th>Identitas Operator / Siswa</th>
                            <th>Aktivitas (Action)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $index => $log)
                            <tr>
                                <td class="font-mono text-slate-400">{{ $logs->firstItem() + $index }}</td>
                                <td class="font-mono text-slate-600 whitespace-nowrap">
                                    <i class="fa-regular fa-clock text-slate-300 mr-1"></i>
                                    {{ $log->created_at->format('d M Y, H:i:s') }}
                                </td>
                                <td>
                                    <span class="font-bold text-slate-900">{{ $log->user->name ?? 'Guest/System' }}</span>
                                    <div class="text-[10px] text-slate-500 font-mono">{{ $log->user->email ?? '-' }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-neutral">
                                        <i class="fa-solid fa-user"></i>
                                        {{ $log->operator_name }}
                                    </span>
                                </td>
                                <td class="font-mono text-slate-800">{{ $log->action }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-12 text-center text-slate-400 text-xs">
                    <i class="fa-solid fa-folder-open text-3xl mb-3 block"></i>
                    Belum ada data aktivitas log yang tercatat.
                </div>
            @endif
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-[#e2e8f0] bg-slate-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
