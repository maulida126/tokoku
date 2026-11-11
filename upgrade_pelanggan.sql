-- Jalankan sekali setelah database db_tokoku, tbl_pelanggan, tbl_transaksi,
-- dan tbl_user dibuat.
ALTER TABLE tbl_user
  MODIFY role ENUM('admin', 'user', 'kasir', 'pelanggan') DEFAULT 'pelanggan',
  ADD COLUMN IF NOT EXISTS nama VARCHAR(100) DEFAULT NULL AFTER id_user,
  ADD COLUMN id_pelanggan INT(11) DEFAULT NULL AFTER role,
  ADD UNIQUE KEY uq_tbl_user_id_pelanggan (id_pelanggan),
  ADD CONSTRAINT fk_tbl_user_pelanggan
    FOREIGN KEY (id_pelanggan) REFERENCES tbl_pelanggan (id_pelanggan)
    ON DELETE SET NULL;

UPDATE tbl_user SET role = 'kasir' WHERE role IN ('user', '');
ALTER TABLE tbl_user
  MODIFY role ENUM('admin', 'kasir', 'pelanggan') NOT NULL DEFAULT 'pelanggan';

CREATE TABLE IF NOT EXISTS tbl_detail_transaksi (
  id_detail INT(11) NOT NULL AUTO_INCREMENT,
  id_transaksi INT(11) DEFAULT NULL,
  nama_produk VARCHAR(100) DEFAULT NULL,
  jumlah INT(11) DEFAULT NULL,
  harga DECIMAL(12,2) DEFAULT NULL,
  PRIMARY KEY (id_detail),
  KEY id_transaksi (id_transaksi),
  CONSTRAINT fk_detail_transaksi_transaksi
    FOREIGN KEY (id_transaksi) REFERENCES tbl_transaksi (id_transaksi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE tbl_transaksi
  ADD COLUMN IF NOT EXISTS metode_pembayaran ENUM('tunai', 'transfer', 'qris') NOT NULL DEFAULT 'tunai';

CREATE TABLE IF NOT EXISTS tbl_produk (
  id_produk INT(11) NOT NULL AUTO_INCREMENT,
  nama_produk VARCHAR(100) NOT NULL,
  harga DECIMAL(12,2) NOT NULL,
  stok INT(10) UNSIGNED NOT NULL DEFAULT 0,
  aktif TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id_produk),
  UNIQUE KEY uq_tbl_produk_nama (nama_produk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE tbl_produk
  ADD COLUMN IF NOT EXISTS stok INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER harga;

INSERT IGNORE INTO tbl_produk (nama_produk, harga) VALUES
  ('Buku Tulis', 5000),
  ('Buku Gambar', 10000),
  ('Penghapus', 2000),
  ('Penggaris', 5000),
  ('Pensil', 2000),
  ('Pulpen', 3000),
  ('Sepatu', 275000),
  ('Tas Sekolah', 250000);
