# WMS Prototipe 2 — Referensi Lanjutan Phase 3, 4, 5
### Dokumen Kontinuitas & Refleksi Phase 1-2

> **Peringatan Keras**: DILARANG reset proyek dari nol! Gunakan, pertahankan, dan rapikan codebase yang sudah ada.

---

## 📌 STATUS PROYEK

| Phase | Nama | Status |
|---|---|---|
| Phase 1 | Auth, Middleware, Base Layout | ✅ SELESAI & TERVERIFIKASI |
| Phase 2 | Dashboard Interactive & Master Data | ✅ SELESAI & TERVERIFIKASI |
| Phase 3 | Workflow Transaksi Inbound Kompleks | 🔄 SEDANG BERJALAN (Step 1 & 2 selesai) |
| Phase 4 | Outbound Workflow, Picking List, Surat Jalan PDF | ⏳ BELUM DIMULAI |
| Phase 5 | Inventory, Stock Opname, Laporan Excel, Polish | ⏳ BELUM DIMULAI |

---

## 🛡️ REFLEKSI PHASE 1 & 2 — PELAJARAN & PANTANGAN

### ❌ KESALAHAN YANG HARUS DIHINDARI

#### 1. Jangan Gunakan Kolom `Stok` Langsung di Database
**Masalah yang terjadi di Phase 1**: Error `SQLSTATE[42703]: column "Stok" does not exist` karena tabel `master_barang` **TIDAK MEMILIKI kolom `Stok` secara fisik**.

**Solusi yang sudah berjalan (WAJIB DIPERTAHANKAN)**:
```php
// Di MasterBarang model — Stok DIHITUNG DINAMIS via accessor
public function getStokAttribute(): int
{
    return $this->inboundDetails()->sum('Qty') - $this->outboundDetails()->sum('Qty');
}
```
> ⚠️ **JANGAN PERNAH** `$item->Stok`, `sum("Stok")`, atau `->where('Stok', ...)` langsung ke DB. Selalu gunakan `$item->stok` (accessor lowercase).

#### 2. Testing WAJIB Menggunakan SQLite In-Memory
**Masalah yang terjadi di Phase 2**: `php artisan test` (RefreshDatabase) menghapus seluruh data PostgreSQL production, termasuk akun admin/siswa.

**Fix yang sudah diterapkan di `phpunit.xml` (JANGAN DIUBAH KEMBALI)**:
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

#### 3. Seeder Harus Kompatibel dengan Kolom Migrasi
**Masalah di Phase 2**: `SupplierSeeder` menggunakan kolom `Kontak` tapi migrasi hanya punya `No_Kontak`. Harus sinkron.

**Pola yang benar**: Migrasi suppliers & customers sekarang memiliki KEDUA kolom (`Kontak` dan `No_Kontak`) untuk kompatibilitas backward.

#### 4. Error 419 Page Expired
**Masalah di Phase 1**: Login admin menampilkan `419 Page Expired`.

**Fix yang sudah diterapkan di `bootstrap/app.php`**:
- `TokenMismatchException` di-handle dengan redirect ke `/login` + flash message informatif.
- `redirect()->route('dashboard')` — bukan `redirect()->intended()` yang bisa redirect ke URL POST lama.

#### 5. Password di Database Harus Di-Hash Ulang Setelah `migrate:fresh`
Setelah `php artisan migrate:fresh --seed`, password di-hash otomatis via `Hash::make('password')` di `UserSeeder`. Jangan skip seeder!

---

### ✅ POLA YANG SUDAH TERBUKTI BERFUNGSI

#### Pola Kalkulasi Stok Real-Time
```php
// MasterBarang — GUNAKAN INI SELALU
$stok = $item->stok;           // accessor → inbound sum - outbound sum
$nilai = $item->nilai_barang;  // stok × harga

// JANGAN GUNAKAN INI
$stok = $item->Stok;           // kolom fisik tidak ada → ERROR
```

#### Pola Activity Log
```php
ActivityLog::record("Deskripsi aksi yang dilakukan oleh [{$user->name}].");
```

#### Pola Title Case Engine (Supplier & Customer)
```php
// Model Supplier & Customer sudah punya mutator via HasTitleCaseAttributes trait
// Input apapun → otomatis tersimpan Title Case
Supplier::create(['Nama' => 'pt indo jaya', 'Alamat' => 'jl. merdeka 1']);
// → Tersimpan: 'Pt Indo Jaya', 'Jl. Merdeka 1'
```

