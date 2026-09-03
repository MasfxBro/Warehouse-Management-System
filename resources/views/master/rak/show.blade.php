@extends('layouts.app')

@section('title', 'Detail Rak - ' . $rack->Kode_Rak)
@section('page_heading', 'Detail Lokasi Rak: ' . $rack->Kode_Rak)

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <a href="{{ route('master.rak.index') }}" class="btn btn-ghost btn-sm gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Rak
        </a>
    </div>

    {{-- Info + Foto --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Data Rak --}}
        <div class="lg:col-span-2 wms-card p-6 space-y-5">
            <div class="flex items-start justify-between border-b border-surface-low pb-4">
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-1">Kode Rak</p>
                    <h2 class="text-2xl font-black font-mono text-secondary">{{ $rack->Kode_Rak }}</h2>
                </div>
                @php $status = $rack->status_kapasitas; @endphp
                @if($status === 'Penuh')
                    <span class="badge badge-danger"><i class="fa-solid fa-circle-xmark"></i> Penuh</span>
                @elseif($status === 'Hampir Penuh')
                    <span class="badge badge-warning"><i class="fa-solid fa-triangle-exclamation"></i> Hampir Penuh</span>
                @else
                    <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Tersedia</span>
                @endif
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                @foreach([
                    ['Lorong (Aisle)',  $rack->Aisle,                            false],
                    ['Tingkat (Level)', $rack->Level,                            false],
                    ['Kapasitas Maks.',  number_format($rack->Kapasitas) . ' unit', true],
                    ['Kapasitas Terpakai', number_format($rack->kapasitas_terpakai) . ' unit', true],
                ] as [$lbl, $val, $mono])
                    <div class="p-3 rounded-lg bg-surface border border-surface-low">
                        <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-1">{{ $lbl }}</p>
                        <p class="font-bold text-slate-900 {{ $mono ? 'font-mono' : '' }}">{{ $val }}</p>
                    </div>
                @endforeach
            </div>

            @if(auth()->user()->isAdmin())
                <div class="flex items-center gap-3 pt-2 border-t border-surface-low">
                    <button type="button" onclick="openEditRakModal()"
                            class="btn btn-outline gap-1.5">
                        <i class="fa-solid fa-pen-to-square"></i> Edit Data Rak
                    </button>
                    <button type="button" onclick="confirmDeleteRak()"
                            class="btn btn-danger gap-1.5">
                        <i class="fa-solid fa-trash"></i> Hapus Rak
                    </button>
                    <form id="form-delete-rak"
                          action="{{ route('master.rak.destroy', $rack->Rack_ID) }}"
                          method="POST" class="hidden">
                        @csrf @method('DELETE')
                    </form>
                </div>
            @endif
        </div>

        {{-- Preview Foto --}}
        <div class="wms-card p-6 flex flex-col items-center justify-center gap-3 bg-surface">
            <div class="w-full aspect-square max-w-[200px] rounded-xl bg-slate-100 border-2 border-dashed border-slate-300 flex flex-col items-center justify-center gap-3">
                <i class="fa-solid fa-image text-4xl text-slate-300"></i>
                <p class="text-xs text-slate-400 text-center font-medium">Foto Rak</p>
                <p class="text-[10px] text-slate-300 text-center">Developer dapat menambahkan<br>foto rak di sini</p>
            </div>
            <p class="text-[10px] text-slate-400 text-center">{{ $rack->Kode_Rak }} · Lorong {{ $rack->Aisle }} Level {{ $rack->Level }}</p>
        </div>

    </div>

    {{-- Daftar Barang di Rak --}}
    <div class="wms-card overflow-hidden">
        <div class="wms-card-header">
            <h3 class="wms-card-title flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-secondary"></i>
                Barang di Rak Ini
            </h3>
            <span class="text-xs text-slate-400">{{ $barangs->count() }} jenis barang</span>
        </div>
        <div class="overflow-x-auto">
            @if($barangs->count() > 0)
                <table class="wms-table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th class="text-right">Stok</th>
                            <th class="text-right">Min. Stok</th>
                            @if(auth()->user()->isAdmin())
                                <th class="text-right">Pindah ke Rak</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($barangs as $barang)
                            <tr>
                                <td class="font-mono font-bold text-secondary">{{ $barang->SKU }}</td>
                                <td class="font-medium text-slate-900">{{ $barang->Nama }}</td>
                                <td><span class="badge badge-neutral">{{ $barang->Kategori }}</span></td>
                                <td class="text-right font-mono font-bold {{ $barang->stok > $barang->Min_Stok ? 'text-slate-900' : 'text-on-error-container' }}">
                                    {{ number_format($barang->stok) }}
                                </td>
                                <td class="text-right font-mono text-slate-500">{{ number_format($barang->Min_Stok) }}</td>
                                @if(auth()->user()->isAdmin())
                                    <td class="text-right">
                                        <button type="button"
                                                onclick="openPindahModal('{{ $barang->SKU }}', '{{ addslashes($barang->Nama) }}')"
                                                class="btn btn-outline btn-sm gap-1">
                                            <i class="fa-solid fa-right-left"></i> Pindah
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="py-12 text-center text-slate-400 text-xs space-y-2">
                    <i class="fa-solid fa-box-open text-3xl block"></i>
                    <p>Tidak ada barang yang tersimpan di rak ini.</p>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- Modal Edit Rak --}}
