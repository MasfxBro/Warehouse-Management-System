@extends('layouts.app')

@section('title', 'Buat Stock Opname')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Buat Stock Opname</h1>
        <p class="text-gray-600 mt-1">Audit fisik persediaan vs stok terhitung sistem</p>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('inventory.stock-opname.store') }}" class="bg-white rounded-lg shadow-md p-6 space-y-6">
        @csrf

        <!-- SKU Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Barang <span class="text-red-500">*</span></label>
            <select 
                name="SKU" 
                id="skuSelect"
                required
                onchange="updateSystemStock()"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('SKU') border-red-500 @enderror"
            >
                <option value="">-- Pilih Barang --</option>
                @foreach($barangs as $barang)
                <option value="{{ $barang->SKU }}" data-stock="{{ $barang->stok_sistem }}" data-satuan="{{ $barang->satuan ?? 'PCS' }}">
                    {{ $barang->SKU }} - {{ $barang->Nama }} (Stok Sistem: {{ number_format($barang->stok_sistem) }})
                </option>
                @endforeach
            </select>
            @error('SKU')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tanggal -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Opname <span class="text-red-500">*</span></label>
            <input 
                type="date" 
                name="tanggal_opname" 
                value="{{ old('tanggal_opname', date('Y-m-d')) }}"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
        </div>

        <!-- Stok Sistem (Read-only) -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Stok Sistem (Terhitung)</label>
            <div class="flex items-center gap-2">
                <input 
                    type="text" 
                    id="stokSistem"
                    value="0"
                    readonly
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 font-bold text-lg"
                >
                <span id="satuanSistem" class="text-sm text-gray-600 font-semibold">PCS</span>
            </div>
        </div>

        <!-- Stok Fisik -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Stok Fisik (Hasil Hitung Lapangan) <span class="text-red-500">*</span></label>
            <input 
                type="number" 
                name="stok_fisik" 
                id="stokFisik"
                min="0"
                required
                onkeyup="calculateVariance()"
                onchange="calculateVariance()"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('stok_fisik') border-red-500 @enderror"
                placeholder="0"
            >
            @error('stok_fisik')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Variance Display -->
        <div id="varianceBox" class="hidden p-4 rounded-lg border-2">
            <div class="flex justify-between items-center">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider mb-1">Selisih (Variance):</label>
                    <div class="text-3xl font-bold" id="varianceValue">0</div>
                </div>
                <div>
                    <span id="statusBadge" class="px-3 py-1 text-xs font-bold rounded-full"></span>
                </div>
            </div>
        </div>

        <!-- Action Taken -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tindakan / Koreksi yang Diambil</label>
            <textarea 
                name="action_taken" 
                rows="2"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Jelaskan tindakan koreksi atau investigasi jika terdapat selisih"
            >{{ old('action_taken') }}</textarea>
        </div>

        <!-- Notes -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Audit</label>
            <textarea 
                name="notes" 
                rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Catatan kondisi fisik barang di lapangan (opsional)"
            >{{ old('notes') }}</textarea>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-4 pt-4 border-t">
            <a href="{{ route('inventory.stock-opname.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                Batal
            </a>
            <button 
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200"
            >
                Simpan Hasil Audit
            </button>
        </div>
    </form>
</div>

<script>
function updateSystemStock() {
    const select = document.getElementById('skuSelect');
    const option = select.options[select.selectedIndex];
    const stock = option.dataset.stock || 0;
    const satuan = option.dataset.satuan || 'PCS';
    
    document.getElementById('stokSistem').value = stock;
    document.getElementById('satuanSistem').textContent = satuan;
    
    calculateVariance();
}

function calculateVariance() {
    const stokSistem = parseInt(document.getElementById('stokSistem').value) || 0;
    const stokFisikInput = document.getElementById('stokFisik').value;
    const stokFisik = parseInt(stokFisikInput) || 0;
    
    const varianceBox = document.getElementById('varianceBox');
    const varianceValue = document.getElementById('varianceValue');
    const statusBadge = document.getElementById('statusBadge');
    
    if (stokFisikInput !== '') {
        const variance = stokFisik - stokSistem;
        varianceBox.classList.remove('hidden');
        varianceValue.textContent = (variance >= 0 ? '+' : '') + variance.toLocaleString();
        
        if (variance > 0) {
            varianceBox.className = 'p-4 rounded-lg border-2 border-blue-500 bg-blue-50';
            varianceValue.className = 'text-3xl font-bold text-blue-600';
            statusBadge.className = 'px-3 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800';
            statusBadge.textContent = '🔺 LEBIH';
        } else if (variance < 0) {
            varianceBox.className = 'p-4 rounded-lg border-2 border-red-500 bg-red-50';
            varianceValue.className = 'text-3xl font-bold text-red-600';
            statusBadge.className = 'px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800';
            statusBadge.textContent = '🔻 KURANG';
        } else {
            varianceBox.className = 'p-4 rounded-lg border-2 border-green-500 bg-green-50';
            varianceValue.className = 'text-3xl font-bold text-green-600';
            statusBadge.className = 'px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800';
            statusBadge.textContent = '✓ SESUAI';
        }
    } else {
        varianceBox.classList.add('hidden');
    }
}
</script>
@endsection
