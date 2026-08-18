@extends('layouts.app')

@section('title', 'Picking List - ' . $outbound->No_Shipping)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Picking List (FIFO)</h1>
            <p class="text-gray-600 mt-1">{{ $outbound->No_Shipping }}</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors duration-200 flex items-center gap-2 print:hidden">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
            <a href="{{ route('outbound.show', $outbound->Outbound_ID) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-semibold transition-colors duration-200 print:hidden">
                Kembali
            </a>
        </div>
    </div>

    <!-- Outbound Info -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">No Shipping</label>
                <p class="text-sm font-semibold text-gray-900 font-mono">{{ $outbound->No_Shipping }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal</label>
                <p class="text-sm font-semibold text-gray-900">{{ $outbound->Tanggal->format('d/m/Y') }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Customer</label>
                <p class="text-sm font-semibold text-gray-900">{{ $outbound->customer->Nama }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Print Date</label>
                <p class="text-sm font-semibold text-gray-900">{{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    <!-- Picking Instructions -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 print:bg-white print:border-gray-300">
        <h3 class="font-semibold text-blue-900 mb-2">Instruksi Picking:</h3>
        <ul class="text-sm text-blue-800 space-y-1">
            <li>✓ Ambil barang sesuai urutan FIFO (First In, First Out)</li>
            <li>✓ Periksa batch dan expired date sebelum mengambil</li>
            <li>✓ Centang checkbox setelah item diambil</li>
            <li>✓ Laporkan jika ada perbedaan qty atau batch tidak ditemukan</li>
        </ul>
    </div>

    <!-- Picking List -->
    @foreach($pickingData as $index => $item)
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6 print:break-inside-avoid">
        <!-- Item Header -->
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $item['nama'] }}</h3>
                    <p class="text-sm text-gray-600 font-mono">SKU: {{ $item['sku'] }}</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($item['qty_total']) }}</p>
                    <p class="text-sm text-gray-600">Total Qty</p>
                </div>
            </div>
        </div>

        <!-- Pick Details -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-16">✓</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi Rak</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty to Pick</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expired Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($item['picks'] as $pickIndex => $pick)
                    <tr class="{{ $pickIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                        <td class="px-6 py-4">
                            <input type="checkbox" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-sm font-semibold text-gray-900">{{ $pick['batch'] }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-sm font-bold text-blue-600">{{ $pick['rack'] }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-lg font-bold text-gray-900">{{ number_format($pick['qty']) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($pick['expired'])
                                {{ \Carbon\Carbon::parse($pick['expired'])->format('d/m/Y') }}
                                @if(\Carbon\Carbon::parse($pick['expired'])->isPast())
                                    <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        EXPIRED
                                    </span>
                                @elseif(\Carbon\Carbon::parse($pick['expired'])->diffInDays(now()) <= 30)
                                    <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Soon
                                    </span>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    <!-- Signature Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mt-8 print:break-before-avoid">
        <div class="grid grid-cols-2 gap-8">
            <div class="text-center">
                <p class="text-sm font-medium text-gray-700 mb-16">Picker</p>
                <div class="border-t-2 border-gray-300 pt-2">
                    <p class="text-sm text-gray-600">Nama & Tanda Tangan</p>
                </div>
            </div>
            <div class="text-center">
                <p class="text-sm font-medium text-gray-700 mb-16">Checker</p>
                <div class="border-t-2 border-gray-300 pt-2">
                    <p class="text-sm text-gray-600">Nama & Tanda Tangan</p>
                </div>
            </div>
        </div>
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
}
</style>
@endsection
