@extends('layouts.app')

@section('title', 'Data Supplier')
@section('page_heading', 'Master Data - Directory Supplier')

@section('content')
<div class="space-y-6">

    {{-- Info Banner --}}
    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-xs flex items-start gap-3 shadow-xs">
        <i class="fa-solid fa-circle-info text-[#0058be] text-base mt-0.5 shrink-0"></i>
        <div>
            <strong class="font-bold text-slate-900 text-sm">Ketentuan Master Data Supplier (Read-Only Directory)</strong>
            <p class="text-slate-600 mt-0.5">
                Halaman ini bertindak murni sebagai Direktori Rekapitulasi Data Supplier. Data supplier bertambah secara
                otomatis ketika operator/siswa melakukan pengisian pada <strong>Formulir Transaksi Inbound</strong>.
            </p>
        </div>
    </div>

    {{-- Header + Search --}}
    <div class="wms-card p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-building text-[#0058be]"></i>
                Daftar Pemasok Barang (Supplier)
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Menampilkan seluruh entitas perusahaan supplier yang telah tercatat dari transaksi inbound.</p>
        </div>

        <form action="{{ route('master.supplier.index') }}" method="GET" class="flex items-center gap-2">
            <div class="search-group">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Cari nama / kontak / alamat..."
                       class="search-input" style="width:16rem;">
                <button type="submit" class="search-btn">Cari</button>
            </div>
            @if($search)
                <a href="{{ route('master.supplier.index') }}"
                   class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1">
                    <i class="fa-solid fa-xmark text-[10px]"></i> Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Supplier Table --}}
    <div class="wms-card overflow-hidden">
        <div class="overflow-x-auto">
            @if($suppliers->count() > 0)
                <table class="wms-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Supplier</th>
                            <th>No. Telepon Kontak</th>
                            <th>Email Perusahaan</th>
                            <th>Alamat Perusahaan</th>
                            <th>Total Inbound</th>
                            @if(auth()->user()->isAdmin())
                                <th class="text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliers as $index => $supplier)
                            <tr>
                                <td class="font-mono text-slate-400">{{ $suppliers->firstItem() + $index }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-building text-slate-400"></i>
                                        <span class="font-bold text-slate-900">{{ $supplier->Nama }}</span>
                                    </div>
                                </td>
                                <td class="font-mono text-slate-700">{{ $supplier->No_Kontak ?? $supplier->Kontak ?? '-' }}</td>
                                <td class="font-mono text-slate-600">{{ $supplier->Email ?? '-' }}</td>
                                <td class="text-slate-700 max-w-xs truncate">{{ $supplier->Alamat ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-info">
                                        <i class="fa-solid fa-arrow-down"></i>
                                        {{ $supplier->inbound_transactions_count }} transaksi
                                    </span>
                                </td>
                                @if(auth()->user()->isAdmin())
                                    <td class="text-right">
                                        <button type="button"
                                                onclick="openEditSupplier(
                                                    {{ $supplier->Supplier_ID }},
                                                    {{ json_encode($supplier->Nama) }},
                                                    {{ json_encode($supplier->No_Kontak ?? $supplier->Kontak ?? '') }},
                                                    {{ json_encode($supplier->Email ?? '') }},
                                                    {{ json_encode($supplier->Alamat ?? '') }}
                                                )"
                                                class="btn btn-outline btn-sm gap-1">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-12 text-center text-slate-400 text-xs">
                    <i class="fa-solid fa-building text-3xl mb-3 block"></i>
                    Belum ada data supplier yang tercatat dari proses Inbound.
                </div>
            @endif
        </div>

        @if($suppliers->hasPages())
            <div class="p-4 border-t border-[#e2e8f0] bg-slate-50">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>

</div>

{{-- Modal Edit Supplier (Admin Only) --}}
@if(auth()->user()->isAdmin())
<div id="modal-edit-supplier" class="fixed inset-0 bg-black/50 backdrop-blur-sm items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full border border-[#e2e8f0] overflow-hidden">
        <div class="bg-slate-900 px-6 py-4 text-white flex items-center justify-between">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-building"></i> Edit Data Supplier
            </h3>
            <button type="button" onclick="closeEditSupplier()"
                    class="text-slate-400 hover:text-white transition-colors cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form id="form-edit-supplier" method="POST" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="wms-label">Nama Supplier / PT <span class="text-red-500">*</span></label>
                <input type="text" name="Nama" id="edit-nama" required class="wms-input">
            </div>
            <div>
                <label class="wms-label">No. Kontak
                    <span class="text-[10px] text-slate-400 font-normal">(Diharuskan diisi jika ada, hanya angka)</span>
                </label>
                <input type="text" name="No_Kontak" id="edit-kontak" inputmode="numeric"
                       pattern="[0-9]*" placeholder="08xx..." class="wms-input">
            </div>
            <div>
                <label class="wms-label">Email
                    <span class="text-[10px] text-slate-400 font-normal">(Diharuskan diisi jika ada, wajib ada @)</span>
                </label>
                <input type="text" name="Email" id="edit-email" placeholder="info@..." class="wms-input">
            </div>
            <div>
                <label class="wms-label">Alamat
                    <span class="text-[10px] text-slate-400 font-normal">(Diharuskan diisi jika ada)</span>
                </label>
                <textarea name="Alamat" id="edit-alamat" rows="2" class="wms-textarea" placeholder="Jl. ..."></textarea>
            </div>
            <p id="edit-error" class="text-red-500 text-xs hidden"></p>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeEditSupplier()" class="btn btn-outline flex-1">Batal</button>
                <button type="submit" class="btn btn-primary flex-1 gap-1.5">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditSupplier(id, nama, kontak, email, alamat) {
    document.getElementById('form-edit-supplier').action = `/master-data/supplier/${id}`;
    document.getElementById('edit-nama').value   = nama;
    document.getElementById('edit-kontak').value = kontak;
    document.getElementById('edit-email').value  = email;
    document.getElementById('edit-alamat').value = alamat;
    document.getElementById('edit-error').classList.add('hidden');

    const m = document.getElementById('modal-edit-supplier');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function closeEditSupplier() {
    const m = document.getElementById('modal-edit-supplier');
    m.classList.add('hidden');
    m.classList.remove('flex');
}
// Validasi sebelum submit
document.getElementById('form-edit-supplier').addEventListener('submit', function(e) {
    const kontak = document.getElementById('edit-kontak').value.trim();
    const email  = document.getElementById('edit-email').value.trim();
    const errEl  = document.getElementById('edit-error');

    if (kontak && !/^\d+$/.test(kontak)) {
        e.preventDefault();
        errEl.textContent = 'No. Kontak hanya boleh berisi angka.';
        errEl.classList.remove('hidden');
        return;
    }
    if (email && !email.includes('@')) {
        e.preventDefault();
        errEl.textContent = 'Email harus mengandung karakter @.';
        errEl.classList.remove('hidden');
        return;
    }
});
document.getElementById('modal-edit-supplier')?.addEventListener('click', function(e) {
    if (e.target === this) closeEditSupplier();
});
</script>
@endif
@endsection
