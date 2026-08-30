@extends('layouts.app')

@section('title', 'Tambah Inbound')
@section('page_heading', 'Inbound - Formulir Penerimaan Barang')

@section('content')
<div class="space-y-5">
<form action="{{ route('inbound.store') }}" method="POST" id="inbound-form">
@csrf

    {{-- SEKSI 1: INFORMASI TRANSAKSI --}}
    <div class="wms-card p-6 space-y-5">
        <h3 class="wms-card-title border-b border-[#f2f4f6] pb-3 flex items-center gap-2">
            <i class="fa-solid fa-clipboard-list text-[#0058be]"></i> Informasi Transaksi
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- Tanggal --}}
            <div>
                <label class="wms-label">Tanggal Penerimaan <span class="text-red-500">*</span></label>
                <input type="date" name="Tanggal" value="{{ old('Tanggal', date('Y-m-d')) }}"
                       required class="wms-input @error('Tanggal') border-red-400 @enderror">
                @error('Tanggal')<p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>@enderror
            </div>
            {{-- Supplier --}}
            <div>
                <label class="wms-label">Supplier <span class="text-red-500">*</span></label>
                <div class="flex gap-2">
                    <select name="Supplier_ID" id="supplier-select" required
                            class="wms-select flex-1 @error('Supplier_ID') border-red-400 @enderror">
                        <option value="">— Pilih Supplier —</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->Supplier_ID }}" {{ old('Supplier_ID') == $s->Supplier_ID ? 'selected' : '' }}>
                                {{ $s->Nama }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" onclick="openSupplierModal()"
                            class="btn btn-success btn-sm gap-1.5 flex-shrink-0">
                        <i class="fa-solid fa-plus"></i> Baru
                    </button>
                </div>
                @error('Supplier_ID')<p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        {{-- Catatan --}}
        <div>
            <label class="wms-label">Catatan (Opsional)</label>
            <textarea name="Catatan" rows="2" placeholder="Catatan tambahan..."
                      class="wms-textarea">{{ old('Catatan') }}</textarea>
        </div>
    </div>

    {{-- SEKSI 2: DETAIL BARANG --}}
    <div class="wms-card p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-[#f2f4f6] pb-3">
            <h3 class="wms-card-title flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-[#0058be]"></i> Detail Barang
            </h3>
            <button type="button" onclick="addBarisBarang()"
                    class="btn btn-primary btn-sm gap-1.5">
                <i class="fa-solid fa-plus"></i> Tambah Baris
            </button>
        </div>
        <div id="items-container" class="space-y-4"></div>
    </div>

    {{-- Submit --}}
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('inbound.index') }}" class="btn btn-outline">Batal</a>
        <button type="submit" class="btn btn-primary btn-lg gap-2">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Transaksi Inbound
        </button>
    </div>

</form>
</div>

{{-- MODAL SUPPLIER --}}
<div id="supplier-modal" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="modal-header">
            <h4 class="modal-title flex items-center gap-2"><i class="fa-solid fa-building text-[#0058be]"></i> Tambah Supplier Baru</h4>
            <button type="button" onclick="closeSupplierModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="modal-body">
            <div><label class="wms-label">Nama Supplier <span class="text-red-500">*</span></label>
                <input type="text" id="modal-nama" placeholder="PT. Maju Jaya..." class="wms-input"></div>
            <div><label class="wms-label">No. Kontak</label>
                <input type="text" id="modal-kontak" placeholder="08xx..." class="wms-input"></div>
            <div><label class="wms-label">Email</label>
                <input type="email" id="modal-email" placeholder="info@..." class="wms-input"></div>
            <div><label class="wms-label">Alamat</label>
                <textarea id="modal-alamat" rows="2" class="wms-textarea" placeholder="Jl. ..."></textarea></div>
            <p id="modal-error" class="text-red-500 text-xs hidden"></p>
            <div class="modal-footer">
                <button type="button" onclick="closeSupplierModal()" class="btn btn-outline flex-1">Batal</button>
                <button type="button" onclick="submitSupplierModal()" id="modal-submit-btn"
                        class="btn btn-success flex-1 gap-1.5">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const masterBarangs  = @json($masterBarangs->map(fn($b) => ['sku'=>$b->SKU,'nama'=>$b->Nama,'kategori'=>$b->Kategori,'rack_id'=>$b->Rack_ID,'min_stok'=>$b->Min_Stok]));