#### Pola Proteksi Route Role
```php
// routes/web.php
Route::middleware('role:admin')->group(function () { ... }); // Admin Only
Route::middleware(['auth', 'student.identity'])->group(...); // Auth + Identity
```

#### Kredensial Default (PostgreSQL Production)
- **Guru (Admin)**: `admin` / `password` (atau `admin@wms.local`)
- **Siswa (Operator)**: `siswa` / `password` (atau `siswa@wms.local`)

---

## 📋 PHASE 3 — WORKFLOW TRANSAKSI INBOUND KOMPLEKS

### Status Step
- [x] Update migrations `inbound_transactions` (+ kolom `Catatan`)
- [x] Update migrations `inbound_details` (+ kolom `No_Resi_Supplier`)
- [x] Update model `InboundTransaction.php` (fillable: Catatan, accessor: No_Resi)
- [x] Update model `InboundDetail.php` (fillable: No_Resi_Supplier)
- [ ] Buat `InboundController.php`
- [ ] Update `routes/web.php`
- [ ] Buat view `inbound/index.blade.php`
- [ ] Buat view `inbound/create.blade.php`
- [ ] Buat view `inbound/show.blade.php`
- [ ] Verifikasi & Testing

### 1. Halaman Utama Inbound (`/inbound`)
- Tabel Transaksi Inbound: No. Resi (`RSI-...`), Tanggal, Nama Supplier, Total Jenis Barang Masuk, Catatan, Tombol "Detail Inbound".
- **Live Search Real-Time** (tanpa reload): JS event listener `input` pada search bar → filter baris tabel secara client-side.
- **Filter Supplier**: Dropdown onchange reload halaman dengan `?supplier_id=X`.
- Tombol "+ Tambah Inbound Baru".
- Akses: Admin & Siswa (semua authenticated + identity).

### 2. Formulir Transaksi Inbound (`/inbound/create`)

#### Seksi 1: Informasi Transaksi
- Tanggal: `<input type="date">` dengan `value="{{ date('Y-m-d') }}"` (default hari ini).
- Supplier Selection:
  - `<select>` searchable dari tabel `suppliers`.
  - Tombol **"+ Tambah Supplier Baru"** → buka Modal Popup (AJAX, tanpa berpindah halaman).
- Modal Tambah Supplier:
  - Input: Nama, No. Kontak, Email, Alamat.
  - Submit via AJAX ke endpoint `/inbound/supplier-ajax`.
  - Backend: `Str::title()` otomatis pada `Nama` dan `Alamat`.
  - Response JSON: `{ success: true, supplier: { id, nama } }`.
  - Setelah sukses: Modal tutup → option baru ditambahkan ke `<select>` → auto select supplier baru.
- Catatan: `<textarea>` opsional.

#### Seksi 2: Detail Barang (Multi-Item)
- Baris pertama tersedia by default. Tombol **"+ Tambah Baris Barang"** men-clone baris.
- Tombol ❌ **"Hapus Baris"** tersedia di setiap baris (kecuali baris pertama jika hanya ada 1 baris).

**Field per baris**:
1. **Toggle Jenis Barang**: Radio `barang_lama` / `barang_baru`.
2. **Barang Lama** (jika toggle = lama):
   - `<select>` searchable dari `master_barang`.
   - Saat dipilih, **JavaScript auto-fill** dan **lock (disabled)** field: `Kategori`, `Lokasi Rak`, `Min Stok`.
   - Siswa hanya input: `Qty` & `No_Resi_Supplier`.
3. **Barang Baru** (jika toggle = baru):
   - `<input>` text untuk Nama Barang.
   - `<input>` Kategori (bisa ketik atau select dari kategori yang ada — data via JS array dari PHP).
   - **SKU Prefix Engine** (JavaScript real-time): Ambil konsonan dari Kategori → `ELK`, `FRN`, dst.
   - `<select>` Lokasi Rak dari `rack_locations`.
   - `<input number>` Min Stok.
   - `<input number>` Qty.
4. **No Resi Supplier**: Input text + Checkbox "Tidak tercantum nomor resi pada barang" (jika dicentang → input disabled, value null).

