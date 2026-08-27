@extends('layouts.app')

@section('title', 'Kartu Stok — ' . $barang->SKU)
@section('page_heading', 'Kartu Stok — ' . $barang->SKU)

@section('content')
<div class="space-y-5">

    <a href="{{ route('inventory.kartu-stok.index') }}" class="btn btn-ghost btn-sm gap-1.5">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Kartu Stok
    </a>

    {{-- Info Barang --}}
    <div class="wms-card p-6">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div class="space-y-3">
                <h2 class="text-lg font-black text-slate-900">{{ $barang->Nama }}</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                    @foreach([
                        ['SKU',         $barang->SKU,                    true,  'text-[#0058be]'],
                        ['Kategori',    $barang->Kategori,               false, 'text-slate-800'],
                        ['Lokasi Rak',  $barang->rackLocation->Kode_Rak ?? '-', true, 'text-slate-800'],
                        ['Min. Stok',   number_format($barang->Min_Stok) . ' unit', true, 'text-slate-700'],
                    ] as [$lbl, $val, $mono, $color])
                        <div>
                            <p class="text-[10px] text-slate-400 mb-0.5">{{ $lbl }}</p>
                            <p class="font-bold {{ $mono ? 'font-mono' : '' }} {{ $color }}">{{ $val }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Stok Saat Ini</p>
                <p class="text-4xl font-black font-mono {{ $barang->stok > $barang->Min_Stok ? 'text-[#10b981]' : 'text-[#93000a]' }}">
                    {{ number_format($barang->stok) }}
                </p>
                <p class="text-xs text-slate-400">unit</p>
                @if($barang->stok <= $barang->Min_Stok)
                    <span class="badge badge-warning mt-1"><i class="fa-solid fa-triangle-exclamation"></i> Reorder Point</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Timeline Mutasi --}}
    <div class="wms-card overflow-hidden">
        <div class="wms-card-header">
            <div>
                <h3 class="wms-card-title flex items-center gap-2">
                    <i class="fa-solid fa-timeline text-[#0058be]"></i> Timeline Mutasi Stok
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Terbaru di atas. Saldo dihitung kumulatif.</p>
            </div>
            <span class="badge badge-neutral">{{ $mutations->count() }} mutasi</span>
        </div>
        <div class="overflow-x-auto">
            @if($mutations->count() > 0)
                <table class="wms-table">
                    <thead><tr>
                        <th>No</th><th>Tanggal</th><th>Jenis</th><th>No. Referensi</th>
                        <th class="text-right">Qty Masuk (+)</th><th class="text-right">Qty Keluar (-)</th>
                        <th class="text-right">Saldo Stok</th><th>Operator</th>
                    </tr></thead>
                    <tbody>
                        @foreach($mutations as $i => $m)
                            <tr>
                                <td class="font-mono text-slate-400">{{ $mutations->count() - $i }}</td>
                                <td class="font-mono text-slate-700">
                                    {{ \Carbon\Carbon::parse($m['tanggal'])->format('d/m/Y') }}
                                </td>
                                <td>
                                    @if($m['jenis'] === 'Inbound')
                                        <span class="badge badge-success"><i class="fa-solid fa-arrow-down"></i> Inbound</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fa-solid fa-arrow-up"></i> Outbound</span>
                                    @endif
                                </td>
                                <td class="font-mono font-semibold text-[#0058be]">{{ $m['no_ref'] }}</td>
                                <td class="text-right font-mono">
                                    @if($m['qty_in'] > 0)
                                        <span class="font-bold text-[#10b981]">+{{ number_format($m['qty_in']) }}</span>
                                    @else <span class="text-slate-300">—</span> @endif
                                </td>
                                <td class="text-right font-mono">
                                    @if($m['qty_out'] > 0)
                                        <span class="font-bold text-[#93000a]">-{{ number_format($m['qty_out']) }}</span>
                                    @else <span class="text-slate-300">—</span> @endif
                                </td>
                                <td class="text-right font-mono font-black {{ $m['saldo'] > 0 ? 'text-slate-900' : 'text-[#93000a]' }}">
                                    {{ number_format($m['saldo']) }}
                                </td>
                                <td class="text-slate-600 text-xs">{{ $m['operator'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="py-12 text-center text-slate-400 text-xs space-y-2">
                    <i class="fa-solid fa-timeline text-3xl block"></i>
                    <p>Belum ada mutasi stok untuk barang ini.</p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
