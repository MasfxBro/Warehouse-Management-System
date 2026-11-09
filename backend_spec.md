# Backend Specification — Warehouse Management System

> Dokumen ini dihasilkan secara otomatis dari seluruh file migration di `database/migrations` dan file route di `routes/web.php`.
> Catatan: Proyek ini tidak menggunakan `routes/api.php`; semua endpoint dilayani melalui `routes/web.php` dengan session-based authentication.

---

## 1. Struktur Database

### 1.1 `users`
Tabel bawaan Laravel, dimodifikasi untuk menambahkan kolom `role`.

| Kolom               | Tipe Data              | Constraint / Keterangan                              |
|---------------------|------------------------|------------------------------------------------------|
| `id`                | `bigint` (auto incr.)  | Primary Key                                          |
| `name`              | `varchar(255)`         | Nama pengguna                                        |
| `email`             | `varchar(255)`         | Unique                                               |
| `email_verified_at` | `timestamp`            | Nullable                                             |
| `password`          | `varchar(255)`         | Hash password                                        |
| `role`              | `varchar(20)`          | Default: `'user'` — nilai: `admin`, `user`; indexed  |
| `remember_token`    | `varchar(100)`         | Nullable, untuk fitur "remember me"                  |
| `created_at`        | `timestamp`            | —                                                    |
| `updated_at`        | `timestamp`            | —                                                    |

**Relasi:** Direferensikan oleh `inbound_transactions.User_ID`, `outbound_transactions.User_ID`, `stock_opnames.User_ID`, `activity_logs.user_id`.

---

### 1.2 `password_reset_tokens`
Tabel bawaan Laravel untuk reset password.

| Kolom        | Tipe Data     | Keterangan           |
|--------------|---------------|----------------------|
| `email`      | `varchar(255)` | Primary Key          |
| `token`      | `varchar(255)` | Token reset password |
| `created_at` | `timestamp`    | Nullable             |

---

### 1.3 `sessions`
Tabel bawaan Laravel untuk manajemen sesi.

| Kolom           | Tipe Data      | Keterangan                         |
|-----------------|----------------|------------------------------------|
| `id`            | `varchar(255)` | Primary Key                        |
| `user_id`       | `bigint`       | Nullable, indexed; FK ke `users`   |
| `ip_address`    | `varchar(45)`  | Nullable                           |
| `user_agent`    | `text`         | Nullable                           |
| `payload`       | `longtext`     | —                                  |
| `last_activity` | `integer`      | Indexed                            |

---

### 1.4 `cache` & `cache_locks`
Tabel bawaan Laravel untuk caching.

**`cache`**

| Kolom        | Tipe Data      | Keterangan  |
|--------------|----------------|-------------|
| `key`        | `varchar(255)` | Primary Key |
| `value`      | `mediumtext`   | —           |
| `expiration` | `integer`      | —           |

**`cache_locks`**

| Kolom        | Tipe Data      | Keterangan  |
|--------------|----------------|-------------|
| `key`        | `varchar(255)` | Primary Key |
| `owner`      | `varchar(255)` | —           |
| `expiration` | `integer`      | —           |

---

### 1.5 `jobs`, `job_batches`, `failed_jobs`
Tabel bawaan Laravel untuk queue.

**`jobs`**

| Kolom          | Tipe Data             | Keterangan        |
|----------------|-----------------------|-------------------|
| `id`           | `bigint` (auto incr.) | Primary Key       |
| `queue`        | `varchar(255)`        | Indexed           |
| `payload`      | `longtext`            | —                 |
| `attempts`     | `tinyint unsigned`    | —                 |
| `reserved_at`  | `int unsigned`        | Nullable          |
| `available_at` | `int unsigned`        | —                 |
| `created_at`   | `int unsigned`        | —                 |

**`job_batches`**

| Kolom           | Tipe Data      | Keterangan  |
|-----------------|----------------|-------------|
| `id`            | `varchar(255)` | Primary Key |
| `name`          | `varchar(255)` | —           |
| `total_jobs`    | `integer`      | —           |
| `pending_jobs`  | `integer`      | —           |
| `failed_jobs`   | `integer`      | —           |
| `failed_job_ids`| `longtext`     | —           |
| `options`       | `mediumtext`   | Nullable    |
| `cancelled_at`  | `integer`      | Nullable    |
| `created_at`    | `integer`      | —           |
| `finished_at`   | `integer`      | Nullable    |

