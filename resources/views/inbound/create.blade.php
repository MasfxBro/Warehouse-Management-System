@extends('layouts.app')

@section('title', 'Tambah Inbound')
@section('page_heading', 'Inbound - Formulir Penerimaan Barang')

@section('content')
<div class="space-y-5">
    <a href="{{ route('inbound.index') }}" class="btn btn-ghost btn-sm gap-1.5">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Inbound
    </a>
<form action="{{ route('inbound.store') }}" method="POST" id="inbound-form" novalidate>
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
                <input type="date" name="Tanggal" id="input-tanggal-inbound" value="{{ old('Tanggal', date('Y-m-d')) }}"
                       required class="wms-input @error('Tanggal') border-red-400 @enderror">
                @error('Tanggal')<p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>@enderror
                <p id="err-input-tanggal-inbound" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Tanggal penerimaan wajib diisi.
                </p>
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
                <p id="err-supplier-select" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Supplier wajib dipilih.
                </p>
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
    <div class="flex items-center justify-end gap-8 pt-2">
        <a href="{{ route('inbound.index') }}" class="btn btn-outline px-8">Batal</a>
        <button type="submit" class="btn btn-primary btn-lg gap-2 px-10">
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
            <div>
                <label class="wms-label">No. Kontak
                    <span class="text-slate-400 font-normal normal-case text-[10px]">(Diharuskan diisi jika ada)</span>
                </label>
                <input type="text" id="modal-kontak" placeholder="08xx..." inputmode="numeric"
                       class="wms-input">
                <p class="text-[10px] text-slate-400 mt-0.5">Hanya angka</p>
            </div>
            <div>
                <label class="wms-label">Email
                    <span class="text-slate-400 font-normal normal-case text-[10px]">(Diharuskan diisi jika ada)</span>
                </label>
                <input type="text" id="modal-email" placeholder="info@..." class="wms-input">
                <p class="text-[10px] text-slate-400 mt-0.5">Wajib mengandung @</p>
            </div>
            <div>
                <label class="wms-label">Alamat
                    <span class="text-slate-400 font-normal normal-case text-[10px]">(Diharuskan diisi jika ada)</span>
                </label>
                <textarea id="modal-alamat" rows="2" class="wms-textarea" placeholder="Jl. ..."></textarea>
            </div>
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
const masterBarangs  = @json($masterBarangsJs);
const rackLocations  = @json($rackLocationsJs);
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
        {{-- Pilih barang + info read-only --}}
        <div>
            <label class="wms-label">Pilih Barang *</label>
            <select name="items[${idx}][SKU_lama]" id="select-barang-${idx}" onchange="autoFillBarang(${idx})" class="wms-select">${buildBarangOptions()}</select>
        </div>

        {{-- Info read-only barang: tampil setelah barang dipilih --}}
        <div id="autofill-info-${idx}" class="hidden">
            <div class="grid grid-cols-2 gap-3 p-3 rounded-lg bg-slate-50 border border-slate-200">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Kategori</p>
                    <p id="af-kategori-${idx}" class="text-xs font-semibold text-slate-700">—</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Min. Stok</p>
                    <p id="af-minstok-${idx}" class="text-xs font-semibold font-mono text-slate-700">—</p>
                </div>
            </div>
            <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                <i class="fa-solid fa-lock text-[9px]"></i> Data di atas diambil otomatis dari master barang dan tidak dapat diubah di sini.
            </p>
        </div>

        {{-- Pilih Rak + Qty: selalu bisa dipilih bebas seperti barang baru --}}
        <div id="rak-qty-lama-${idx}" class="hidden grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="wms-label">Lokasi Rak Tujuan *</label>
                <select name="items[${idx}][Rack_ID_lama]" id="rack-lama-${idx}" onchange="onRakLamaChange(${idx})" class="wms-select">
                    ${buildRackOptions()}
                </select>
                <p id="err-rack-lama-${idx}" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Rak tujuan wajib dipilih.
                </p>
            </div>
            <div>
                <label class="wms-label">Qty *</label>
                <input type="number" id="qty-lama-${idx}" name="items[${idx}][Qty]" min="1" value="1" class="wms-input font-mono">
                <p id="err-qty-lama-${idx}" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                    <span id="err-qty-lama-msg-${idx}">Qty melebihi kapasitas rak.</span>
                </p>
            </div>
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
                <select name="items[${idx}][Rack_ID_baru]" id="rack-baru-${idx}" onchange="onRakBaruChange(${idx})" class="wms-select">${buildRackOptions()}</select>
            </div>
            <div>
                <label class="wms-label">Min. Stok</label>
                <input type="number" name="items[${idx}][Min_Stok_baru]" min="0" value="0" class="wms-input font-mono">
            </div>
            <div>
                <label class="wms-label">Qty *</label>
                <input type="number" id="qty-baru-${idx}" name="items[${idx}][Qty]" min="1" value="1" class="wms-input font-mono">
                <p id="err-qty-baru-${idx}" class="hidden items-center gap-1 text-[11px] text-red-500 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                    <span id="err-qty-baru-msg-${idx}">Qty melebihi kapasitas rak.</span>
                </p>
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
    renumberRows();
    attachQtyListeners(idx);
}

