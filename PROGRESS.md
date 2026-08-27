# WMS Prototipe 2 — Dokumen Progress & Kontinuitas

> Dokumen ini mencatat status implementasi lengkap proyek WMS agar bisa dilanjutkan kapan saja.
> Update terakhir: Phase 5 + Polish selesai.

---

## ✅ STATUS KESELURUHAN

| Phase | Nama | Status |
|---|---|---|
| Phase 1 | Auth, Middleware, Base Layout | ✅ SELESAI |
| Phase 2 | Dashboard Interactive & Master Data | ✅ SELESAI |
| Phase 3 | Workflow Transaksi Inbound Kompleks | ✅ SELESAI |
| Phase 4 | Outbound Workflow, Picking List, Surat Jalan PDF | ✅ SELESAI |
| Phase 5 | Inventory, Stock Opname, Laporan Excel, Polish | ✅ SELESAI |
| Polish  | Redesign UI, Design System, Font Awesome Icons | ✅ SELESAI |

**Test Suite:** 11/11 ✅ | **Build:** `npm run build` ✅

---

## 🗂️ PETA FILE LENGKAP

### Controllers (`app/Http/Controllers/`)
| File | Status | Keterangan |
|---|---|---|
| `AuthController.php` | ✅ | Login/Logout |
| `DashboardController.php` | ✅ | Stat cards + chart data |
| `RackLocationController.php` | ✅ | CRUD Admin, RO Siswa |
| `SupplierController.php` | ✅ | Pure Read-Only |
| `CustomerController.php` | ✅ | Pure Read-Only |
| `MasterBarangController.php` | ✅ | Read-Only + Detail QR (Admin) |
| `InboundController.php` | ✅ | CRUD + Supplier AJAX + RSI generator |
| `OutboundController.php` | ✅ | CRUD + Customer AJAX + Picking + Surat Jalan PDF |
| `InventoryController.php` | ✅ | Kartu Stok index + detail timeline mutasi |
| `StockOpnameController.php` | ✅ | Full CRUD catatan kondisi fisik |
| `LaporanController.php` | ✅ | Export Excel (Inventori, Inbound, Outbound) |
| `ActivityLogController.php` | ✅ | Read-Only log admin |
| `StudentIdentityController.php` | ✅ | Identitas siswa per sesi |

### Models (`app/Models/`)
| File | Status | Catatan Penting |
|---|---|---|
| `User.php` | ✅ | Role cast ke Enum UserRole |
| `MasterBarang.php` | ✅ | **Stok via accessor (BUKAN kolom DB)** |
| `RackLocation.php` | ✅ | kapasitas_terpakai & status_kapasitas accessor |
| `Supplier.php` | ✅ | Title Case mutator via Trait |
| `Customer.php` | ✅ | Title Case mutator via Trait |
| `InboundTransaction.php` | ✅ | fillable: Catatan, accessor: No_Resi |
| `InboundDetail.php` | ✅ | fillable: No_Resi_Supplier |
| `OutboundTransaction.php` | ✅ | picking_status, priority, Nama_Penerima, Catatan |
| `OutboundDetail.php` | ✅ | FK ke master_barang dan rack_locations |
| `StockOpname.php` | ✅ | FK ke master_barang + users |
| `ActivityLog.php` | ✅ | `ActivityLog::record()` static helper |

### Views (`resources/views/`)
| Path | Status |
|---|---|
| `layouts/app.blade.php` | ✅ Redesigned — FA Icons, sidebar lengkap |
| `auth/login.blade.php` | ✅ Redesigned — clean card layout |
| `dashboard.blade.php` | ✅ Redesigned — stat cards, chart, picking queue, alerts |
| `master/barang/index.blade.php` | ✅ Redesigned |
| `master/barang/show.blade.php` | ✅ Redesigned — QR admin only |
| `master/rak/index.blade.php` | ✅ Redesigned — modal CRUD admin |
| `master/supplier/index.blade.php` | ✅ Redesigned — read-only |
| `master/customer/index.blade.php` | ✅ Redesigned — read-only |
| `inbound/index.blade.php` | ✅ Redesigned |
| `inbound/create.blade.php` | ✅ Redesigned — multi-item, toggle lama/baru, SKU engine, modal supplier |
| `inbound/show.blade.php` | ✅ Redesigned |
| `outbound/index.blade.php` | ✅ Redesigned — 2 tabel: queue + riwayat |
| `outbound/create.blade.php` | ✅ Redesigned — multi-item, stok badge, modal customer |
| `outbound/show.blade.php` | ✅ Redesigned — CTA berubah sesuai status picking |
| `outbound/picking-list.blade.php` | ✅ Redesigned — checkbox per baris, Mark as Complete |
| `outbound/surat-jalan-pdf.blade.php` | ✅ Format PDF dompdf |
| `inventory/kartu-stok.blade.php` | ✅ Redesigned — live search |
| `inventory/kartu-stok-detail.blade.php` | ✅ Redesigned — timeline mutasi + running saldo |
| `inventory/stock-opname.blade.php` | ✅ Redesigned |
| `inventory/stock-opname-create.blade.php` | ✅ Redesigned |
| `inventory/stock-opname-edit.blade.php` | ✅ Redesigned |
| `laporan/index.blade.php` | ✅ Redesigned — 3 card export |
| `logs/index.blade.php` | ✅ Redesigned |

### CSS & Assets
| File | Status |
|---|---|
| `resources/css/app.css` | ✅ Design System lengkap (pure CSS, no @apply custom classes) |
| `resources/js/app.js` | ✅ Bootstrap import |
| Font Awesome 6.5.2 | ✅ Via CDN di `layouts/app.blade.php` |
| Inter + JetBrains Mono | ✅ Via Google Fonts di `app.css` |

