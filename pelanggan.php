<?php
require 'config.php';
$pesan = "";
$isAdmin = $_SESSION['role'] === 'admin';

// ===== 1. TAMBAH (via SP) =====
if (isset($_POST['tambah'])) {
    if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $pesan = "<div class='alert alert-danger'>Form sudah kedaluwarsa. Muat ulang halaman lalu coba kembali.</div>";
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $kota = trim($_POST['kota'] ?? '');
        if ($nama === '' || $kota === '') {
            $pesan = "<div class='alert alert-danger'>Nama dan kota wajib diisi.</div>";
        } else {
            $stmt = $conn->prepare("CALL TambahPelanggan(?, ?)");
            $stmt->bind_param("ss", $nama, $kota);
            if ($stmt->execute()) {
                $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle'></i> Pelanggan berhasil ditambahkan!</div>";
            }
            $stmt->close();
            clearResults($conn);
        }
    }
}

// ===== 2. HAPUS =====
if (isset($_POST['hapus']) && $isAdmin) {
    if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $pesan = "<div class='alert alert-danger'>Form sudah kedaluwarsa. Muat ulang halaman lalu coba kembali.</div>";
    } else {
        $id = filter_var($_POST['hapus'], FILTER_VALIDATE_INT);
        if ($id) {
            try {
                $stmt = $conn->prepare("DELETE FROM tbl_pelanggan WHERE id_pelanggan = ?");
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle'></i> Pelanggan dihapus.</div>";
                }
                $stmt->close();
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() !== 1451) {
                    throw $e;
                }
                $pesan = "<div class='alert alert-danger'><i class='bi bi-x-circle'></i> Pelanggan tidak dapat dihapus karena memiliki riwayat transaksi.</div>";
            }
        }
    }
}

$keyword = trim($_GET['q'] ?? '');
$jumlah = (int)$conn->query("SELECT COUNT(*) AS n FROM tbl_pelanggan")->fetch_assoc()['n'];
$sql = "SELECT p.id_pelanggan, p.nama_pelanggan, p.kota,
               COUNT(t.id_transaksi) AS total_transaksi,
               COALESCE(SUM(t.total_belanja), 0) AS total_belanja
        FROM tbl_pelanggan p
        LEFT JOIN tbl_transaksi t ON t.id_pelanggan = p.id_pelanggan";
if ($keyword !== '') {
    $cari = '%' . $keyword . '%';
    $stmt = $conn->prepare(
        $sql . " WHERE p.nama_pelanggan LIKE ? OR p.kota LIKE ? OR CAST(p.id_pelanggan AS CHAR) LIKE ?
        GROUP BY p.id_pelanggan, p.nama_pelanggan, p.kota
        ORDER BY p.id_pelanggan DESC"
    );
    $stmt->bind_param("sss", $cari, $cari, $cari);
} else {
    $stmt = $conn->prepare(
        $sql . " GROUP BY p.id_pelanggan, p.nama_pelanggan, p.kota
        ORDER BY p.id_pelanggan DESC"
    );
}
$stmt->execute();
$resultPelanggan = $stmt->get_result();
$rows = [];
while ($row = $resultPelanggan->fetch_assoc()) {
    $rows[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelanggan - TokoKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="management-page">
<?php include 'navbar.php'; ?>

<main class="management-container">
    <?php if ($pesan !== ''): ?><div class="management-alert"><?= $pesan ?></div><?php endif; ?>
    <header class="management-heading">
        <div>
            <span class="management-eyebrow"><i class="bi bi-people"></i> DATA TOKO</span>
            <h1>Pelanggan</h1>
            <p>Kelola informasi dan riwayat belanja pelanggan TokoKu.</p>
        </div>
        <span class="management-count"><i class="bi bi-person-check"></i> <?= number_format($jumlah, 0, ',', '.') ?> pelanggan</span>
    </header>

    <section class="management-summary">
        <span class="management-summary-icon"><i class="bi bi-person-lines-fill"></i></span>
        <span><small>Jumlah pelanggan terdaftar</small><strong><?= number_format($jumlah, 0, ',', '.') ?></strong></span>
    </section>

    <section class="management-panel add-customer-panel">
        <div class="management-panel-heading"><span class="management-panel-icon"><i class="bi bi-person-plus"></i></span><div><h2>Tambah pelanggan</h2><p>Masukkan data pelanggan baru untuk memulai transaksi.</p></div></div>
        <form class="customer-add-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <label><span>Nama pelanggan</span><input type="text" name="nama" class="form-control" maxlength="100" placeholder="Contoh: Budi Santoso" required></label>
            <label><span>Kota</span><input type="text" name="kota" class="form-control" maxlength="50" placeholder="Contoh: Ponorogo" required></label>
            <button name="tambah" class="management-primary-button"><i class="bi bi-plus-lg"></i> Simpan pelanggan</button>
        </form>
    </section>

    <section class="management-panel customer-list-panel">
        <div class="management-panel-heading">
            <span class="management-panel-icon"><i class="bi bi-card-list"></i></span>
            <div><h2>Daftar pelanggan</h2><p>Informasi dan ringkasan aktivitas belanja.</p></div>
        </div>
        <form class="management-search" method="get">
            <i class="bi bi-search"></i>
            <input type="search" name="q" value="<?= htmlspecialchars($keyword) ?>" placeholder="Cari nama, kota, atau ID pelanggan">
            <?php if ($keyword !== ''): ?><a href="pelanggan.php" class="search-reset">Reset</a><?php endif; ?>
            <button aria-label="Cari pelanggan"><i class="bi bi-arrow-right"></i></button>
        </form>

        <div class="management-table-wrap">
            <table class="table management-table align-middle mb-0">
                <thead><tr><th>Pelanggan</th><th>Kota</th><th class="text-center">Transaksi</th><th class="text-end">Total belanja</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                <?php if ($rows): ?>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><span class="management-person"><span class="management-avatar"><?= htmlspecialchars(strtoupper(substr($row['nama_pelanggan'] ?? '?', 0, 1))) ?></span><span><strong><?= htmlspecialchars($row['nama_pelanggan'] ?? 'Pelanggan') ?></strong><small>ID #<?= (int)$row['id_pelanggan'] ?></small></span></span></td>
                        <td><span class="management-city"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($row['kota'] ?? '-') ?></span></td>
                        <td class="text-center"><span class="management-pill"><?= (int)$row['total_transaksi'] ?></span></td>
                        <td class="text-end management-money"><?= rupiah($row['total_belanja']) ?></td>
                        <td class="text-end">
                            <a href="transaksi.php?pelanggan=<?= (int)$row['id_pelanggan'] ?>" class="management-action" title="Lihat transaksi pelanggan"><i class="bi bi-receipt"></i></a>
                            <?php if ($isAdmin): ?>
                                <form method="post" class="d-inline" onsubmit="return confirm('Hapus pelanggan ini?')">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                    <button name="hapus" value="<?= (int)$row['id_pelanggan'] ?>" class="management-action is-danger" title="Hapus pelanggan"><i class="bi bi-trash"></i></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5"><div class="management-empty"><i class="bi bi-person-exclamation"></i><strong>Pelanggan tidak ditemukan</strong><span>Coba kata kunci lain atau tambahkan pelanggan baru.</span></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>