**`failed_jobs`**

| Kolom       | Tipe Data             | Keterangan           |
|-------------|-----------------------|----------------------|
| `id`        | `bigint` (auto incr.) | Primary Key          |
| `uuid`      | `varchar(255)`        | Unique               |
| `connection`| `text`                | —                    |
| `queue`     | `text`                | —                    |
| `payload`   | `longtext`            | —                    |
| `exception` | `longtext`            | —                    |
| `failed_at` | `timestamp`           | Default: current time|

---

### 1.6 `rack_locations`
Tabel master lokasi rak di gudang.

| Kolom       | Tipe Data         | Keterangan                              |
|-------------|-------------------|-----------------------------------------|
| `Rack_ID`   | `uuid`            | Primary Key                             |
| `Kode_Rak`  | `varchar(50)`     | Unique — contoh: `R-A1-01`             |
| `Aisle`     | `varchar(20)`     | Lorong tempat rak berada                |
| `Level`     | `varchar(20)`     | Tingkat/level rak                       |
| `Kapasitas` | `int unsigned`    | Kapasitas maksimal unit barang          |
| `foto_path` | `varchar(500)`    | Nullable — path foto rak di storage     |
| `created_at`| `timestamp`       | —                                       |
| `updated_at`| `timestamp`       | —                                       |

**Relasi:** Direferensikan oleh `master_barang.Rack_ID`, `inbound_details.Rack_ID`, `outbound_details.Rack_ID`.

---

### 1.7 `master_barang`
Tabel master data barang/produk.

| Kolom        | Tipe Data      | Keterangan                                        |
|--------------|----------------|---------------------------------------------------|
| `SKU`        | `varchar(50)`  | Primary Key — kode unik barang                    |
| `Nama`       | `varchar(255)` | Nama lengkap barang                               |
| `Kategori`   | `varchar(100)` | Kategori/jenis barang; indexed                    |
| `Min_Stok`   | `int unsigned` | Default: `0` — batas minimum stok reorder        |
| `Barcode_ID` | `varchar(100)` | Nullable, Unique — ID barcode scanning fisik      |
| `Rack_ID`    | `uuid`         | Nullable — FK ke `rack_locations.Rack_ID`; indexed|
| `created_at` | `timestamp`    | —                                                 |
| `updated_at` | `timestamp`    | —                                                 |

**Relasi:**
- `Rack_ID` → `rack_locations.Rack_ID` (onDelete: set null)
- Direferensikan oleh `inbound_details.SKU`, `outbound_details.SKU`, `stock_opnames.SKU`.

---

### 1.8 `suppliers`
Tabel master data supplier/pemasok.

| Kolom         | Tipe Data      | Keterangan                  |
|---------------|----------------|-----------------------------|
| `Supplier_ID` | `uuid`         | Primary Key                 |
| `Nama`        | `varchar(255)` | Nama supplier; indexed      |
| `Kontak`      | `varchar(255)` | Nullable — kontak ringkas   |
| `No_Kontak`   | `varchar(100)` | Nullable — nomor telepon    |
| `Email`       | `varchar(150)` | Nullable                    |
| `Alamat`      | `text`         | Nullable                    |
| `created_at`  | `timestamp`    | —                           |
| `updated_at`  | `timestamp`    | —                           |

**Relasi:** Direferensikan oleh `inbound_transactions.Supplier_ID`.

---

### 1.9 `customers`
Tabel master data customer/pelanggan.

| Kolom         | Tipe Data      | Keterangan                  |
|---------------|----------------|-----------------------------|
| `Customer_ID` | `uuid`         | Primary Key                 |
| `Nama`        | `varchar(255)` | Nama customer; indexed      |
| `Kontak`      | `varchar(255)` | Nullable — kontak ringkas   |
| `No_Kontak`   | `varchar(100)` | Nullable — nomor telepon    |
| `Email`       | `varchar(150)` | Nullable                    |
| `Alamat`      | `text`         | Nullable                    |
| `created_at`  | `timestamp`    | —                           |
| `updated_at`  | `timestamp`    | —                           |

**Relasi:** Direferensikan oleh `outbound_transactions.Customer_ID`.

---

### 1.10 `inbound_transactions`
Tabel header transaksi penerimaan barang (inbound).

