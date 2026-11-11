<?php
require 'config.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$id = $id && $id > 0 ? $id : 0;
$rows = [];
$pelangganTerpilih = null;
$jumlahTransaksi = 0;
$grandTotal = 0;

$pelanggan = $conn->query(
    "SELECT id_pelanggan, nama_pelanggan, kota
     FROM tbl_pelanggan
     ORDER BY nama_pelanggan"
);

if ($id > 0) {
    $stmt = $conn->prepare(
        "SELECT id_pelanggan, nama_pelanggan, kota
         FROM tbl_pelanggan
         WHERE id_pelanggan = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $pelangganTerpilih = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($pelangganTerpilih) {
        $stmt = $conn->prepare(
            "SELECT id_transaksi, tanggal, total_belanja, metode_pembayaran
             FROM tbl_transaksi
             WHERE id_pelanggan = ?
             ORDER BY tanggal DESC, id_transaksi DESC"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
            $grandTotal += (float)$row['total_belanja'];
        }
        $stmt->close();
        $jumlahTransaksi = count($rows);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - TokoKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="management-page report-page">
<?php include 'navbar.php'; ?>

<main class="management-container">
    <header class="management-heading report-heading">
        <div>
            <span class="management-eyebrow"><i class="bi bi-file-earmark-bar-graph"></i> ANALISIS TOKO</span>
            <h1>Laporan transaksi</h1>
            <p>Lihat rangkuman pembelian dan total belanja per pelanggan.</p>
        </div>
        <?php if ($pelangganTerpilih && $jumlahTransaksi > 0): ?>
            <button type="button" class="management-outline-button no-print" onclick="window.print()"><i class="bi bi-printer"></i> Cetak laporan</button>
        <?php endif; ?>
    </header>

    <section class="management-panel report-filter-panel no-print">
        <div class="management-panel-heading">
            <span class="management-panel-icon"><i class="bi bi-funnel"></i></span>
            <div><h2>Pilih pelanggan</h2><p>Gunakan laporan ini untuk meninjau transaksi pelanggan tertentu.</p></div>
        </div>
        <form method="get" class="report-filter-form">
            <label for="report-customer">Pelanggan</label>
            <select id="report-customer" name="id" class="form-select" required>
                <option value="">Pilih pelanggan untuk melihat laporan</option>
                <?php while ($p = $pelanggan->fetch_assoc()): ?>
                    <option value="<?= (int)$p['id_pelanggan'] ?>" <?= $id === (int)$p['id_pelanggan'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nama_pelanggan'] . ' - ' . $p['kota']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button class="management-primary-button"><i class="bi bi-bar-chart-line"></i> Tampilkan laporan</button>
        </form>
    </section>

    <?php if ($id > 0 && !$pelangganTerpilih): ?>
        <div class="management-alert">Pelanggan tidak ditemukan. Silakan pilih pelanggan yang tersedia.</div>
    <?php elseif ($pelangganTerpilih): ?>
        <section class="report-summary">
            <div class="report-customer-card">
                <span class="management-avatar"><i class="bi bi-person"></i></span>
                <span><small>LAPORAN PELANGGAN</small><strong><?= htmlspecialchars($pelangganTerpilih['nama_pelanggan']) ?></strong><em><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($pelangganTerpilih['kota']) ?> · ID #<?= (int)$id ?></em></span>
            </div>
            <div class="report-metric"><small>Jumlah transaksi</small><strong><?= number_format($jumlahTransaksi, 0, ',', '.') ?></strong></div>
            <div class="report-metric is-total"><small>Total belanja</small><strong><?= rupiah($grandTotal) ?></strong></div>
        </section>

        <section class="management-panel report-results">
            <div class="management-panel-heading">
                <span class="management-panel-icon"><i class="bi bi-receipt"></i></span>
                <div><h2>Rincian transaksi</h2><p><?= $jumlahTransaksi ? 'Daftar seluruh transaksi pelanggan yang dipilih.' : 'Belum ada transaksi untuk pelanggan ini.' ?></p></div>
            </div>
            <div class="management-table-wrap">
                <table class="table management-table align-middle mb-0">
                    <thead><tr><th>Transaksi</th><th>Tanggal</th><th>Metode pembayaran</th><th class="text-end">Total belanja</th><th class="text-end no-print">Detail</th></tr></thead>
                    <tbody>
                    <?php if ($rows): ?>
                        <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><strong class="transaction-number">#<?= (int)$row['id_transaksi'] ?></strong></td>
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($row['tanggal']))) ?></td>
                            <td><span class="management-payment"><?= htmlspecialchars(strtoupper($row['metode_pembayaran'] ?? 'tunai')) ?></span></td>
                            <td class="text-end management-money"><?= rupiah($row['total_belanja']) ?></td>
                            <td class="text-end no-print"><a href="detail_transaksi.php?id=<?= (int)$row['id_transaksi'] ?>" class="management-detail-link" aria-label="Detail transaksi #<?= (int)$row['id_transaksi'] ?>"><i class="bi bi-arrow-up-right"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5"><div class="management-empty"><i class="bi bi-receipt"></i><strong>Belum ada transaksi</strong><span>Transaksi pelanggan akan ditampilkan di sini.</span></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                    <?php if ($rows): ?>
                    <tfoot><tr><th colspan="3" class="text-end">Total keseluruhan</th><th class="text-end management-money"><?= rupiah($grandTotal) ?></th><th class="no-print"></th></tr></tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
