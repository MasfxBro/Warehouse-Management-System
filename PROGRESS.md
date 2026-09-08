# Status Finalisasi Demo WMS Sekolah

Update: 8 September 2026

## Status

Demo WMS sudah berada pada tahap **matang untuk simulasi kegiatan belajar mengajar**. Pekerjaan deployment production—server, domain, HTTPS, backup terjadwal, kredensial final, monitoring, dan hardening environment—sengaja belum dilakukan.

## Workflow yang selesai

- Login Guru/Admin dan akun Siswa dengan identitas operator per sesi browser.
- Sesi praktikum yang dapat dibuka/ditutup Guru; sesi tidak dapat ditutup jika picking masih tertunda.
- Inbound multi-item untuk barang lama/baru, Qty aktual, kapasitas rak, supplier, satuan fleksibel, serta harga dasar yang dikunci sejak penerimaan pertama.
- Outbound multi-item dengan reservasi stok, alokasi berdasarkan saldo rak aktual, picking list, completion, dan surat jalan PDF.
- Pembatalan inbound/outbound yang mempertahankan nomor dokumen, detail audit, alasan, waktu, dan pelaku.
- Pemisahan stok fisik, reservasi, dan tersedia pada dashboard, master barang, kartu stok, detail rak, serta export inventori.
- Perpindahan barang antar-rak hanya untuk stok tersedia dan tidak dapat melewati kapasitas.
- Stock opname berupa catatan pemeriksaan kondisi fisik, activity log, label QR, dan export Excel.
- Nomor RSI/SJ berurutan per tanggal dengan counter database.
- Seeder deterministik serta audit integritas stok, lokasi, kapasitas, dan harga.

## Verifikasi terakhir

- `php artisan test`: **25 test, 243 assertion lulus**.
- `npm run build`: lulus.
- `composer validate --no-check-publish`: lulus.
- `php vendor/bin/pint --test app database routes tests bootstrap/app.php bootstrap/providers.php config`: lulus.
- Cache route, view, dan config: dapat dibuat tanpa error.
- `php artisan wms:audit` pada database demo aktif: lulus tanpa masalah.
- Test memiliki guard yang menolak berjalan jika koneksi bukan SQLite in-memory.

## Aturan yang tidak boleh dilanggar

1. Jangan menambah atau memperbarui kolom stok manual; gunakan kalkulasi relasi pada model/service.
2. Stok tersedia memperhitungkan seluruh outbound aktif, sedangkan stok fisik hanya dikurangi outbound yang picking-nya selesai.
3. Harga barang lama selalu memakai `Harga_Dasar`; harga request untuk barang lama diabaikan oleh server.
4. Jangan hard-delete transaksi koreksi. Gunakan fitur pembatalan agar jejak audit tetap ada.
5. Jangan mencetak surat jalan sebelum picking selesai.
6. Jangan menjalankan `migrate:fresh` pada database yang perlu dipertahankan.

## Perintah utama

```bash
php artisan test
npm run build
php artisan wms:audit
php artisan migrate:status
```

Reset dataset latihan hanya untuk lokal/testing:

```bash
php artisan wms:demo-reset --force
```

## Batas tahap demo

Hal-hal berikut masuk fase production, bukan kekurangan workflow demo: penyediaan server/domain, TLS/HTTPS, database production, rotasi kredensial, backup dan restore drill, observability, email, serta kebijakan retensi data sekolah.