| Kolom          | Tipe Data      | Keterangan                                          |
|----------------|----------------|-----------------------------------------------------|
| `Inbound_ID`   | `uuid`         | Primary Key                                         |
| `No_Receiving` | `varchar(100)` | Unique — format: `RSI-YYYYMMDD-XXXX`; indexed      |
| `Tanggal`      | `date`         | Tanggal penerimaan; indexed                         |
| `Supplier_ID`  | `uuid`         | FK ke `suppliers.Supplier_ID`; indexed              |
| `User_ID`      | `bigint unsigned`| FK ke `users.id`; indexed                        |
| `Catatan`      | `text`         | Nullable                                            |
| `created_at`   | `timestamp`    | —                                                   |
| `updated_at`   | `timestamp`    | —                                                   |
| `deleted_at`   | `timestamp`    | Nullable — Soft Delete                              |

**Relasi:**
- `Supplier_ID` → `suppliers.Supplier_ID` (onDelete: restrict)
- `User_ID` → `users.id` (onDelete: restrict)
- Memiliki banyak `inbound_details`.

---

### 1.11 `inbound_details`
Tabel detail per-baris transaksi inbound.

| Kolom              | Tipe Data        | Keterangan                                    |
|--------------------|------------------|-----------------------------------------------|
| `Detail_ID`        | `uuid`           | Primary Key                                   |
| `Inbound_ID`       | `uuid`           | FK ke `inbound_transactions.Inbound_ID`; indexed (onDelete: cascade) |
| `SKU`              | `varchar(50)`    | FK ke `master_barang.SKU`; indexed            |
| `Rack_ID`          | `uuid`           | FK ke `rack_locations.Rack_ID`; indexed       |
| `Qty`              | `int unsigned`   | Jumlah unit barang diterima                   |
| `No_Resi_Supplier` | `varchar(150)`   | Nullable — nomor resi dari supplier           |
| `Batch`            | `varchar(100)`   | Nullable — nomor batch/lot barang             |
| `created_at`       | `timestamp`      | —                                             |
| `updated_at`       | `timestamp`      | —                                             |
| `deleted_at`       | `timestamp`      | Nullable — Soft Delete                        |

**Relasi:**
- `Inbound_ID` → `inbound_transactions.Inbound_ID` (onDelete: cascade)
- `SKU` → `master_barang.SKU` (onDelete: restrict)
- `Rack_ID` → `rack_locations.Rack_ID` (onDelete: restrict)

---

### 1.12 `outbound_transactions`
Tabel header transaksi pengiriman barang (outbound).

| Kolom           | Tipe Data          | Keterangan                                                    |
|-----------------|--------------------|---------------------------------------------------------------|
| `Outbound_ID`   | `uuid`             | Primary Key                                                   |
| `No_Shipping`   | `varchar(100)`     | Unique — nomor dokumen pengiriman                             |
| `Tanggal`       | `date`             | Tanggal pengiriman; indexed                                   |
| `Customer_ID`   | `uuid`             | FK ke `customers.Customer_ID`; indexed                        |
| `No_Surat_Jalan`| `varchar(100)`     | Nullable — nomor surat jalan fisik                            |
| `User_ID`       | `bigint unsigned`  | FK ke `users.id`; indexed                                     |
| `picking_status`| `varchar(20)`      | Default: `'not_complete'` — nilai: `not_complete`, `complete`; indexed |
| `priority`      | `varchar(10)`      | Default: `'decent'` — nilai: `high`, `normal`, `decent`; indexed      |
| `Nama_Penerima` | `varchar(255)`     | Nullable — nama kurir/penerima                                |
| `Catatan`       | `text`             | Nullable                                                      |
| `created_at`    | `timestamp`        | —                                                             |
| `updated_at`    | `timestamp`        | —                                                             |
| `deleted_at`    | `timestamp`        | Nullable — Soft Delete                                        |

**Relasi:**
- `Customer_ID` → `customers.Customer_ID` (onDelete: restrict)
- `User_ID` → `users.id` (onDelete: restrict)
- Memiliki banyak `outbound_details`.

---

### 1.13 `outbound_details`
Tabel detail per-baris transaksi outbound.

