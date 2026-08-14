@extends('layouts.app')
@section('title', 'Edit Rak')
@section('page-title', 'Edit Rak')

@section('content')
<div class="max-w-2xl bg-white rounded-lg shadow p-6">
    <form method="POST" action="{{ route('master.rack.update', $rack->Rack_ID) }}">
        @csrf @method('PUT')
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Rak *</label>
                <input type="text" name="Kode_Rak" value="{{ old('Kode_Rak', $rack->Kode_Rak) }}" required 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi *</label>
                <input type="text" name="Lokasi" value="{{ old('Lokasi', $rack->Lokasi) }}" required 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas *</label>
                <input type="number" name="Kapasitas" value="{{ old('Kapasitas', $rack->Kapasitas) }}" required min="1" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">Kapasitas Terisi: <span class="font-bold">{{ number_format($rack->kapasitas_terisi) }}</span></p>
            </div>
        </div>
        <div class="flex gap-3 mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Update</button>
            <a href="{{ route('master.rack.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">Batal</a>
        </div>
    </form>
</div>
@endsection
