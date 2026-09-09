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
                @php
                    $status    = $rack->status_kapasitas;
                    $terpakai  = $rack->kapasitas_terpakai;
                    $sisa      = max(0, $rack->Kapasitas - $terpakai);
                @endphp
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
                    ['Lorong (Aisle)',     $rack->Aisle,                             false],
                    ['Tingkat (Level)',    $rack->Level,                             false],
                    ['Kapasitas Maks.',    number_format($rack->Kapasitas) . ' unit', true],
                    ['Kapasitas Terpakai', number_format($terpakai) . ' unit',        true],
                ] as [$lbl, $val, $mono])
                    <div class="p-3 rounded-lg bg-surface border border-surface-low">
                        <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-1">{{ $lbl }}</p>
                        <p class="font-bold text-slate-900 {{ $mono ? 'font-mono' : '' }}">{{ $val }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Progress bar kapasitas: dua segmen (terpakai + sisa) + legenda --}}
            <div>
                <div class="flex items-center justify-between text-[11px] text-slate-500 mb-2">
                    <span class="font-medium text-slate-700">Kapasitas Rak</span>
                    <span class="font-mono text-[10px] text-slate-400">{{ number_format($rack->Kapasitas) }} unit maks.</span>
                </div>
                @php $pct = $rack->Kapasitas > 0 ? min(100, round($terpakai / $rack->Kapasitas * 100)) : 0; @endphp

                {{-- Bar dua segmen --}}
                <div class="h-3 bg-slate-100 rounded-full overflow-hidden flex">
                    {{-- Segmen terpakai --}}
                    <div class="h-full rounded-l-full transition-all
                        {{ $pct >= 100 ? 'bg-on-error-container rounded-r-full' : ($pct >= 80 ? 'bg-amber-400' : 'bg-[#10b981]') }}"
                         style="width: {{ $pct }}%"></div>
                    {{-- Segmen sisa: selalu putih/transparan, bar sudah di-clip oleh overflow-hidden --}}
                </div>

                {{-- Label terpakai & sisa --}}
                <div class="flex items-center justify-between mt-2 text-[11px]">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm inline-block
                            {{ $pct >= 100 ? 'bg-on-error-container' : ($pct >= 80 ? 'bg-amber-400' : 'bg-[#10b981]') }}"></span>
                        <span class="text-slate-600">Terpakai: <strong class="font-mono">{{ number_format($terpakai) }}</strong> unit</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-slate-200 inline-block"></span>
                        <span class="text-slate-600">Sisa: <strong class="font-mono">{{ number_format($sisa) }}</strong> unit</span>
                    </div>
                </div>

                {{-- Legenda warna --}}
                <div class="flex items-center gap-4 mt-2 pt-2 border-t border-surface-low">
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest">Note:</p>
                    <div class="flex items-center gap-1.5 text-[10px] text-slate-500">
                        <span class="w-2 h-2 rounded-sm bg-[#10b981] inline-block"></span> Tersedia (&lt;80%)
                    </div>
                    <div class="flex items-center gap-1.5 text-[10px] text-slate-500">
                        <span class="w-2 h-2 rounded-sm bg-amber-400 inline-block"></span> Hampir Penuh (80–99%)
                    </div>
                    <div class="flex items-center gap-1.5 text-[10px] text-slate-500">
                        <span class="w-2 h-2 rounded-sm bg-on-error-container inline-block"></span> Penuh (100%)
                    </div>
                </div>
            </div>

            @if(auth()->user()->isAdmin())
                <div class="flex items-center gap-3 pt-2 border-t border-surface-low flex-wrap">
                    <button type="button" onclick="openEditRakModal()" class="btn btn-outline gap-1.5">
                        <i class="fa-solid fa-pen-to-square"></i> Edit Data Rak
                    </button>
                    <button type="button" onclick="confirmDeleteRak()" class="btn btn-danger gap-1.5">
                        <i class="fa-solid fa-trash"></i> Hapus Rak
                    </button>
                    <form id="form-delete-rak" action="{{ route('master.rak.destroy', $rack->Rack_ID) }}"
                          method="POST" class="hidden">
                        @csrf @method('DELETE')
                    </form>
                </div>
            @endif
        </div>

        {{-- Foto Rak --}}
        <div class="wms-card p-6 flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Foto Rak</p>
                @if(auth()->user()->isAdmin())
                    <button type="button" onclick="openFotoModal()"
                            class="btn btn-outline btn-sm gap-1">
                        <i class="fa-solid fa-camera text-[10px]"></i>
                        {{ $rack->foto_path ? 'Ganti Foto' : 'Upload Foto' }}
                    </button>
                @endif
            </div>

            @if($rack->foto_path)
                <div class="w-full rounded-xl overflow-hidden border border-surface-low">
                    <img src="{{ asset('storage/' . $rack->foto_path) }}"
                         alt="Foto Rak {{ $rack->Kode_Rak }}"
                         class="w-full object-cover aspect-square">
                </div>
            @else
                <div class="w-full aspect-square rounded-xl bg-slate-50 border-2 border-dashed border-slate-200
                            flex flex-col items-center justify-center gap-3">
                    <i class="fa-solid fa-image text-4xl text-slate-300"></i>
                    <p class="text-xs text-slate-400 text-center font-medium">Belum ada foto</p>
                    @if(auth()->user()->isAdmin())
                        <p class="text-[10px] text-slate-300 text-center">Klik "Upload Foto" untuk<br>menambahkan foto rak</p>
                    @endif
                </div>
            @endif

            <p class="text-[10px] text-slate-400 text-center">
                {{ $rack->Kode_Rak }} · Lorong {{ $rack->Aisle }} · Level {{ $rack->Level }}
            </p>
        </div>

    </div>

    {{-- Daftar Barang di Rak --}}
    <div class="wms-card overflow-hidden">
        <div class="wms-card-header">
            <h3 class="wms-card-title flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-secondary"></i>
                Barang di Rak Ini
            </h3>
            <span class="text-xs text-slate-400">{{ $barangs->total() }} jenis barang</span>
        </div>
        <div class="overflow-x-auto">
            @if($barangs->total() > 0)
                <table class="wms-table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th class="text-right">Stok di Rak</th>
                            <th class="text-right">Min. Stok</th>
                            <th class="text-right">Pindah ke Rak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($barangs as $barang)
                            <tr>
                                <td class="font-mono font-bold text-secondary">{{ $barang->SKU }}</td>
                                <td class="font-medium text-slate-900">{{ $barang->Nama }}</td>
                                <td><span class="badge badge-neutral">{{ $barang->Kategori }}</span></td>
                                <td class="text-right font-mono font-bold {{ $barang->stok_di_rak > $barang->Min_Stok ? 'text-slate-900' : 'text-on-error-container' }}">
                                    {{ number_format($barang->stok_di_rak) }}
                                </td>
                                <td class="text-right font-mono text-slate-500">{{ number_format($barang->Min_Stok) }}</td>
                                <td class="text-right">
                                    @if($barang->stok_di_rak > 0)
                                        <button type="button"
                                                onclick="openPindahModal('{{ $barang->SKU }}', '{{ addslashes($barang->Nama) }}', {{ $barang->stok_di_rak }})"
                                                class="btn btn-outline btn-sm gap-1">
                                            <i class="fa-solid fa-right-left"></i> Pindah
                                        </button>
                                    @else
                                        <span class="text-[11px] text-slate-400">Stok habis</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($barangs->hasPages())
                    <div class="px-5 py-3 border-t border-[#e2e8f0] bg-surface-low flex items-center justify-between gap-4 flex-wrap">
                        <p class="text-[11px] text-slate-400">
                            Menampilkan {{ $barangs->firstItem() }}–{{ $barangs->lastItem() }}
                            dari <span class="font-semibold text-slate-600">{{ $barangs->total() }}</span> barang
                        </p>
                        <div class="flex items-center gap-1">
                            @if($barangs->onFirstPage())
                                <span class="px-2.5 py-1 rounded-md text-[11px] text-slate-300 border border-[#e2e8f0] cursor-not-allowed bg-white">
                                    <i class="fa-solid fa-chevron-left text-[9px]"></i>
                                </span>
                            @else
                                <a href="{{ $barangs->previousPageUrl() }}"
                                   class="px-2.5 py-1 rounded-md text-[11px] text-slate-600 border border-[#e2e8f0] hover:bg-white hover:border-secondary hover:text-secondary transition-colors">
                                    <i class="fa-solid fa-chevron-left text-[9px]"></i>
                                </a>
                            @endif
                            @foreach($barangs->getUrlRange(1, $barangs->lastPage()) as $page => $url)
                                @if($page == $barangs->currentPage())
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-secondary text-white border border-secondary">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="px-2.5 py-1 rounded-md text-[11px] text-slate-600 border border-[#e2e8f0] hover:bg-white hover:border-secondary hover:text-secondary transition-colors">{{ $page }}</a>
                                @endif
                            @endforeach
                            @if($barangs->hasMorePages())
                                <a href="{{ $barangs->nextPageUrl() }}"
                                   class="px-2.5 py-1 rounded-md text-[11px] text-slate-600 border border-[#e2e8f0] hover:bg-white hover:border-secondary hover:text-secondary transition-colors">
                                    <i class="fa-solid fa-chevron-right text-[9px]"></i>
                                </a>
                            @else
                                <span class="px-2.5 py-1 rounded-md text-[11px] text-slate-300 border border-[#e2e8f0] cursor-not-allowed bg-white">
                                    <i class="fa-solid fa-chevron-right text-[9px]"></i>
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <div class="py-12 text-center text-slate-400 text-xs space-y-2">
                    <i class="fa-solid fa-box-open text-3xl block"></i>
                    <p>Tidak ada barang yang tersimpan di rak ini.</p>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ================================================================
     MODALS — Admin Only: Edit Rak, Hapus Rak, Upload Foto
     ================================================================ --}}