### 3. Backend `InboundController::store()` Logic

```
1. Validasi request (Tanggal, Supplier_ID, baris items).
2. Generate No_Receiving:
   - Format: RSI-[YYYYMMDD]-[4 DIGIT URUTAN]
   - Hitung: count transaksi inbound di hari yg sama + 1
   - Contoh: RSI-20260827-0001, RSI-20260827-0002, dst.
3. Loop setiap baris item:
   a. Jika Barang Baru:
      - Generate SKU: [PREFIX_KONSONAN_KATEGORI]-[5 DIGIT RANDOM/URUTAN]
      - Insert ke master_barang (SKU, Nama, Kategori, Rack_ID, Min_Stok)
   b. Simpan InboundDetail (Inbound_ID, SKU, Rack_ID, Qty, No_Resi_Supplier)
4. Insert InboundTransaction header.
5. ActivityLog::record().
6. Redirect ke inbound/index dengan flash success.
```

#### SKU Engine (PHP Backend)
```php
private function generateSkuPrefix(string $kategori): string
{
    // Ambil 3 konsonan pertama dari string kategori (case-insensitive)
    $konsonan = preg_replace('/[aeiou\s]/i', '', $kategori);
    $prefix = strtoupper(substr($konsonan, 0, 3));
    return str_pad($prefix, 3, 'X'); // pad jika < 3 huruf
}

private function generateSku(string $prefix): string
{
    $count = MasterBarang::where('SKU', 'LIKE', $prefix . '-%')->count();
    return $prefix . '-' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    // Contoh: ELK-00001, ELK-00002
}
```

#### RSI Generator (PHP Backend)
```php
private function generateNoReceiving(): string
{
    $today = now()->format('Ymd');
    $count = InboundTransaction::whereDate('Tanggal', today())->count();
    return 'RSI-' . $today . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    // Contoh: RSI-20260827-0001
}
```

### 4. Routes yang Dibutuhkan
```php
Route::prefix('inbound')->name('inbound.')->group(function () {
    Route::get('/', [InboundController::class, 'index'])->name('index');
    Route::get('/create', [InboundController::class, 'create'])->name('create');
    Route::post('/', [InboundController::class, 'store'])->name('store');
    Route::get('/{id}', [InboundController::class, 'show'])->name('show');
    Route::post('/supplier-ajax', [InboundController::class, 'storeSupplierAjax'])->name('supplier.ajax');
});
```

### 5. Sidebar Navigation Update
Tambahkan link **"Inbound (Masuk)"** di sidebar `layouts/app.blade.php` di bawah seksi "Transaksi".

### 6. Tes yang Diperlukan (`InboundTest.php`)
- `test_generate_rsi_format_correctly()` — pastikan format `RSI-YYYYMMDD-XXXX`.
- `test_inbound_with_new_item_creates_master_barang()` — barang baru muncul di master_barang.
- `test_inbound_with_existing_item_auto_fills_correctly()` — barang lama stok bertambah.
- `test_new_supplier_ajax_stores_with_title_case()` — Title Case Engine berjalan.

---

## 📋 PHASE 4 — OUTBOUND WORKFLOW, PICKING LIST, & SURAT JALAN PDF

### 1. Halaman Utama Outbound (`/outbound`)
- Search Bar + Filter.
- **Tabel 1: Picking List Task Queue** (Status `Not Complete`):
  - Kolom: No. Outbound, Tanggal, Customer, Total Item, Prioritas Badge, Tombol "Detail Picking List".
  - Badge Prioritas: `High` (Merah, >50 unit), `Normal` (Kuning, 11-50 unit), `Decent` (Hijau, 1-10 unit).
- **Tabel 2: Tabel Riwayat Outbound** (Status `Complete`):
  - Kolom: No. Shipping, Tanggal, Customer, Status PL, Tombol "Detail Outbound".

### 2. Formulir Outbound (`/outbound/create`)

#### Seksi 1: Informasi Outbound
- Tanggal: `date` default hari ini.
- Customer Selection:
  - Searchable `<select>`.
  - Tombol **"+ Tambah Customer Baru"** → Modal AJAX (sama polanya dengan supplier di Phase 3).
  - Backend: Title Case Engine pada `Nama` & `Alamat`.
