<?php
require 'config.php';
$filter = $_GET['pelanggan'] ?? '';

$sql = "SELECT * FROM view_transaksi_pelanggan";
if ($filter !== '') {
    $sql .= " WHERE nama_pelanggan IN (SELECT nama_pelanggan FROM tbl_pelanggan WHERE id_pelanggan=" . intval($filter) . ")";
}
$sql .= " ORDER BY tanggal DESC, id_transaksi DESC";
$data = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi - TokoKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="bi bi-cart"></i> Data Transaksi</h3>
        <?php if ($filter !== ''): ?>
            <a href="transaksi.php" class="btn btn-sm btn-outline-dark"><i class="bi bi-x-lg"></i> Hapus Filter</a>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th><th>Tanggal</th><th>Pelanggan</th><th>Kota</th>
                            <th class="text-end">Total</th><th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($data && $data->num_rows > 0): ?>
                        <?php while ($row = $data->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $row['id_transaksi'] ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                            <td><?= htmlspecialchars($row['kota']) ?></td>
                            <td class="text-end fw-semibold text-success"><?= rupiah($row['total_belanja']) ?></td>
                            <td class="text-center">
                                <a href="detail_transaksi.php?id=<?= $row['id_transaksi'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada transaksi.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>