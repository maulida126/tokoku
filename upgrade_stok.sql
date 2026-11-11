-- Jalankan satu kali pada database yang sudah memakai tbl_produk.
-- Barang yang sudah ada mendapat stok awal 0 agar admin dapat memasukkan
-- jumlah persediaan gudang yang sebenarnya.
ALTER TABLE tbl_produk
  ADD COLUMN IF NOT EXISTS stok INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER harga;
