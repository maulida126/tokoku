<?php
require 'config.php';
$pesan = "";

// ===== 1. TAMBAH (via SP) =====
if (isset($_POST['tambah'])) {
    $stmt = $conn->prepare("CALL TambahPelanggan(?, ?)");
    $stmt->bind_param("ss", $_POST['nama'], $_POST['kota']);
    if ($stmt->execute()) {
        $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle'></i> Pelanggan berhasil ditambahkan!</div>";
    }
    $stmt->close();
    clearResults($conn);
}

// ===== 2. HAPUS =====
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    try {
        $conn->query("DELETE FROM tbl_pelanggan WHERE id_pelanggan=$id");
        $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle'></i> Pelanggan dihapus.</div>";
    } catch (Exception $e) {
        $pesan = "<div class='alert alert-danger'><i class='bi bi-x-circle'></i> Gagal hapus: pelanggan masih punya transaksi.</div>";
    }
}

// ===== 3. HITUNG TOTAL (OUT param) — panggil DULU =====
$conn->query("CALL HitungPelanggan(@jumlah)");
$jumlah = $conn->query("SELECT @jumlah AS n")->fetch_assoc()['n'];
clearResults($conn);

// ===== 4. AMBIL DATA =====
$keyword = $_GET['q'] ?? '';
$rows = [];

if ($keyword !== '' && is_numeric($keyword)) {
    $stmt = $conn->prepare("CALL CariPelanggan(?)");
    $stmt->bind_param("i", $keyword);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    $stmt->close();
    clearResults($conn);
} else {
    $res = $conn->query("CALL TampilPelanggan()");
    if ($res) {
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $res->free();
    }
    clearResults($conn);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pelanggan - TokoKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="bi bi-people"></i> Data Pelanggan</h3>
        <span class="badge bg-primary fs-6">Total: <?= $jumlah ?></span>
    </div>

    <?= $pesan ?>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form class="row g-2" method="post">
                <div class="col-md-4">
                    <input type="text" name="nama" class="form-control" placeholder="Nama Pelanggan" required>
                </div>
                <div class="col-md-4">
                    <input type="text" name="kota" class="form-control" placeholder="Kota" required>
                </div>
                <div class="col-md-4">
                    <button name="tambah" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form class="mb-3" method="get">
                <div class="input-group">
                    <input type="number" name="q" class="form-control" placeholder="Cari ID pelanggan..." value="<?= htmlspecialchars($keyword) ?>">
                    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Cari</button>
                    <a href="pelanggan.php" class="btn btn-outline-dark">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr><th>ID</th><th>Nama</th><th>Kota</th><th class="text-center">Aksi</th></tr>
                    </thead>
                    <tbody>
                    <?php if (count($rows) > 0): ?>
                        <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= $row['id_pelanggan'] ?></td>
                            <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($row['kota']) ?></span></td>
                            <td class="text-center">
                                <a href="transaksi.php?pelanggan=<?= $row['id_pelanggan'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-cart"></i></a>
                                <a href="?hapus=<?= $row['id_pelanggan'] ?>" onclick="return confirm('Hapus pelanggan ini?')" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada data.</td></tr>
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