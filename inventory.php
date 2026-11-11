<?php

class StokTidakCukupException extends RuntimeException
{
}

function ambilDanKurangiStok(mysqli $conn, array $keranjang): array
{
    ksort($keranjang, SORT_NUMERIC);
    $produkDipilih = [];
    $select = $conn->prepare(
        "SELECT id_produk, nama_produk, harga, stok
         FROM tbl_produk
         WHERE id_produk = ? AND aktif = 1
         FOR UPDATE"
    );
    $update = $conn->prepare(
        "UPDATE tbl_produk
         SET stok = stok - ?
         WHERE id_produk = ? AND aktif = 1 AND stok >= ?"
    );

    foreach ($keranjang as $idProduk => $jumlah) {
        $select->bind_param("i", $idProduk);
        $select->execute();
        $produk = $select->get_result()->fetch_assoc();

        if (!$produk) {
            throw new StokTidakCukupException('Salah satu barang sudah tidak tersedia. Periksa kembali katalog.');
        }
        if ((int)$produk['stok'] < $jumlah) {
            throw new StokTidakCukupException(
                'Stok ' . $produk['nama_produk'] . ' tidak mencukupi. Sisa stok: ' . (int)$produk['stok'] . '.'
            );
        }

        $update->bind_param("iii", $jumlah, $idProduk, $jumlah);
        $update->execute();
        if ($update->affected_rows !== 1) {
            throw new StokTidakCukupException('Stok barang berubah. Muat ulang halaman lalu coba kembali.');
        }

        $produk['jumlah'] = $jumlah;
        $produkDipilih[] = $produk;
    }

    $select->close();
    $update->close();

    return $produkDipilih;
}
