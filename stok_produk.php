<?php
require 'config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Metode permintaan tidak didukung.']);
    exit;
}

$result = $conn->query(
    "SELECT id_produk, nama_produk, harga, stok
     FROM tbl_produk
     WHERE aktif = 1
     ORDER BY id_produk"
);
$produk = [];
while ($row = $result->fetch_assoc()) {
    $produk[] = [
        'id' => (int)$row['id_produk'],
        'nama' => $row['nama_produk'],
        'harga' => (float)$row['harga'],
        'stok' => (int)$row['stok'],
    ];
}

echo json_encode(['produk' => $produk], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
