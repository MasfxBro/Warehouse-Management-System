Kritikan WMS Prototype 2

Login Page :
1. Sudah aman

Dashboard role admin :

1. Tab Dashboard
- sudah aman

2. Tab Data Barang
- [UNTUK ROLE SISWA/USER DAN GURU/ADMIN YA] saat di detail tiap data barang, untuk data dari Riwayat inbound serta outbound. Itu tampilkan datanya dari dua data terbaru dari transaksi tersebut, agar tampilannya itu ga penuh kebawah, dan supaya tampilan halaman pada detail tiap barangnya agar tidak ada fitur scroll. Ya contohnya biar kamu paham, yaitu jika belum ada Riwayat baru lagi, maka tampilin data dari yang terakhir, jika ada data baru maka update riwayatnya, menjadi dua data terbaru aja, nah biar bisa melihat data fullnya, yaitu dengan adanya tombol untuk navigasi kehalaman kartu stok di halaman timeline masing masing barang ya.
- [UNTUK ROLE SISWA/USER DAN GURU/ADMIN YA] icon pada halaman detail barnag di bagian judul "Riwayat inbound" itu tidak muncul

3. Tab Lokasi Rak
-) [KHUSUS GURU/ADMIN] Pada fitur tombol edit dan hapus, itu diganti menjadi fitur detail yang fungsinya adalah menunjukan detail dari rak tersebut. Yang isinya ada data rak tersebut (dari Kode Rak, dan semua data dari rak tersebut), ditambah dengan seluruh data barang yang ada pada rak tersebut. Dimana bakal ada foto dari raknya (Jadi buat preview dari fotonya itu berupa tulisan aja, misalnya "foto raknya", yang nanti tinggal developer tambahkan saja foto rak aslinya). Dan juga didalamnya ada fitur dengan tombol mengedit (ya kita bisa mengedit kode rak, dan data lain dari raknya) dan tombol menghapus data raknya (pada fitur ini, muncul peringatan kayak "apakah anda yakin?", lalu juga data barang yang ditaruh pada rak ini sebelum dihapus data rak tersebut, maka harus dipindahkan dulu barangnya). Nah untuk fitur list data barang pada rak tersebut, itu sistemnya menampilkan setiap data barangnya, dengan detail dari barang tersebut (hanya bisa menampilkan saja, ibaratnya data detail barangnya diambil dari data yang sudah ada), lalu juga ada fitur tombol layaknya untuk memindahkan barang tersebut (ibaratnya sebelum dihapus raknya, barang tersebut perlu di taruh dimana? kan harus dipindahkan dulu ke rak lainnya yang masih kosong atau rak yang bisa menampung dari total suatu barang tersebut), nah dari situ otomatis semua data tentang rak itu ikut berubah di semua fitur dan system web ini (mempengaruhi pada role admin dan siswa), contoh (salah satu contohnya, yaitu pada data barang pada tab data barang, pada detail suatu barang itu ada melihat lokasi rak untuk data tersebut kan? jadi pas adanya fitur baru itu lokasi raknya akan ikut berubah).

4. Tab Data Supplier
- [KHUSUS ADMIN/GURU] tambahkan fitur edit untuk tiap data supplier, yaitu untuk data supplier yang belum terisi lengkap. Yaitu fitur mengedit ini hanya ada di role admin saja ya. Contohnya, jadi ada data supplier baru yang masuk dari proses inbound, dimana yang baru terisi hanya nama PT aja dan sisanya ga terisi, nah nanti data tetep masuk, dan di tab ini, admin yang mengedit datanya (dari nama PT, Nomor Kontak, , Email, dan Alamat). Nah nanti pada dashboard siswa/user, itu tidak muncul fitur editnya, jadi siswa/user hanya bisa melihat data datanya aja, dan untuk datanya jika ada perubahan maka akan otomatis terupdate ya

5. Tab Data Customer
- sudah aman

