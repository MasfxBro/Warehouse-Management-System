# Warehouse Management System Sekolah

WMS berbasis Laravel untuk kegiatan belajar mengajar Jurusan Manajemen Logistik. Aplikasi mensimulasikan operasional gudang dari penerimaan, penyimpanan, perpindahan rak, picking, pengiriman, stock opname, sampai pelaporan.

## Modul

- Autentikasi Guru/Admin dan akun bersama Siswa/Operator.
- Identitas siswa per sesi untuk jejak audit praktikum.
- Sesi praktikum per kelas/periode yang dibuka dan ditutup Guru/Admin.
- Master barang, lokasi rak, supplier, dan customer.
- Inbound multi-item, barang baru/lama, satuan dasar, harga pembelian per penerimaan, kapasitas rak, dan nomor receiving otomatis.
- Outbound multi-item dengan alokasi stok sesuai saldo aktual setiap rak.
- Picking list, prioritas otomatis, dan surat jalan PDF.
- Pembatalan inbound/outbound yang aman tanpa menghapus nomor dokumen dan jejak audit.
- Kartu stok, distribusi stok per rak, stock opname kondisi fisik, dan activity log.
- Export laporan inventori, inbound, dan outbound ke Excel.
- Label barang dengan QR code.

## Aturan bisnis penting

- Stok tidak disimpan sebagai angka manual. Sistem menghitung tiga saldo: **fisik** = inbound − outbound selesai, **reservasi** = outbound yang belum selesai picking, dan **tersedia** = fisik − reservasi.
- Satuan dasar melekat pada SKU. Harga dasar ditetapkan saat inbound pertama dan dikunci untuk seluruh penerimaan berikutnya dari SKU yang sama.
- Harga disimpan sebagai snapshot pada detail inbound dan subtotal dihitung dari Qty dikali harga dasar.
- Satuan dapat dipilih dari katalog, diketik langsung, atau ditambahkan melalui modal pada form inbound barang baru; penulisannya dinormalisasi untuk mencegah duplikasi.
- Total stok sebuah SKU harus selalu sama dengan jumlah saldo SKU tersebut pada seluruh rak.
- Outbound dialokasikan dari rak yang benar-benar memiliki saldo; satu permintaan dapat dibagi ke beberapa rak.
- Nomor RSI dan SJ berurutan per jenis dokumen dan tanggal melalui counter database yang aman untuk penggunaan bersamaan.
- Inbound hanya dapat dibatalkan jika stoknya belum dipakai; pembatalan outbound melepas reservasi atau mengembalikan saldo transaksi selesai.
- Sesi praktikum tidak dapat ditutup selama masih ada picking yang tertunda.
- Stock opname hanya mencatat kondisi fisik dan tidak mengubah jumlah stok.
- Surat jalan hanya dapat dibuat setelah picking selesai.
- Siswa harus mengisi nama, kelas, dan NIS sebelum dapat mengubah data.

## Kebutuhan sistem

- PHP 8.2 atau lebih baru beserta extension yang dibutuhkan Laravel, GD, dan Zip.
- PostgreSQL untuk development, staging, dan production.
- Composer 2.
- Node.js dan npm.
- Web server yang mengarah ke direktori `public`, bukan root repository.

## Instalasi development

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Sesuaikan koneksi PostgreSQL di `.env`. Jangan pernah menjalankan `migrate:fresh` pada database yang berisi data sekolah.

Untuk mengulang dataset latihan lokal secara deterministik, gunakan perintah khusus berikut. Perintah ini ditolak di environment selain `local`/`testing` dan tetap memerlukan flag eksplisit:

```bash
php artisan wms:demo-reset --force
```

## Verifikasi

```bash
php artisan test
npm run build
composer validate --no-check-publish
php artisan migrate:status
php artisan wms:audit
php vendor/bin/pint --test app database routes tests bootstrap/app.php bootstrap/providers.php config
```

Test menggunakan SQLite in-memory melalui `phpunit.xml`; database PostgreSQL development tidak dihapus oleh test.
Test juga memiliki pengaman fail-fast dan akan dibatalkan jika aplikasi tidak benar-benar memakai SQLite `:memory:`. Jalankan `php artisan optimize:clear` sebelum test jika config pernah dicache.

## Deployment production

1. Siapkan domain HTTPS, PostgreSQL, backup otomatis, dan user database dengan hak minimum.
2. Gunakan `APP_ENV=production`, `APP_DEBUG=false`, `APP_TIMEZONE=Asia/Jakarta`, URL HTTPS, serta password unik.
3. Jalankan `composer install --no-dev --optimize-autoloader` dan `npm ci && npm run build`.
4. Jalankan `php artisan migrate --force`—jangan gunakan `migrate:fresh`.
5. Jalankan `php artisan storage:link`, lalu pastikan `storage` dan `bootstrap/cache` dapat ditulis web server.
6. Jalankan `php artisan optimize` dan siapkan scheduler/queue worker jika fitur antrean ditambahkan.
7. Uji login, inbound, outbound, picking, PDF, Excel, upload foto, dan restore backup pada staging.
8. Ganti seluruh kredensial contoh sebelum sistem dibuka untuk siswa.

## Operasional sekolah

- Guru bertanggung jawab membuka/menutup periode praktikum dan memverifikasi activity log.
- Selesaikan atau batalkan seluruh picking sebelum menutup sesi praktikum.
- Setiap siswa wajib mereset identitas ketika berganti operator pada perangkat yang sama.
- Backup database dilakukan harian dan sebelum migrasi/deploy.
- Data latihan sebaiknya dipisahkan per kelas atau periode agar transaksi antarkelas tidak bercampur.
- Lakukan restore drill berkala; backup yang belum pernah diuji belum dapat dianggap aman.

## Catatan keamanan

- File `.env`, session, compiled view, log, dan build lokal tidak boleh di-commit.
- Login dibatasi lima percobaan per menit per sumber request.
- Detail exception disimpan di log dan tidak ditampilkan kepada pengguna.
- Activity log menyimpan akun serta identitas siswa yang aktif pada saat aksi terjadi.

## Perintah pemeliharaan demo

- `php artisan wms:audit` — pemeriksaan read-only untuk stok negatif, ketidaksesuaian rak, kapasitas, dan harga.
- `php artisan wms:demo-repair --force` — khusus lokal/testing untuk memperbaiki data demo lama melalui transaksi penyesuaian yang tercatat.
- `php artisan wms:demo-reset --force` — khusus lokal/testing; menghapus database demo, membuat skema ulang, seed deterministik, lalu menjalankan audit.
