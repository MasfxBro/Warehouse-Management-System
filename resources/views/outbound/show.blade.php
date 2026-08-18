@extends('layouts.app')

@section('title', 'Detail Outbound - ' . $outbound->No_Shipping)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Detail Transaksi Outbound</h1>
            <p class="text-gray-600 mt-1">{{ $outbound->No_Shipping }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('outbound.picking-list', $outbound->Outbound_ID) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors duration-200 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Picking List
            </a>
            <a href="{{ route('outbound.surat-jalan', $outbound->Outbound_ID) }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors duration-200 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Surat Jalan
            </a>
            <a href="{{ route('outbound.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-semibold transition-colors duration-200">
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
                <label class="block text-sm font-medium text-gray-500 mb-1">No Shipping</label>
                <p class="text-lg font-semibold text-gray-900 font-mono">{{ $outbound->No_Shipping }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal</label>
                <p class="text-lg font-semibold text-gray-900">{{ $outbound->Tanggal->format('d F Y') }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full {{ $outbound->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ ucfirst($outbound->status) }}
                </span>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Customer</label>
                <p class="text-lg font-semibold text-gray-900">{{ $outbound->customer->Nama }}</p>
                <p class="text-sm text-gray-600">{{ $outbound->customer->Kontak }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Dibuat Oleh</label>
                <p class="text-lg font-semibold text-gray-900">{{ $outbound->user->name }}</p>
                <p class="text-sm text-gray-600">{{ $outbound->user->email }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Waktu Dibuat</label>
                <p class="text-lg font-semibold text-gray-900">{{ $outbound->created_at->format('d/m/Y H:i') }}</p>
            </div>
            
            @if($outbound->notes)
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-500 mb-1">Catatan</label>
                <p class="text-gray-900">{{ $outbound->notes }}</p>
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
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php $totalQty = 0; @endphp
                    @foreach($outbound->outboundDetails as $detail)
                    @php $totalQty += $detail->Qty; @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-sm font-semibold text-gray-900">{{ $detail->SKU }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $detail->masterBarang->Nama }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-lg font-bold text-gray-900">{{ number_format($detail->Qty) }}</span>
                            <span class="text-sm text-gray-600 ml-1">{{ $detail->masterBarang->satuan }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-right font-semibold text-gray-900">
                            Total:
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-xl font-bold text-blue-600">{{ number_format($totalQty) }}</span>
                            <span class="text-sm text-gray-600 ml-1">items</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