6. Tab Inbound
-) [UNTUK ROLE SISWA/USER DAN GURU/ADMIN YA] Icon pada disamping tulisan "Riwayat Transaksi Inbound" itu ga muncul
-) [UNTUK ROLE SISWA/USER DAN GURU/ADMIN YA] Ukuran button disesuaikan dengan lebar dan Panjang dari tombol semua kategori
-) [UNTUK ROLE SISWA/USER DAN GURU/ADMIN YA] Ketika masuk ke proses inboundnya, Ketika tidak ada no resi dan user belum klik kotak centang "tidak ada resi", maka proses inbound tidak dapat tersubmit, dan nanti diberikan warning gitu, kalo diwajibkan mencetang jika tidak ada. Nah jikalau ada no resi, maka user tidak apa apa jika tidak klik kotak centang itu.
-) [UNTUK ROLE SISWA/USER DAN GURU/ADMIN YA] untuk fitur menambahkan supplier baru, tolong untuk bagian nomor kontak, email, dan alamat. Yaitu pada judul label nya di sampingnya ada tulisan untuk "Diharuskan diisi", ibaratnya jika ada maka harus diisi, kalau ga ada maka gapapa walau cuman nama pt nya aja. Nah nanti ini berhubungan dengan fitur edit pada tiap tiap data supplier di tab data supplier. Jadi melakukan penambahan supplier, maka data yang wajib diisi adalah nama Supplier (yaitu asal perusahan atau nama PT nya). 
-) [UNTUK ROLE SISWA/USER DAN GURU/ADMIN YA] untuk form menambahkan supplier baru itu, untuk nomor kontak (jika tidak di isi dengan angka, maka ga bisa di submit datanya), dan untuk email (jika tidak di isi dengan menggunakan "@" di ketikannya, maka ga bisa di submit datanya)
-) [INI UNTUK DI DASHBOARD SISWA DAN GURU JUGA] : Masih terlalu dempet bagian button batal dan button "simpan transaksi inbound": 
<div class="flex items-center justify-end gap-3">
        <a href="http://127.0.0.1:8000/inbound" class="btn btn-outline">Batal</a>
        <button type="submit" class="btn btn-primary btn-lg gap-2">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Transaksi Inbound
        </button>
    </div>
-) [UNTUK ROLE SISWA/USER DAN GURU/ADMIN YA] saat mau submit data proses inbound yaitu dengan cara "klik simpan transaksi inbound", itu ga mau langsung pindah ke luar ke halaman inboundnya saja (halaman yang belum masuk proses inbound ya maksud saya), jadi tolong supaya Ketika sudah di submit, itu bisa langsung balik kehalaman sebelumnya (jangan diam di halaman form proses transaksi inboundnya)
-) [UNTUK ROLE SISWA/USER DAN GURU/ADMIN YA] jangan lupa data inbound pada data list adalah data tidak berurutan ya, pokoknya data nomor 1 yang muncul adalah data terbaru, data nomor terakhir adalah data inbound paling awal


7. Tab Outbound 
-) [TERJADI DI ROLE SISWA/USER DAN GURU/ADMIN] : masih ada error Ketika mau buat outbound :
# ParseError - Internal Server Error

Unclosed '[' does not match ')'

PHP 8.3.30
Laravel 12.53.0
127.0.0.1:8000

## Stack Trace

