@extends('layouts.app')

@section('title', 'Tambah Catatan Stock Opname')
@section('page_heading', 'Stock Opname - Tambah Catatan')

@section('content')
<div class="max-w-2xl space-y-5">

    <a href="{{ route('inventory.stock-opname.index') }}" class="btn btn-ghost btn-sm gap-1.5">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Opname
    </a>

    <div class="wms-card p-6">
        <h3 class="wms-card-title border-b border-[#f2f4f6] pb-3 mb-5 flex items-center gap-2">
            <i class="fa-solid fa-clipboard-check text-[#0058be]"></i> Form Catatan Kondisi Fisik Barang
        </h3>
        <form action="{{ route('inventory.stock-opname.store') }}" method="POST" class="space-y-5" id="opname-create-form" novalidate>
            @csrf
            <div>
                <label class="wms-label">Barang yang Diperiksa <span class="text-red-500">*</span></label>
                <select name="SKU" id="input-sku" required class="wms-select @error('SKU') border-red-400 @enderror">
                    <option value="">— Pilih Barang —</option>
                    @foreach($barangs as $b)
                        <option value="{{ $b->SKU }}" {{ old('SKU') == $b->SKU ? 'selected' : '' }}>
                            {{ $b->SKU }} — {{ $b->Nama }}
                        </option>
                    @endforeach
                </select>
                @error('SKU')<p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>@enderror
                <p id="err-input-sku" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Barang wajib dipilih.
                </p>
            </div>
            <div>
                <label class="wms-label">Tanggal Pemeriksaan <span class="text-red-500">*</span></label>
                <input type="date" name="Tanggal" id="input-tanggal" value="{{ old('Tanggal', date('Y-m-d')) }}"
                       required class="wms-input @error('Tanggal') border-red-400 @enderror">
                @error('Tanggal')<p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>@enderror
                <p id="err-input-tanggal" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Tanggal pemeriksaan wajib diisi.
                </p>
            </div>
            <div>
                <label class="wms-label">
                    Deskripsi Kondisi Fisik <span class="text-red-500">*</span>
                    <span class="text-slate-400 font-normal normal-case">(min. 5 karakter)</span>
                </label>
                <textarea name="Kondisi" id="input-kondisi" rows="4" required
                          placeholder="Contoh: Kemasan dalam kondisi baik, tidak ada kerusakan. / Terdapat 3 unit dengan kemasan penyok, barang di dalam masih utuh."
                          class="wms-textarea @error('Kondisi') border-red-400 @enderror">{{ old('Kondisi') }}</textarea>
                @error('Kondisi')<p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>@enderror
                <p id="err-input-kondisi" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Deskripsi kondisi wajib diisi (min. 5 karakter).
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('inventory.stock-opname.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary btn-lg gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Catatan
                </button>
            </div>
        </form>
    </div>

</div>

<script>
(function () {
    var form = document.getElementById('opname-create-form');
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
    ['input-sku', 'input-tanggal', 'input-kondisi'].forEach(function (id) {
        var el = document.getElementById(id);
        var ev = (id === 'input-kondisi') ? 'input' : 'change';
        if (el) el.addEventListener(ev, function () { clearErr(id); });
    });
    form.addEventListener('submit', function (e) {
        var valid = true;
        var sku     = document.getElementById('input-sku');
        var tanggal = document.getElementById('input-tanggal');
        var kondisi = document.getElementById('input-kondisi');
        if (!sku     || !sku.value)                       { showErr('input-sku',     'Barang wajib dipilih.'); valid = false; }
        if (!tanggal || !tanggal.value)                   { showErr('input-tanggal', 'Tanggal pemeriksaan wajib diisi.'); valid = false; }
        if (!kondisi || kondisi.value.trim().length < 5)  { showErr('input-kondisi', 'Deskripsi kondisi wajib diisi (min. 5 karakter).'); valid = false; }
        if (!valid) e.preventDefault();
    });
})();
</script>
@endsection
