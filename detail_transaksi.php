<?php
require 'config.php';
$id = intval($_GET['id'] ?? 0);

$trx    = $conn->query("SELECT * FROM view_transaksi_pelanggan WHERE id_transaksi=$id")->fetch_assoc();
$detail = $conn->query("SELECT * FROM tbl_detail_transaksi WHERE id_transaksi=$id");

if (!$trx) { die("Transaksi tidak ditemukan."); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Transaksi - TokoKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-4">
    <a href="transaksi.php" class="btn btn-sm btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Kembali</a>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <strong>Detail Transaksi #<?= $trx['id_transaksi'] ?></strong>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Pelanggan:</strong> <?= htmlspecialchars($trx['nama_pelanggan']) ?></p>
                    <p class="mb-1"><strong>Kota:</strong> <?= htmlspecialchars($trx['kota']) ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-1"><strong>Tanggal:</strong> <?= date('d/m/Y', strtotime($trx['tanggal'])) ?></p>
                    <p class="mb-1"><strong>Total:</strong> <span class="text-success fw-bold"><?= rupiah($trx['total_belanja']) ?></span></p>
                </div>
            </div>

            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th><th>Produk</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no=1; $total=0; while($d = $detail->fetch_assoc()): $sub=$d['jumlah']*$d['harga']; $total+=$sub; ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($d['nama_produk']) ?></td>
                        <td class="text-center"><?= $d['jumlah'] ?></td>
                        <td class="text-end"><?= rupiah($d['harga']) ?></td>
                        <td class="text-end"><?= rupiah($sub) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr class="table-active">
                        <th colspan="4" class="text-end">Total</th>
                        <th class="text-end"><?= rupiah($total) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>