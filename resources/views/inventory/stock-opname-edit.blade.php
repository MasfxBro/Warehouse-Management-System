@extends('layouts.app')

@section('title', 'Edit Catatan Stock Opname')
@section('page_heading', 'Stock Opname - Edit Catatan')

@section('content')
<div class="max-w-2xl space-y-5">

    <a href="{{ route('inventory.stock-opname.index') }}" class="btn btn-ghost btn-sm gap-1.5">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Opname
    </a>

    <div class="wms-card p-6">
        <h3 class="wms-card-title border-b border-[#f2f4f6] pb-3 mb-5 flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square text-[#0058be]"></i> Edit Catatan #{{ $opname->Opname_ID }}
        </h3>
        <form action="{{ route('inventory.stock-opname.update', $opname->Opname_ID) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')
            <div>
                <label class="wms-label">Barang yang Diperiksa <span class="text-red-500">*</span></label>
                <select name="SKU" required class="wms-select @error('SKU') border-red-400 @enderror">
                    <option value="">— Pilih Barang —</option>
                    @foreach($barangs as $b)
                        <option value="{{ $b->SKU }}" {{ old('SKU', $opname->SKU) == $b->SKU ? 'selected' : '' }}>
                            {{ $b->SKU }} — {{ $b->Nama }}
                        </option>
                    @endforeach
                </select>
                @error('SKU')<p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="wms-label">Tanggal Pemeriksaan <span class="text-red-500">*</span></label>
                <input type="date" name="Tanggal"
                       value="{{ old('Tanggal', $opname->Tanggal->format('Y-m-d')) }}"
                       required class="wms-input @error('Tanggal') border-red-400 @enderror">
                @error('Tanggal')<p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="wms-label">Deskripsi Kondisi Fisik <span class="text-red-500">*</span></label>
                <textarea name="Kondisi" rows="4" required
                          class="wms-textarea @error('Kondisi') border-red-400 @enderror">{{ old('Kondisi', $opname->Kondisi) }}</textarea>
                @error('Kondisi')<p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('inventory.stock-opname.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary btn-lg gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
