@extends('layouts.app')

@section('title', 'Buat Outbound')
@section('page_heading', 'Outbound — Formulir Pengiriman Barang')

@section('content')
<div class="space-y-5">
<form action="{{ route('outbound.store') }}" method="POST" id="outbound-form">
@csrf

    {{-- SEKSI 1 --}}
    <div class="wms-card p-6 space-y-5">
        <h3 class="wms-card-title border-b border-[#f2f4f6] pb-3 flex items-center gap-2">
            <i class="fa-solid fa-clipboard-list text-[#0058be]"></i> Informasi Pengiriman
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="wms-label">Tanggal Pengiriman <span class="text-red-500">*</span></label>
                <input type="date" name="Tanggal" value="{{ old('Tanggal', date('Y-m-d')) }}"
                       required class="wms-input @error('Tanggal') border-red-400 @enderror">
            </div>
            <div>
                <label class="wms-label">Customer <span class="text-red-500">*</span></label>
                <div class="flex gap-2">
                    <select name="Customer_ID" id="customer-select" required
                            class="wms-select flex-1 @error('Customer_ID') border-red-400 @enderror">
                        <option value="">— Pilih Customer —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->Customer_ID }}" {{ old('Customer_ID') == $c->Customer_ID ? 'selected' : '' }}>
                                {{ $c->Nama }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" onclick="openCustomerModal()"
                            class="btn btn-success btn-sm gap-1.5 flex-shrink-0">
                        <i class="fa-solid fa-plus"></i> Baru
                    </button>
                </div>
                @error('Customer_ID')<p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="wms-label">Nama Penerima / Kurir <span class="text-red-500">*</span></label>
                <input type="text" name="Nama_Penerima" value="{{ old('Nama_Penerima') }}"
                       required placeholder="Nama orang yang mengambil..."
                       class="wms-input @error('Nama_Penerima') border-red-400 @enderror">
                @error('Nama_Penerima')<p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="wms-label">Catatan (Opsional)</label>
                <textarea name="Catatan" rows="1" placeholder="Instruksi pengiriman..."
                          class="wms-textarea">{{ old('Catatan') }}</textarea>
            </div>
        </div>
    </div>

    {{-- SEKSI 2: DETAIL BARANG --}}
    <div class="wms-card p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-[#f2f4f6] pb-3">
            <div>
                <h3 class="wms-card-title flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-[#0058be]"></i> Detail Barang Dipesan
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Hanya barang dengan stok > 0 yang ditampilkan.</p>
            </div>
            <button type="button" onclick="addBarisOutbound()"
                    class="btn btn-primary btn-sm gap-1.5">
                <i class="fa-solid fa-plus"></i> Tambah Baris
            </button>
        </div>
        <div id="items-container" class="space-y-4"></div>
    </div>

    {{-- Submit --}}
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('outbound.index') }}" class="btn btn-outline">Batal</a>
        <button type="submit" class="btn btn-primary btn-lg gap-2">
            <i class="fa-solid fa-floppy-disk"></i> Buat Outbound & Picking List
        </button>
    </div>

</form>
</div>

{{-- MODAL CUSTOMER --}}
<div id="customer-modal" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="modal-header">
            <h4 class="modal-title flex items-center gap-2"><i class="fa-solid fa-users text-[#0058be]"></i> Tambah Customer Baru</h4>
            <button type="button" onclick="closeCustomerModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="modal-body">
            <div><label class="wms-label">Nama Customer <span class="text-red-500">*</span></label>
                <input type="text" id="modal-nama" placeholder="CV. Jaya Abadi..." class="wms-input"></div>
            <div><label class="wms-label">No. Kontak</label>
                <input type="text" id="modal-kontak" placeholder="08xx..." class="wms-input"></div>
            <div><label class="wms-label">Email</label>
                <input type="email" id="modal-email" placeholder="info@..." class="wms-input"></div>
            <div><label class="wms-label">Alamat</label>
                <textarea id="modal-alamat" rows="2" class="wms-textarea" placeholder="Jl. ..."></textarea></div>
            <p id="modal-error" class="text-red-500 text-xs hidden"></p>
            <div class="modal-footer">
                <button type="button" onclick="closeCustomerModal()" class="btn btn-outline flex-1">Batal</button>
                <button type="button" onclick="submitCustomerModal()" id="modal-submit-btn"
                        class="btn btn-success flex-1 gap-1.5">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const barangs = @json($barangs->map(fn($b)=>['sku'=>$b->SKU,'nama'=>$b->Nama,'stok'=>$b->stok,'rack_id'=>$b->Rack_ID,'kode_rak'=>$b->rackLocation?->Kode_Rak??'-']));
