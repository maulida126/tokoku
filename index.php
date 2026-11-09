<?php
require 'config.php';

$totalPelanggan  = $conn->query("SELECT COUNT(*) AS n FROM tbl_pelanggan")->fetch_assoc()['n'];
$totalTransaksi  = $conn->query("SELECT COUNT(*) AS n FROM tbl_transaksi")->fetch_assoc()['n'];
$totalPendapatan = $conn->query("SELECT COALESCE(SUM(total_belanja),0) AS n FROM tbl_transaksi")->fetch_assoc()['n'];
$totalCabang     = $conn->query("SELECT COUNT(*) AS n FROM tbl_cabang")->fetch_assoc()['n'];

$pelangganTerbaik = $conn->query("SELECT * FROM view_total_belanja ORDER BY total_belanja DESC LIMIT 5");
$transaksiTerbaru = $conn->query("SELECT * FROM view_transaksi_pelanggan ORDER BY tanggal DESC, id_transaksi DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - TokoKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard</h3>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'akses'): ?>
        <div class="alert alert-danger"><i class="bi bi-shield-x"></i> Anda tidak punya akses ke halaman tersebut.</div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Total Pelanggan</div>
                            <h2 class="mb-0"><?= $totalPelanggan ?></h2>
                        </div>
                        <i class="bi bi-people fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Total Transaksi</div>
                            <h2 class="mb-0"><?= $totalTransaksi ?></h2>
                        </div>
                        <i class="bi bi-cart-check fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Total Pendapatan</div>
                            <h5 class="mb-0"><?= rupiah($totalPendapatan) ?></h5>
                        </div>
                        <i class="bi bi-cash-coin fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Total Cabang</div>
                            <h2 class="mb-0"><?= $totalCabang ?></h2>
                        </div>
                        <i class="bi bi-shop fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white"><strong><i class="bi bi-trophy"></i> Top 5 Pelanggan</strong></div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Nama</th><th class="text-center">Transaksi</th><th class="text-end">Total Belanja</th></tr>
                        </thead>
                        <tbody>
                        <?php if ($pelangganTerbaik && $pelangganTerbaik->num_rows > 0): ?>
                            <?php while ($p = $pelangganTerbaik->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['nama_pelanggan']) ?></td>
                                <td class="text-center"><span class="badge bg-secondary"><?= $p['total_transaksi'] ?></span></td>
                                <td class="text-end"><?= rupiah($p['total_belanja']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Belum ada data.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white"><strong><i class="bi bi-clock-history"></i> Transaksi Terbaru</strong></div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Tanggal</th><th>Pelanggan</th><th class="text-end">Total</th></tr>
                        </thead>
                        <tbody>
                        <?php if ($transaksiTerbaru && $transaksiTerbaru->num_rows > 0): ?>
                            <?php while ($t = $transaksiTerbaru->fetch_assoc()): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($t['tanggal'])) ?></td>
                                <td><?= htmlspecialchars($t['nama_pelanggan']) ?></td>
                                <td class="text-end"><?= rupiah($t['total_belanja']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Belum ada transaksi.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>