- Nama Penerima: `<input type="text">`.
- Catatan: `<textarea>` opsional.

#### Seksi 2: Detail Barang Dipesan (Multi-Item)
- Dropdown Barang: **HANYA** tampilkan barang yang stok > 0.
- **Stok Tersedia Badge**: Di samping dropdown, tampilkan dinamis via JavaScript atau data-attribute.
  ```html
  <span id="stok-badge-0" class="...">Stok: 45 Pcs</span>
  ```
- Qty: Input number. Validasi backend: qty ≤ stok tersedia.

### 3. Picking List System

#### Model & Migration yang Dibutuhkan
```sql
-- outbound_transactions (sudah ada, pastikan kolom ini ada)
picking_status ENUM('not_complete', 'complete') DEFAULT 'not_complete'
No_Shipping VARCHAR(100) UNIQUE  -- SJ-YYYYMMDD-XXXX format
Nama_Penerima VARCHAR(255) NULL
Catatan TEXT NULL
```

#### Auto-Priority Engine
```php
private function calculatePriority(int $totalQty): string
{
    if ($totalQty > 50)  return 'high';
    if ($totalQty > 10)  return 'normal';
    return 'decent';
}
```

#### Mekanisme Complete Picking List
- Halaman Detail Picking List: `/outbound/{id}/picking-list`
  - Tampilkan daftar barang + rak lokasi + qty yang harus diambil.
  - Tombol **"Mark as Complete"** → POST request.
  - Backend: Update `picking_status = 'complete'` → stok dikurangi (`Qty` dicatat di `outbound_details`).
  - ActivityLog dicatat.

### 4. Surat Jalan PDF Generator

#### Package yang Dibutuhkan
```bash
composer require barryvdh/laravel-dompdf
```

#### Gatekeeping Rule (STRICT)
```php
// Di controller — jangan tampilkan PDF jika picking belum complete
if ($outbound->picking_status !== 'complete') {
    abort(403, 'Surat Jalan hanya dapat dicetak setelah Picking List selesai.');
}
```

Di view `outbound/show.blade.php`:
```blade
@if($outbound->picking_status === 'complete')
    <a href="{{ route('outbound.surat-jalan', $outbound->Outbound_ID) }}" class="btn-primary">
        📄 Download Surat Jalan PDF
    </a>
@else
    <button disabled class="btn-disabled" title="Selesaikan Picking List terlebih dahulu">
        🔒 Surat Jalan (Menunggu Picking)
    </button>
@endif
```

#### Format Surat Jalan PDF
- File view: `resources/views/outbound/surat-jalan-pdf.blade.php`
- Konten:
  - **Header**: Logo/Judul WMS, No. Surat Jalan (`SJ-YYYYMMDD-XXXX`), Tanggal cetak.
  - **Detail Pengirim**: Nama Gudang / Alamat Gudang.
  - **Detail Customer**: Nama, Alamat, No. Kontak.
  - **Nama Penerima**: Nama kurir/orang yang mengambil.
  - **Tabel Barang**: No | SKU | Nama Barang | Qty | Satuan.
  - **Area TTD**: Pengirim (Siswa Logistik), Penerima/Kurir, Supervisor (Guru).

#### No. Surat Jalan Generator
```php
private function generateNoShipping(): string
{
    $today = now()->format('Ymd');
    $count = OutboundTransaction::whereDate('Tanggal', today())->count();
    return 'SJ-' . $today . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}
```

### 5. Routes yang Dibutuhkan
```php
Route::prefix('outbound')->name('outbound.')->group(function () {
    Route::get('/', [OutboundController::class, 'index'])->name('index');
    Route::get('/create', [OutboundController::class, 'create'])->name('create');
    Route::post('/', [OutboundController::class, 'store'])->name('store');
    Route::get('/{id}', [OutboundController::class, 'show'])->name('show');
    Route::get('/{id}/picking-list', [OutboundController::class, 'showPickingList'])->name('picking-list');
    Route::post('/{id}/picking-complete', [OutboundController::class, 'completePicking'])->name('picking-complete');
    Route::get('/{id}/surat-jalan', [OutboundController::class, 'downloadSuratJalan'])->name('surat-jalan');
    Route::post('/customer-ajax', [OutboundController::class, 'storeCustomerAjax'])->name('customer.ajax');
});
```

