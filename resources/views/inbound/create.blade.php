@extends('layouts.app')

@section('title', 'Buat Inbound Baru')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Buat Transaksi Inbound</h1>
        <p class="text-gray-600 mt-1">Penerimaan barang masuk ke gudang</p>
    </div>

    <!-- Alert Messages -->
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
        {{ session('error') }}
    </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('inbound.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Informasi Header</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tanggal -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal <span class="text-red-500">*</span></label>
                    <input 
                        type="date" 
                        name="tanggal" 
                        value="{{ old('tanggal', date('Y-m-d')) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('tanggal') border-red-500 @enderror"
                    >
                    @error('tanggal')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Supplier -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Supplier <span class="text-red-500">*</span></label>
                    <select 
                        name="supplier_id" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('supplier_id') border-red-500 @enderror"
                    >
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->Supplier_ID }}" {{ old('supplier_id') == $supplier->Supplier_ID ? 'selected' : '' }}>
                            {{ $supplier->Nama }}
                        </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                    <textarea 
                        name="notes" 
                        rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Catatan tambahan (opsional)"
                    >{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Detail Items -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Detail Barang</h2>
                <button 
                    type="button" 
                    onclick="addDetailRow()"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors duration-200 text-sm flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Item
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="detailsTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rak</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expired Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="detailsBody">
                        <!-- Initial row will be added by JS -->
                    </tbody>
                </table>
            </div>

            @error('details')
            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror

            <div id="emptyMessage" class="text-center py-8 text-gray-500">
                <p>Klik "Tambah Item" untuk menambahkan barang</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('inbound.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                Batal
            </a>
            <button 
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200"
            >
                Simpan Transaksi
            </button>
        </div>
    </form>
</div>

<script>
let detailIndex = 0;
const barangs = @json($barangs);
const racks = @json($racks);

function addDetailRow() {
    const tbody = document.getElementById('detailsBody');
    const emptyMessage = document.getElementById('emptyMessage');
    
    const row = document.createElement('tr');
    row.id = `detail-row-${detailIndex}`;
    row.className = 'hover:bg-gray-50';
    
    row.innerHTML = `
        <td class="px-4 py-3">
            <select name="details[${detailIndex}][sku]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                <option value="">-- Pilih Barang --</option>
                ${barangs.map(b => `<option value="${b.SKU}">${b.SKU} - ${b.Nama}</option>`).join('')}
            </select>
        </td>
        <td class="px-4 py-3">
            <select name="details[${detailIndex}][rack_id]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                <option value="">-- Pilih Rak --</option>
                ${racks.map(r => `<option value="${r.Rack_ID}">${r.Kode_Rak} (${r.Kapasitas - r.kapasitas_terisi} free)</option>`).join('')}
            </select>
        </td>
        <td class="px-4 py-3">
            <input 
                type="number" 
                name="details[${detailIndex}][qty]" 
                min="1" 
                required
                class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                placeholder="0"
            >
        </td>
        <td class="px-4 py-3">
            <input 
                type="text" 
                name="details[${detailIndex}][batch]" 
                required
                class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                placeholder="BATCH-001"
            >
        </td>
        <td class="px-4 py-3">
            <input 
                type="date" 
                name="details[${detailIndex}][expired_date]"
                class="w-40 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            >
        </td>
        <td class="px-4 py-3">
            <button 
                type="button" 
                onclick="removeDetailRow(${detailIndex})"
                class="text-red-600 hover:text-red-900"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    emptyMessage.style.display = 'none';
    detailIndex++;
}

function removeDetailRow(index) {
    const row = document.getElementById(`detail-row-${index}`);
    row.remove();
    
    const tbody = document.getElementById('detailsBody');
    const emptyMessage = document.getElementById('emptyMessage');
    
    if (tbody.children.length === 0) {
        emptyMessage.style.display = 'block';
    }
}

// Add first row on page load
document.addEventListener('DOMContentLoaded', function() {
    addDetailRow();
});
</script>
@endsection
