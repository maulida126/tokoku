# 🛒 TokoKu - Sistem Informasi Toko Berbasis Web

Aplikasi web sederhana untuk manajemen data toko (pelanggan, transaksi, laporan) yang dibangun menggunakan **PHP Native** dan **MySQL**. Proyek ini dibuat untuk memenuhi tugas mata kuliah Basis Data / Pemrograman Web.

## ✨ Fitur Utama

- 🔐 **Sistem Login & Logout** (Multi-user dengan password hash Bcrypt)
- 📊 **Dashboard Interaktif** (Statistik total pelanggan, transaksi, pendapatan)
- 👥 **Manajemen Pelanggan** (Tambah, Hapus, Cari via Stored Procedure)
- 🛒 **Manajemen Transaksi** (Lihat daftar transaksi & detail item belanja)
- 📑 **Laporan Transaksi** (Filter per pelanggan + fitur Cetak/Print)
- 🗄️ **Integrasi Database Lanjutan**:
  - Menggunakan **Stored Procedure** (`TambahPelanggan`, `HitungPelanggan`, `LaporanTransaksi`, dll).
  - Menggunakan **View** (`view_transaksi_pelanggan`, `view_total_belanja`).

## 🛠️ Teknologi yang Digunakan

- **Bahasa Pemrograman:** PHP 8.x (Native)
- **Database:** MySQL / MariaDB
- **Framework CSS:** Bootstrap 5.3
- **Ikon:** Bootstrap Icons
- **Web Server:** Apache (XAMPP / Laragon)

## 🚀 Cara Instalasi & Menjalankan

Ikuti langkah-langkah berikut untuk menjalankan proyek di komputer Anda:

### 1. Persyaratan Sistem
- Sudah menginstall **XAMPP** (atau Laragon).
- Browser modern (Chrome, Edge, Firefox).

### 2. Clone / Download Proyek
Pindahkan seluruh folder `tokoku` ke dalam direktori web server Anda:
- XAMPP: `C:\xampp\htdocs\tokoku`
- Laragon: `C:\laragon\www\tokoku`

### 3. Import Database
1. Buka browser dan akses `http://localhost/phpmyadmin`.
2. Buat database baru dengan nama `db_tokoku`.
3. Klik tab **Import**, pilih file `db_tokoku (2).sql` yang sudah disediakan.
4. Klik **Go / Kirim** untuk mengimport struktur beserta data awalnya.

### 4. Buat Tabel User (Login)
Karena tabel user dibuat terpisah, jalankan query SQL berikut di tab SQL phpMyAdmin:

```sql
CREATE TABLE `tbl_user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `role` enum('admin','kasir') DEFAULT 'admin',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tbl_user` (`username`, `password`, `nama_lengkap`, `role`) VALUES
('admin', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdN.KS0U8mZ9i2J8a', 'Administrator', 'admin');