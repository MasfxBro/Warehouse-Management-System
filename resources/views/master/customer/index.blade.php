@extends('layouts.app')
@section('title', 'Master Customer')
@section('page-title', 'Master Customer')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Cari Nama atau Kontak..." 
                   class="border border-gray-300 rounded-lg px-4 py-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Cari</button>
        </form>
        <a href="{{ route('master.customer.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">+ Tambah Customer</a>
    </div>

    <table class="w-full">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Kontak</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Alamat</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($customers as $customer)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">{{ $customer->Nama }}</td>
                <td class="px-4 py-3">{{ $customer->Kontak }}</td>
                <td class="px-4 py-3">{{ $customer->Alamat }}</td>
                <td class="px-4 py-3 text-center">
                    <a href="{{ route('master.customer.edit', $customer->Customer_ID) }}" class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                    <form method="POST" action="{{ route('master.customer.destroy', $customer->Customer_ID) }}" class="inline" onsubmit="return confirm('Yakin?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:text-red-800 font-medium ml-3">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center py-8 text-gray-500">Belum ada data customer</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-6">{{ $customers->links() }}</div>
</div>
@endsection