@if(auth()->user()->isAdmin())

{{-- Modal Edit Rak --}}
<div id="modal-edit-rak" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="modal-header">
            <h4 class="modal-title flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-[#0058be]"></i> Edit Lokasi Rak
            </h4>
            <button type="button" onclick="closeEditRakModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form action="{{ route('master.rak.update', $rack->Rack_ID) }}" method="POST"
              class="modal-body" id="form-edit-rak-show" novalidate>
            @csrf @method('PUT')
            <div>
                <label class="wms-label">Kode Rak <span class="text-red-500">*</span></label>
                <input type="text" id="edit-kode-rak" name="Kode_Rak" value="{{ $rack->Kode_Rak }}"
                       required class="wms-input font-mono">
                <p id="err-edit-kode-rak" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Kode Rak wajib diisi.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="wms-label">Lorong <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-aisle" name="Aisle" value="{{ $rack->Aisle }}" required class="wms-input">
                    <p id="err-edit-aisle" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                        <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Lorong wajib diisi.
                    </p>
                </div>
                <div>
                    <label class="wms-label">Tingkat <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-level" name="Level" value="{{ $rack->Level }}" required class="wms-input">
                    <p id="err-edit-level" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                        <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Tingkat wajib diisi.
                    </p>
                </div>
            </div>
            <div>
                <label class="wms-label">Kapasitas Maksimal <span class="text-red-500">*</span></label>
                <input type="number" id="edit-kapasitas" name="Kapasitas" value="{{ $rack->Kapasitas }}"
                       min="1" required class="wms-input font-mono">
                <p id="err-edit-kapasitas" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Kapasitas wajib diisi dan minimal 1.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditRakModal()" class="btn btn-outline flex-1">Batal</button>
                <button type="submit" class="btn btn-primary flex-1 gap-1.5">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Upload Foto --}}
