# 📱 Panduan Pemula — WMS Flutter + Laravel API

> Panduan ini menjelaskan **seluruh alur kerja** dari database PostgreSQL hingga aplikasi Flutter di HP.  
> Ditulis dengan bahasa yang mudah dipahami, cocok untuk pemula.

---

## 🗺️ Gambaran Besar Sistem

```
HP/Flutter  ←→  Laravel API  ←→  PostgreSQL
(Tampilan)       (Otak/Logic)     (Gudang Data)
```

**Bayangkan seperti ini:**
- **PostgreSQL** = lemari arsip, menyimpan semua data (barang, transaksi, dll.)
- **Laravel** = manager kantor, yang tahu cara membaca & menulis ke lemari arsip, lalu mengemas hasilnya sebagai laporan JSON
- **Flutter** = aplikasi di HP, yang "minta laporan" ke manager dan menampilkannya ke layar

---

## 📊 Alur Kerja Lengkap: Seeder → PostgreSQL → API → Flutter

### 1. Seeder (Mengisi Data Awal)

**Apa itu Seeder?**  
Seeder adalah skrip PHP yang "menanam" data awal ke database. Ibarat kamu memberi benih data agar aplikasi tidak kosong saat pertama dibuka.

```bash
# Jalankan di terminal, di folder Laravel:
php artisan db:seed
```

Data yang ditanam:
- 2 User: `admin@wms.local` (Guru) dan `siswa@wms.local` (Operator)
- Rack Locations (lokasi rak gudang)
- Master Barang (data produk)
- Supplier & Customer
- Contoh transaksi Inbound & Outbound

---

### 2. Tabel PostgreSQL yang Dipakai

| Tabel | Isi | Primary Key |
|-------|-----|-------------|
| `users` | Akun login admin & siswa | `id` (auto) |
| `rack_locations` | Lokasi rak di gudang | `Rack_ID` (auto) |
| `master_barang` | Data master produk/barang | `SKU` (string, misal: `ELK-00001`) |
| `suppliers` | Data pemasok barang | `Supplier_ID` (auto) |
| `customers` | Data pelanggan | `Customer_ID` (auto) |
| `inbound_transactions` | Header transaksi masuk | `Inbound_ID` (auto) |
| `inbound_details` | Detail baris per inbound | `Detail_ID` (auto) |
| `outbound_transactions` | Header transaksi keluar | `Outbound_ID` (auto) |
| `outbound_details` | Detail baris per outbound | `Detail_ID` (auto) |
| `stock_opnames` | Catatan kondisi fisik barang | `Opname_ID` (auto) |
| `activity_logs` | Riwayat aktivitas semua user | `id` (auto) |
| `personal_access_tokens` | Token login Sanctum (API) | `id` (auto) |

**Catatan penting:**
- Stok barang **tidak disimpan** sebagai angka langsung. Stok dihitung otomatis:  
  `Stok = SUM(inbound_details.Qty) - SUM(outbound_details.Qty)`

---

### 3. Laravel API (Jembatan)

**Apa itu REST API?**  
API adalah "pintu" yang bisa dipanggil dari aplikasi manapun (Flutter, web, Postman) menggunakan format JSON.

**Contoh alur login:**
1. Flutter kirim request:
   ```
   POST http://10.0.2.2:8000/api/login
   Body: { "login": "admin", "password": "password" }
   ```
2. Laravel cek ke tabel `users` di PostgreSQL
3. Laravel balas:
   ```json
   {
     "success": true,
     "token": "1|AbCdEf...",
     "user": { "id": 1, "name": "Admin", "role": "admin" }
   }
   ```
4. Flutter simpan `token` di HP (SharedPreferences)
5. Token ini dikirim di setiap request berikutnya:
   ```
   Header: Authorization: Bearer 1|AbCdEf...
   ```

**Daftar Endpoint API yang tersedia:**