function removeRow(idx){const el=document.getElementById(`item-row-${idx}`);if(el)el.remove();updateRemoveButtons();renumberRows();}
function updateRemoveButtons(){const rows=document.querySelectorAll('.item-row');rows.forEach(r=>{const b=r.querySelector('.remove-btn');if(b)b.style.display=rows.length<=1?'none':'flex';});}
function renumberRows(){document.querySelectorAll('.row-num').forEach((el,i)=>el.textContent=i+1);}

function toggleJenis(idx,jenis){
    document.getElementById(`panel-lama-${idx}`).classList.toggle('hidden',jenis!=='lama');
    document.getElementById(`panel-baru-${idx}`).classList.toggle('hidden',jenis!=='baru');
}
function autoFillBarang(idx){
    const sku = document.getElementById(`select-barang-${idx}`).value;
    const b   = masterBarangs.find(x => x.sku === sku);
    const info        = document.getElementById(`autofill-info-${idx}`);
    const rakQtyWrap  = document.getElementById(`rak-qty-lama-${idx}`);

    if (b) {
        // Isi info read-only
        document.getElementById(`af-kategori-${idx}`).textContent = b.kategori || '-';
        document.getElementById(`af-minstok-${idx}`).textContent  = b.min_stok || '0';
        info.classList.remove('hidden');

        // Tampilkan rak + qty, reset rak select
        rakQtyWrap.classList.remove('hidden');
        const rakSel = document.getElementById(`rack-lama-${idx}`);
        if (rakSel) rakSel.value = '';

        // Reset qty
        const qtyEl = document.getElementById(`qty-lama-${idx}`);
        if (qtyEl) { qtyEl.value = 1; qtyEl.removeAttribute('max'); }
    } else {
        info.classList.add('hidden');
        rakQtyWrap.classList.add('hidden');
    }
}

