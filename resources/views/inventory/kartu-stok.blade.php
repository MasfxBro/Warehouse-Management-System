@extends('layouts.app')

@section('title', 'Kartu Stok')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Kartu Stok</h1>
        <p class="text-gray-600 mt-1">Riwayat pergerakan stok per barang</p>
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

    <!-- Stock Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($barangs as $barang)
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r {{ $barang['status'] === 'Low' ? 'from-red-500 to-red-600' : ($barang['status'] === 'OK' ? 'from-green-500 to-green-600' : 'from-blue-500 to-blue-600') }} p-4">
                <h3 class="text-white font-bold text-lg">{{ $barang['nama'] }}</h3>
                <p class="text-white text-sm opacity-90 font-mono">{{ $barang['sku'] }}</p>
            </div>

            <!-- Body -->
            <div class="p-4">
                <!-- Stock Info -->
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Stok Saat Ini:</span>
                        <span class="text-2xl font-bold text-gray-900">{{ number_format($barang['stok_real']) }}</span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Min. Stok:</span>
                        <span class="text-sm font-semibold text-gray-700">{{ number_format($barang['min_stok']) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Satuan:</span>
                        <span class="text-sm font-semibold text-gray-700">{{ $barang['satuan'] }}</span>
                    </div>
                </div>

                <!-- Value -->
                <div class="border-t pt-3 mb-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Nilai Persediaan:</span>
                        <span class="text-lg font-bold text-blue-600">Rp {{ number_format($barang['nilai']) }}</span>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="mb-4">
                    @if($barang['status'] === 'Low')
                    <span class="inline-block w-full text-center px-3 py-2 text-sm font-semibold rounded-lg bg-red-100 text-red-800">
                        ⚠️ Stok Rendah
                    </span>
                    @elseif($barang['status'] === 'OK')
                    <span class="inline-block w-full text-center px-3 py-2 text-sm font-semibold rounded-lg bg-green-100 text-green-800">
                        ✓ Stok Aman
                    </span>
                    @else
                    <span class="inline-block w-full text-center px-3 py-2 text-sm font-semibold rounded-lg bg-blue-100 text-blue-800">
                        ✓ Stok Lebih
                    </span>
                    @endif
                </div>

                <!-- Action -->
                <a href="{{ route('inventory.kartu-stok.show', $barang['sku']) }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors duration-200">
                    Lihat Detail Kartu Stok
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white rounded-lg shadow-md p-10 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-gray-500">Tidak ada data barang</p>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
