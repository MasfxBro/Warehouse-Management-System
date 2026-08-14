@extends('layouts.app')

@section('title', 'Master Barang')
@section('page-title', 'Master Barang')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Cari SKU atau Nama..." 
                   class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('master.barang.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    Reset
                </a>
            @endif
        </form>
        <a href="{{ route('master.barang.create') }}" 
           class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
            + Tambah Barang
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">SKU</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Kategori</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Stok</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Min Stok</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Harga</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($barangs as $barang)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium">{{ $barang->SKU }}</td>
                    <td class="px-4 py-3 text-sm">{{ $barang->Nama }}</td>
                    <td class="px-4 py-3 text-sm">{{ $barang->Kategori }}</td>
                    <td class="px-4 py-3 text-sm text-right">{{ number_format($barang->stok_real) }} {{ $barang->satuan }}</td>
                    <td class="px-4 py-3 text-sm text-right">{{ number_format($barang->Min_Stok) }}</td>
                    <td class="px-4 py-3 text-sm text-right">Rp {{ number_format($barang->harga, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold 
                            {{ $barang->needsReorder() ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                            {{ $barang->getStockStatus() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('master.barang.edit', $barang->SKU) }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                            <form method="POST" action="{{ route('master.barang.destroy', $barang->SKU) }}" 
                                  onsubmit="return confirm('Yakin ingin menghapus barang ini?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        Belum ada data barang
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-6">
        {{ $barangs->links() }}
    </div>
</div>
@endsection
