# 🛒 TokoKu - Sistem Informasi Toko Berbasis Web

Aplikasi web sederhana untuk manajemen data toko (pelanggan, transaksi, laporan) yang dibangun menggunakan **PHP Native** dan **MySQL**. Proyek ini dibuat untuk memenuhi tugas mata kuliah Basis Data / Pemrograman Web.

## ✨ Fitur Utama

- 🔐 **Sistem Login & Logout** (Multi-user dengan password hash Bcrypt)
- 📊 **Dashboard Overview** (Ringkasan pendapatan bulanan, tren 7 hari, transaksi terbaru, produk terlaris, dan pelanggan terbaik)
- 👥 **Manajemen Pelanggan** (Tambah, Hapus, Cari via Stored Procedure)
- 🛒 **Manajemen Transaksi** (Lihat daftar transaksi & detail item belanja)
- 📦 **Produk & Persediaan** (Admin menambahkan barang dan stok; stok dipotong otomatis saat pembelian berhasil)
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
Jika tabel `tbl_user` belum ada, jalankan query SQL berikut di tab SQL phpMyAdmin:

```sql
CREATE TABLE `tbl_user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kasir','pelanggan') NOT NULL DEFAULT 'pelanggan',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tbl_user` (`nama`, `username`, `password`, `role`) VALUES
('Administrator', 'admin', '$2y$10$PKteaysSbOSRA41UZkCKt.bvEzRdJae78562OE1J0w8heb60VYT82', 'admin')
ON DUPLICATE KEY UPDATE
  `password` = '$2y$10$PKteaysSbOSRA41UZkCKt.bvEzRdJae78562OE1J0w8heb60VYT82',
  `nama` = 'Administrator',
  `role` = 'admin';
```

Query di atas juga memperbaiki akun `admin` yang sudah ada, sehingga password default `admin123` dapat digunakan untuk masuk ke dashboard.

### 5. Aktifkan Role, Pembayaran, dan Katalog Barang
Setelah tabel `tbl_user` dibuat, import/jalankan file `upgrade_pelanggan.sql` satu kali melalui phpMyAdmin. File ini menyiapkan tepat tiga role, menghubungkan akun pelanggan dengan data pelanggan, dan membuat katalog barang awal. Akun lama dengan role `user` akan diubah menjadi `kasir`.

- **Admin:** dapat mengakses dashboard, data pelanggan, pembayaran/transaksi, laporan, serta membuat akun kasir dari menu **Pengguna**.
- **Admin:** juga dapat menambah barang dan stok gudang dari menu **Produk & Stok**.
- **Kasir:** dapat menambahkan pelanggan, menambah stok barang yang sudah terdaftar dari menu **Produk & Stok**, serta memproses pembayaran dari barang katalog dengan metode tunai, transfer, atau QRIS. Penambahan barang baru hanya dapat dilakukan admin.
- **Pelanggan:** dapat mendaftar, melihat katalog yang disediakan TokoKu, membuat pesanan, dan melihat pesanan miliknya saja.

### 6. Aktifkan Persediaan Barang
Pada database yang sudah pernah menjalankan `upgrade_pelanggan.sql`, jalankan `upgrade_stok.sql` satu kali melalui phpMyAdmin. Instalasi baru juga dapat menjalankan file ini; kolom stok tidak akan dibuat dua kali.

Barang baru dibuat melalui menu **Produk & Stok**, termasuk harga jual dan jumlah awal di gudang. Barang lama yang sudah ada saat migrasi mendapat stok awal `0`; masukkan jumlah gudang sebenarnya melalui menu tersebut. Stok yang sudah tersedia dapat ditambah saat barang masuk gudang. Pelanggan melihat katalog dan status stok barang; barang yang stoknya habis tidak dapat dipesan. Pembelian kasir maupun pelanggan mengurangi stok otomatis, dan pesanan ditolak jika stok tidak mencukupi. Harga serta stok divalidasi dari database saat transaksi diproses.

Persediaan yang sama digunakan oleh admin, kasir, dan pelanggan. Halaman inventaris, pembayaran kasir, dan katalog pelanggan memperbarui tampilan stok secara otomatis setiap 10 detik; transaksi tetap memeriksa stok terbaru di server sebelum disimpan.

Untuk menampilkan foto barang, jalankan `upgrade_gambar_produk.sql` satu kali melalui phpMyAdmin. Admin dapat mengunggah foto saat menambah barang atau memilih foto barang lama dari menu **Produk & Stok**. Format JPG, PNG, dan WEBP didukung dengan batas ukuran 5 MB; foto tersebut ditampilkan di katalog pelanggan dan pembayaran kasir. Jika foto belum diunggah, aplikasi menampilkan ikon bawaan.

Pembayaran QRIS pada kasir menampilkan kode dan total keranjang. Untuk mengaktifkannya, admin perlu menempatkan gambar QRIS resmi toko di `assets/qris-tokoku.png`. Tanpa gambar tersebut, sistem tidak mengizinkan transaksi QRIS dicatat.