<div id="modal-upload-foto" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="modal-header">
            <h4 class="modal-title flex items-center gap-2">
                <i class="fa-solid fa-camera text-[#0058be]"></i> Upload Foto Rak
            </h4>
            <button type="button" onclick="closeFotoModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form action="{{ route('master.rak.upload-foto', $rack->Rack_ID) }}" method="POST"
              enctype="multipart/form-data" class="modal-body" id="form-upload-foto" novalidate>
            @csrf
            <div>
                <label class="wms-label">Pilih Foto <span class="text-red-500">*</span></label>
                <input type="file" name="foto" id="input-foto" accept="image/jpeg,image/jpg,image/png,image/webp"
                       class="wms-input p-2 cursor-pointer" required>
                <p class="text-[10px] text-slate-400 mt-1">Format: JPG, PNG, WebP. Maks. 2 MB.</p>
                <p id="err-input-foto" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Pilih file foto terlebih dahulu.
                </p>
            </div>
            {{-- Preview --}}
            <div id="foto-preview-wrap" class="hidden">
                <img id="foto-preview" src="" alt="Preview" class="w-full rounded-lg border border-surface-low object-cover max-h-48">
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeFotoModal()" class="btn btn-outline flex-1">Batal</button>
                <button type="submit" class="btn btn-primary flex-1 gap-1.5">
                    <i class="fa-solid fa-upload"></i> Upload
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

