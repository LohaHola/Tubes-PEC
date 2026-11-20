# Arbyte — E-Commerce Web Application

Arbyte adalah aplikasi toko online berbasis web yang dikembangkan sebagai proyek tugas kuliah sekaligus portofolio. Aplikasi ini menyediakan alur belanja untuk pelanggan dan fitur pengelolaan toko melalui panel admin.

## Fitur

### Pelanggan
- Melihat katalog, kategori, detail produk, dan ketersediaan stok.
- Membuat akun, login, dan mengelola profil termasuk foto profil.
- Mengisi alamat pengiriman dengan pilihan provinsi dan kabupaten/kota bertingkat.
- Menambahkan produk ke keranjang dan melakukan checkout.
- Melihat riwayat serta detail pesanan dan status pembayaran.
- Memberikan ulasan produk sesuai ketentuan aplikasi.

### Storefront
- Homepage dengan section Promo dan Rekomendasi yang dikelola admin.
- Section Promo/Rekomendasi hanya ditampilkan jika memiliki konten yang aktif dan memenuhi periode penayangan serta ketersediaan produk.
- Harga promo diterapkan pada tampilan produk, keranjang, dan checkout.

### Admin
- Dashboard ringkasan toko, omzet, pesanan, pelanggan, dan inventaris.
- Mengelola produk, kategori, pesanan, dan data pelanggan.
- Melihat detail pelanggan, statistik pembelian, serta riwayat pesanan.
- Mengatur konten Promo dan Rekomendasi homepage, termasuk produk, judul, deskripsi, urutan, status aktif, periode tayang, dan harga promo.

## Teknologi

- **Backend:** PHP 8.3+, Laravel 13
- **Database:** MySQL (konfigurasi SQLite juga tersedia sebagai default pada template `.env.example`)
- **Frontend:** Blade, Vite, Tailwind CSS 4
- **Pembayaran:** Midtrans Snap
- **Data wilayah:** Dataset JSON lokal (38 provinsi dan 514 kabupaten/kota), sehingga dropdown alamat tidak bergantung pada API eksternal.

## Persyaratan

Pastikan perangkat sudah memiliki:
- PHP 8.3 atau lebih baru beserta ekstensi yang dibutuhkan Laravel dan PDO MySQL.
- Composer.
- Node.js dan npm.
- MySQL / MariaDB (jika menggunakan MySQL).

## Instalasi Lokal

### 1. Clone repository

```bash
git clone https://github.com/LohaHola/Tubes-PEC.git
cd Tubes-PEC
```

### 2. Instal dependensi PHP

```bash
composer install
```

### 3. Siapkan file environment

Windows Command Prompt:

```bat
copy .env.example .env
```

macOS / Linux:

```bash
cp .env.example .env
```

Buat application key:

```bash
php artisan key:generate
```

### 4. Konfigurasi database

Buat database kosong, misalnya `arbyte`, lalu sesuaikan bagian database pada file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=arbyte
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan username dan password dengan konfigurasi MySQL lokal masing-masing.

### 5. Jalankan migration dan seeder

```bash
php artisan migrate --seed
```

Seeder dapat membuat akun contoh untuk kebutuhan pengembangan. Periksa file di `database/seeders` untuk mengetahui data akun yang dibuat dan jangan gunakan kredensial contoh pada lingkungan produksi.

### 6. Instal dependensi frontend dan build aset

```bash
npm install
npm run build
```

### 7. Siapkan penyimpanan file publik

```bash
php artisan storage:link
```

### 8. Jalankan aplikasi

```bash
php artisan serve
```

Buka alamat lokal yang ditampilkan Artisan, biasanya:

```
http://127.0.0.1:8000
```

Untuk pengembangan frontend dengan Vite, jalankan `npm run dev` pada terminal terpisah.

## Konfigurasi Midtrans

Arbyte menggunakan Midtrans Snap. Tambahkan kredensial Sandbox pada file `.env` untuk pengujian:

```env
MIDTRANS_SERVER_KEY=
MIDTRANS_SIGNATURE_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
MIDTRANS_NOTIFICATION_URL=
```

Atur URL notifikasi (webhook) agar dapat dijangkau oleh server Midtrans. Endpoint notifikasi aplikasi adalah:

```
POST /midtrans/notification
```

Server Key (`MIDTRANS_SERVER_KEY`) digunakan untuk autentikasi API. Signature Secret Key (`MIDTRANS_SIGNATURE_KEY`) digunakan secara terpisah untuk memverifikasi signature notifikasi dan respons status Midtrans. Sejak perubahan Midtrans pada 28 Oktober 2026, kedua key tersebut dapat memiliki nilai berbeda. Gunakan kredensial Sandbox selama pengembangan dan jangan pernah mengunggah file `.env`, Server Key, Signature Secret Key, Client Key, atau kredensial pembayaran ke repository.

## Catatan

- Data provinsi dan kabupaten/kota disimpan sebagai dataset JSON lokal agar dropdown alamat tidak bergantung pada API eksternal.
- Dashboard menampilkan omzet dari pesanan yang sudah dibayar. Aplikasi belum menghitung laba bersih karena data HPP dan biaya operasional belum tersedia.
- Sebelum digunakan pada lingkungan produksi, lakukan pengujian menyeluruh, atur HTTPS, backup database, dan tinjau konfigurasi keamanan serta pembayaran.

## Lisensi

Repository ini dikembangkan untuk kebutuhan pembelajaran dan portofolio. Framework dan dependensi pihak ketiga tetap mengikuti lisensi masing-masing.