### 6. Migrasi Outbound yang Perlu Diupdate
```sql
-- Pastikan outbound_transactions memiliki kolom:
picking_status  VARCHAR(20) DEFAULT 'not_complete'
priority        VARCHAR(10) DEFAULT 'decent'  -- high/normal/decent
No_Shipping     VARCHAR(100) UNIQUE
Nama_Penerima   VARCHAR(255) NULL
Catatan         TEXT NULL
```

---

## 📋 PHASE 5 — INVENTORY SYSTEM, STOCK OPNAME, LAPORAN EXCEL, & POLISH

### 1. Inventory — Kartu Stok (`/inventory/kartu-stok`)

#### Halaman Index
- Live Search by nama barang / SKU (client-side JS filter).
- Tabel: SKU, Nama Barang, Kategori, Lokasi Rak, Total Stok, Status (Aman/Reorder).
- Tombol "Detail Kartu Stok" per baris.

#### Halaman Detail Kartu Stok (`/inventory/kartu-stok/{sku}`)
- Info Barang: SKU, Nama, Kategori, Rak, Stok Saat Ini, Min Stok, Harga.
- **Tabel Timeline Mutasi Stok** (CRUCIAL):
  - Urutan kronologis: terbaru di atas.
  - Kolom: No | Tanggal | Jenis (Inbound/Outbound) | No. Ref | Qty Masuk (+) | Qty Keluar (-) | Saldo Stok | Operator.
  - **Saldo running total** dihitung dari awal: ini dihitung dari data `inbound_details` + `outbound_details` secara berurutan.

#### Query Kartu Stok
```php
// Ambil semua mutasi berurutan berdasarkan tanggal
$mutations = collect();

$inbounds = InboundDetail::with(['inboundTransaction.supplier', 'inboundTransaction.user'])
    ->where('SKU', $sku)
    ->get()
    ->map(fn($d) => [
        'tanggal'  => $d->inboundTransaction->Tanggal,
        'jenis'    => 'Inbound',
        'no_ref'   => $d->inboundTransaction->No_Receiving,
        'qty_in'   => $d->Qty,
        'qty_out'  => 0,
        'operator' => $d->inboundTransaction->user->name,
    ]);

$outbounds = OutboundDetail::with(['outboundTransaction.user'])
    ->where('SKU', $sku)
    ->get()
    ->map(fn($d) => [
        'tanggal'  => $d->outboundTransaction->Tanggal,
        'jenis'    => 'Outbound',
        'no_ref'   => $d->outboundTransaction->No_Shipping,
        'qty_in'   => 0,
        'qty_out'  => $d->Qty,
        'operator' => $d->outboundTransaction->user->name,
    ]);

$mutations = $mutations->concat($inbounds)->concat($outbounds)
    ->sortBy('tanggal')
    ->values();

// Hitung running saldo
$saldo = 0;
foreach ($mutations as &$m) {
    $saldo += $m['qty_in'] - $m['qty_out'];
    $m['saldo'] = $saldo;
}
```

### 2. Inventory — Stock Opname (`/inventory/stock-opname`)

> ⚠️ **KONSEP KETAT**: Stock Opname di WMS ini BUKAN mengubah angka stok. Ini adalah pencatatan **KONDISI FISIK BARANG** (deskripsi hasil pengecekan lapangan).

