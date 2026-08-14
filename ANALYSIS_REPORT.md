# WMS PROJECT - ANALISIS DATABASE & STRUKTUR

**Tanggal Analisis:** 2026-08-04  
**Database:** PostgreSQL (`wms_db`)  
**Framework:** Laravel 12  
**Frontend:** Blade + Tailwind CSS v4 (sudah terinstall)

---

## ✅ TABEL YANG SUDAH ADA

### 1. **users** (8 records)
- ✅ id (PK, bigint)
- ✅ name
- ✅ email (unique)
- ✅ password (hashed)
- ✅ role (varchar) — SUDAH ADA KOLOM ROLE!
- ✅ email_verified_at
- ✅ remember_token
- ✅ timestamps

**Status:** ✅ LENGKAP untuk authentication  
**Note:** Role sudah tersedia, tinggal implementasi middleware

---

### 2. **master_barang** (30 records)
- ✅ SKU (PK, varchar)
- ✅ Nama
- ✅ Kategori
- ✅ Min_Stok (integer)
- ✅ Barcode_ID (nullable) — SUDAH ADA!
- ✅ Rack_ID (FK ke rack_locations)
- ✅ timestamps

**Status:** ⚠️ PERLU TAMBAHAN  
**Missing:**
- ❌ Stok Real-time (current_stock) — DIBUTUHKAN untuk inventory
- ❌ Harga/Nilai (price) — DIBUTUHKAN untuk Total Nilai Persediaan
- ❌ Satuan (unit) — Nice to have
- ❌ Merk (brand) — Nice to have
- ❌ Country of Origin — Nice to have

---

### 3. **suppliers** (10 records)
- ✅ Supplier_ID (PK, bigint)
- ✅ Nama
- ✅ Kontak
- ✅ timestamps

**Status:** ⚠️ MINIMAL (bisa diperluas)  
**Missing (optional):**
- Alamat lengkap
- Email
- Telepon terpisah
- Contact Person

---

### 4. **customers** (10 records)
- ✅ Customer_ID (PK, bigint)
- ✅ Nama
- ✅ Alamat (text)
- ✅ timestamps

**Status:** ⚠️ MINIMAL (bisa diperluas)  
**Missing (optional):**
- Telepon
- Email
- Contact Person

---

### 5. **rack_locations** (20 records)
- ✅ Rack_ID (PK, bigint)
- ✅ Kode_Rak (unique)
- ✅ Aisle
- ✅ Level
- ✅ Kapasitas (max capacity)
- ✅ timestamps

**Status:** ⚠️ PERLU TAMBAHAN  
**Missing:**
- ❌ Kapasitas_Terisi (current_capacity) — DIBUTUHKAN untuk logika inbound/outbound

---

### 6. **inbound_transactions** (15 records)
- ✅ Inbound_ID (PK, bigint)
- ✅ No_Receiving (unique)
- ✅ Tanggal (date)
- ✅ Supplier_ID (FK)
- ✅ User_ID (FK)
- ✅ timestamps
- ✅ deleted_at (soft delete)

**Status:** ⚠️ MINIMAL  
**Missing (optional):**
- Status (draft/completed/cancelled)
- Notes/Catatan

---

### 7. **inbound_details** (53 records)
- ✅ Detail_ID (PK, bigint)
- ✅ Inbound_ID (FK)
- ✅ SKU (FK)
- ✅ Rack_ID (FK)
- ✅ Qty (integer)
- ✅ Batch (nullable)
- ✅ timestamps
- ✅ deleted_at (soft delete)

**Status:** ⚠️ PERLU TAMBAHAN  
**Missing:**
- ❌ Expired_Date — DIBUTUHKAN untuk FIFO

---

### 8. **outbound_transactions** (12 records)
- ✅ Outbound_ID (PK, bigint)
- ✅ No_Shipping (unique)
- ✅ Tanggal (date)
- ✅ Customer_ID (FK)
- ✅ No_Surat_Jalan (nullable) — SUDAH ADA!
- ✅ User_ID (FK)
- ✅ timestamps
- ✅ deleted_at (soft delete)

**Status:** ✅ LENGKAP untuk surat jalan  
**Missing (optional):**
- Status
- Notes

---

### 9. **outbound_details** (37 records)
- ✅ Detail_ID (PK, bigint)
- ✅ Outbound_ID (FK)
- ✅ SKU (FK)
- ✅ Rack_ID (FK)
- ✅ Qty (integer)
- ✅ timestamps
- ✅ deleted_at (soft delete)

**Status:** ✅ LENGKAP  
**Note:** Cukup untuk FIFO picking (ambil dari inbound_details berdasarkan batch tertua)

---

## ❌ TABEL YANG BELUM ADA

### 10. **stock_opname** (WAJIB DIBUAT)
Diperlukan untuk OPSI E (Stock Opname).

**Struktur yang diperlukan:**
- opname_id (PK, bigint, auto increment)
- SKU (FK ke master_barang)
- tanggal_opname (date)
- stok_sistem (integer)
- stok_fisik (integer)
- variance (integer) — calculated: stok_fisik - stok_sistem
- status (enum: MATCH, SELISIH)
- action_taken (text, nullable) — tindakan koreksi
- notes (text, nullable)
- user_id (FK ke users)
- timestamps

