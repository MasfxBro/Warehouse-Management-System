@extends('layouts.app')

@section('title', 'Detail Inbound - ' . $inbound->No_Receiving)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Detail Transaksi Inbound</h1>
            <p class="text-gray-600 mt-1">{{ $inbound->No_Receiving }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('inbound.barcode', $inbound->Inbound_ID) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors duration-200 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                Generate Barcode
            </a>
            <a href="{{ route('inbound.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-semibold transition-colors duration-200">
                Kembali
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif

    <!-- Header Info -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Informasi Header</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">No Receiving</label>
                <p class="text-lg font-semibold text-gray-900 font-mono">{{ $inbound->No_Receiving }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal</label>
                <p class="text-lg font-semibold text-gray-900">{{ $inbound->Tanggal->format('d F Y') }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full {{ $inbound->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ ucfirst($inbound->status) }}
                </span>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Supplier</label>
                <p class="text-lg font-semibold text-gray-900">{{ $inbound->supplier->Nama }}</p>
                <p class="text-sm text-gray-600">{{ $inbound->supplier->Kontak }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Dibuat Oleh</label>
                <p class="text-lg font-semibold text-gray-900">{{ $inbound->user->name }}</p>
                <p class="text-sm text-gray-600">{{ $inbound->user->email }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Waktu Dibuat</label>
                <p class="text-lg font-semibold text-gray-900">{{ $inbound->created_at->format('d/m/Y H:i') }}</p>
            </div>
            
            @if($inbound->notes)
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-500 mb-1">Catatan</label>
                <p class="text-gray-900">{{ $inbound->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Detail Items -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Detail Barang</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rak</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expired Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php $totalQty = 0; @endphp
                    @foreach($inbound->inboundDetails as $detail)
                    @php $totalQty += $detail->Qty; @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-sm font-semibold text-gray-900">{{ $detail->SKU }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $detail->masterBarang->Nama }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <span class="font-mono">{{ $detail->rackLocation->Kode_Rak }}</span>
                            <br>
                            <span class="text-xs text-gray-500">{{ $detail->rackLocation->Lokasi }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-lg font-bold text-gray-900">{{ number_format($detail->Qty) }}</span>
                            <span class="text-sm text-gray-600 ml-1">{{ $detail->masterBarang->satuan }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-sm text-gray-900">{{ $detail->Batch }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @if($detail->expired_date)
                                {{ $detail->expired_date->format('d/m/Y') }}
                                @if($detail->expired_date->isPast())
                                    <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Expired
                                    </span>
                                @elseif($detail->expired_date->diffInDays(now()) <= 30)
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
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-right font-semibold text-gray-900">
                            Total:
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-xl font-bold text-blue-600">{{ number_format($totalQty) }}</span>
                            <span class="text-sm text-gray-600 ml-1">items</span>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