### Migrations
| Migration | Status |
|---|---|
| `create_rack_locations_table` | ✅ |
| `create_master_barang_table` | ✅ |
| `create_suppliers_table` | ✅ |
| `create_customers_table` | ✅ |
| `create_inbound_transactions_table` | ✅ |
| `create_inbound_details_table` | ✅ |
| `create_outbound_transactions_table` | ✅ |
| `create_outbound_details_table` | ✅ |
| `create_activity_logs_table` | ✅ |
| `add_phase4_columns_to_outbound_transactions_table` | ✅ picking_status, priority, Nama_Penerima, Catatan |
| `create_stock_opnames_table` | ✅ |

### Tests (`tests/Feature/`)
| File | Status | Jumlah Test |
|---|---|---|
| `AuthTest.php` | ✅ 2/2 | Login admin + siswa + identity |
| `MasterDataTest.php` | ✅ 3/3 | Title case, rak CRUD, barang detail |
| `InboundTest.php` | ✅ 4/4 | RSI format, barang baru, barang lama, supplier AJAX |

---

## 🔑 PANTANGAN WAJIB (Jangan Dilanggar)

1. **JANGAN** gunakan kolom `Stok` langsung di DB. Selalu gunakan `$item->stok` (accessor).
2. **JANGAN** ubah `phpunit.xml` — sudah dikonfigurasi SQLite in-memory untuk testing.
3. **JANGAN** tambah `no_surat_jalan` di Outbound — sudah digantikan `No_Shipping` (SJ-YYYYMMDD-XXXX).
4. **JANGAN** cetak Surat Jalan jika `picking_status !== 'complete'` — ada gatekeeping 403.
5. **JANGAN** reset project dari nol — gunakan `php artisan migrate:fresh --seed`.

---

## 🚀 PERINTAH OPERASIONAL

```bash
# Reset & seed database production (PostgreSQL)
php artisan migrate:fresh --seed

# Jalankan test suite (SQLite in-memory, tidak merusak DB)
php artisan test

# Build frontend assets
npm run build

# Dev server (run manually di terminal)
npm run dev

# Verifikasi login
php artisan tinker --execute="use Illuminate\Support\Facades\Auth; echo Auth::attempt(['email'=>'admin@wms.local','password'=>'password']) ? 'Admin: OK' : 'Admin: FAIL';"
```

---

## 🎨 DESIGN SYSTEM RINGKASAN

| Token | Nilai |
|---|---|
| Background | `#f7f9fb` |
| Card | `#ffffff`, border `#e2e8f0`, `rounded-xl` |
| Primary (CTA) | `#0058be` (blue) |
| Success/Inbound | `#10b981` (emerald) |
| Danger text | `#93000a`, bg `#ffdad6` |
| Warning text | `#92400e`, bg `#fef3c7` |
| Font Sans | Inter |
| Font Mono | JetBrains Mono (SKU, angka, kode) |
| Icons | Font Awesome 6 Free (CDN) |
| Sidebar | Fixed 240px, active bar `3px #0058be` kiri |

**CSS Classes tersedia di `app.css`:**
- Cards: `.wms-card`, `.wms-card-header`, `.wms-card-title`
- Tables: `.wms-table`
- Badges: `.badge`, `.badge-success`, `.badge-warning`, `.badge-danger`, `.badge-info`, `.badge-neutral`, `.badge-high`, `.badge-normal`, `.badge-decent`
- Buttons: `.btn`, `.btn-primary`, `.btn-success`, `.btn-outline`, `.btn-danger`, `.btn-ghost`, `.btn-sm`, `.btn-lg`
- Forms: `.wms-input`, `.wms-select`, `.wms-textarea`, `.wms-label`
- Flash: `.alert-success`, `.alert-error`, `.alert-info`, `.alert-warning`
- Stats: `.stat-card`, `.stat-card-icon`, `.stat-card-label`, `.stat-card-value`, `.stat-card-sub`
- Modals: `.modal-overlay`, `.modal-box`, `.modal-header`, `.modal-title`, `.modal-body`, `.modal-footer`

---

## 📦 PACKAGES TERINSTALL

| Package | Versi | Fungsi |
|---|---|---|
| `barryvdh/laravel-dompdf` | latest | Generate PDF Surat Jalan |
| `phpoffice/phpspreadsheet` | ^5.9 | Export Excel (support PHP 8.5) |

> ⚠️ `maatwebsite/excel` TIDAK kompatibel dengan PHP 8.5 — gunakan `phpoffice/phpspreadsheet` langsung.

---

## 🔐 KREDENSIAL DEFAULT

| Role | Email | Username | Password |
|---|---|---|---|
| Guru (Admin) | `admin@wms.local` | `admin` | `password` |
| Siswa (Operator) | `siswa@wms.local` | `siswa` | `password` |

---

## 📋 JIKA ADA YANG PERLU DITAMBAHKAN SELANJUTNYA

Semua Phase dari ArahanWMS.md sudah selesai. Yang bisa dikerjakan sebagai improvement:
- [ ] Notifikasi real-time (Laravel Echo / Pusher) untuk low stock alert
- [ ] Fitur cetak label QR langsung dari daftar barang (batch print)
- [ ] Laporan Stock Opname export Excel
- [ ] Filter tanggal pada halaman Inbound index
- [ ] Pagination pada halaman Outbound picking queue
- [ ] Dark mode toggle