---

## 📊 KOLOM YANG PERLU DITAMBAHKAN

### Priority 1 (WAJIB):

1. **master_barang**
   - `stok_real` (integer, default 0) — Stok real-time saat ini
   - `harga` (decimal 15,2, default 0) — Harga per unit

2. **rack_locations**
   - `kapasitas_terisi` (integer, default 0) — Kapasitas yang sudah terisi

3. **inbound_details**
   - `expired_date` (date, nullable) — Tanggal kadaluarsa untuk FIFO

### Priority 2 (OPTIONAL, bisa ditambahkan nanti):

1. **inbound_transactions**
   - `status` (varchar, default 'completed')
   - `notes` (text, nullable)

2. **outbound_transactions**
   - `status` (varchar, default 'completed')
   - `notes` (text, nullable)

3. **master_barang**
   - `satuan` (varchar, default 'pcs')
   - `merk` (varchar, nullable)
   - `country_of_origin` (varchar, nullable)

4. **suppliers**
   - `alamat` (text, nullable)
   - `email` (varchar, nullable)
   - `telepon` (varchar, nullable)
   - `contact_person` (varchar, nullable)

5. **customers**
   - `telepon` (varchar, nullable)
   - `email` (varchar, nullable)
   - `contact_person` (varchar, nullable)

---

## 🚀 TEKNOLOGI YANG SUDAH TERINSTALL

✅ **Laravel 12** (PHP 8.2+)  
✅ **PostgreSQL** 18.4  
✅ **Tailwind CSS v4** (via Vite)  
✅ **Vite** (untuk bundling)  
✅ **Axios** (untuk HTTP requests)

---

## ❌ DEPENDENCY YANG PERLU DITAMBAHKAN

### Backend (Composer):
1. **barryvdh/laravel-dompdf** — untuk generate PDF Surat Jalan
2. **milon/barcode** atau **picqer/php-barcode-generator** — untuk generate barcode
3. **maatwebsite/excel** — untuk export Excel

### Frontend (NPM):
1. **Chart.js** — untuk grafik dashboard
2. **@alpinejs/alpinejs** (optional) — untuk interaktivitas kecil tanpa full JS framework

---

## 📁 CONTROLLER YANG PERLU DIBUAT

### Sudah Ada:
- ❌ BELUM ADA CONTROLLER SAMA SEKALI (hanya Controller.php base)

### Perlu Dibuat:
1. **AuthController** — login, logout
2. **DashboardController** — statistik, chart, low stock alert
3. **MasterBarangController** — CRUD barang
4. **SupplierController** — CRUD supplier
5. **CustomerController** — CRUD customer
6. **RackLocationController** — CRUD lokasi rak
7. **InboundController** — penerimaan barang, barcode
8. **OutboundController** — pengeluaran barang, FIFO picking, surat jalan PDF
9. **StockLedgerController** — kartu stok per SKU
10. **StockOpnameController** — audit fisik
11. **ReportController** — laporan & export
12. **UserController** — CRUD user (admin only)

---

## 🎨 VIEWS YANG PERLU DIBUAT

### Sudah Ada:
- welcome.blade.php (default Laravel)

