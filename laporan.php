<?php
require 'config.php';

$id = intval($_GET['id'] ?? 0);
$rows = [];

if ($id > 0) {
    $res = $conn->query("CALL LaporanTransaksi($id)");
    if ($res) {
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $res->free();
    }
    clearResults($conn);
}

$pelanggan = $conn->query("SELECT * FROM tbl_pelanggan ORDER BY nama_pelanggan");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan - TokoKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-4">
    <h3 class="mb-3"><i class="bi bi-file-earmark-text"></i> Laporan Transaksi Pelanggan</h3>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="get" class="row g-2">
                <div class="col-md-8">
                    <select name="id" class="form-select" required>
                        <option value="">-- Pilih Pelanggan --</option>
                        <?php while($p = $pelanggan->fetch_assoc()): ?>
                            <option value="<?= $p['id_pelanggan'] ?>" <?= $id==$p['id_pelanggan']?'selected':'' ?>>
                                <?= htmlspecialchars($p['nama_pelanggan']) ?> - <?= htmlspecialchars($p['kota']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary w-100"><i class="bi bi-search"></i> Tampilkan</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($id > 0 && count($rows) > 0): ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-2">
                <button onclick="window.print()" class="btn btn-sm btn-outline-dark"><i class="bi bi-printer"></i> Cetak</button>
            </div>
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID Transaksi</th><th>Tanggal</th>
                        <th>Pelanggan</th><th>Kota</th>
                        <th class="text-end">Total Belanja</th>
                    </tr>
                </thead>
                <tbody>
                <?php $grand=0; foreach($rows as $r): $grand+=$r['total_belanja']; ?>
                    <tr>
                        <td>#<?= $r['id_transaksi'] ?></td>
                        <td><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($r['nama_pelanggan']) ?></td>
                        <td><?= htmlspecialchars($r['kota']) ?></td>
                        <td class="text-end"><?= rupiah($r['total_belanja']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-active">
                        <th colspan="4" class="text-end">Grand Total</th>
                        <th class="text-end"><?= rupiah($grand) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php elseif ($id > 0): ?>
        <div class="alert alert-warning"><i class="bi bi-info-circle"></i> Tidak ada transaksi untuk pelanggan ini.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>