@endif

{{-- ================================================================
     MODAL PINDAH BARANG — Admin & Siswa bisa
     ================================================================ --}}
<div id="modal-pindah-barang" class="modal-overlay hidden">
    <div class="modal-box" style="max-width:32rem">
        <div class="modal-header">
            <h4 class="modal-title flex items-center gap-2">
                <i class="fa-solid fa-right-left text-[#0058be]"></i> Pindah Barang
            </h4>
            <button type="button" onclick="closePindahModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form action="{{ route('master.rak.pindah-barang', $rack->Rack_ID) }}" method="POST"
              class="modal-body" id="form-pindah-barang" novalidate>
            @csrf
            <input type="hidden" name="sku" id="pindah-sku">

            {{-- Info Barang --}}
            <div class="bg-surface rounded-lg px-4 py-3 text-xs">
                <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-0.5">Barang yang Dipindah</p>
                <p id="pindah-nama" class="font-bold text-slate-900 text-sm"></p>
                <p class="text-slate-500 mt-0.5">
                    Stok tersedia di rak ini: <span id="pindah-stok" class="font-mono font-semibold text-secondary"></span> unit
                </p>
            </div>

            {{-- Pilih Rak Tujuan --}}
            <div>
                <label class="wms-label">Pindah ke Rak <span class="text-red-500">*</span></label>
                <select name="new_rack_id" id="select-rak-tujuan" required class="wms-select"
                        onchange="updateSisaKapasitas()">
                    <option value="">-- Pilih Rak Tujuan --</option>
                    @foreach($otherRacks as $r)
                        <option value="{{ $r->Rack_ID }}"
                                data-sisa="{{ $r->sisa_kapasitas }}"
                                {{ $r->sisa_kapasitas <= 0 ? 'disabled' : '' }}>
                            {{ $r->Kode_Rak }} (L.{{ $r->Aisle }}, Lvl.{{ $r->Level }})
                            — Sisa: {{ number_format($r->sisa_kapasitas) }} / {{ number_format($r->Kapasitas) }} unit
                            {{ $r->sisa_kapasitas <= 0 ? '[PENUH]' : '' }}
                        </option>
                    @endforeach
                </select>
                <p id="err-select-rak-tujuan" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Rak tujuan wajib dipilih.
                </p>
                {{-- Info sisa kapasitas rak tujuan --}}
                <div id="info-sisa-kapasitas" class="hidden mt-1.5 text-[11px] text-slate-500 flex items-center gap-1">
                    <i class="fa-solid fa-circle-info text-secondary text-[10px]"></i>
                    Sisa kapasitas rak tujuan: <span id="sisa-kapasitas-val" class="font-mono font-semibold text-secondary ml-1"></span> unit
                </div>
            </div>

            {{-- Jumlah yang dipindahkan --}}
            <div>
                <label class="wms-label">Jumlah yang Dipindahkan <span class="text-red-500">*</span></label>
                <input type="number" name="qty" id="pindah-qty" min="1" value="1"
                       required class="wms-input font-mono" novalidate>
                <p class="text-[10px] text-slate-400 mt-1">
                    Maksimal sesuai stok barang dan sisa kapasitas rak tujuan.
                </p>
                <p id="err-pindah-qty" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Jumlah wajib diisi minimal 1.
                </p>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-xs text-amber-800 flex items-start gap-2">
                <i class="fa-solid fa-circle-info shrink-0 mt-0.5"></i>
                <span>Perpindahan barang akan mempengaruhi kapasitas rak asal dan rak tujuan secara real-time.</span>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closePindahModal()" class="btn btn-outline flex-1">Batal</button>
                <button type="submit" class="btn btn-primary flex-1 gap-1.5">
                    <i class="fa-solid fa-right-left"></i> Pindahkan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Modal helpers ──────────────────────────────────────────────────
