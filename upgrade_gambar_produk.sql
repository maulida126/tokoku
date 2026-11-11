-- Jalankan satu kali pada database TokoKu yang sudah memiliki tbl_produk.
-- Lokasi gambar yang diunggah disimpan sebagai path relatif pada kolom gambar.
ALTER TABLE tbl_produk
  ADD COLUMN IF NOT EXISTS gambar VARCHAR(255) NULL AFTER stok;
