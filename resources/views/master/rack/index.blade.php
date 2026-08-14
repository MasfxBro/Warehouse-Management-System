@extends('layouts.app')
@section('title', 'Master Rak')
@section('page-title', 'Master Rak')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Cari Kode/Lokasi..." 
                   class="border border-gray-300 rounded-lg px-4 py-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Cari</button>
        </form>
        <a href="{{ route('master.rack.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">+ Tambah Rak</a>
    </div>

    <table class="w-full">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Kode Rak</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Lokasi</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Kapasitas</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 uppercase">Terisi</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Status</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($racks as $rack)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium">{{ $rack->Kode_Rak }}</td>
                <td class="px-4 py-3">{{ $rack->Lokasi }}</td>
                <td class="px-4 py-3 text-right">{{ number_format($rack->Kapasitas) }}</td>
                <td class="px-4 py-3 text-right">{{ number_format($rack->kapasitas_terisi) }}</td>
                <td class="px-4 py-3 text-center">
                    @php
                        $percentage = $rack->Kapasitas > 0 ? ($rack->kapasitas_terisi / $rack->Kapasitas * 100) : 0;
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        {{ $percentage >= 90 ? 'bg-red-100 text-red-700' : ($percentage >= 70 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                        {{ number_format($percentage, 1) }}%
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <a href="{{ route('master.rack.edit', $rack->Rack_ID) }}" class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                    <form method="POST" action="{{ route('master.rack.destroy', $rack->Rack_ID) }}" class="inline" onsubmit="return confirm('Yakin?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:text-red-800 font-medium ml-3">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-8 text-gray-500">Belum ada data rak</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-6">{{ $racks->links() }}</div>
</div>
@endsection