function onRakLamaChange(idx){
    const rakSel = document.getElementById(`rack-lama-${idx}`);
    const qtyEl  = document.getElementById(`qty-lama-${idx}`);
    const errRak = document.getElementById(`err-rack-lama-${idx}`);
    if (errRak) { errRak.classList.add('hidden'); errRak.classList.remove('flex'); }
    if (!rakSel || !qtyEl) return;
    const sisa = getSisaRak(rakSel.value);
    if (sisa !== Infinity) {
        qtyEl.max   = sisa;
        if (parseInt(qtyEl.value) > sisa) qtyEl.value = sisa;
    } else {
        qtyEl.removeAttribute('max');
    }
    validateQtyKapasitas(qtyEl, `err-qty-lama-${idx}`, `err-qty-lama-msg-${idx}`, sisa);
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

// ── Validasi kapasitas rak saat input qty ──────────────────────────
function getSisaRak(rackId) {
    const r = rackLocations.find(x => x.id == rackId);
    return r ? (r.sisa || 0) : Infinity;
}

function validateQtyKapasitas(qtyEl, errId, msgId, sisa) {
    const qty = parseInt(qtyEl.value) || 0;
    const errEl = document.getElementById(errId);
    const msgEl = document.getElementById(msgId);
    if (sisa !== Infinity && qty > sisa) {
        if (errEl) { errEl.classList.remove('hidden'); errEl.classList.add('flex'); }
        if (msgEl) msgEl.textContent = `Qty (${qty}) melebihi sisa kapasitas rak (${sisa} unit).`;
        qtyEl.classList.add('border-red-400');
        return false;
    } else {
        if (errEl) { errEl.classList.add('hidden'); errEl.classList.remove('flex'); }
        qtyEl.classList.remove('border-red-400');
        return true;
    }
}

// Dipanggil saat pilih rak baru (barang baru) — update max qty dan validasi
function onRakBaruChange(idx) {
    const rakSel = document.getElementById(`rack-baru-${idx}`);
    const qtyEl  = document.getElementById(`qty-baru-${idx}`);
    if (!rakSel || !qtyEl) return;
    const sisa = getSisaRak(rakSel.value);
    if (sisa !== Infinity) {
        qtyEl.max = sisa;
        if (parseInt(qtyEl.value) > sisa) qtyEl.value = sisa;
    }
    validateQtyKapasitas(qtyEl, `err-qty-baru-${idx}`, `err-qty-baru-msg-${idx}`, sisa);
}

// Listener realtime qty barang baru — dipasang setelah innerHTML di-set
function attachQtyListeners(idx) {
    // Barang baru: qty berubah → validasi vs kapasitas rak
    const qtyBaruEl = document.getElementById(`qty-baru-${idx}`);
    const rakSel    = document.getElementById(`rack-baru-${idx}`);
    if (qtyBaruEl) {
        qtyBaruEl.addEventListener('input', function() {
            const sisa = rakSel ? getSisaRak(rakSel.value) : Infinity;
            if (sisa !== Infinity && parseInt(this.value) > sisa) this.value = sisa;
            validateQtyKapasitas(this, `err-qty-baru-${idx}`, `err-qty-baru-msg-${idx}`, sisa);
        });
    }
    // Barang lama: qty berubah → validasi vs kapasitas rak yang dipilih
    const qtyLamaEl = document.getElementById(`qty-lama-${idx}`);
    const rakLamaSel = document.getElementById(`rack-lama-${idx}`);
    if (qtyLamaEl) {
        qtyLamaEl.addEventListener('input', function() {
            const sisa = rakLamaSel ? getSisaRak(rakLamaSel.value) : Infinity;
            if (sisa !== Infinity && parseInt(this.value) > sisa) this.value = sisa;
            validateQtyKapasitas(this, `err-qty-lama-${idx}`, `err-qty-lama-msg-${idx}`, sisa);
        });
    }
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
    const kontak=document.getElementById('modal-kontak').value.trim();
    const email=document.getElementById('modal-email').value.trim();
    const errEl=document.getElementById('modal-error');
    const btn=document.getElementById('modal-submit-btn');

    // Validasi nama
    if(!nama){errEl.textContent='Nama supplier wajib diisi.';errEl.classList.remove('hidden');return;}
    // Validasi kontak: jika diisi, harus angka
    if(kontak && !/^\d+$/.test(kontak)){errEl.textContent='No. Kontak hanya boleh berisi angka.';errEl.classList.remove('hidden');return;}
    // Validasi email: jika diisi, harus mengandung @
    if(email && !email.includes('@')){errEl.textContent='Email harus mengandung karakter @.';errEl.classList.remove('hidden');return;}

    errEl.classList.add('hidden');
    btn.disabled=true;btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
    fetch(supplierAjaxUrl,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body:JSON.stringify({Nama:nama,No_Kontak:kontak,Email:email,Alamat:document.getElementById('modal-alamat').value})})
    .then(r=>r.json()).then(d=>{
        if(d.success){const sel=document.getElementById('supplier-select');sel.add(new Option(d.supplier.nama,d.supplier.id,true,true));closeSupplierModal();}
        else{errEl.textContent=d.message||'Terjadi kesalahan.';errEl.classList.remove('hidden');}
    }).catch(()=>{errEl.textContent='Gagal terhubung.';errEl.classList.remove('hidden');})
    .finally(()=>{btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-floppy-disk"></i> Simpan';});
}
document.getElementById('supplier-modal').addEventListener('click',function(e){if(e.target===this)closeSupplierModal();});
document.addEventListener('DOMContentLoaded',()=>addBarisBarang());

// Validasi resi sebelum submit
document.getElementById('inbound-form').addEventListener('submit', function(e) {
    // Validasi field utama dulu
    var valid = true;
    function showFieldErr(id, msg) {
        var p = document.getElementById('err-' + id);
        var inp = document.getElementById(id);
        if (p) { p.innerHTML = '<i class="fa-solid fa-circle-exclamation text-[10px] mr-1"></i>' + msg; p.classList.remove('hidden'); p.classList.add('flex'); }
        if (inp) inp.classList.add('border-red-400');
    }
    var tgl = document.getElementById('input-tanggal-inbound');
    var sup = document.getElementById('supplier-select');
    if (!tgl || !tgl.value) { showFieldErr('input-tanggal-inbound', 'Tanggal penerimaan wajib diisi.'); valid = false; }
    if (!sup || !sup.value)  { showFieldErr('supplier-select', 'Supplier wajib dipilih.'); valid = false; }
    ['input-tanggal-inbound','supplier-select'].forEach(function(id){
        var el=document.getElementById(id);
        if(el) el.addEventListener('change', function(){
            var p=document.getElementById('err-'+id);
            if(p){p.classList.add('hidden');p.classList.remove('flex');}
            el.classList.remove('border-red-400');
        },{once:true});
    });
    if (!valid) { e.preventDefault(); return; }

    // Validasi duplikat nama barang baru dalam satu transaksi
    var namaBaruSet = {};
    document.querySelectorAll('.item-row').forEach(function(row) {
        const idx = row.id.replace('item-row-', '');
        const jenisBaru = row.querySelector(`input[name="items[${idx}][jenis]"][value="baru"]`);
        if (jenisBaru && jenisBaru.checked) {
            const namaInput = row.querySelector(`input[name="items[${idx}][Nama_baru]"]`);
            if (namaInput && namaInput.value.trim()) {
                const namaLower = namaInput.value.trim().toLowerCase();
                if (namaBaruSet[namaLower]) {
                    // Tandai merah field yang duplikat
                    namaInput.classList.add('border-red-400');
                    valid = false;
                    // Tampilkan error di bawah input
                    var errId = `err-nama-baru-dup-${idx}`;
                    var errEl = document.getElementById(errId);
                    if (!errEl) {
                        errEl = document.createElement('p');
                        errEl.id = errId;
                        errEl.className = 'flex items-center gap-1 text-[11px] text-red-500 mt-1';
                        namaInput.parentNode.appendChild(errEl);
                    }
                    errEl.innerHTML = '<i class="fa-solid fa-circle-exclamation text-[10px]"></i> Nama barang ini sudah dipakai di baris lain. Hapus salah satu baris duplikat.';
                    errEl.classList.remove('hidden'); errEl.classList.add('flex');
                } else {
                    namaBaruSet[namaLower] = true;
                    namaInput.classList.remove('border-red-400');
                    var errEl2 = document.getElementById(`err-nama-baru-dup-${idx}`);
                    if (errEl2) { errEl2.classList.add('hidden'); errEl2.classList.remove('flex'); }
                }
            }
        }
    });
    if (!valid) { e.preventDefault(); return; }

    // Validasi kapasitas rak untuk semua baris (barang baru DAN lama)
    document.querySelectorAll('.item-row').forEach(function(row) {
        const idx = row.id.replace('item-row-', '');
        const jenisBaru = row.querySelector(`input[name="items[${idx}][jenis]"][value="baru"]`);
        const jenisLama = row.querySelector(`input[name="items[${idx}][jenis]"][value="lama"]`);

        if (jenisBaru && jenisBaru.checked) {
            const rakSel  = document.getElementById(`rack-baru-${idx}`);
            const qtyBaru = document.getElementById(`qty-baru-${idx}`);
            if (rakSel && rakSel.value && qtyBaru) {
                const sisa = getSisaRak(rakSel.value);
                if (!validateQtyKapasitas(qtyBaru, `err-qty-baru-${idx}`, `err-qty-baru-msg-${idx}`, sisa)) {
                    valid = false;
                }
            }
        } else if (jenisLama && jenisLama.checked) {
            const rakLamaSel = document.getElementById(`rack-lama-${idx}`);
            const qtyLama    = document.getElementById(`qty-lama-${idx}`);
            // Cek wajib pilih rak
            if (!rakLamaSel || !rakLamaSel.value) {
                const errRak = document.getElementById(`err-rack-lama-${idx}`);
                if (errRak) { errRak.innerHTML = '<i class="fa-solid fa-circle-exclamation text-[10px] mr-1"></i>Rak tujuan wajib dipilih.'; errRak.classList.remove('hidden'); errRak.classList.add('flex'); }
                valid = false;
            } else if (qtyLama) {
                const sisa = getSisaRak(rakLamaSel.value);
                if (!validateQtyKapasitas(qtyLama, `err-qty-lama-${idx}`, `err-qty-lama-msg-${idx}`, sisa)) {
                    valid = false;
                }
            }
        }
    });
    if (!valid) { e.preventDefault(); return; }

    // Validasi resi per baris
    const rows = document.querySelectorAll('.item-row');
    for (const row of rows) {
        const idx = row.id.replace('item-row-', '');
        const resiInput = document.getElementById(`resi-input-${idx}`);
        const tanpaResi = document.getElementById(`tanpa-resi-${idx}`);
        if (!resiInput || !tanpaResi) continue;
        // Jika resi kosong DAN checkbox "tanpa resi" tidak dicentang
        if (!tanpaResi.checked && resiInput.value.trim() === '') {
            e.preventDefault();
            resiInput.classList.add('border-red-400');
            resiInput.focus();
            resiInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            // Tampilkan warning inline
            let warn = resiInput.parentElement.querySelector('.resi-warn');
            if (!warn) {
                warn = document.createElement('p');
                warn.className = 'resi-warn text-red-500 text-[11px] mt-1';
                warn.textContent = 'Masukkan no. resi atau centang "Tidak ada resi".';
                resiInput.parentElement.appendChild(warn);
            }
            return;
        }
        resiInput.classList.remove('border-red-400');
    }
});
</script>
@endsection