| Method | Endpoint | Keterangan |
|--------|----------|-----------|
| POST | `/api/login` | Login, dapat token |
| POST | `/api/logout` | Logout, hapus token |
| GET | `/api/me` | Data user saat ini |
| GET | `/api/dashboard` | Stat cards + chart + picking queue |
| GET | `/api/barang` | Daftar barang (search & filter) |
| GET | `/api/barang/{sku}` | Detail satu barang |
| GET | `/api/inbound` | Daftar transaksi masuk |
| POST | `/api/inbound` | Buat transaksi masuk baru |
| GET | `/api/inbound/{id}` | Detail transaksi masuk |
| GET | `/api/outbound` | Daftar transaksi keluar |
| POST | `/api/outbound` | Buat transaksi keluar baru |
| GET | `/api/outbound/{id}` | Detail transaksi keluar |
| POST | `/api/outbound/{id}/picking-complete` | Selesaikan picking |
| GET | `/api/inventory/kartu-stok` | Kartu stok semua barang |
| GET | `/api/suppliers` | Daftar supplier |
| GET | `/api/customers` | Daftar customer |

---

## 🐦 Penjelasan Kode Flutter

### File Penting & Fungsinya

```
lib/
├── main.dart                  → Entry point, cek login awal
├── main_scaffold.dart         → Shell utama + bottom navigation
├── core/
│   ├── constants.dart         → URL server, kunci penyimpanan
│   └── app_theme.dart         → Warna & tema (sama dengan web)
├── services/
│   ├── api_service.dart       → Semua komunikasi HTTP ke Laravel
│   └── auth_service.dart      → Kelola sesi login di HP
├── widgets/
│   └── common_widgets.dart    → Widget reusable (card, badge, tombol)
└── screens/
    ├── auth/login_screen.dart → Halaman login
    ├── dashboard/...          → Dashboard
    ├── barang/...             → Master data barang
    ├── inbound/...            → Transaksi masuk
    ├── outbound/...           → Transaksi keluar
    ├── inventory/...          → Kartu stok
    ├── supplier/...           → Data supplier
    └── customer/...           → Data customer
```

---

### Penjelasan Fungsi-Fungsi Utama

#### `http.get()` — Ambil data dari server
```dart
// Seperti membuka halaman web, tapi hasilnya JSON bukan HTML
final response = await http.get(
  Uri.parse('http://10.0.2.2:8000/api/barang'),
  headers: {'Authorization': 'Bearer TOKEN_KAMU'},
);
```
- `await` = tunggu sampai server balas (bisa 0.1–3 detik)
- Hasilnya berupa `response.body` berupa teks JSON

#### `http.post()` — Kirim data baru ke server
```dart
// Seperti mengisi form dan menekan Submit di web
final response = await http.post(
  Uri.parse('http://10.0.2.2:8000/api/login'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({'login': 'admin', 'password': 'password'}),
);
```

#### `jsonDecode()` — Ubah teks JSON menjadi Map Dart
```dart
// JSON dari server: '{"success": true, "token": "abc"}'
// Setelah jsonDecode jadi Map yang bisa dipakai:
final data = jsonDecode(response.body);
print(data['token']); // abc
```

#### `FutureBuilder` — Tampilkan UI berdasarkan status loading
```dart
// Pola yang umum di Flutter untuk data dari API:
FutureBuilder(
  future: ApiService().getBarang(),  // Panggil API
  builder: (context, snapshot) {
    if (snapshot.connectionState == ConnectionState.waiting) {
      return CircularProgressIndicator(); // Masih loading
    }
    if (snapshot.hasError) {
      return Text('Error: ${snapshot.error}'); // Ada masalah
    }
    final data = snapshot.data!;
    return Text(data['nama']); // Tampilkan data
  },
)
```

#### `SharedPreferences` — Penyimpanan lokal di HP
```dart
// Simpan token setelah login berhasil
final prefs = await SharedPreferences.getInstance();
await prefs.setString('wms_token', '1|AbCdEf...');

// Baca token
final token = prefs.getString('wms_token');
```
> Bayangkan seperti `localStorage` di browser — data tetap ada meski HP di-restart.