0 - resources\views\outbound\create.blade.php:115
1 - vendor\laravel\framework\src\Illuminate\Filesystem\Filesystem.php:124
2 - vendor\laravel\framework\src\Illuminate\View\Engines\PhpEngine.php:57
3 - vendor\laravel\framework\src\Illuminate\View\Engines\CompilerEngine.php:76
4 - vendor\laravel\framework\src\Illuminate\View\View.php:208
5 - vendor\laravel\framework\src\Illuminate\View\View.php:191
6 - vendor\laravel\framework\src\Illuminate\View\View.php:160
7 - vendor\laravel\framework\src\Illuminate\Http\Response.php:78
8 - vendor\laravel\framework\src\Illuminate\Http\Response.php:34
9 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:939
10 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:906
11 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:821
12 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180
13 - app\Http\Middleware\CheckStudentIdentity.php:27
14 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
15 - app\Http\Middleware\NoPrefetch.php:28
16 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
17 - vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php:50
18 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
19 - vendor\laravel\framework\src\Illuminate\Auth\Middleware\Authenticate.php:63
20 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
21 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken.php:87
22 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
23 - vendor\laravel\framework\src\Illuminate\View\Middleware\ShareErrorsFromSession.php:48
24 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
25 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:120
26 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:63
27 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
28 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse.php:36
29 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
30 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\EncryptCookies.php:74
31 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
32 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
33 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:821
34 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:800
35 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:764
36 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:753
37 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:200
38 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180
39 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
40 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php:31
41 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
42 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
43 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php:51
44 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
45 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php:27
46 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
47 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php:109
48 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
49 - vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php:61
50 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
51 - vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php:58
52 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
53 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php:22
54 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
55 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php:26
56 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
57 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
58 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:175
59 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:144
60 - vendor\laravel\framework\src\Illuminate\Foundation\Application.php:1220
61 - public\index.php:20
62 - vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php:23

## Request

GET /outbound/create

## Headers

* **host**: 127.0.0.1:8000
* **connection**: keep-alive
* **cache-control**: max-age=0
* **sec-ch-ua**: "Chromium";v="152", "Not?A_Brand";v="24", "Microsoft Edge";v="152"
* **sec-ch-ua-mobile**: ?0
* **sec-ch-ua-platform**: "Windows"
* **upgrade-insecure-requests**: 1
* **user-agent**: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
* **sec-fetch-site**: same-origin
* **sec-fetch-mode**: navigate
* **sec-fetch-dest**: document
* **referer**: http://127.0.0.1:8000/outbound/create
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: en-US,en;q=0.9,id;q=0.8,en-ID;q=0.7,id-ID;q=0.6
* **cookie**: XSRF-TOKEN=eyJpdiI6Im9pU3VESUlCaHZyT2JhMmM4RmpTaXc9PSIsInZhbHVlIjoieVRsTm5id2dpMFZZa1F3cVlFdXhLUFFvK0dISGttS0c4ZTdhZmRjbDFnUCtrYTRVamxGZ0JSNmp6V3huTDM0YkNWaElVOHpadEZ3Si9hMnI0Yk5DTUQzSnZ6Y0dvOExzeFlicWI0QzNQbUZKSHlWQUtjWnhqOTFzMnJmZS9CdDkiLCJtYWMiOiI1NmY5MjU4MTkwYzdmMDEyMDZjMzcyMmU2ZDkyZjk0MmM3OWIxZGRiZWU1ZjIxZWMzNzRkMjlhOGY4Y2QzY2IzIiwidGFnIjoiIn0%3D; laravel_session=eyJpdiI6IlJSbzF0T0ZUNFI0alRCM0ZKTnE5NEE9PSIsInZhbHVlIjoiRnhIZlBUOVdmeTBUUTV0RTM4VkJQQ2lkQlRGTlR4U3YxUmI0NXNuY01aaXJYYVU3bXhZUG9MWE1mS0NhYlpNallFWkhYUkw1OVlLcU01cHkybmtreWZ0MGQxMDFzRkpjOXVSN1QweXI2elZnT1lYbGdzYzR1TjlmelhZN3d4ZTEiLCJtYWMiOiJlYjRmM2I0YmVmYWM2YTY4ZTk0OTAyZDZkNWYyOTMwNzIwMjYxMDkwOTQxNGM2YTI2YjczMjI2ZDFjYjQ0OTQxIiwidGFnIjoiIn0%3D

## Route Context

controller: App\Http\Controllers\OutboundController@create
route name: outbound.create
middleware: web, auth, student.identity

