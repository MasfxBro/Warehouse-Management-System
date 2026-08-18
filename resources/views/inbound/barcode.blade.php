@extends('layouts.app')

@section('title', 'Barcode Labels - ' . $inbound->No_Receiving)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Barcode Labels</h1>
            <p class="text-gray-600 mt-1">{{ $inbound->No_Receiving }}</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors duration-200 flex items-center gap-2 print:hidden">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
            <a href="{{ route('inbound.show', $inbound->Inbound_ID) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-semibold transition-colors duration-200 print:hidden">
                Kembali
            </a>
        </div>
    </div>

    <!-- Barcode Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 print:gap-2">
        @foreach($barcodes as $barcode)
        <div class="bg-white border-2 border-gray-300 rounded-lg p-4 text-center print:break-inside-avoid">
            <!-- Barcode Image -->
            <div class="mb-2 flex justify-center">
                <img src="data:image/png;base64,{{ $barcode['image'] }}" alt="Barcode" class="max-w-full h-auto">
            </div>
            
            <!-- SKU -->
            <div class="font-mono font-bold text-lg text-gray-900 mb-1">
                {{ $barcode['sku'] }}
            </div>
            
            <!-- Nama Barang -->
            <div class="text-sm text-gray-700 mb-2 line-clamp-2">
                {{ $barcode['nama'] }}
            </div>
            
            <!-- Batch & Qty -->
            <div class="border-t pt-2 mt-2">
                <div class="flex justify-between text-xs text-gray-600">
                    <span>Batch:</span>
                    <span class="font-mono font-semibold">{{ $barcode['batch'] }}</span>
                </div>
                <div class="flex justify-between text-xs text-gray-600 mt-1">
                    <span>Qty:</span>
                    <span class="font-bold text-blue-600">{{ number_format($barcode['qty']) }}</span>
                </div>
            </div>
            
            <!-- No Receiving -->
            <div class="text-xs text-gray-500 mt-2 font-mono">
                {{ $inbound->No_Receiving }}
            </div>
        </div>
        @endforeach
    </div>
</div>

<style>
@media print {
    body {
        background: white;
    }
    .container {
        max-width: 100%;
        padding: 0;
    }
    .grid {
        gap: 8px;
    }
    .rounded-lg {
        border-radius: 4px;
    }
}
</style>
@endsection