@if(auth()->user()->isAdmin())
function openEditRakModal()  { toggleModal('modal-edit-rak', true); }
function closeEditRakModal() { toggleModal('modal-edit-rak', false); }
function confirmDeleteRak()  { toggleModal('modal-confirm-hapus-rak', true); }
function closeConfirmHapus() { toggleModal('modal-confirm-hapus-rak', false); }
function openFotoModal()  { toggleModal('modal-upload-foto', true); }
function closeFotoModal() { toggleModal('modal-upload-foto', false); document.getElementById('foto-preview-wrap').classList.add('hidden'); document.getElementById('err-input-foto').classList.add('hidden'); }
@endif

function openPindahModal(sku, nama, stok) {
    document.getElementById('pindah-sku').value = sku;
    document.getElementById('pindah-nama').textContent = sku + ' — ' + nama;
    document.getElementById('pindah-stok').textContent = stok;
    document.getElementById('pindah-qty').max = stok;
    document.getElementById('pindah-qty').value = 1;
    document.getElementById('select-rak-tujuan').value = '';
    document.getElementById('info-sisa-kapasitas').classList.add('hidden');
    document.getElementById('err-select-rak-tujuan').classList.add('hidden');
    document.getElementById('err-pindah-qty').classList.add('hidden');
    toggleModal('modal-pindah-barang', true);
}
function closePindahModal() { toggleModal('modal-pindah-barang', false); }

function toggleModal(id, show) {
    var m = document.getElementById(id);
    if (!m) return;
    m.classList.toggle('hidden', !show);
    m.classList.toggle('flex', show);
}

// Backdrop click
['modal-edit-rak','modal-confirm-hapus-rak','modal-pindah-barang','modal-upload-foto'].forEach(function(id) {
    document.getElementById(id)?.addEventListener('click', function(e) {
        if (e.target === this) toggleModal(id, false);
    });
});

// ── Info sisa kapasitas saat pilih rak tujuan ─────────────────────
function updateSisaKapasitas() {
    var sel      = document.getElementById('select-rak-tujuan');
    var info     = document.getElementById('info-sisa-kapasitas');
    var val      = document.getElementById('sisa-kapasitas-val');
    var qtyInput = document.getElementById('pindah-qty');
    if (sel.value) {
        var opt   = sel.options[sel.selectedIndex];
        var sisa  = parseInt(opt.dataset.sisa || 0);
        var stok  = parseInt(document.getElementById('pindah-stok').textContent) || 0;
        var maxQty = Math.min(stok, sisa);

        val.textContent = sisa;
        info.classList.remove('hidden');

        // LOCK input qty ke max = min(stok, sisa kapasitas) — user tidak bisa input lebih
        qtyInput.max   = maxQty;
        qtyInput.value = Math.min(parseInt(qtyInput.value) || 1, maxQty);
        if (maxQty < 1) {
            qtyInput.value = 0;
            qtyInput.disabled = true;
        } else {
            qtyInput.disabled = false;
        }
    } else {
        info.classList.add('hidden');
        // Reset qty ke stok barang saat belum pilih rak
        var stok = parseInt(document.getElementById('pindah-stok').textContent) || 0;
        qtyInput.max = stok;
        qtyInput.disabled = false;
    }
}