## Route Parameters

No route parameter data available.

## Database Queries

* pgsql - select * from "users" where "id" = 1 limit 1 (60.32 ms)
* pgsql - select * from "customers" order by "Nama" asc (4.23 ms)
* pgsql - select * from "master_barang" (6.04 ms)
* pgsql - select * from "rack_locations" where "rack_locations"."Rack_ID" in (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20) (4.23 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'ELK-00001' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (5.32 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'ELK-00001' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (5.54 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'ELK-00002' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.82 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'ELK-00002' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.95 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'ELK-00003' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.7 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'ELK-00003' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.77 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'ELK-00004' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (1.22 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'ELK-00004' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (1.26 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'ELK-00005' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (1.22 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'ELK-00005' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (1.27 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'FRN-00001' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.84 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'FRN-00001' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.89 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'FRN-00002' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.93 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'FRN-00002' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.83 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'FRN-00003' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.71 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'FRN-00003' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.71 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'FRN-00004' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.65 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'FRN-00004' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.59 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'FRN-00005' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.57 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'FRN-00005' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.52 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'PER-00001' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.63 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'PER-00001' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.62 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'PER-00002' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.78 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'PER-00002' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.79 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'PER-00003' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.89 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'PER-00003' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.93 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'PER-00004' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.84 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'PER-00004' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.97 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'PER-00005' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (1.03 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'PER-00005' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.83 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'KNS-00001' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.92 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'KNS-00001' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.99 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'KNS-00002' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.66 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'KNS-00002' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.52 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'KNS-00003' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.84 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'KNS-00003' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (2.37 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'KNS-00004' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (1.23 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'KNS-00004' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.77 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'KNS-00005' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.77 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'KNS-00005' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.92 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'BBK-00001' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.77 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'BBK-00001' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (1.24 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'BBK-00002' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.54 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'BBK-00002' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.42 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'BBK-00003' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.74 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'BBK-00003' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.78 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'BBK-00004' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.75 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'BBK-00004' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.71 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'BBK-00005' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (1.03 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'BBK-00005' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.96 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'SPR-00001' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.84 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'SPR-00001' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.66 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'SPR-00002' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.43 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'SPR-00002' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.62 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'SPR-00003' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.81 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'SPR-00003' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.74 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'ATS-00001' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (1.12 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'ATS-00001' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.9 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'ATS-00002' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.62 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'ATS-00002' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.74 ms)
* pgsql - select sum("Qty") as aggregate from "inbound_details" where "inbound_details"."SKU" = 'TSX-00001' and "inbound_details"."SKU" is not null and "inbound_details"."deleted_at" is null (0.91 ms)
* pgsql - select sum("Qty") as aggregate from "outbound_details" where "outbound_details"."SKU" = 'TSX-00001' and "outbound_details"."SKU" is not null and "outbound_details"."deleted_at" is null (0.64 ms)
-) [ROLE SISWA/USER DAN GURU/ADMIN] jangan lupa di buat pagination untuk list data transaksi outbound, dibuat pagination Ketika sudah ada lebih dari 15 data ya. 
-) [ROLE SISWA/USER DAN GURU/ADMIN] jangan lupa data outbound pada data list adalah data yang tidak berurutan ya, pokoknya data nomor 1 yang muncul adalah data terbaru, data nomor terakhir adalah data outbound paling awal
-) [ROLE SISWA/USER DAN GURU/ADMIN] untuk design surat jalan, yaitu untuk posisi dari tanda tangan dari semua tanda tangannya yaitu ada dibawah halaman kertas. Soalnya sekarnag masih di Bawah detail dari data barangnya 

8. Tab Kartu stok
-) [ROLE GURU/ADMIN] sudah aman
-) [ROLE SISWA/USER DAN GURU/ADMIN] jangan lupa kalo udah ada lebih dari 15 data di listnya, itu tuh dibikin pagination ya

9. Tab Stock Opname
-) [ROLE SISWA/USER DAN GURU/ADMIN] tolong saat sudah membuat data untuk catatan barang, yaitu langsung Kembali ke halaman sebelumnya, soalnya pada saat saya tes, itu malah Kembali di dalam halaman form pada saat seperti saya mau membuat data stock opname
-) [ROLE GURU/ADMIN SISWA/USER] tolong untuk tombol aksi yaitu edit dan hapus, itu digantikan dengan hanya tombol fitur detail, yaitu untuk memperlihatkan detail lengkap dari data stock opname yang dibuat. Soalnya gini, nanti yang ditampilkan untuk isi dari kondisi fisik dari barang tersebut di sebelum klik tombol detail, maka yang muncul hanyalah kalimat kalimat singkat saja, dan jika Panjang kalimatnya yaitu dengan ditamabh dengan "...", contohnya "barang itu sehat...". Nah baru deh setelah klik detail, baru lah muncul seluruh kalimat dari data stock opname yang kita pilih.

10. Tab Laporan & Export
-) [ROLE SISWA/USER DAN GURU/ADMIN] untuk icon di box laporan inbound itu belum muncul. Karena icon di box laporan lain tuh udah muncul

11. Tab Log Activity
-) tolong dibuat fitur seperti bisa menampilkan data lognya itu dibagi bagi, misal ingin melihat data aktivitas di hari ini, selama seminggu, sebulan, setahun, dan overall/semuanya
-) Sistem search yang strict harus sesuai dengan data nya persis. Sistem search operator/aksi yang dimau adalah, user search konteksnya aja, dari satu huruf, atau beberapa huruf, maka akan muncul data yang sesuai dengan konteks yang kita search. Istilahnya kayak kita search beberapa huruf (dengan tidak peduli pada besar kecil huruf, tetapi tetap peduli pada susunan hurufnya ya (contoh : "lap" itu kan bisa merepresentasikan dari laporan atau data lainnya lah))

2. Design warning (dari hapus data, atau lainnya lah. Kayak yang biasa terjadi ada pop up, lalu kita disuruh apakah yakin memilih/hal lainnya lah) :
-) yaitu dibuat dengan design sendiri yang mengikuti deisg tema web ini, jadi bukan tampilan dari js nya

Dashboard role siswa/user :

1. Tab Dashboard
-) untuk student banner pada dashboard, isinya nama operator (yang diambil dari data form siswa Ketika mau login), kelas (yang diambil dari data form siswa Ketika mau login), dan NIS (yang diambil dari data form siswa Ketika mau login). Jadi ga ada lagi judul sesi praktikum aktif, jadinya adalah hanya tinggal nama operator, kelas, dan nis pada student banner yang muncul di dashboard pada role siswa/user
-) di form pendataan siswa, itu untuk saat mengisi nomor nis (harus diisi dengan angka), jika tidak diisi dnegan ketentuan yang benar maka tidak bisa login ke akun tersebut.

2. Tab Kartu Stok 
-) fitur dan akses kartu stok untuk di dashboard siswa/user itu di hilangkan saja. Jadinya untuk di bagian fitur dari bagian inventory hanyalah stock opname.

Untuk Design :
1. sudah aman

Note :
1. masih terjadi loading lama dari role siswa/user (ya kayak session loadingnya lama banget, hingga di terminal terjadi pengulangan session di halaman yang sama). Terjadi pada saat setelah mau login akun siswa/user, yaitu pada selesai mengisi form pendataan diri
2. masih terjadi session loading lama pada fitur proses inbound, Ketika selesai klik simpan baru muncullah loading session yang lama.
3. masih terajdi session loading lama gitu pada saat membuat stock opname suatu barang, dimana itu loading lama untuk membuat sebuah data stock opnamenya
4. [WAJIB SANGAT] untuk seluruh database yang menggunakan id di datanya, maka id nya di ubah menjadi UUID ya bro