@extends('layouts.app')

@section('title', 'Lokasi Rak')
@section('page_heading', 'Master Data - Lokasi Rak')

@section('content')
<div class="space-y-6">

    {{-- Header + Search + Add button --}}
    <div class="wms-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-map-pin text-[#0058be]"></i>
                Daftar Lokasi Rak Gudang
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola titik lokasi penyimpanan, lorong, tingkat, dan kapasitas rak.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <form action="{{ route('master.rak.index') }}" method="GET" class="flex items-center gap-2">
                <div class="search-group">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari kode rak / lorong..."
                           class="search-input" style="width:13rem;">
                    <button type="submit" class="search-btn">Cari</button>
                </div>
                @if($search)
                    <a href="{{ route('master.rak.index') }}"
                       class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1">
                        <i class="fa-solid fa-xmark text-[10px]"></i> Reset
                    </a>
                @endif
            </form>

            @if(auth()->user()->isAdmin())
                <button type="button"
                        onclick="openAddModal()"
                        class="btn btn-primary gap-1.5">
                    <i class="fa-solid fa-plus"></i> Tambah Rak
                </button>
            @endif
        </div>
    </div>

    {{-- Table --}}
    <div class="wms-card overflow-hidden">
        <div class="overflow-x-auto">
            @if($racks->count() > 0)
                <table class="wms-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Rak</th>
                            <th>Nama / Posisi Lokasi</th>
                            <th>Kapasitas Maksimal</th>
                            <th>Kapasitas Terpakai</th>
                            <th>Status Kapasitas</th>
                            @if(auth()->user()->isAdmin())
                                <th class="text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($racks as $index => $rack)
                            <tr>
                                <td class="font-mono text-slate-400">{{ $racks->firstItem() + $index }}</td>
                                <td class="font-mono font-bold text-[#0058be]">{{ $rack->Kode_Rak }}</td>
                                <td class="font-medium text-slate-800">
                                    Lorong {{ $rack->Aisle }} (Level {{ $rack->Level }})
                                </td>
                                <td class="font-mono text-slate-700">{{ number_format($rack->Kapasitas) }} unit</td>
                                <td class="font-mono font-bold text-slate-900">{{ number_format($rack->kapasitas_terpakai) }} unit</td>
                                <td>
                                    @php $status = $rack->status_kapasitas; @endphp
                                    @if($status === 'Penuh')
                                        <span class="badge badge-danger">
                                            <i class="fa-solid fa-circle-xmark"></i> Penuh
                                        </span>
                                    @elseif($status === 'Hampir Penuh')
                                        <span class="badge badge-warning">
                                            <i class="fa-solid fa-triangle-exclamation"></i> Hampir Penuh
                                        </span>
                                    @else
                                        <span class="badge badge-success">
                                            <i class="fa-solid fa-circle-check"></i> Tersedia
                                        </span>
                                    @endif
                                </td>
                                @if(auth()->user()->isAdmin())
                                    <td class="text-right space-x-1">
                                        <button type="button"
                                                onclick="openEditModal({{ json_encode($rack) }})"
                                                class="btn btn-outline btn-sm gap-1.5">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>
                                        <form action="{{ route('master.rak.destroy', $rack->Rack_ID) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus rak {{ $rack->Kode_Rak }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm gap-1.5">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-12 text-center text-slate-400 text-xs">
                    <i class="fa-solid fa-location-dot text-3xl mb-3 block"></i>
                    Belum ada data lokasi rak yang terdaftar.
                </div>
            @endif
        </div>

        @if($racks->hasPages())
            <div class="p-4 border-t border-[#e2e8f0] bg-slate-50">
                {{ $racks->links() }}
            </div>
        @endif
    </div>

</div>

{{-- ADMIN ADD / EDIT RACK MODAL --}}
@if(auth()->user()->isAdmin())
    <div id="rackModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs hidden items-center justify-center z-50 p-4">
        <div class="max-w-md w-full bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">

            <div class="bg-slate-900 p-5 text-white flex items-center justify-between">
                <h3 id="modalTitle" class="text-base font-bold flex items-center gap-2">
                    <i class="fa-solid fa-map-pin"></i> Tambah Lokasi Rak
                </h3>
                <button type="button" onclick="closeModal()"
                        class="text-slate-400 hover:text-white transition-colors cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="rackForm" action="{{ route('master.rak.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" id="methodField" name="_method" value="POST">

                <div>
                    <label for="Kode_Rak" class="wms-label">Kode Rak <span class="text-red-500">*</span></label>
                    <input type="text" id="Kode_Rak" name="Kode_Rak" required
                           placeholder="Contoh: R-A1-01"
                           class="wms-input w-full font-mono">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="Aisle" class="wms-label">Lorong <span class="text-red-500">*</span></label>
                        <input type="text" id="Aisle" name="Aisle" required
                               placeholder="Contoh: A1"
                               class="wms-input w-full">
                    </div>
                    <div>
                        <label for="Level" class="wms-label">Tingkat Rak <span class="text-red-500">*</span></label>
                        <input type="text" id="Level" name="Level" required
                               placeholder="Contoh: 01"
                               class="wms-input w-full">
                    </div>
                </div>

                <div>
                    <label for="Kapasitas" class="wms-label">Kapasitas Maksimal (Unit) <span class="text-red-500">*</span></label>
                    <input type="number" id="Kapasitas" name="Kapasitas" required min="1"
                           placeholder="Contoh: 500"
                           class="wms-input w-full font-mono">
                </div>

                <div class="pt-3 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeModal()" class="btn-outline">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary gap-1.5">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Data
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-plus mr-1.5"></i> Tambah Lokasi Rak';
            document.getElementById('rackForm').action = "{{ route('master.rak.store') }}";
            document.getElementById('methodField').value = 'POST';
            document.getElementById('Kode_Rak').value = '';
            document.getElementById('Aisle').value = '';
            document.getElementById('Level').value = '';
            document.getElementById('Kapasitas').value = '';

            document.getElementById('rackModal').classList.remove('hidden');
            document.getElementById('rackModal').classList.add('flex');
        }

        function openEditModal(rack) {
            document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square mr-1.5"></i> Edit Rak: ' + rack.Kode_Rak;
            document.getElementById('rackForm').action = "/master-data/rak/" + rack.Rack_ID;
            document.getElementById('methodField').value = 'PUT';
            document.getElementById('Kode_Rak').value = rack.Kode_Rak;
            document.getElementById('Aisle').value = rack.Aisle;
            document.getElementById('Level').value = rack.Level;
            document.getElementById('Kapasitas').value = rack.Kapasitas;

            document.getElementById('rackModal').classList.remove('hidden');
            document.getElementById('rackModal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('rackModal').classList.remove('flex');
            document.getElementById('rackModal').classList.add('hidden');
        }
    </script>
@endif
@endsection