#### Migration (Buat Baru)
```sql
CREATE TABLE stock_opnames (
    Opname_ID BIGINT PRIMARY KEY,
    SKU VARCHAR(50) NOT NULL,  -- FK ke master_barang
    User_ID BIGINT NOT NULL,   -- FK ke users (pemeriksa)
    Tanggal DATE NOT NULL,
    Kondisi TEXT NOT NULL,     -- Deskripsi kondisi fisik: "Kemasan penyok", dll
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### Model `StockOpname.php`
- Fillable: `SKU`, `User_ID`, `Tanggal`, `Kondisi`.
- Relations: `belongsTo(MasterBarang)`, `belongsTo(User)`.

#### Controller `StockOpnameController.php`
- `index()`: Daftar seluruh catatan opname.
- `create()`: Form input opname baru.
- `store()`: Simpan catatan + ActivityLog.
- `edit($id)`, `update($id)`: Edit catatan opname.
- `destroy($id)`: Hapus catatan opname (soft delete atau direct).

#### Hak Akses
- **Admin & Siswa**: Full CRUD (tidak ada pembatasan role).

### 3. Laporan System (`/laporan`)

#### Package yang Dibutuhkan
```bash
composer require maatwebsite/excel
```

#### Halaman Laporan (3 Tab)

**Tab 1 — Laporan Inventori**:
- Filter: Range tanggal (Tanggal Awal - Tanggal Akhir).
- Data: Semua barang + stok + nilai + rak + status.
- Export: `.xlsx` file dengan header tabel terformat.

**Tab 2 — Laporan Inbound**:
- Filter: Range tanggal.
- Data: Semua transaksi inbound + detail supplier + detail barang & qty.
- Export: `.xlsx`.

**Tab 3 — Laporan Outbound**:
- Filter: Range tanggal.
- Data: Semua transaksi outbound + detail customer + barang & qty + status picking.
- Export: `.xlsx`.

#### Export Class (Maatwebsite Excel)
```php
// app/Exports/InventoriExport.php
class InventoriExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return ['SKU', 'Nama Barang', 'Kategori', 'Lokasi Rak', 'Total Stok', 'Nilai Aset', 'Status'];
    }
    public function collection() { ... }
}
```

#### Routes
```php
Route::prefix('laporan')->name('laporan.')->group(function () {
    Route::get('/', [LaporanController::class, 'index'])->name('index');
    Route::get('/inventori/export', [LaporanController::class, 'exportInventori'])->name('inventori.export');
    Route::get('/inbound/export', [LaporanController::class, 'exportInbound'])->name('inbound.export');
    Route::get('/outbound/export', [LaporanController::class, 'exportOutbound'])->name('outbound.export');
});
```

### 4. Final Polishing & Integrity Audit

#### Sidebar Lengkap (Semua Menu)
```
- NAVIGASI UTAMA
  └─ Dashboard
- MASTER DATA
  └─ Data Barang
  └─ Lokasi Rak
  └─ Data Supplier
  └─ Data Customer
- TRANSAKSI
  └─ Inbound (Masuk)
  └─ Outbound (Keluar)
- INVENTORY
  └─ Kartu Stok
  └─ Stock Opname
- LAPORAN
  └─ Laporan & Export
- SISTEM (Admin Only)
  └─ Log Activity
```

#### Activity Log — Seluruh Aksi yang Harus Ter-record
- Login / Logout pengguna.
- Inbound baru dibuat (dengan No. RSI).
- Outbound baru dibuat (dengan No. SJ).
- Picking List di-mark complete.
- Stock Opname baru dibuat / diperbarui.
- Tambah Lokasi Rak (Admin).
- Export laporan.

#### Final Checks
- Tidak ada error 500 / 404 pada seluruh navigasi.
- Semua tampilan responsif.
- Design System konsisten (warna: Blue `#0058BE`, Emerald `#10B981`, background `#F7F9FB`).
- Tidak ada tombol Tambah/Edit/Hapus pada Master Supplier & Customer.
- QR Barcode detail barang hanya terlihat untuk Admin.

---

## 🗂️ DAFTAR FILE PENTING PROYEK

### Models
| File | Keterangan |
|---|---|
| `app/Models/User.php` | Auth user, role cast ke Enum UserRole |
| `app/Models/MasterBarang.php` | Stok via accessor (BUKAN kolom DB) |
| `app/Models/RackLocation.php` | Accessor kapasitas_terpakai & status_kapasitas |
| `app/Models/Supplier.php` | Title Case mutator via Trait |
| `app/Models/Customer.php` | Title Case mutator via Trait |
| `app/Models/InboundTransaction.php` | Header inbound, fillable: Catatan ✅ |
| `app/Models/InboundDetail.php` | Detail baris, fillable: No_Resi_Supplier ✅ |
| `app/Models/OutboundTransaction.php` | Perlu tambah: picking_status, Nama_Penerima, Catatan |
| `app/Models/OutboundDetail.php` | Detail baris outbound |
| `app/Models/ActivityLog.php` | Log engine: `ActivityLog::record()` |

