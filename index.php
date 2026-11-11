<?php
require 'config.php';

$totalPelanggan = (int)$conn->query("SELECT COUNT(*) AS n FROM tbl_pelanggan")->fetch_assoc()['n'];
$totalCabang = (int)$conn->query("SELECT COUNT(*) AS n FROM tbl_cabang")->fetch_assoc()['n'];

$awalBulanIni = (new DateTimeImmutable('first day of this month'))->format('Y-m-d');
$awalBulanDepan = (new DateTimeImmutable('first day of next month'))->format('Y-m-d');
$awalBulanLalu = (new DateTimeImmutable('first day of last month'))->format('Y-m-d');
$statStmt = $conn->prepare(
    "SELECT
        COUNT(*) AS total_transaksi,
        COALESCE(SUM(total_belanja), 0) AS total_pendapatan,
        COALESCE(SUM(CASE WHEN tanggal >= ? AND tanggal < ? THEN total_belanja ELSE 0 END), 0) AS pendapatan_bulan_ini,
        COALESCE(SUM(CASE WHEN tanggal >= ? AND tanggal < ? THEN total_belanja ELSE 0 END), 0) AS pendapatan_bulan_lalu,
        SUM(CASE WHEN tanggal >= ? AND tanggal < ? THEN 1 ELSE 0 END) AS transaksi_bulan_ini"
    . " FROM tbl_transaksi"
);
$statStmt->bind_param(
    "ssssss",
    $awalBulanIni,
    $awalBulanDepan,
    $awalBulanLalu,
    $awalBulanIni,
    $awalBulanIni,
    $awalBulanDepan
);
$statStmt->execute();
$stats = $statStmt->get_result()->fetch_assoc();
$statStmt->close();

$pendapatanBulanIni = (float)$stats['pendapatan_bulan_ini'];
$pendapatanBulanLalu = (float)$stats['pendapatan_bulan_lalu'];
$perubahanPendapatan = $pendapatanBulanLalu > 0
    ? (($pendapatanBulanIni - $pendapatanBulanLalu) / $pendapatanBulanLalu) * 100
    : null;

$awalGrafik = (new DateTimeImmutable('today'))->modify('-6 days')->format('Y-m-d');
$stmt = $conn->prepare(
    "SELECT tanggal, COALESCE(SUM(total_belanja), 0) AS pendapatan
     FROM tbl_transaksi
     WHERE tanggal >= ? AND tanggal <= CURDATE()
     GROUP BY tanggal"
);
$stmt->bind_param("s", $awalGrafik);
$stmt->execute();
$hasilGrafik = $stmt->get_result();
$pendapatanHarian = [];
while ($hari = $hasilGrafik->fetch_assoc()) {
    $pendapatanHarian[$hari['tanggal']] = (float)$hari['pendapatan'];
}
$stmt->close();

$grafikMingguan = [];
for ($i = 6; $i >= 0; $i--) {
    $tanggal = (new DateTimeImmutable('today'))->modify("-$i days");
    $key = $tanggal->format('Y-m-d');
    $grafikMingguan[] = [
        'tanggal' => $tanggal->format('d M'),
        'pendapatan' => $pendapatanHarian[$key] ?? 0,
    ];
}
$puncakGrafik = max(array_column($grafikMingguan, 'pendapatan')) ?: 1;

$pelangganTerbaik = $conn->query(
    "SELECT p.id_pelanggan, p.nama_pelanggan, COUNT(t.id_transaksi) AS total_transaksi,
            COALESCE(SUM(t.total_belanja), 0) AS total_belanja
     FROM tbl_pelanggan p
     LEFT JOIN tbl_transaksi t ON t.id_pelanggan = p.id_pelanggan
     GROUP BY p.id_pelanggan, p.nama_pelanggan
     ORDER BY total_belanja DESC, p.nama_pelanggan
     LIMIT 5"
);

$transaksiTerbaru = $conn->query(
    "SELECT t.id_transaksi, t.tanggal, t.total_belanja, t.metode_pembayaran,
            p.nama_pelanggan, p.kota
     FROM tbl_transaksi t
     INNER JOIN tbl_pelanggan p ON p.id_pelanggan = t.id_pelanggan
     ORDER BY t.tanggal DESC, t.id_transaksi DESC
     LIMIT 6"
);

$produkTerlaris = $conn->query(
    "SELECT nama_produk, SUM(jumlah) AS total_terjual, SUM(jumlah * harga) AS total_pendapatan
     FROM tbl_detail_transaksi
     GROUP BY nama_produk
     ORDER BY total_terjual DESC, total_pendapatan DESC
     LIMIT 5"
);

