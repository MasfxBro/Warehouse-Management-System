@extends('layouts.app')

@section('title', 'Kartu Stok - ' . $barang->SKU)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Kartu Stok Detail</h1>
            <p class="text-gray-600 mt-1">{{ $barang->Nama }} ({{ $barang->SKU }})</p>
        </div>
        <a href="{{ route('inventory.kartu-stok') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-semibold transition-colors duration-200">
            Kembali
        </a>
    </div>

    <!-- Barang Info -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">SKU</label>
                <p class="text-lg font-semibold text-gray-900 font-mono">{{ $barang->SKU }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Stok Real</label>
                <p class="text-2xl font-bold text-blue-600">{{ number_format($barang->stok_real) }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Min Stok</label>
                <p class="text-lg font-semibold text-gray-900">{{ number_format($barang->Min_Stok) }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full {{ $barang->getStockStatus() === 'Low' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                    {{ $barang->getStockStatus() }}
                </span>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Riwayat Transaksi</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No Transaksi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Masuk</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Keluar</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Saldo</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($ledger as $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($item['tanggal'])->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-sm font-semibold text-gray-900">{{ $item['no_trans'] }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($item['jenis'] === 'INBOUND')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                INBOUND
                            </span>
                            @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                OUTBOUND
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">
                            {{ $item['batch'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            @if($item['qty_in'] > 0)
                            <span class="font-bold text-green-600">+{{ number_format($item['qty_in']) }}</span>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            @if($item['qty_out'] > 0)
                            <span class="font-bold text-red-600">-{{ number_format($item['qty_out']) }}</span>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-lg font-bold text-gray-900">{{ number_format($item['saldo']) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                            Belum ada transaksi untuk barang ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
