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
        <form action="{{ route('inventory.stock-opname.update', $opname->Opname_ID) }}" method="POST" class="space-y-5" id="opname-edit-form" novalidate>
            @csrf @method('PUT')
            <div>
                <label class="wms-label">Barang yang Diperiksa <span class="text-red-500">*</span></label>
                <select name="SKU" id="input-sku-edit" required class="wms-select @error('SKU') border-red-400 @enderror">
                    <option value="">— Pilih Barang —</option>
                    @foreach($barangs as $b)
                        <option value="{{ $b->SKU }}" {{ old('SKU', $opname->SKU) == $b->SKU ? 'selected' : '' }}>
                            {{ $b->SKU }} — {{ $b->Nama }}
                        </option>
                    @endforeach
                </select>
                @error('SKU')<p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>@enderror
                <p id="err-input-sku-edit" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Barang wajib dipilih.
                </p>
            </div>
            <div>
                <label class="wms-label">Tanggal Pemeriksaan <span class="text-red-500">*</span></label>
                <input type="date" name="Tanggal" id="input-tanggal-edit"
                       value="{{ old('Tanggal', $opname->Tanggal->format('Y-m-d')) }}"
                       required class="wms-input @error('Tanggal') border-red-400 @enderror">
                @error('Tanggal')<p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>@enderror
                <p id="err-input-tanggal-edit" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Tanggal pemeriksaan wajib diisi.
                </p>
            </div>
            <div>
                <label class="wms-label">Deskripsi Kondisi Fisik <span class="text-red-500">*</span></label>
                <textarea name="Kondisi" id="input-kondisi-edit" rows="4" required
                          class="wms-textarea @error('Kondisi') border-red-400 @enderror">{{ old('Kondisi', $opname->Kondisi) }}</textarea>
                @error('Kondisi')<p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>@enderror
                <p id="err-input-kondisi-edit" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Deskripsi kondisi wajib diisi (min. 5 karakter).
                </p>
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

<script>
(function () {
    var form = document.getElementById('opname-edit-form');
    if (!form) return;
    function showErr(id, msg) {
        var p = document.getElementById('err-' + id);
        var inp = document.getElementById(id);
        if (p) { p.innerHTML = '<i class="fa-solid fa-circle-exclamation text-[10px] mr-1"></i>' + msg; p.classList.remove('hidden'); p.classList.add('flex'); }
        if (inp) inp.classList.add('border-red-400');
    }
    function clearErr(id) {
        var p = document.getElementById('err-' + id);
        var inp = document.getElementById(id);
        if (p) { p.classList.add('hidden'); p.classList.remove('flex'); }
        if (inp) inp.classList.remove('border-red-400');
    }
    ['input-sku-edit', 'input-tanggal-edit', 'input-kondisi-edit'].forEach(function (id) {
        var el = document.getElementById(id);
        var ev = (id === 'input-kondisi-edit') ? 'input' : 'change';
        if (el) el.addEventListener(ev, function () { clearErr(id); });
    });
    form.addEventListener('submit', function (e) {
        var valid = true;
        var sku     = document.getElementById('input-sku-edit');
        var tanggal = document.getElementById('input-tanggal-edit');
        var kondisi = document.getElementById('input-kondisi-edit');
        if (!sku     || !sku.value)                       { showErr('input-sku-edit',     'Barang wajib dipilih.'); valid = false; }
        if (!tanggal || !tanggal.value)                   { showErr('input-tanggal-edit', 'Tanggal pemeriksaan wajib diisi.'); valid = false; }
        if (!kondisi || kondisi.value.trim().length < 5)  { showErr('input-kondisi-edit', 'Deskripsi kondisi wajib diisi (min. 5 karakter).'); valid = false; }
        if (!valid) e.preventDefault();
    });
})();
</script>
@endsection