#### `setState()` — Perbarui tampilan
```dart
setState(() {
  _isLoading = false;   // Ubah variabel state
  _items = newData;     // Flutter otomatis re-render UI
});
```

---

## 🚀 Cara Menjalankan Proyek

### Langkah 1: Jalankan Laravel (Backend)

```bash
# Di folder: C:\Users\LENOVO\Warehouse-Management-System
php artisan serve
```

Laravel akan berjalan di: `http://127.0.0.1:8000`

> **Pastikan PostgreSQL juga berjalan** dan file `.env` sudah dikonfigurasi dengan benar.

---

### Langkah 2: Konfigurasi URL di Flutter

Buka file `lib/core/constants.dart`:

```dart
// Pilih sesuai situasimu:

// Jika pakai Emulator Android (AVD):
static const String baseUrl = 'http://10.0.2.2:8000';

// Jika pakai HP Fisik (sambungkan ke WiFi yang sama):
// 1. Di laptop, buka CMD, ketik: ipconfig
// 2. Catat IP Address kamu (contoh: 192.168.1.5)
// 3. Ganti jadi:
static const String baseUrl = 'http://192.168.1.5:8000';

// Jika pakai Genymotion:
static const String baseUrl = 'http://10.0.3.2:8000';
```

---

### Langkah 3: Jalankan Flutter

```bash
# Di folder: C:\Users\LENOVO\WMS_FLUTTER\wms_flutter

# Cek perangkat yang tersedia
flutter devices

# Jalankan di emulator/HP yang terdeteksi
flutter run

# Atau spesifik ke device ID
flutter run -d emulator-5554
```

---

### Langkah 4: Login di Aplikasi

Gunakan kredensial default:

| Role | Username | Password |
|------|----------|----------|
| Guru (Admin) | `admin` | `password` |
| Siswa (Operator) | `siswa` | `password` |

---

## 🔧 Troubleshooting (Mengatasi Masalah Umum)

### ❌ `Connection refused` / Tidak bisa konek
- Pastikan Laravel sudah berjalan (`php artisan serve`)
- Pastikan URL di `constants.dart` sudah benar
- Jika HP Fisik: pastikan HP dan laptop di WiFi yang **sama**
- Jika Emulator: pastikan pakai `10.0.2.2` bukan `localhost`

### ❌ `401 Unauthorized`
- Token sudah expired. Logout dan login ulang di Flutter.

### ❌ `422 Unprocessable Entity`
- Ada validasi yang gagal. Cek pesan error di response.

### ❌ Flutter tidak build / ada error merah
- Jalankan `flutter pub get` untuk install dependencies
- Restart emulator/HP dan coba lagi `flutter run`

### ❌ `No connected devices`
- Pastikan emulator sudah berjalan atau HP sudah terhubung via USB dengan USB Debugging aktif

---

## 📦 Dependencies yang Dipakai

| Package | Kegunaan |
|---------|----------|
| `http` | Kirim request HTTP ke Laravel API |
| `shared_preferences` | Simpan token & data user di HP |
| `intl` | Format angka (Rupiah) dan tanggal |
| `fl_chart` | Chart/grafik untuk dashboard |

---

## 🎨 Skema Warna (Sama dengan Web)

| Nama | Kode Warna | Digunakan untuk |
|------|-----------|----------------|
| Primary (Biru) | `#0058BE` | Tombol utama, link, fokus input |
| Success (Hijau) | `#10B981` | Stok aman, inbound, status OK |
| Danger (Merah) | `#EF4444` | Stok habis, error, hapus |
| Warning (Kuning) | `#F59E0B` | Stok reorder, pending picking |
| Background | `#F7F9FB` | Latar halaman |
| Surface | `#FFFFFF` | Card, input, navbar |
| Border | `#E2E8F0` | Garis pembatas card |

---

*Panduan ini dibuat otomatis untuk proyek WMS Flutter — SMK Logistik 2026*