@if(auth()->user()->isAdmin())
<div id="modal-edit-rak" class="fixed inset-0 bg-black/50 backdrop-blur-sm items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full border border-[#e2e8f0] overflow-hidden">
        <div class="bg-slate-900 px-6 py-4 text-white flex items-center justify-between">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square"></i> Edit Lokasi Rak
            </h3>
            <button type="button" onclick="closeEditRakModal()" class="text-slate-400 hover:text-white cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form action="{{ route('master.rak.update', $rack->Rack_ID) }}" method="POST" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="wms-label">Kode Rak <span class="text-red-500">*</span></label>
                <input type="text" name="Kode_Rak" value="{{ $rack->Kode_Rak }}" required class="wms-input font-mono">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="wms-label">Lorong <span class="text-red-500">*</span></label>
                    <input type="text" name="Aisle" value="{{ $rack->Aisle }}" required class="wms-input">
                </div>
                <div>
                    <label class="wms-label">Tingkat <span class="text-red-500">*</span></label>
                    <input type="text" name="Level" value="{{ $rack->Level }}" required class="wms-input">
                </div>
            </div>
            <div>
                <label class="wms-label">Kapasitas Maksimal <span class="text-red-500">*</span></label>
                <input type="number" name="Kapasitas" value="{{ $rack->Kapasitas }}" min="1" required class="wms-input font-mono">
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeEditRakModal()" class="btn btn-outline flex-1">Batal</button>
                <button type="submit" class="btn btn-primary flex-1 gap-1.5">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Hapus Rak --}}
<div id="modal-confirm-hapus-rak" class="fixed inset-0 bg-black/50 backdrop-blur-sm items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full border border-[#e2e8f0] overflow-hidden">
        <div class="bg-on-error-container px-6 py-5 text-white text-center">
            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-trash text-2xl"></i>
            </div>
            <h3 class="text-base font-bold">Hapus Rak {{ $rack->Kode_Rak }}?</h3>
        </div>
        <div class="p-6 space-y-4">
            @if($barangs->count() > 0)
                <div class="bg-error-container border border-on-error-container/20 rounded-lg px-4 py-3 text-xs text-on-error-container flex items-start gap-2">
                    <i class="fa-solid fa-triangle-exclamation shrink-0 mt-0.5"></i>
                    <span>Masih ada <strong>{{ $barangs->count() }} barang</strong> di rak ini. Pindahkan semua barang terlebih dahulu sebelum menghapus rak.</span>
                </div>
                <button type="button" onclick="closeConfirmHapus()" class="btn btn-outline w-full">Mengerti</button>
            @else
                <p class="text-sm text-slate-700 text-center">Rak ini kosong. Yakin ingin menghapus?</p>
                <div class="flex gap-3">
                    <button type="button" onclick="closeConfirmHapus()" class="btn btn-outline flex-1">Batal</button>
                    <form action="{{ route('master.rak.destroy', $rack->Rack_ID) }}" method="POST" class="flex-1">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger w-full gap-1.5">
                            <i class="fa-solid fa-trash"></i> Hapus
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Pindah Barang --}}
<div id="modal-pindah-barang" class="fixed inset-0 bg-black/50 backdrop-blur-sm items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full border border-[#e2e8f0] overflow-hidden">
        <div class="bg-slate-900 px-6 py-4 text-white flex items-center justify-between">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-right-left"></i> Pindah Barang
            </h3>
            <button type="button" onclick="closePindahModal()" class="text-slate-400 hover:text-white cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form action="{{ route('master.rak.pindah-barang', $rack->Rack_ID) }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="sku" id="pindah-sku">
            <div>
                <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-1">Barang</p>
                <p id="pindah-nama" class="font-bold text-slate-900 text-sm"></p>
            </div>
            <div>
                <label class="wms-label">Pindah ke Rak <span class="text-red-500">*</span></label>
                <select name="new_rack_id" required class="wms-select">
                    <option value="">-- Pilih Rak Tujuan --</option>
                    @foreach($otherRacks as $r)
                        <option value="{{ $r->Rack_ID }}">
                            {{ $r->Kode_Rak }} (Lorong {{ $r->Aisle }}, Lvl {{ $r->Level }}) — Kapasitas: {{ number_format($r->Kapasitas) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-xs text-amber-800 flex items-start gap-2">
                <i class="fa-solid fa-circle-info shrink-0 mt-0.5"></i>
                <span>Memindah barang akan mengubah lokasi default rak barang tersebut di seluruh sistem.</span>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closePindahModal()" class="btn btn-outline flex-1">Batal</button>
                <button type="submit" class="btn btn-primary flex-1 gap-1.5">
                    <i class="fa-solid fa-right-left"></i> Pindahkan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditRakModal() {
    const m = document.getElementById('modal-edit-rak');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeEditRakModal() {
    const m = document.getElementById('modal-edit-rak');
    m.classList.add('hidden'); m.classList.remove('flex');
}
function confirmDeleteRak() {
    const m = document.getElementById('modal-confirm-hapus-rak');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeConfirmHapus() {
    const m = document.getElementById('modal-confirm-hapus-rak');
    m.classList.add('hidden'); m.classList.remove('flex');
}
function openPindahModal(sku, nama) {
    document.getElementById('pindah-sku').value  = sku;
    document.getElementById('pindah-nama').textContent = sku + ' — ' + nama;
    const m = document.getElementById('modal-pindah-barang');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closePindahModal() {
    const m = document.getElementById('modal-pindah-barang');
    m.classList.add('hidden'); m.classList.remove('flex');
}
['modal-edit-rak','modal-confirm-hapus-rak','modal-pindah-barang'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
            this.classList.remove('flex');
        }
    });
});
</script>
@endif
@endsection
