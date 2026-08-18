@extends('layouts.app')

@section('title', 'Stock Opname')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Stock Opname (Audit Fisik)</h1>
            <p class="text-gray-600 mt-1">Pencatatan hasil audit stok fisik vs stok sistem</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('inventory.stock-opname.export-excel') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg font-semibold text-sm transition-colors duration-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </a>
            <a href="{{ route('inventory.stock-opname.export-pdf') }}" target="_blank" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg font-semibold text-sm transition-colors duration-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Export PDF
            </a>
            <a href="{{ route('inventory.stock-opname.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg font-semibold text-sm transition-colors duration-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Audit Opname
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Sistem</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Fisik</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Selisih (Variance)</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tindakan / Catatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Petugas</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($opnames as $opname)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($opname->tanggal_opname)->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-semibold text-gray-900">
                            {{ $opname->SKU }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                            {{ $opname->masterBarang->Nama ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-700">
                            {{ number_format($opname->stok_sistem) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                            {{ number_format($opname->stok_fisik) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold">
                            @if($opname->variance > 0)
                            <span class="text-green-600">+{{ number_format($opname->variance) }}</span>
                            @elseif($opname->variance < 0)
                            <span class="text-red-600">{{ number_format($opname->variance) }}</span>
                            @else
                            <span class="text-gray-500">0</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($opname->variance == 0)
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800">
                                ✓ SESUAI
                            </span>
                            @elseif($opname->variance < 0)
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                                🔻 KURANG
                            </span>
                            @else
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                                🔺 LEBIH
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                            @if($opname->action_taken)
                            <div class="font-medium text-gray-800">{{ $opname->action_taken }}</div>
                            @endif
                            @if($opname->notes)
                            <div class="text-xs text-gray-500 italic">{{ $opname->notes }}</div>
                            @endif
                            @if(!$opname->action_taken && !$opname->notes)
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $opname->user->name ?? 'System' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-10 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="mt-2 text-gray-600">Belum ada data pencatatan stock opname</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($opnames->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t">
            {{ $opnames->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