$kasirAktif = (int)$conn->query("SELECT COUNT(*) AS n FROM tbl_user WHERE role = 'kasir'")->fetch_assoc()['n'];
$namaAdmin = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin';
$namaBulan = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];
$bulanIni = $namaBulan[(int)date('n')] . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overview - TokoKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="overview-page">
<?php include 'navbar.php'; ?>

<main class="overview-container">
    <?php if (isset($_GET['error']) && $_GET['error'] === 'akses'): ?>
        <div class="alert alert-danger"><i class="bi bi-shield-x"></i> Anda tidak punya akses ke halaman tersebut.</div>
    <?php endif; ?>

    <header class="overview-welcome">
        <div>
            <span class="overview-eyebrow"><i class="bi bi-grid-1x2-fill"></i> RINGKASAN TOKO</span>
            <h1>Overview</h1>
            <p>Selamat datang kembali, <strong><?= htmlspecialchars($namaAdmin) ?></strong>. Berikut ringkasan tokomu.</p>
        </div>
        <div class="overview-date"><i class="bi bi-calendar3"></i> <?= htmlspecialchars(date('d') . ' ' . $namaBulan[(int)date('n')] . ' ' . date('Y')) ?></div>
    </header>

    <section class="overview-metrics" aria-label="Statistik toko">
        <article class="metric-card metric-revenue">
            <div class="metric-top"><span>Pendapatan bulan ini</span><span class="metric-icon"><i class="bi bi-wallet2"></i></span></div>
            <strong class="metric-value"><?= rupiah($pendapatanBulanIni) ?></strong>
            <div class="metric-foot">
                <?php if ($perubahanPendapatan !== null): ?>
                    <span class="metric-change <?= $perubahanPendapatan >= 0 ? 'is-positive' : 'is-negative' ?>">
                        <i class="bi <?= $perubahanPendapatan >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' ?>"></i>
                        <?= number_format(abs($perubahanPendapatan), 1, ',', '.') ?>%
                    </span>
                    <span>vs bulan lalu</span>
                <?php else: ?>
                    <span>Belum ada data bulan lalu</span>
                <?php endif; ?>
            </div>
        </article>

        <article class="metric-card metric-orders">
            <div class="metric-top"><span>Transaksi bulan ini</span><span class="metric-icon"><i class="bi bi-bag-check"></i></span></div>
            <strong class="metric-value"><?= number_format((int)$stats['transaksi_bulan_ini'], 0, ',', '.') ?></strong>
            <div class="metric-foot"><span>Dari <?= number_format((int)$stats['total_transaksi'], 0, ',', '.') ?> transaksi keseluruhan</span></div>
        </article>

        <article class="metric-card metric-customers">
            <div class="metric-top"><span>Total pelanggan</span><span class="metric-icon"><i class="bi bi-people"></i></span></div>
            <strong class="metric-value"><?= number_format($totalPelanggan, 0, ',', '.') ?></strong>
            <div class="metric-foot"><a href="pelanggan.php">Kelola pelanggan <i class="bi bi-arrow-right"></i></a></div>
        </article>

        <article class="metric-card metric-branches">
            <div class="metric-top"><span>Cabang toko</span><span class="metric-icon"><i class="bi bi-shop"></i></span></div>
            <strong class="metric-value"><?= number_format($totalCabang, 0, ',', '.') ?></strong>
            <div class="metric-foot"><span><?= number_format($kasirAktif, 0, ',', '.') ?> kasir terdaftar</span></div>
        </article>
    </section>

    <section class="overview-grid">
        <article class="overview-panel sales-panel">
            <div class="panel-heading">
                <div>
                    <span class="overview-eyebrow">PERFORMA</span>
                    <h2>Ringkasan pendapatan</h2>
                </div>
                <span class="panel-period">7 hari terakhir</span>
            </div>
            <div class="sales-total"><?= rupiah(array_sum(array_column($grafikMingguan, 'pendapatan'))) ?></div>
            <div class="sales-chart" role="img" aria-label="Grafik pendapatan tujuh hari terakhir">
                <?php foreach ($grafikMingguan as $hari): ?>
                    <?php $tinggi = $hari['pendapatan'] > 0 ? max(8, ($hari['pendapatan'] / $puncakGrafik) * 100) : 3; ?>
                    <div class="chart-day" title="<?= htmlspecialchars($hari['tanggal'] . ': ' . rupiah($hari['pendapatan'])) ?>">
                        <div class="chart-track"><span class="chart-bar <?= $hari['pendapatan'] > 0 ? 'has-sales' : '' ?>" style="height: <?= number_format($tinggi, 2, '.', '') ?>%"></span></div>
                        <span class="chart-label"><?= htmlspecialchars($hari['tanggal']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="overview-panel quick-panel">
            <div class="panel-heading">
                <div>
                    <span class="overview-eyebrow">AKSES CEPAT</span>
                    <h2>Kelola toko</h2>
                </div>
            </div>
            <a class="quick-link" href="transaksi.php">
                <span class="quick-link-icon quick-orange"><i class="bi bi-credit-card"></i></span>
                <span><strong>Pembayaran</strong><small>Buat transaksi baru</small></span>
                <i class="bi bi-chevron-right"></i>
            </a>
            <a class="quick-link" href="pelanggan.php">
                <span class="quick-link-icon quick-green"><i class="bi bi-person-plus"></i></span>
                <span><strong>Tambah pelanggan</strong><small>Kelola daftar pelanggan</small></span>
                <i class="bi bi-chevron-right"></i>
            </a>
            <a class="quick-link" href="laporan.php">
                <span class="quick-link-icon quick-blue"><i class="bi bi-file-earmark-bar-graph"></i></span>
                <span><strong>Laporan</strong><small>Lihat ringkasan transaksi</small></span>
                <i class="bi bi-chevron-right"></i>
            </a>
        </article>
    </section>

    <section class="overview-grid lower-grid">
        <article class="overview-panel latest-panel">
            <div class="panel-heading">
                <div>
                    <span class="overview-eyebrow">AKTIVITAS TOKO</span>
                    <h2>Transaksi terbaru</h2>
                </div>
                <a class="panel-view-all" href="transaksi.php">Lihat semua <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table overview-table align-middle mb-0">
                    <thead><tr><th>Pelanggan</th><th>Tanggal</th><th>Pembayaran</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                    <?php if ($transaksiTerbaru && $transaksiTerbaru->num_rows > 0): ?>
                        <?php while ($t = $transaksiTerbaru->fetch_assoc()): ?>
                            <tr>
                                <td><span class="customer-name"><?= htmlspecialchars($t['nama_pelanggan']) ?></span><small><?= htmlspecialchars($t['kota'] ?? '') ?></small></td>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($t['tanggal']))) ?></td>
                                <td><span class="payment-badge"><?= htmlspecialchars(strtoupper($t['metode_pembayaran'] ?? 'tunai')) ?></span></td>
                                <td class="text-end fw-semibold"><?= rupiah($t['total_belanja']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>

        <article class="overview-panel ranking-panel">
            <div class="panel-heading">
                <div>
                    <span class="overview-eyebrow">FAVORIT TOKO</span>
                    <h2>Produk terlaris</h2>
                </div>
            </div>
            <?php if ($produkTerlaris && $produkTerlaris->num_rows > 0): ?>
                <div class="ranking-list">
                    <?php $nomor = 0; while ($produk = $produkTerlaris->fetch_assoc()): $nomor++; ?>
                        <div class="ranking-item">
                            <span class="ranking-number"><?= $nomor ?></span>
                            <span class="ranking-product"><strong><?= htmlspecialchars($produk['nama_produk']) ?></strong><small><?= number_format((int)$produk['total_terjual'], 0, ',', '.') ?> terjual</small></span>
                            <span class="ranking-total"><?= rupiah($produk['total_pendapatan']) ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="ranking-empty"><i class="bi bi-box-seam"></i><span>Data produk muncul setelah transaksi berisi barang dicatat.</span></div>
            <?php endif; ?>

            <div class="top-customers-heading"><i class="bi bi-trophy"></i> Pelanggan terbaik</div>
            <div class="customer-list">
                <?php if ($pelangganTerbaik && $pelangganTerbaik->num_rows > 0): ?>
                    <?php while ($p = $pelangganTerbaik->fetch_assoc()): ?>
                        <div class="customer-rank">
                            <span class="customer-avatar"><?= htmlspecialchars(strtoupper(substr($p['nama_pelanggan'], 0, 1))) ?></span>
                            <span class="ranking-product"><strong><?= htmlspecialchars($p['nama_pelanggan']) ?></strong><small><?= (int)$p['total_transaksi'] ?> transaksi</small></span>
                            <span class="ranking-total"><?= rupiah($p['total_belanja']) ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted small mb-0">Belum ada data pelanggan.</p>
                <?php endif; ?>
            </div>
        </article>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
