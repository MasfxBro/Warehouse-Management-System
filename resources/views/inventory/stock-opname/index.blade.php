@extends('layouts.app')

@section('title', 'Stock Opname')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Stock Opname</h1>
            <p class="text-gray-600 mt-1">Audit dan koreksi stok fisik</p>
        </div>
        <a href="{{ route('inventory.stock-opname.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Stock Opname
        </a>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stok Sistem</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stok Fisik</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Selisih</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($opnames as $opname)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $opname->tanggal_opname->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="font-mono text-sm font-semibold text-gray-900">{{ $opname->SKU }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ $opname->masterBarang->Nama }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                        {{ number_format($opname->stok_sistem) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                        {{ number_format($opname->stok_fisik) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        @if($opname->selisih > 0)
                        <span class="px-2 py-1 text-sm font-bold rounded-full bg-green-100 text-green-800">
                            +{{ number_format($opname->selisih) }}
                        </span>
                        @elseif($opname->selisih < 0)
                        <span class="px-2 py-1 text-sm font-bold rounded-full bg-red-100 text-red-800">
                            {{ number_format($opname->selisih) }}
                        </span>
                        @else
                        <span class="px-2 py-1 text-sm font-bold rounded-full bg-gray-100 text-gray-800">
                            0
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $opname->user->name }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="mt-2">Belum ada stock opname</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50">
            {{ $opnames->links() }}
        </div>
    </div>
</div>
@endsection