| Kolom        | Tipe Data       | Keterangan                                                          |
|--------------|-----------------|---------------------------------------------------------------------|
| `Detail_ID`  | `uuid`          | Primary Key                                                         |
| `Outbound_ID`| `uuid`          | FK ke `outbound_transactions.Outbound_ID`; indexed (onDelete: cascade) |
| `SKU`        | `varchar(50)`   | FK ke `master_barang.SKU`; indexed                                  |
| `Rack_ID`    | `uuid`          | FK ke `rack_locations.Rack_ID`; indexed — rak sumber pengambilan    |
| `Qty`        | `int unsigned`  | Jumlah unit barang dikirim                                          |
| `created_at` | `timestamp`     | —                                                                   |
| `updated_at` | `timestamp`     | —                                                                   |
| `deleted_at` | `timestamp`     | Nullable — Soft Delete                                              |

**Relasi:**
- `Outbound_ID` → `outbound_transactions.Outbound_ID` (onDelete: cascade)
- `SKU` → `master_barang.SKU` (onDelete: restrict)
- `Rack_ID` → `rack_locations.Rack_ID` (onDelete: restrict)

---

### 1.14 `stock_opnames`
Tabel pencatatan kondisi fisik barang hasil pemeriksaan lapangan.

| Kolom      | Tipe Data         | Keterangan                                     |
|------------|-------------------|------------------------------------------------|
| `Opname_ID`| `uuid`            | Primary Key                                    |
| `SKU`      | `varchar(50)`     | FK ke `master_barang.SKU`; indexed (onDelete: cascade) |
| `User_ID`  | `bigint unsigned` | FK ke `users.id`; indexed                      |
| `Tanggal`  | `date`            | Tanggal pemeriksaan; composite index (SKU, Tanggal) |
| `Kondisi`  | `text`            | Deskripsi kondisi fisik barang                 |
| `created_at`| `timestamp`      | —                                              |
| `updated_at`| `timestamp`      | —                                              |

> Catatan: Tabel ini hanya mencatat deskripsi kondisi — **tidak** mengubah nilai stok.

---

### 1.15 `activity_logs`
Tabel log aktivitas pengguna di sistem.

| Kolom           | Tipe Data         | Keterangan                                      |
|-----------------|-------------------|-------------------------------------------------|
| `id`            | `uuid`            | Primary Key (diubah dari bigint ke UUID via migration) |
| `user_id`       | `bigint unsigned` | Nullable — FK ke `users.id` (nullOnDelete)      |
| `operator_name` | `varchar(255)`    | Nama operator yang melakukan aksi               |
| `action`        | `text`            | Deskripsi aksi yang dilakukan                   |
| `created_at`    | `timestamp`       | —                                               |
| `updated_at`    | `timestamp`       | —                                               |

---

## 2. Diagram Relasi Ringkas

```
users ──────────────────────────────────────┐
  │                                          │
  ├─(User_ID)──→ inbound_transactions        │
  │                  │                       │
  │                  └─(Inbound_ID)──→ inbound_details ──→ rack_locations
  │                                          │                     ↑
  ├─(User_ID)──→ outbound_transactions       │                     │
  │                  │                       └──(Rack_ID)──────────┘
  │                  └─(Outbound_ID)→ outbound_details ──→ master_barang ──→ rack_locations
  │
  ├─(User_ID)──→ stock_opnames ──→ master_barang
  │
  └─(user_id)──→ activity_logs

suppliers ──(Supplier_ID)──→ inbound_transactions
customers ──(Customer_ID)──→ outbound_transactions
```

---

## 3. Daftar Endpoint (Web Routes)

> Semua route di bawah ini memerlukan autentikasi sesi (`auth` middleware), kecuali rute guest.
> Role `admin` = Guru; Role `user` = Operator/Siswa.

### 3.1 Authentication

| Method | URL                    | Nama Route               | Akses  | Fungsi                                 |
|--------|------------------------|--------------------------|--------|----------------------------------------|
| GET    | `/login`               | `login`                  | Guest  | Tampilkan halaman form login           |
| POST   | `/login`               | `login.store`            | Guest  | Proses autentikasi & buat sesi         |
| POST   | `/logout`              | `logout`                 | Auth   | Hapus sesi & logout pengguna           |

---

### 3.2 Student Identity

| Method | URL                        | Nama Route                   | Akses | Fungsi                               |
|--------|----------------------------|------------------------------|-------|--------------------------------------|
| POST   | `/student-identity`        | `student-identity.store`     | Auth  | Simpan identitas siswa baru          |
| POST   | `/student-identity/reset`  | `student-identity.reset`     | Auth  | Reset / hapus identitas siswa        |