// ── Foto preview ──────────────────────────────────────────────────
@if(auth()->user()->isAdmin())
document.getElementById('input-foto')?.addEventListener('change', function() {
    var wrap    = document.getElementById('foto-preview-wrap');
    var preview = document.getElementById('foto-preview');
    if (this.files && this.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { preview.src = e.target.result; wrap.classList.remove('hidden'); };
        reader.readAsDataURL(this.files[0]);
    }
});

// ── Validasi form edit rak ─────────────────────────────────────────
(function () {
    var form = document.getElementById('form-edit-rak-show');
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
    ['edit-kode-rak','edit-aisle','edit-level','edit-kapasitas'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', function() { clearErr(id); });
    });
    form.addEventListener('submit', function(e) {
        var valid = true;
        var kode  = document.getElementById('edit-kode-rak');
        var aisle = document.getElementById('edit-aisle');
        var level = document.getElementById('edit-level');
        var kap   = document.getElementById('edit-kapasitas');
        if (!kode  || !kode.value.trim())  { showErr('edit-kode-rak',  'Kode Rak wajib diisi.'); valid = false; }
        if (!aisle || !aisle.value.trim()) { showErr('edit-aisle',     'Lorong wajib diisi.'); valid = false; }
        if (!level || !level.value.trim()) { showErr('edit-level',     'Tingkat wajib diisi.'); valid = false; }
        if (!kap   || !kap.value || parseInt(kap.value) < 1) { showErr('edit-kapasitas', 'Kapasitas wajib diisi dan minimal 1.'); valid = false; }
        if (!valid) e.preventDefault();
    });
})();

// ── Validasi form upload foto ──────────────────────────────────────
(function () {
    var form = document.getElementById('form-upload-foto');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        var input  = document.getElementById('input-foto');
        var errEl  = document.getElementById('err-input-foto');
        if (!input || !input.files || !input.files[0]) {
            e.preventDefault();
            errEl.classList.remove('hidden'); errEl.classList.add('flex');
        } else {
            errEl.classList.add('hidden'); errEl.classList.remove('flex');
        }
    });
})();
@endif

// ── Validasi form pindah barang ────────────────────────────────────
(function () {
    var form = document.getElementById('form-pindah-barang');
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
    document.getElementById('select-rak-tujuan')?.addEventListener('change', function() { clearErr('select-rak-tujuan'); });
    // Clamp nilai qty secara real-time saat mengetik — tidak bisa melebihi max
    var qtyEl = document.getElementById('pindah-qty');
    if (qtyEl) {
        qtyEl.addEventListener('input', function() {
            clearErr('pindah-qty');
            var max = parseInt(this.max) || 0;
            var val = parseInt(this.value) || 0;
            if (max > 0 && val > max) {
                this.value = max;
            }
            if (val < 1 && this.value !== '') {
                this.value = 1;
            }
        });
        qtyEl.addEventListener('keydown', function(e) {
            // Setelah value berubah, pastikan tidak melebihi max
            setTimeout(function() {
                var max = parseInt(qtyEl.max) || 0;
                var val = parseInt(qtyEl.value) || 0;
                if (max > 0 && val > max) qtyEl.value = max;
            }, 0);
        });
    }
    form.addEventListener('submit', function(e) {
        var valid  = true;
        var sel    = document.getElementById('select-rak-tujuan');
        var qty    = document.getElementById('pindah-qty');
        if (!sel || !sel.value)                            { showErr('select-rak-tujuan', 'Rak tujuan wajib dipilih.'); valid = false; }
        if (!qty || !qty.value || parseInt(qty.value) < 1) { showErr('pindah-qty', 'Jumlah wajib diisi minimal 1.'); valid = false; }
        if (!valid) e.preventDefault();
    });
})();
</script>
@endsection