const csrfToken = '{{ csrf_token() }}';
const customerAjaxUrl = '{{ route('outbound.customer.ajax') }}';
let itemCount = 0;

function buildBarangOptions(){return '<option value="">— Pilih Barang —</option>'+barangs.map(b=>`<option value="${b.sku}" data-stok="${b.stok}">${b.sku} — ${b.nama} (Stok: ${b.stok})</option>`).join('');}

function addBarisOutbound(){
    const idx=itemCount++;
    const div=document.createElement('div');
    div.id=`item-row-${idx}`;
    div.className='border border-[#e2e8f0] rounded-xl p-4 space-y-3 bg-[#f7f9fb] item-row';
    div.innerHTML=`
    <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-slate-600"><i class="fa-solid fa-box mr-1 text-slate-400"></i>Barang #<span class="row-num">${idx+1}</span></span>
        <button type="button" onclick="removeRow(${idx})" class="remove-btn btn btn-danger btn-sm gap-1"><i class="fa-solid fa-xmark"></i> Hapus</button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
        <div class="md:col-span-2">
            <label class="wms-label">Barang *</label>
            <select name="items[${idx}][SKU]" id="select-sku-${idx}" onchange="updateStokBadge(${idx})" required class="wms-select">${buildBarangOptions()}</select>
            <div id="stok-badge-${idx}" class="mt-1.5 hidden text-[11px] text-slate-500">
                Stok: <span id="stok-val-${idx}" class="font-mono font-bold text-[#10b981]"></span>
            </div>
        </div>
        <div>
            <label class="wms-label">Qty *</label>
            <input type="number" name="items[${idx}][Qty]" id="qty-${idx}" min="1" value="1" required class="wms-input font-mono">
        </div>
    </div>`;
    document.getElementById('items-container').appendChild(div);
    updateRemoveButtons();
}

function removeRow(idx){const el=document.getElementById(`item-row-${idx}`);if(el)el.remove();updateRemoveButtons();renumberRows();}
function updateRemoveButtons(){const rows=document.querySelectorAll('.item-row');rows.forEach(r=>{const b=r.querySelector('.remove-btn');if(b)b.style.display=rows.length<=1?'none':'flex';});}
function renumberRows(){document.querySelectorAll('.row-num').forEach((el,i)=>el.textContent=i+1);}

function updateStokBadge(idx){
    const sku=document.getElementById(`select-sku-${idx}`).value;
    const badge=document.getElementById(`stok-badge-${idx}`);
    const valEl=document.getElementById(`stok-val-${idx}`);
    const qtyEl=document.getElementById(`qty-${idx}`);
    const b=barangs.find(x=>x.sku===sku);
    if(b){valEl.textContent=`${b.stok} unit`;badge.classList.remove('hidden');qtyEl.max=b.stok;}
    else badge.classList.add('hidden');
}

function openCustomerModal(){document.getElementById('customer-modal').classList.remove('hidden');document.getElementById('modal-nama').focus();}
function closeCustomerModal(){
    document.getElementById('customer-modal').classList.add('hidden');
    document.getElementById('modal-error').classList.add('hidden');
    ['modal-nama','modal-kontak','modal-email','modal-alamat'].forEach(id=>document.getElementById(id).value='');
}
function submitCustomerModal(){
    const nama=document.getElementById('modal-nama').value.trim();
    const errEl=document.getElementById('modal-error');
    const btn=document.getElementById('modal-submit-btn');
    if(!nama){errEl.textContent='Nama customer wajib diisi.';errEl.classList.remove('hidden');return;}
    btn.disabled=true;btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
    fetch(customerAjaxUrl,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body:JSON.stringify({Nama:nama,No_Kontak:document.getElementById('modal-kontak').value,Email:document.getElementById('modal-email').value,Alamat:document.getElementById('modal-alamat').value})})
    .then(r=>r.json()).then(d=>{
        if(d.success){const sel=document.getElementById('customer-select');sel.add(new Option(d.customer.nama,d.customer.id,true,true));closeCustomerModal();}
        else{errEl.textContent=d.message||'Terjadi kesalahan.';errEl.classList.remove('hidden');}
    }).catch(()=>{errEl.textContent='Gagal terhubung.';errEl.classList.remove('hidden');})
    .finally(()=>{btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-floppy-disk"></i> Simpan';});
}
document.getElementById('customer-modal').addEventListener('click',function(e){if(e.target===this)closeCustomerModal();});
document.addEventListener('DOMContentLoaded',()=>addBarisOutbound());
</script>
@endsection
