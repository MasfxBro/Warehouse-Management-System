@extends('layouts.app')

@section('title', 'Sesi Praktikum')
@section('page_heading', 'Sesi Praktikum WMS')

@section('content')
<div class="space-y-5">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="wms-card p-6">
            <h2 class="wms-card-title mb-4"><i class="fa-solid fa-chalkboard-user text-secondary mr-1"></i> Sesi Aktif</h2>
            @if($current)
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4">
                    <span class="badge badge-success mb-2">Aktif</span>
                    <h3 class="font-bold text-slate-900">{{ $current->Nama }}</h3>
                    <p class="text-xs text-slate-600 mt-1">{{ $current->Kelas ?? 'Semua kelas' }} · {{ $current->Tanggal->format('d/m/Y') }}</p>
                </div>
                <form action="{{ route('practice-sessions.close', $current->Practice_Session_ID) }}" method="POST" class="mt-4 space-y-2">
                    @csrf
                    <label class="wms-label">Ketik <strong>TUTUP SESI</strong> untuk menutup</label>
                    <div class="flex gap-2">
                        <input name="confirmation" class="wms-input" autocomplete="off" placeholder="TUTUP SESI">
                        <button class="btn btn-danger whitespace-nowrap"><i class="fa-solid fa-lock"></i> Tutup</button>
                    </div>
                    @error('confirmation')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                </form>
            @else
                <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-xs text-amber-800">
                    Tidak ada sesi aktif. Inbound, outbound, dan stock opname baru tidak dapat dibuat.
                </div>
            @endif
        </div>

        <div class="wms-card p-6">
            <h2 class="wms-card-title mb-4"><i class="fa-solid fa-plus text-secondary mr-1"></i> Buka Sesi Baru</h2>
            <form action="{{ route('practice-sessions.store') }}" method="POST" class="space-y-3">
                @csrf
                <div><label class="wms-label">Nama Sesi *</label><input name="Nama" value="{{ old('Nama') }}" required class="wms-input" placeholder="Praktikum Inbound Kelas X"></div>
                <div><label class="wms-label">Kelas</label><input name="Kelas" value="{{ old('Kelas') }}" class="wms-input" placeholder="X Manajemen Logistik 1"></div>
                <div><label class="wms-label">Tanggal *</label><input type="date" name="Tanggal" value="{{ old('Tanggal', now()->toDateString()) }}" required class="wms-input"></div>
                <button class="btn btn-primary w-full justify-center" {{ $current ? 'disabled' : '' }}><i class="fa-solid fa-play"></i> Buka Sesi Praktikum</button>
            </form>
        </div>
    </div>

    <div class="wms-card overflow-hidden">
        <div class="wms-card-header"><h3 class="wms-card-title">Riwayat Sesi</h3></div>
        <div class="overflow-x-auto">
            <table class="wms-table">
                <thead><tr><th>Nama</th><th>Kelas</th><th>Tanggal</th><th>Status</th><th>Dibuat Oleh</th></tr></thead>
                <tbody>
                    @foreach($sessions as $session)
                        <tr>
                            <td class="font-semibold">{{ $session->Nama }}</td>
                            <td>{{ $session->Kelas ?? '-' }}</td>
                            <td class="font-mono">{{ $session->Tanggal->format('d/m/Y') }}</td>
                            <td><span class="badge {{ $session->Status === 'active' ? 'badge-success' : 'badge-neutral' }}">{{ $session->Status === 'active' ? 'Aktif' : 'Ditutup' }}</span></td>
                            <td>{{ $session->creator->name ?? 'Sistem' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($sessions->hasPages())<div class="p-4 border-t border-slate-200">{{ $sessions->links() }}</div>@endif
    </div>
</div>
@endsection