---

### 3.3 Dashboard

| Method | URL          | Nama Route  | Akses      | Fungsi                                          |
|--------|--------------|-------------|------------|-------------------------------------------------|
| GET    | `/`          | —           | Auth       | Redirect ke dashboard                           |
| GET    | `/dashboard` | `dashboard` | Auth       | Tampilkan ringkasan statistik & data dashboard  |

---

### 3.4 Master Data — Lokasi Rak (`/master-data/rak`)

| Method | URL                                   | Nama Route             | Akses      | Fungsi                                  |
|--------|---------------------------------------|------------------------|------------|-----------------------------------------|
| GET    | `/master-data/rak`                    | `master.rak.index`     | Auth       | Daftar semua lokasi rak                 |
| GET    | `/master-data/rak/{id}`               | `master.rak.show`      | Auth       | Detail satu lokasi rak                  |
| POST   | `/master-data/rak/{id}/pindah-barang` | `master.rak.pindah-barang` | Auth   | Pindahkan barang antar rak              |
| POST   | `/master-data/rak`                    | `master.rak.store`     | Admin only | Tambah lokasi rak baru                  |
| PUT    | `/master-data/rak/{id}`               | `master.rak.update`    | Admin only | Update data lokasi rak                  |
| DELETE | `/master-data/rak/{id}`               | `master.rak.destroy`   | Admin only | Hapus lokasi rak                        |
| POST   | `/master-data/rak/{id}/upload-foto`   | `master.rak.upload-foto`| Admin only| Upload foto rak                         |

---

### 3.5 Master Data — Supplier (`/master-data/supplier`)

| Method | URL                              | Nama Route               | Akses      | Fungsi                   |
|--------|----------------------------------|--------------------------|------------|--------------------------|
| GET    | `/master-data/supplier`          | `master.supplier.index`  | Auth       | Daftar semua supplier    |
| PUT    | `/master-data/supplier/{id}`     | `master.supplier.update` | Admin only | Update data supplier     |

---

### 3.6 Master Data — Customer (`/master-data/customer`)

| Method | URL                              | Nama Route               | Akses      | Fungsi                   |
|--------|----------------------------------|--------------------------|------------|--------------------------|
| GET    | `/master-data/customer`          | `master.customer.index`  | Auth       | Daftar semua customer    |
| PUT    | `/master-data/customer/{id}`     | `master.customer.update` | Admin only | Update data customer     |

---

### 3.7 Master Data — Barang (`/master-data/barang`)

| Method | URL                                   | Nama Route               | Akses | Fungsi                                  |
|--------|---------------------------------------|--------------------------|-------|-----------------------------------------|
| GET    | `/master-data/barang`                 | `master.barang.index`    | Auth  | Daftar semua barang                     |
| GET    | `/master-data/barang/{sku}`           | `master.barang.show`     | Auth  | Detail satu barang berdasarkan SKU      |
| GET    | `/master-data/barang/{sku}/label-pdf` | `master.barang.label-pdf`| Auth  | Download label barang dalam format PDF  |

---

### 3.8 Transaksi Inbound (`/inbound`)

| Method | URL                          | Nama Route              | Akses | Fungsi                                        |
|--------|------------------------------|-------------------------|-------|-----------------------------------------------|
| GET    | `/inbound`                   | `inbound.index`         | Auth  | Daftar semua transaksi inbound                |
| GET    | `/inbound/create`            | `inbound.create`        | Auth  | Form buat transaksi inbound baru              |
| POST   | `/inbound`                   | `inbound.store`         | Auth  | Simpan transaksi inbound baru                 |
| POST   | `/inbound/supplier-ajax`     | `inbound.supplier.ajax` | Auth  | Tambah supplier baru via AJAX                 |
| GET    | `/inbound/{id}`              | `inbound.show`          | Auth  | Detail satu transaksi inbound                 |

---

### 3.9 Transaksi Outbound (`/outbound`)

