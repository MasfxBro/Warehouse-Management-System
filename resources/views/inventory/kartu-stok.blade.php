@extends('layouts.app')

@section('title', 'Kartu Stok')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Kartu Stok / Stock Ledger</h1>
            <p class="text-gray-600 mt-1">Ringkasan kondisi persediaan & pergerakan barang real-time</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('inventory.kartu-stok.export-excel') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors duration-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </a>
            <a href="{{ route('inventory.kartu-stok.export-pdf') }}" target="_blank" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors duration-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Export PDF
            </a>
        </div>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('inventory.kartu-stok') }}" class="flex gap-4">
            <div class="flex-1">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Cari SKU atau Nama Barang..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>
            <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-2 rounded-lg font-semibold transition-colors duration-200">
                Cari
            </button>
            @if(request('search'))
            <a href="{{ route('inventory.kartu-stok') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold transition-colors duration-200">
                Reset
            </a>
            @endif
        </form>
    </div>

    <!-- Table View -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Awal</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Masuk</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Keluar</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Akhir</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Min. Stok</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($barangs as $barang)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-semibold text-gray-900">
                            {{ $barang['sku'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                            {{ $barang['nama'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                            {{ number_format($barang['stok_awal']) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-green-600">
                            +{{ number_format($barang['total_masuk']) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-red-600">
                            -{{ number_format($barang['total_keluar']) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-base font-bold text-gray-900">
                            {{ number_format($barang['stok_akhir']) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                            {{ number_format($barang['min_stok']) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($barang['status'] === 'REORDER')
                            <span class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                                ⚠️ REORDER
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800">
                                ✓ AMAN
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                            <a href="{{ route('inventory.kartu-stok.show', $barang['sku']) }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition-colors duration-200">
                                Detail Riwayat
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-10 text-center text-gray-500">
                            Tidak ada data barang
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
