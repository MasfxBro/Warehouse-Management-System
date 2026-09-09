@extends('layouts.app')

@section('title', 'Data Customer')
@section('page_heading', 'Master Data - Directory Customer')

@section('content')
<div class="space-y-6">

    {{-- Info Banner --}}
    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-xs flex items-start gap-3 shadow-xs">
        <i class="fa-solid fa-circle-info text-[#0058be] text-base mt-0.5 shrink-0"></i>
        <div>
            <strong class="font-bold text-slate-900 text-sm">Ketentuan Master Data Customer (Read-Only Directory)</strong>
            <p class="text-slate-600 mt-0.5">
                Halaman ini bertindak murni sebagai Direktori Rekapitulasi Data Customer/Pelanggan. Data customer bertambah
                secara otomatis ketika operator/siswa melakukan pengisian pada <strong>Formulir Transaksi Outbound</strong>.
            </p>
        </div>
    </div>

    {{-- Header + Search --}}
    <div class="wms-card p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-users text-[#0058be]"></i>
                Daftar Pelanggan (Customer)
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Menampilkan seluruh entitas customer pemesan barang yang telah tercatat dari transaksi outbound.</p>
        </div>

        <form action="{{ route('master.customer.index') }}" method="GET" class="flex items-center gap-2">
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
                <a href="{{ route('master.customer.index') }}"
                   class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1">
                    <i class="fa-solid fa-xmark text-[10px]"></i> Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Customer Table --}}
    <div class="wms-card overflow-hidden">
        <div class="overflow-x-auto">
            @if($customers->count() > 0)
                <table class="wms-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Customer</th>
                            <th>No. Telepon Kontak</th>
                            <th>Email Perusahaan</th>
                            <th>Alamat Perusahaan</th>
                            <th>Total Outbound</th>
                            @if(auth()->user()->isAdmin())
                                <th class="text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $index => $customer)
                            <tr>
                                <td class="font-mono text-slate-400">{{ $customers->firstItem() + $index }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-store text-slate-400"></i>
                                        <span class="font-bold text-slate-900">{{ $customer->Nama }}</span>
                                    </div>
                                </td>
                                <td class="font-mono text-slate-700">{{ $customer->No_Kontak ?? '-' }}</td>
                                <td class="font-mono text-slate-600">{{ $customer->Email ?? '-' }}</td>
                                <td class="text-slate-700 max-w-xs truncate">{{ $customer->Alamat ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-warning">
                                        <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                        {{ $customer->outbound_transactions_count }} transaksi
                                    </span>
                                </td>
                                @if(auth()->user()->isAdmin())
                                    <td class="text-right">
                                        <button type="button"
                                                onclick="openEditCustomer(
                                                    '{{ $customer->Customer_ID }}',
                                                    {{ json_encode($customer->Nama) }},
                                                    {{ json_encode($customer->No_Kontak ?? '') }},
                                                    {{ json_encode($customer->Email ?? '') }},
                                                    {{ json_encode($customer->Alamat ?? '') }}
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
                    <i class="fa-solid fa-users text-3xl mb-3 block"></i>
                    Belum ada data customer yang tercatat dari proses Outbound.
                </div>
            @endif
        </div>

        @if($customers->hasPages())
            <div class="p-4 border-t border-[#e2e8f0] bg-slate-50">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

</div>

{{-- Modal Edit Customer (Admin Only) --}}
@if(auth()->user()->isAdmin())
<div id="modal-edit-customer" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="modal-header">
            <h4 class="modal-title flex items-center gap-2">
                <i class="fa-solid fa-users text-[#0058be]"></i> Edit Data Customer
            </h4>
            <button type="button" onclick="closeEditCustomer()"
                    class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form id="form-edit-customer" method="POST" class="modal-body" novalidate>
            @csrf @method('PUT')
            <div>
                <label class="wms-label">Nama Customer <span class="text-red-500">*</span></label>
                <input type="text" name="Nama" id="edit-customer-nama" required class="wms-input">
                <p id="err-edit-customer-nama" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Nama customer wajib diisi.
                </p>
            </div>
            <div>
                <label class="wms-label">No. Kontak
                    <span class="text-[10px] text-slate-400 font-normal">(hanya angka)</span>
                </label>
                <input type="text" name="No_Kontak" id="edit-customer-kontak" inputmode="numeric"
                       placeholder="08xx..." class="wms-input">
            </div>
            <div>
                <label class="wms-label">Email
                    <span class="text-[10px] text-slate-400 font-normal">(wajib ada @)</span>
                </label>
                <input type="text" name="Email" id="edit-customer-email" placeholder="info@..." class="wms-input">
            </div>
            <div>
                <label class="wms-label">Alamat</label>
                <textarea name="Alamat" id="edit-customer-alamat" rows="2" class="wms-textarea" placeholder="Jl. ..."></textarea>
            </div>
            <p id="edit-customer-error" class="text-red-500 text-xs hidden"></p>
            <div class="modal-footer">
                <button type="button" onclick="closeEditCustomer()" class="btn btn-outline flex-1">Batal</button>
                <button type="submit" class="btn btn-primary flex-1 gap-1.5">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditCustomer(id, nama, kontak, email, alamat) {
    document.getElementById('form-edit-customer').action = `/master-data/customer/${id}`;
    document.getElementById('edit-customer-nama').value   = nama;
    document.getElementById('edit-customer-kontak').value = kontak;
    document.getElementById('edit-customer-email').value  = email;
    document.getElementById('edit-customer-alamat').value = alamat;
    document.getElementById('edit-customer-error').classList.add('hidden');
    document.getElementById('err-edit-customer-nama').classList.add('hidden');
    document.getElementById('err-edit-customer-nama').classList.remove('flex');
    document.getElementById('edit-customer-nama').classList.remove('border-red-400');
    document.getElementById('modal-edit-customer').classList.remove('hidden');
}
function closeEditCustomer() {
    document.getElementById('modal-edit-customer').classList.add('hidden');
}

// Validasi sebelum submit
document.getElementById('form-edit-customer').addEventListener('submit', function(e) {
    const nama    = document.getElementById('edit-customer-nama').value.trim();
    const kontak  = document.getElementById('edit-customer-kontak').value.trim();
    const email   = document.getElementById('edit-customer-email').value.trim();
    const errEl   = document.getElementById('edit-customer-error');
    const namaEl  = document.getElementById('edit-customer-nama');
    const errNama = document.getElementById('err-edit-customer-nama');

    errEl.classList.add('hidden');
    errNama.classList.add('hidden'); errNama.classList.remove('flex');
    namaEl.classList.remove('border-red-400');

    if (!nama) {
        e.preventDefault();
        errNama.classList.remove('hidden'); errNama.classList.add('flex');
        namaEl.classList.add('border-red-400');
        return;
    }
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

document.getElementById('modal-edit-customer')?.addEventListener('click', function(e) {
    if (e.target === this) closeEditCustomer();
});
</script>
@endif
@endsection
