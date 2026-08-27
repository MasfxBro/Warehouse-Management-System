@extends('layouts.app')

@section('title', 'Log Activity')
@section('page_heading', 'System Activity Log')

@section('content')
<div class="space-y-6">

    {{-- Header + Search --}}
    <div class="wms-card p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-[#0058be]"></i>
                Riwayat Aktivitas Sistem
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Mencatat seluruh aksi Guru (Admin) dan Siswa (Operator) selama penggunaan sistem WMS. Read-only.</p>
        </div>

        <form action="{{ route('logs.index') }}" method="GET" class="flex items-center gap-2">
            <div class="search-group">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Cari operator / aksi..."
                       class="search-input" style="width:16rem;">
                <button type="submit" class="search-btn">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari
                </button>
            </div>
            @if($search)
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