### Controllers
| File | Keterangan |
|---|---|
| `app/Http/Controllers/AuthController.php` | Login/Logout |
| `app/Http/Controllers/DashboardController.php` | Stat cards + chart data |
| `app/Http/Controllers/RackLocationController.php` | CRUD Admin, RO Siswa |
| `app/Http/Controllers/SupplierController.php` | Pure Read-Only |
| `app/Http/Controllers/CustomerController.php` | Pure Read-Only |
| `app/Http/Controllers/MasterBarangController.php` | Read-Only + Detail QR |
| `app/Http/Controllers/InboundController.php` | ⬅️ PERLU DIBUAT Phase 3 |
| `app/Http/Controllers/OutboundController.php` | ⬅️ Perlu direfactor Phase 4 |
| `app/Http/Controllers/StockOpnameController.php` | ⬅️ PERLU DIBUAT Phase 5 |
| `app/Http/Controllers/LaporanController.php` | ⬅️ PERLU DIBUAT Phase 5 |

### Views
| File | Keterangan |
|---|---|
| `resources/views/layouts/app.blade.php` | Base layout + sidebar + topbar |
| `resources/views/dashboard.blade.php` | Dashboard Chart.js interaktif |
| `resources/views/master/rak/index.blade.php` | Tabel rak + Admin CRUD modal |
| `resources/views/master/supplier/index.blade.php` | Read-only (NO CRUD BUTTONS) |
| `resources/views/master/customer/index.blade.php` | Read-only (NO CRUD BUTTONS) |
| `resources/views/master/barang/index.blade.php` | Read-only tabel barang |
| `resources/views/master/barang/show.blade.php` | Detail + QR Admin Only |
| `resources/views/inbound/index.blade.php` | ⬅️ PERLU DIBUAT Phase 3 |
| `resources/views/inbound/create.blade.php` | ⬅️ PERLU DIBUAT Phase 3 |
| `resources/views/inbound/show.blade.php` | ⬅️ PERLU DIBUAT Phase 3 |
| `resources/views/outbound/index.blade.php` | ⬅️ Perlu refactor Phase 4 |
| `resources/views/outbound/create.blade.php` | ⬅️ Perlu refactor Phase 4 |
| `resources/views/outbound/show.blade.php` | ⬅️ Perlu refactor Phase 4 |
| `resources/views/outbound/picking-list.blade.php` | ⬅️ PERLU DIBUAT Phase 4 |
| `resources/views/outbound/surat-jalan-pdf.blade.php` | ⬅️ PERLU DIBUAT Phase 4 |
| `resources/views/inventory/kartu-stok.blade.php` | ⬅️ PERLU DIBUAT Phase 5 |
| `resources/views/inventory/kartu-stok-detail.blade.php` | ⬅️ PERLU DIBUAT Phase 5 |
| `resources/views/inventory/stock-opname.blade.php` | ⬅️ PERLU DIBUAT Phase 5 |
| `resources/views/laporan/index.blade.php` | ⬅️ PERLU DIBUAT Phase 5 |

---

## 🧪 PERINTAH WAJIB SETELAH SETIAP PHASE

```bash
# 1. Reset & seed database
php artisan migrate:fresh --seed

# 2. Jalankan seluruh test (gunakan sqlite in-memory — tidak merusak DB production)
php artisan test

# 3. Build frontend assets
npm run build

# 4. Verifikasi kredensial login
php artisan tinker --execute="echo Auth::attempt(['email'=>'admin@wms.local','password'=>'password']) ? 'OK' : 'FAIL';"
```

---

## 🔑 ATURAN DESAIN YANG TIDAK BOLEH DILANGGAR

1. **Background App**: `#F7F9FB` (abu-abu sangat muda).
2. **Warna Primary**: `#0058BE` (biru tua — tombol utama, link, badge info).
3. **Warna Success/Inbound**: `#10B981` (emerald green).
4. **Warna Danger**: `#93000A` / `#FFDAD6` (merah tua + background merah muda).
5. **Font Teks Umum**: Inter / Tailwind default sans.
6. **Font Data/Kode**: `font-mono` untuk SKU, No. Resi, angka stok.
7. **Sidebar**: Fixed 240px, active indicator bar biru 3px kiri, tidak scrollable horizontal.
8. **Cards/Panel**: `rounded-xl`, `border border-[#E2E8F0]`, `shadow-xs`, background putih.
