@extends('layouts.app')

@section('title', 'Stock Opname')
@section('page_heading', 'Inventory - Stock Opname')

@section('content')
<div class="space-y-5">

    {{-- Info Banner --}}
    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-xs flex items-start gap-3">
        <i class="fa-solid fa-circle-info text-[#0058be] text-base mt-0.5 flex-shrink-0"></i>
        <div>
            <strong class="font-bold text-slate-900 text-sm">Konsep Stock Opname</strong>
            <p class="text-slate-600 mt-0.5">
                Stock Opname di sini adalah pencatatan <strong>kondisi fisik barang</strong> hasil pemeriksaan lapangan.
                Catatan ini <strong>tidak mengubah angka stok</strong> — hanya mendokumentasikan temuan lapangan
                seperti kerusakan kemasan, kualitas barang, dsb.
            </p>
        </div>
    </div>

    {{-- Header --}}
    <div class="wms-card p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h2 class="wms-card-title flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check text-[#0058be]"></i> Riwayat Catatan Stock Opname
            </h2>
            <p class="page-subtitle">Total {{ $opnames->total() }} catatan kondisi fisik tersimpan.</p>
        </div>
        <a href="{{ route('inventory.stock-opname.create') }}" class="btn btn-primary btn-sm gap-1.5">
            <i class="fa-solid fa-plus"></i> Tambah Catatan
        </a>
    </div>

    {{-- Table --}}
    <div class="wms-card overflow-hidden">
        <div class="overflow-x-auto">
            @if($opnames->count() > 0)
                <table class="wms-table">
                    <thead><tr>
                        <th>Tanggal</th><th>SKU</th><th>Nama Barang</th>
                        <th>Kondisi Fisik</th><th>Pemeriksa</th><th class="text-right">Aksi</th>
                    </tr></thead>
                    <tbody>
                        @foreach($opnames as $op)
                            <tr>
                                <td class="font-mono text-slate-700">{{ $op->Tanggal->format('d/m/Y') }}</td>
                                <td class="font-mono font-semibold text-[#0058be]">{{ $op->SKU }}</td>
                                <td class="font-medium text-slate-900">{{ $op->masterBarang->Nama ?? '-' }}</td>
                                <td class="text-slate-700 max-w-xs text-xs leading-relaxed">
                                    {{ Str::limit($op->Kondisi, 80) }}
                                </td>
                                <td class="text-slate-600 text-xs">{{ $op->user->name ?? '-' }}</td>
                                <td class="text-right space-x-1">
                                    <a href="{{ route('inventory.stock-opname.edit', $op->Opname_ID) }}"
                                       class="btn btn-outline btn-sm gap-1">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </a>
                                    <form action="{{ route('inventory.stock-opname.destroy', $op->Opname_ID) }}" method="POST"
                                          class="inline" onsubmit="return confirm('Hapus catatan ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm gap-1">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="py-14 text-center text-slate-400 text-xs space-y-2">
                    <i class="fa-solid fa-clipboard-check text-3xl block"></i>
                    <p>Belum ada catatan stock opname.</p>
                </div>
            @endif
        </div>
        @if($opnames->hasPages())
            <div class="p-4 border-t border-[#e2e8f0] bg-[#f7f9fb]">{{ $opnames->links() }}</div>
        @endif
    </div>

</div>
@endsection
