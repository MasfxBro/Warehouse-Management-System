@extends('layouts.app')

@section('title', 'Detail Barang - ' . $barang->SKU)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Detail Master Barang</h1>
            <p class="text-gray-600 mt-1">{{ $barang->SKU }} - {{ $barang->Nama }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('master.barang.edit', $barang->SKU) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors duration-200">
                Edit
            </a>
            <a href="{{ route('master.barang.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-semibold transition-colors duration-200">
                Kembali
            </a>
        </div>
    </div>

    <!-- Barang Info -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Informasi Barang</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">SKU</label>
                <p class="text-lg font-semibold text-gray-900 font-mono">{{ $barang->SKU }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Nama Barang</label>
                <p class="text-lg font-semibold text-gray-900">{{ $barang->Nama }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Kategori</label>
                <p class="text-lg font-semibold text-gray-900">{{ $barang->Kategori }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Satuan</label>
                <p class="text-lg font-semibold text-gray-900">{{ $barang->satuan }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Stok Real</label>
                <p class="text-2xl font-bold text-blue-600">{{ number_format($barang->stok_real) }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Minimum Stok</label>
                <p class="text-lg font-semibold text-gray-900">{{ number_format($barang->Min_Stok) }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Harga</label>
                <p class="text-lg font-semibold text-gray-900">Rp {{ number_format($barang->harga) }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Nilai Persediaan</label>
                <p class="text-lg font-semibold text-green-600">Rp {{ number_format($barang->getNilaiPersediaan()) }}</p>
            </div>
            
            @if($barang->rackLocation)
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Lokasi Rak</label>
                <p class="text-lg font-semibold text-gray-900 font-mono">{{ $barang->rackLocation->Kode_Rak }}</p>
                <p class="text-sm text-gray-600">{{ $barang->rackLocation->Lokasi }}</p>
            </div>
            @endif
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Status Stok</label>
                <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full {{ $barang->getStockStatus() === 'Low' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                    {{ $barang->getStockStatus() }}
                </span>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Dibuat</label>
                <p class="text-sm text-gray-900">{{ $barang->created_at->format('d/m/Y H:i') }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Diupdate</label>
                <p class="text-sm text-gray-900">{{ $barang->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('inventory.kartu-stok.show', $barang->SKU) }}" class="block bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow duration-200">
            <div class="flex items-center">
                <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <div>
                    <h3 class="font-semibold text-gray-900">Kartu Stok</h3>
                    <p class="text-sm text-gray-600">Lihat riwayat transaksi</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