const rackLocations  = @json($rackLocations->map(fn($r) => ['id'=>$r->Rack_ID,'label'=>$r->Kode_Rak.' (Aisle '.$r->Aisle.', Lvl '.$r->Level.')']));
const kategoriList   = @json($kategoriList);
const csrfToken      = '{{ csrf_token() }}';
const supplierAjaxUrl = '{{ route("inbound.supplier.ajax") }}';
let itemCount = 0;

function buildRackOptions(sel=null){return '<option value="">— Pilih Rak —</option>'+rackLocations.map(r=>`<option value="${r.id}"${sel==r.id?' selected':''}>${r.label}</option>`).join('');}
function buildBarangOptions(){return '<option value="">— Pilih Barang —</option>'+masterBarangs.map(b=>`<option value="${b.sku}">${b.sku} — ${b.nama}</option>`).join('');}

function addBarisBarang(){
    const idx=itemCount++;
    const div=document.createElement('div');
    div.id=`item-row-${idx}`;
    div.className='border border-[#e2e8f0] rounded-xl p-4 space-y-4 bg-[#f7f9fb] item-row';
    div.innerHTML=`
    <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-slate-600"><i class="fa-solid fa-box mr-1 text-slate-400"></i>Barang #<span class="row-num">${idx+1}</span></span>
        <button type="button" onclick="removeRow(${idx})" class="remove-btn btn btn-danger btn-sm gap-1"><i class="fa-solid fa-xmark"></i> Hapus</button>
    </div>
    <div class="flex items-center gap-5">
        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
            <input type="radio" name="items[${idx}][jenis]" value="lama" onchange="toggleJenis(${idx},'lama')" checked> Barang Lama
        </label>
        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
            <input type="radio" name="items[${idx}][jenis]" value="baru" onchange="toggleJenis(${idx},'baru')"> Barang Baru
        </label>
    </div>
    <div id="panel-lama-${idx}" class="space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="md:col-span-2">
                <label class="wms-label">Pilih Barang *</label>
                <select name="items[${idx}][SKU_lama]" id="select-barang-${idx}" onchange="autoFillBarang(${idx})" class="wms-select">${buildBarangOptions()}</select>
            </div>
            <div>
                <label class="wms-label">Qty *</label>
                <input type="number" name="items[${idx}][Qty]" min="1" value="1" class="wms-input font-mono">
            </div>
        </div>
        <div id="autofill-info-${idx}" class="hidden grid grid-cols-3 gap-2 text-xs">
            <div><label class="wms-label text-[10px]">Kategori</label><input type="text" id="af-kategori-${idx}" disabled class="wms-input bg-[#eceef0] text-slate-500 text-xs"></div>
            <div><label class="wms-label text-[10px]">Rak Default</label><input type="text" id="af-rak-${idx}" disabled class="wms-input bg-[#eceef0] text-slate-500 text-xs"></div>
            <div><label class="wms-label text-[10px]">Min Stok</label><input type="text" id="af-minstok-${idx}" disabled class="wms-input bg-[#eceef0] text-slate-500 text-xs"></div>
        </div>
    </div>
    <div id="panel-baru-${idx}" class="hidden space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="wms-label">Nama Barang Baru *</label>
                <input type="text" name="items[${idx}][Nama_baru]" placeholder="Nama lengkap..." class="wms-input">
            </div>
            <div>
                <label class="wms-label">Kategori <span class="text-[#0058be] font-mono text-[10px]">(SKU Prefix: <span id="sku-preview-${idx}">---</span>)</span></label>
                <input type="text" name="items[${idx}][Kategori_baru]" id="input-kategori-${idx}"
                       oninput="updateSkuPreview(${idx})" placeholder="Elektronik / Furnitur..." list="kat-list-${idx}" class="wms-input">
                <datalist id="kat-list-${idx}">${kategoriList.map(k=>`<option value="${k}">`).join('')}</datalist>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="wms-label">Lokasi Rak *</label>
                <select name="items[${idx}][Rack_ID_baru]" class="wms-select">${buildRackOptions()}</select>
            </div>
            <div>
                <label class="wms-label">Min. Stok</label>
                <input type="number" name="items[${idx}][Min_Stok_baru]" min="0" value="0" class="wms-input font-mono">
            </div>
            <div>
                <label class="wms-label">Qty *</label>
                <input type="number" name="items[${idx}][Qty]" min="1" value="1" class="wms-input font-mono">
            </div>
        </div>
    </div>
    <div class="border-t border-[#e2e8f0] pt-3">
        <label class="wms-label">No. Resi Supplier</label>
        <div class="flex items-center gap-3">
            <input type="text" name="items[${idx}][No_Resi_Supplier]" id="resi-input-${idx}"
                   placeholder="Contoh: PO-2026-00123" class="wms-input flex-1 font-mono">
            <label class="flex items-center gap-2 cursor-pointer flex-shrink-0 text-xs text-slate-600">
                <input type="checkbox" id="tanpa-resi-${idx}" name="items[${idx}][tanpa_resi]" value="1"
                       onchange="toggleResiInput(${idx})" class="w-4 h-4 rounded"> Tidak ada resi
            </label>
        </div>
    </div>`;
    document.getElementById('items-container').appendChild(div);
    updateRemoveButtons();
}

