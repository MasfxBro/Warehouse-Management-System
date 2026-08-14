@extends('layouts.app')

@section('title', 'Edit Barang')
@section('page-title', 'Edit Barang')

@section('content')
<div class="max-w-2xl bg-white rounded-lg shadow p-6">
    <form method="POST" action="{{ route('master.barang.update', $barang->SKU) }}">
        @csrf
        @method('PUT')
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                <input type="text" value="{{ $barang->SKU }}" disabled
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100">
                <p class="text-xs text-gray-500 mt-1">SKU tidak dapat diubah</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang *</label>
                <input type="text" name="Nama" value="{{ old('Nama', $barang->Nama) }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none @error('Nama') border-red-500 @enderror">
                @error('Nama')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label>
                <input type="text" name="Kategori" value="{{ old('Kategori', $barang->Kategori) }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none @error('Kategori') border-red-500 @enderror">
                @error('Kategori')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Min Stok *</label>
                    <input type="number" name="Min_Stok" value="{{ old('Min_Stok', $barang->Min_Stok) }}" required min="0"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none @error('Min_Stok') border-red-500 @enderror">
                    @error('Min_Stok')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Satuan *</label>
                    <select name="satuan" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none @error('satuan') border-red-500 @enderror">
                        <option value="">Pilih Satuan</option>
                        <option value="Pcs" {{ old('satuan', $barang->satuan) == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                        <option value="Box" {{ old('satuan', $barang->satuan) == 'Box' ? 'selected' : '' }}>Box</option>
                        <option value="Kg" {{ old('satuan', $barang->satuan) == 'Kg' ? 'selected' : '' }}>Kg</option>
                        <option value="Liter" {{ old('satuan', $barang->satuan) == 'Liter' ? 'selected' : '' }}>Liter</option>
                        <option value="Unit" {{ old('satuan', $barang->satuan) == 'Unit' ? 'selected' : '' }}>Unit</option>
                    </select>
                    @error('satuan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Harga *</label>
                <input type="number" name="harga" value="{{ old('harga', $barang->harga) }}" required min="0" step="0.01"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none @error('harga') border-red-500 @enderror">
                @error('harga')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Rak (Optional)</label>
                <select name="Rack_ID"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">Tidak ada rak</option>
                    @foreach($racks as $rack)
                        <option value="{{ $rack->Rack_ID }}" {{ old('Rack_ID', $barang->Rack_ID) == $rack->Rack_ID ? 'selected' : '' }}>
                            {{ $rack->Kode_Rak }} - {{ $rack->Lokasi }} ({{ $rack->kapasitas_terisi }}/{{ $rack->Kapasitas }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">Stok Real: <span class="font-bold">{{ number_format($barang->stok_real) }}</span></p>
                <p class="text-xs text-gray-500 mt-1">Stok diupdate otomatis melalui transaksi inbound/outbound</p>
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Update
            </button>
            <a href="{{ route('master.barang.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
