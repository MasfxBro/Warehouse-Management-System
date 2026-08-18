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

    <!-- Barang Info Grid -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 border-b pb-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">SKU</label>
                <p class="text-base font-semibold text-gray-900 font-mono">{{ $barang->SKU }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nama Barang</label>
                <p class="text-base font-semibold text-gray-900">{{ $barang->Nama }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Stok Awal</label>
                <p class="text-base font-semibold text-gray-700">{{ number_format($stokAwal) }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Total Masuk</label>
                <p class="text-base font-bold text-green-600">+{{ number_format($totalMasuk) }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Total Keluar</label>
                <p class="text-base font-bold text-red-600">-{{ number_format($totalKeluar) }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Stok Akhir</label>
                <p class="text-xl font-bold text-gray-900">{{ number_format($stokAkhir) }}</p>
            </div>
        </div>

        <div class="flex justify-between items-center text-sm">
            <div>
                <span class="text-gray-500">Min. Stok:</span>
                <span class="font-semibold text-gray-800 ml-1">{{ number_format($barang->Min_Stok) }}</span>
            </div>
            <div>
                <span class="text-gray-500 mr-2">Status:</span>
                @if($status === 'REORDER')
                <span class="px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                    ⚠️ REORDER
                </span>
                @else
                <span class="px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800">
                    ✓ AMAN
                </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Transaction History Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Riwayat Pergerakan Barang</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Dokumen</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Setelah Transaksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($ledger as $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($item['tanggal'])->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-sm font-semibold text-gray-900">{{ $item['no_trans'] }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($item['jenis'] === 'INBOUND')
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800">
                                INBOUND
                            </span>
                            @else
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                                OUTBOUND
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-800">
                            {{ $item['sku'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            @if($item['jenis'] === 'INBOUND')
                            <span class="font-bold text-green-600">+{{ $item['qty'] }}</span>
                            @else
                            <span class="font-bold text-red-600">-{{ $item['qty'] }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-base font-bold text-gray-900">{{ number_format($item['saldo']) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                            Belum ada transaksi pergerakan barang untuk SKU ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
