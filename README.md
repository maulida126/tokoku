# 🛒 TokoKu - Sistem Informasi Toko Berbasis Web

Aplikasi web sederhana untuk manajemen data toko (pelanggan, transaksi, produk, stok, dan laporan) yang dibangun menggunakan **PHP Native** dan **MySQL**.

## ✨ Fitur Utama

- 🔐 **Login & Logout** — Multi-user dengan password hash Bcrypt.
- 📊 **Dashboard Overview** — Ringkasan pendapatan bulanan, tren penjualan 7 hari, transaksi terbaru, produk terlaris, dan pelanggan terbaik.
- 👥 **Manajemen Pelanggan** — Tambah, hapus, dan cari pelanggan melalui Stored Procedure.
- 🛒 **Manajemen Transaksi** — Melihat daftar transaksi dan detail item belanja.
- 📦 **Produk & Persediaan** — Menambahkan barang, mengelola stok, dan mengurangi stok otomatis saat pembelian berhasil.
- 🧾 **Pembayaran** — Mendukung pembayaran tunai, transfer, dan QRIS.
- 🖼️ **Foto Produk** — Upload foto produk dengan format JPG, PNG, dan WEBP.
- 📑 **Laporan Transaksi** — Filter transaksi berdasarkan pelanggan dan fitur cetak laporan.
- 👤 **Manajemen Pengguna** — Mengelola akun admin dan kasir.
- 🛍️ **Katalog Pelanggan** — Pelanggan dapat melihat produk, membuat pesanan, dan melihat pesanan miliknya.
- 🔄 **Pembaruan Stok Otomatis** — Tampilan stok diperbarui setiap 10 detik.
- 🗄️ **Integrasi Database Lanjutan** — Menggunakan Stored Procedure dan View.

## 🛠️ Teknologi yang Digunakan

- 💻 **Bahasa Pemrograman:** PHP 8.x (Native)
- 🗄️ **Database:** MySQL / MariaDB
- 🎨 **Framework CSS:** Bootstrap 5.3
- 🧩 **Ikon:** Bootstrap Icons
- 🌐 **Web Server:** Apache (XAMPP / Laragon)
- 🔒 **Keamanan:** Password Hash Bcrypt dan validasi transaksi.

## 📁 Struktur Database

Database yang digunakan bernama `db_tokoku`.

Beberapa tabel utama:

- 👤 `tbl_user` — Menyimpan data pengguna.
- 👥 `tbl_pelanggan` — Menyimpan data pelanggan.
- 📦 Tabel produk — Menyimpan katalog barang dan stok.
- 🛒 Tabel transaksi — Menyimpan data transaksi.
- 🧾 Tabel detail transaksi — Menyimpan rincian barang yang dibeli.

## 🚀 Cara Instalasi & Menjalankan

### 1. Persyaratan Sistem

- XAMPP atau Laragon.
- PHP versi 8.x.
- MySQL / MariaDB.
- Browser modern seperti Chrome, Edge, atau Firefox.

### 2. Download Proyek

Pindahkan folder `tokoku` ke direktori web server:

- **XAMPP:** `C:\xampp\htdocs\tokoku`
- **Laragon:** `C:\laragon\www\tokoku`

### 3. Import Database

1. Jalankan Apache dan MySQL.
2. Buka `http://localhost/phpmyadmin`.
3. Buat database bernama `db_tokoku`.
4. Pilih database tersebut.
5. Klik menu **Import**.
6. Pilih file `db_tokoku (2).sql`.
7. Klik **Import / Go**.

### 4. Buat Tabel User

Jika tabel `tbl_user` belum tersedia, jalankan query SQL pembuatan tabel dan akun administrator yang disediakan pada dokumentasi instalasi proyek.

Akun administrator awal:

- 👤 Username: `admin`
- 🔑 Password: `admin123`
- 🛡️ Role: `admin`

### 5. Upgrade Database Pelanggan

Jalankan file `upgrade_pelanggan.sql` satu kali melalui phpMyAdmin.

File ini berfungsi untuk:

- Mengatur tiga role pengguna: admin, kasir, dan pelanggan.
- Menghubungkan akun pelanggan dengan data pelanggan.
- Menyiapkan katalog barang awal.

### 6. Upgrade Database Stok

Jalankan file `upgrade_stok.sql` satu kali.

Fungsinya:

- Menambahkan sistem persediaan barang.
- Mengatur stok awal produk.
- Mengelola penambahan stok.
- Mengurangi stok secara otomatis saat transaksi berhasil.
- Mencegah pembelian melebihi stok tersedia.

### 7. Upgrade Foto Produk

Jalankan file `upgrade_gambar_produk.sql` satu kali melalui phpMyAdmin.

Format foto yang didukung:

- JPG
- PNG
- WEBP

Ukuran maksimal foto: **5 MB**.

### 8. Konfigurasi QRIS

Untuk mengaktifkan pembayaran QRIS, tempatkan gambar QRIS resmi toko pada lokasi:

`assets/qris-tokoku.png`

Jika gambar belum tersedia, pembayaran QRIS tidak dapat diproses.

### 9. Jalankan Aplikasi

Buka browser dan akses:

`http://localhost/tokoku`

## 👥 Hak Akses Pengguna

| Role | Hak Akses |
|---|---|
| 🛡️ Admin | Dashboard, pelanggan, transaksi, laporan, pengguna, produk, dan stok |
| 🧑‍💼 Kasir | Pelanggan, stok barang terdaftar, dan pembayaran |
| 🛍️ Pelanggan | Katalog produk, pemesanan, dan riwayat pesanan sendiri |

## 🗄️ Integrasi Database Lanjutan

Aplikasi menggunakan fitur database MySQL berikut:

- ⚙️ **Stored Procedure** — `TambahPelanggan`, `HitungPelanggan`, `LaporanTransaksi`, dan lainnya.
- 👁️ **View** — `view_transaksi_pelanggan` dan `view_total_belanja`.
- 🔄 **Automatic Stock Update** — Pengurangan stok ketika transaksi berhasil.
- 🔍 **Data Validation** — Validasi harga dan stok langsung dari database.

## 🔒 Keamanan Sistem

- Password disimpan menggunakan Bcrypt.
- Validasi input pengguna.
- Penggunaan prepared statement.
- Pembatasan akses berdasarkan role.
- Validasi stok sebelum transaksi disimpan.
- Pelanggan hanya dapat melihat pesanan miliknya sendiri.

## 🎯 Tujuan Pembuatan

Proyek TokoKu dibuat sebagai penerapan materi **Basis Data dan Pemrograman Web**, khususnya:

- CRUD (Create, Read, Update, Delete).
- Relasi antar tabel.
- Primary Key dan Foreign Key.
- Stored Procedure.
- Database View.
- Transaksi database.
- Manajemen persediaan.
- Sistem autentikasi dan otorisasi.

## 👨‍💻 Penutup

TokoKu merupakan aplikasi pengelolaan toko berbasis web yang mengintegrasikan PHP Native dan MySQL untuk membantu proses pengelolaan pelanggan, produk, persediaan, transaksi, serta laporan penjualan secara lebih terstruktur.

**© 2026 TokoKu - Sistem Informasi Toko Berbasis Web**