function removeRow(idx){const el=document.getElementById(`item-row-${idx}`);if(el)el.remove();updateRemoveButtons();renumberRows();}
function updateRemoveButtons(){const rows=document.querySelectorAll('.item-row');rows.forEach(r=>{const b=r.querySelector('.remove-btn');if(b)b.style.display=rows.length<=1?'none':'flex';});}
function renumberRows(){document.querySelectorAll('.row-num').forEach((el,i)=>el.textContent=i+1);}

function toggleJenis(idx,jenis){
    document.getElementById(`panel-lama-${idx}`).classList.toggle('hidden',jenis!=='lama');
    document.getElementById(`panel-baru-${idx}`).classList.toggle('hidden',jenis!=='baru');
}
function autoFillBarang(idx){
    const sku=document.getElementById(`select-barang-${idx}`).value;
    const b=masterBarangs.find(x=>x.sku===sku);
    const info=document.getElementById(`autofill-info-${idx}`);
    if(b){
        document.getElementById(`af-kategori-${idx}`).value=b.kategori||'-';
        const r=rackLocations.find(x=>x.id==b.rack_id);
        document.getElementById(`af-rak-${idx}`).value=r?r.label:'-';
        document.getElementById(`af-minstok-${idx}`).value=b.min_stok;
        info.classList.remove('hidden');info.classList.add('grid');
    }else{info.classList.add('hidden');info.classList.remove('grid');}
}
function updateSkuPreview(idx){
    const v=document.getElementById(`input-kategori-${idx}`).value;
    const k=v.replace(/[aeiou\s]/gi,'').substring(0,3).toUpperCase().padEnd(3,'X');
    document.getElementById(`sku-preview-${idx}`).textContent=k+'-XXXXX';
}
function toggleResiInput(idx){
    const cb=document.getElementById(`tanpa-resi-${idx}`);
    const inp=document.getElementById(`resi-input-${idx}`);
    inp.disabled=cb.checked;inp.value='';
    inp.classList.toggle('bg-[#eceef0]',cb.checked);
}

// Supplier Modal
function openSupplierModal(){document.getElementById('supplier-modal').classList.remove('hidden');document.getElementById('modal-nama').focus();}
function closeSupplierModal(){
    document.getElementById('supplier-modal').classList.add('hidden');
    document.getElementById('modal-error').classList.add('hidden');
    ['modal-nama','modal-kontak','modal-email','modal-alamat'].forEach(id=>document.getElementById(id).value='');
}
function submitSupplierModal(){
    const nama=document.getElementById('modal-nama').value.trim();
    const errEl=document.getElementById('modal-error');
    const btn=document.getElementById('modal-submit-btn');
    if(!nama){errEl.textContent='Nama supplier wajib diisi.';errEl.classList.remove('hidden');return;}
    btn.disabled=true;btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
    fetch(supplierAjaxUrl,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body:JSON.stringify({Nama:nama,No_Kontak:document.getElementById('modal-kontak').value,Email:document.getElementById('modal-email').value,Alamat:document.getElementById('modal-alamat').value})})
    .then(r=>r.json()).then(d=>{
        if(d.success){const sel=document.getElementById('supplier-select');sel.add(new Option(d.supplier.nama,d.supplier.id,true,true));closeSupplierModal();}
        else{errEl.textContent=d.message||'Terjadi kesalahan.';errEl.classList.remove('hidden');}
    }).catch(()=>{errEl.textContent='Gagal terhubung.';errEl.classList.remove('hidden');})
    .finally(()=>{btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-floppy-disk"></i> Simpan';});
}
document.getElementById('supplier-modal').addEventListener('click',function(e){if(e.target===this)closeSupplierModal();});
document.addEventListener('DOMContentLoaded',()=>addBarisBarang());
</script>
@endsection
