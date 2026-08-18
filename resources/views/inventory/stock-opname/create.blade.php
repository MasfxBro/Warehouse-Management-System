@extends('layouts.app')

@section('title', 'Buat Stock Opname')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Buat Stock Opname</h1>
        <p class="text-gray-600 mt-1">Audit stok fisik dan sistem</p>
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
                <option value="{{ $barang->SKU }}" data-stock="{{ $barang->stok_real }}" data-satuan="{{ $barang->satuan }}">
                    {{ $barang->SKU }} - {{ $barang->Nama }} (Stok: {{ $barang->stok_real }})
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
            <label class="block text-sm font-medium text-gray-700 mb-2">Stok Sistem</label>
            <div class="flex items-center gap-2">
                <input 
                    type="text" 
                    id="stokSistem"
                    value="0"
                    readonly
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 font-bold text-lg"
                >
                <span id="satuanSistem" class="text-sm text-gray-600">unit</span>
            </div>
        </div>

        <!-- Stok Fisik -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Stok Fisik (Hasil Hitung) <span class="text-red-500">*</span></label>
            <input 
                type="number" 
                name="stok_fisik" 
                id="stokFisik"
                min="0"
                required
                onkeyup="calculateVariance()"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('stok_fisik') border-red-500 @enderror"
                placeholder="0"
            >
            @error('stok_fisik')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Variance Display -->
        <div id="varianceBox" class="hidden p-4 rounded-lg border-2">
            <label class="block text-sm font-medium mb-2">Selisih:</label>
            <div class="text-3xl font-bold" id="varianceValue">0</div>
        </div>

        <!-- Auto Correct -->
        <div class="flex items-center">
            <input 
                type="checkbox" 
                name="auto_correct" 
                id="autoCorrect"
                value="1"
                class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            >
            <label for="autoCorrect" class="ml-3 text-sm text-gray-700">
                <span class="font-medium">Koreksi stok otomatis</span>
                <span class="text-gray-500 block">Update stok sistem sesuai hasil hitung fisik</span>
            </label>
        </div>

        <!-- Action Taken -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tindakan yang Diambil</label>
            <textarea 
                name="action_taken" 
                rows="2"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Jelaskan tindakan yang diambil untuk mengatasi selisih"
            >{{ old('action_taken') }}</textarea>
        </div>

        <!-- Notes -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
            <textarea 
                name="notes" 
                rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Catatan tambahan (opsional)"
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
                Simpan Opname
            </button>
        </div>
    </form>
</div>

<script>
function updateSystemStock() {
    const select = document.getElementById('skuSelect');
    const option = select.options[select.selectedIndex];
    const stock = option.dataset.stock || 0;
    const satuan = option.dataset.satuan || 'unit';
    
    document.getElementById('stokSistem').value = stock;
    document.getElementById('satuanSistem').textContent = satuan;
    
    calculateVariance();
}

function calculateVariance() {
    const stokSistem = parseInt(document.getElementById('stokSistem').value) || 0;
    const stokFisik = parseInt(document.getElementById('stokFisik').value) || 0;
    const variance = stokFisik - stokSistem;
    
    const varianceBox = document.getElementById('varianceBox');
    const varianceValue = document.getElementById('varianceValue');
    
    if (document.getElementById('stokFisik').value !== '') {
        varianceBox.classList.remove('hidden');
        varianceValue.textContent = (variance >= 0 ? '+' : '') + variance.toLocaleString();
        
        if (variance > 0) {
            varianceBox.className = 'p-4 rounded-lg border-2 border-green-500 bg-green-50';
            varianceValue.className = 'text-3xl font-bold text-green-600';
        } else if (variance < 0) {
            varianceBox.className = 'p-4 rounded-lg border-2 border-red-500 bg-red-50';
            varianceValue.className = 'text-3xl font-bold text-red-600';
        } else {
            varianceBox.className = 'p-4 rounded-lg border-2 border-gray-300 bg-gray-50';
            varianceValue.className = 'text-3xl font-bold text-gray-600';
        }
    } else {
        varianceBox.classList.add('hidden');
    }
}
</script>
@endsection