### Perlu Dibuat:
1. **layouts/app.blade.php** — master layout dengan sidebar + topbar
2. **auth/login.blade.php** — halaman login
3. **dashboard.blade.php** — dashboard utama
4. **master/barang/** — index, create, edit
5. **master/supplier/** — index, create, edit
6. **master/customer/** — index, create, edit
7. **master/rack/** — index, create, edit
8. **transaksi/inbound/** — index, create, show (+ barcode)
9. **transaksi/outbound/** — index, create, show (+ picking list + PDF)
10. **inventory/kartu-stok.blade.php**
11. **inventory/stock-opname/** — index, create
12. **laporan/index.blade.php**
13. **users/** — index, create, edit (admin only)

---

## 🔐 MIDDLEWARE YANG PERLU DIBUAT

1. **EnsureUserHasRole** — cek role user (admin/manager/operator)
2. **LogActivity** (optional) — log setiap aktivitas user

---

## 📋 ROUTES YANG PERLU DIBUAT

```php
// Auth
POST /login
POST /logout

// Dashboard
GET /dashboard

// Master Data
GET|POST|PUT|DELETE /master/barang
GET|POST|PUT|DELETE /master/supplier
GET|POST|PUT|DELETE /master/customer
GET|POST|PUT|DELETE /master/rack

// Transaksi
GET|POST /inbound
GET /inbound/{id}
GET /inbound/{id}/barcode
POST /inbound/{id}/complete

GET|POST /outbound
GET /outbound/{id}
GET /outbound/{id}/picking-list
GET /outbound/{id}/surat-jalan/pdf
POST /outbound/{id}/complete

// Inventory
GET /inventory/kartu-stok
GET /inventory/kartu-stok/{sku}
GET|POST /inventory/stock-opname

// Laporan
GET /laporan
GET /laporan/inventory/export
GET /laporan/inbound/export
GET /laporan/outbound/export
GET /laporan/stock-opname/export

// User Management (admin only)
GET|POST|PUT|DELETE /users
```

---

## 📊 QUERY YANG DIPERLUKAN

### Dashboard:
```sql
-- Total SKU
SELECT COUNT(*) FROM master_barang

-- Total Stok
SELECT SUM(stok_real) FROM master_barang

-- Total Nilai Persediaan
SELECT SUM(stok_real * harga) FROM master_barang

-- Alert Reorder
SELECT COUNT(*) FROM master_barang WHERE stok_real < Min_Stok

-- Chart 7 hari
SELECT DATE(Tanggal), 
       SUM(Qty) as total_in
FROM inbound_transactions it
JOIN inbound_details id ON it.Inbound_ID = id.Inbound_ID
WHERE Tanggal >= CURRENT_DATE - 6
GROUP BY DATE(Tanggal)

-- Similar untuk outbound
```

### FIFO Picking:
```sql
SELECT id.SKU, id.Rack_ID, id.Batch, id.Qty, id.expired_date, it.Tanggal
FROM inbound_details id
JOIN inbound_transactions it ON id.Inbound_ID = it.Inbound_ID
WHERE id.SKU = ?
  AND id.Qty > 0
ORDER BY id.expired_date ASC NULLS LAST, it.Tanggal ASC
```

---

## 🧪 SEEDER YANG SUDAH ADA

✅ Data sudah di-seed:
- 8 users (dengan role)
- 30 master_barang
- 10 suppliers
- 10 customers
- 20 rack_locations
- 15 inbound_transactions + 53 details
- 12 outbound_transactions + 37 details

**Note:** Data sudah cukup untuk testing

---

## ⚠️ PERHATIAN PENTING

### Database:
- ❌ JANGAN DROP TABLE
- ❌ JANGAN DROP DATABASE
- ❌ JANGAN HAPUS DATA EXISTING
- ✅ HANYA TAMBAHKAN kolom yang WAJIB dengan ALTER TABLE
- ✅ CREATE TABLE hanya untuk stock_opname

### Logika:
- Stok real-time HARUS dihitung dari transaksi inbound - outbound
- ATAU simpan di kolom `stok_real` dan update via trigger/transaction
- Kapasitas rak HARUS dihitung dari sum(qty) per rak
- ATAU simpan di kolom `kapasitas_terisi` dan update via trigger/transaction

---

## 📝 REKOMENDASI IMPLEMENTASI

### PENDEKATAN 1 (Recommended): Kolom Stok Real-time
**Pros:**
- Query dashboard lebih cepat (langsung SELECT)
- Tidak perlu JOIN kompleks
- Real-time untuk low stock alert

**Cons:**
- Harus dijaga konsistensi dengan transaction
- Perlu DB transaction yang ketat

**Implementation:**
- Tambah kolom `stok_real` dan `kapasitas_terisi`
- Update setiap kali inbound/outbound
- Validasi stok tidak boleh negatif

### PENDEKATAN 2: Kalkulasi On-the-fly
**Pros:**
- Data selalu konsisten (source of truth dari transaksi)
- Tidak perlu update kolom tambahan

**Cons:**
- Query lebih lambat (harus SUM setiap kali)
- Dashboard bisa jadi bottleneck

---

## ✅ KESIMPULAN

### Yang Sudah Ada & Siap Pakai:
✅ Struktur database dasar lengkap  
✅ Model Eloquent sudah dibuat  
✅ Relasi antar tabel sudah benar  
✅ Tailwind CSS sudah terinstall  
✅ Data dummy sudah di-seed

### Yang Perlu Ditambahkan:
❌ 3-4 kolom WAJIB (stok_real, harga, kapasitas_terisi, expired_date)  
❌ 1 tabel baru (stock_opname)  
❌ 12 Controllers  
❌ 20+ Views (Blade)  
❌ Middleware auth & role  
❌ 3 package dependency (PDF, Barcode, Excel)

### Estimasi Waktu:
- Migration & Kolom Tambahan: **15 menit**
- Install Dependencies: **10 menit**
- Layout Master + Auth: **30 menit**
- Dashboard (OPSI A): **45 menit**
- Master Data (OPSI B): **1 jam**
- Inbound + Barcode (OPSI C): **1 jam**
- Outbound + FIFO + PDF (OPSI D): **1.5 jam**
- Kartu Stok + Stock Opname (OPSI E): **1 jam**
- Testing & Bug Fix: **30 menit**

**TOTAL:** ~6-7 jam implementasi penuh

---

## 🎯 LANGKAH SELANJUTNYA

**STEP 2:** Buat migration untuk kolom & tabel tambahan  
**STEP 3:** Install dependencies (composer & npm)  
**STEP 4:** Buat layout master & authentication  
**STEP 5-9:** Implementasi 5 OPSI secara berurutan  
**STEP 10:** Testing menyeluruh

---

**Status:** ✅ ANALISIS SELESAI  
**Next Action:** Menunggu approval untuk lanjut ke STEP 2 (Migration)