| Method | URL                               | Nama Route                  | Akses | Fungsi                                        |
|--------|-----------------------------------|-----------------------------|-------|-----------------------------------------------|
| GET    | `/outbound`                       | `outbound.index`            | Auth  | Daftar semua transaksi outbound               |
| GET    | `/outbound/create`                | `outbound.create`           | Auth  | Form buat transaksi outbound baru             |
| POST   | `/outbound`                       | `outbound.store`            | Auth  | Simpan transaksi outbound baru                |
| POST   | `/outbound/customer-ajax`         | `outbound.customer.ajax`    | Auth  | Tambah customer baru via AJAX                 |
| GET    | `/outbound/{id}`                  | `outbound.show`             | Auth  | Detail satu transaksi outbound                |
| GET    | `/outbound/{id}/picking-list`     | `outbound.picking-list`     | Auth  | Tampilkan picking list untuk operator gudang  |
| POST   | `/outbound/{id}/picking-complete` | `outbound.picking-complete` | Auth  | Tandai picking list sebagai selesai           |
| GET    | `/outbound/{id}/surat-jalan`      | `outbound.surat-jalan`      | Auth  | Download surat jalan dalam format PDF         |

---

### 3.10 Activity Log (`/logs`)

| Method | URL     | Nama Route    | Akses      | Fungsi                                |
|--------|---------|---------------|------------|---------------------------------------|
| GET    | `/logs` | `logs.index`  | Admin only | Daftar seluruh log aktivitas sistem   |

---

### 3.11 Inventory — Kartu Stok (`/inventory/kartu-stok`)

| Method | URL                                  | Nama Route                      | Akses | Fungsi                                |
|--------|--------------------------------------|---------------------------------|-------|---------------------------------------|
| GET    | `/inventory/kartu-stok`              | `inventory.kartu-stok.index`    | Auth  | Daftar ringkasan stok semua barang    |
| GET    | `/inventory/kartu-stok/{sku}`        | `inventory.kartu-stok.detail`   | Auth  | Riwayat mutasi stok per-SKU           |

---

### 3.12 Inventory — Stock Opname (`/inventory/stock-opname`)

| Method | URL                            | Nama Route                        | Akses | Fungsi                                  |
|--------|--------------------------------|-----------------------------------|-------|-----------------------------------------|
| GET    | `/inventory/stock-opname`      | `inventory.stock-opname.index`    | Auth  | Daftar semua record stock opname        |
| GET    | `/inventory/stock-opname/create` | `inventory.stock-opname.create` | Auth  | Form tambah stock opname baru           |
| POST   | `/inventory/stock-opname`      | `inventory.stock-opname.store`    | Auth  | Simpan record stock opname baru         |

---

### 3.13 Laporan & Export (`/laporan`)

| Method | URL                           | Nama Route                   | Akses | Fungsi                                        |
|--------|-------------------------------|------------------------------|-------|-----------------------------------------------|
| GET    | `/laporan`                    | `laporan.index`              | Auth  | Halaman laporan — pilih jenis export          |
| GET    | `/laporan/inventori/export`   | `laporan.inventori.export`   | Auth  | Export data inventori ke file Excel           |
| GET    | `/laporan/inbound/export`     | `laporan.inbound.export`     | Auth  | Export data transaksi inbound ke file Excel   |
| GET    | `/laporan/outbound/export`    | `laporan.outbound.export`    | Auth  | Export data transaksi outbound ke file Excel  |

---

## 4. Ringkasan Middleware & Akses

| Middleware             | Keterangan                                                               |
|------------------------|--------------------------------------------------------------------------|
| `guest`                | Hanya untuk pengguna yang belum login (halaman login)                    |
| `auth`                 | Wajib login (session-based Laravel)                                      |
| `student.identity`     | Cek bahwa pengguna sudah mengisi identitas siswa                         |
| `role:admin`           | Pembatasan akses hanya untuk role `admin` (Guru)                         |

---

## 5. Catatan Teknis

- **Database:** PostgreSQL (terlihat dari penggunaan `pgcrypto` extension untuk UUID di migration activity_logs).
- **Primary Key:** Mayoritas tabel bisnis menggunakan UUID (`uuid()`). Tabel bawaan Laravel (`users`, `jobs`, `failed_jobs`) menggunakan `bigint` auto-increment.
- **Soft Delete:** Digunakan pada `inbound_transactions`, `inbound_details`, `outbound_transactions`, `outbound_details` — data tidak dihapus permanen.
- **Auth:** Session-based (bukan API token/JWT). Tidak ada `routes/api.php`.
- **Role:** Dua level — `admin` (Guru, akses penuh) dan `user` (Operator/Siswa, akses terbatas).
- **Export:** Menggunakan library Laravel Excel (lihat folder `app/Exports/`).
