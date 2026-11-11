<?php
require 'config.php';

$pesan = '';
$jenisPesan = 'success';
$isAdmin = $_SESSION['role'] === 'admin';
$namaProdukForm = '';
$hargaProdukForm = '';
$stokProdukForm = '';

function simpanGambarProduk(array $file, string &$error): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $error = 'Gambar gagal diunggah. Coba pilih file kembali.';
        return null;
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'] ?? '')) {
        $error = 'Ukuran gambar maksimal 5 MB.';
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $formatGambar = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($formatGambar[$mime]) || @getimagesize($file['tmp_name']) === false) {
        $error = 'Format gambar harus JPG, PNG, atau WEBP.';
        return null;
    }

    $direktori = __DIR__ . '/assets/product-images';
    if (!is_dir($direktori) && !mkdir($direktori, 0755, true) && !is_dir($direktori)) {
        throw new RuntimeException('Folder penyimpanan gambar produk tidak dapat dibuat.');
    }
    $namaFile = bin2hex(random_bytes(16)) . '.' . $formatGambar[$mime];
    if (!move_uploaded_file($file['tmp_name'], $direktori . '/' . $namaFile)) {
        throw new RuntimeException('Gambar produk tidak dapat disimpan ke folder assets/product-images.');
    }
    return 'assets/product-images/' . $namaFile;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $pesan = 'Form sudah kedaluwarsa. Muat ulang halaman lalu coba kembali.';
        $jenisPesan = 'danger';
    } elseif (isset($_POST['tambah_produk']) && !$isAdmin) {
        http_response_code(403);
        $pesan = 'Hanya admin yang dapat menambahkan barang baru. Kasir hanya dapat menambah stok barang yang sudah ada.';
        $jenisPesan = 'danger';
    } elseif (isset($_POST['ubah_gambar']) && !$isAdmin) {
        http_response_code(403);
        $pesan = 'Hanya admin yang dapat mengubah gambar barang.';
        $jenisPesan = 'danger';
    } elseif (isset($_POST['tambah_produk'])) {
        $nama = trim($_POST['nama_produk'] ?? '');
        $namaProdukForm = $nama;
        $hargaProdukForm = trim((string)($_POST['harga'] ?? ''));
        $stokProdukForm = trim((string)($_POST['stok'] ?? ''));
        $harga = filter_var($_POST['harga'] ?? null, FILTER_VALIDATE_FLOAT);
        $stok = filter_var($_POST['stok'] ?? null, FILTER_VALIDATE_INT);

        if ($nama === '' || strlen($nama) > 100 || $harga === false || $harga <= 0 || $stok === false || $stok < 0 || $stok > 2147483647) {
            $pesan = 'Isi nama barang, harga di atas 0, dan stok awal 0 atau lebih dengan benar.';
            $jenisPesan = 'danger';
        } else {
            $gambar = simpanGambarProduk($_FILES['gambar_produk'] ?? [], $pesan);
            if ($gambar === null && $pesan !== '') {
                $jenisPesan = 'danger';
            } else {
                $stmt = $conn->prepare("INSERT INTO tbl_produk (nama_produk, harga, stok, gambar) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sdis", $nama, $harga, $stok, $gambar);
                try {
                    $stmt->execute();
                    header("Location: produk.php?status=ditambahkan");
                    exit;
                } catch (mysqli_sql_exception $e) {
                    if ($gambar !== null) {
                        unlink(__DIR__ . '/' . $gambar);
                    }
                    if ($e->getCode() !== 1062) {
                        throw $e;
                    }
                    $pesan = 'Nama barang sudah ada di katalog. Gunakan formulir Tambah Stok pada barang tersebut, atau masukkan nama yang berbeda jika itu barang baru.';
                    $jenisPesan = 'danger';
                } finally {
                    $stmt->close();
                }
            }
        }
    } elseif (isset($_POST['ubah_gambar'])) {
        $idProduk = filter_var($_POST['id_produk'] ?? null, FILTER_VALIDATE_INT);
        if (!$idProduk || $idProduk < 1) {
            $pesan = 'Barang yang dipilih tidak valid.';
            $jenisPesan = 'danger';
        } else {
            $gambar = simpanGambarProduk($_FILES['gambar_produk'] ?? [], $pesan);
            if ($gambar === null && $pesan === '') {
                $pesan = 'Pilih gambar barang terlebih dahulu.';
            }
            if ($gambar === null) {
                $jenisPesan = 'danger';
            } else {
                $stmt = $conn->prepare("SELECT gambar FROM tbl_produk WHERE id_produk = ? AND aktif = 1");
                $stmt->bind_param("i", $idProduk);
                $stmt->execute();
                $gambarLama = $stmt->get_result()->fetch_assoc()['gambar'] ?? null;
                $stmt->close();
                $stmt = $conn->prepare("UPDATE tbl_produk SET gambar = ? WHERE id_produk = ? AND aktif = 1");
                $stmt->bind_param("si", $gambar, $idProduk);
                try {
                    $stmt->execute();
                    if ($stmt->affected_rows !== 1) {
                        unlink(__DIR__ . '/' . $gambar);
                        $pesan = 'Gambar tidak dapat diperbarui. Periksa barang yang dipilih.';
                        $jenisPesan = 'danger';
                    } else {
                        if ($gambarLama
                            && str_starts_with($gambarLama, 'assets/product-images/')
                            && basename($gambarLama) === substr($gambarLama, strlen('assets/product-images/'))) {
                            $pathGambarLama = __DIR__ . '/' . $gambarLama;
                            if (is_file($pathGambarLama)) {
                                unlink($pathGambarLama);
                            }
                        }
                        header("Location: produk.php?status=gambar");
                        exit;
                    }
                } catch (Throwable $e) {
                    unlink(__DIR__ . '/' . $gambar);
                    throw $e;
                } finally {
                    $stmt->close();
                }
            }
        }
    } elseif (isset($_POST['tambah_stok'])) {
        $idProduk = filter_var($_POST['id_produk'] ?? null, FILTER_VALIDATE_INT);
        $jumlahTambah = filter_var($_POST['jumlah_tambah'] ?? null, FILTER_VALIDATE_INT);

        if (!$idProduk || $idProduk < 1 || $jumlahTambah === false || $jumlahTambah < 1 || $jumlahTambah > 2147483647) {
            $pesan = 'Masukkan jumlah stok tambahan minimal 1.';
            $jenisPesan = 'danger';
        } else {
            $stmt = $conn->prepare(
                "UPDATE tbl_produk
                 SET stok = stok + ?
                 WHERE id_produk = ? AND aktif = 1 AND stok <= 4294967295 - ?"
            );
            $stmt->bind_param("iii", $jumlahTambah, $idProduk, $jumlahTambah);
            $stmt->execute();
            if ($stmt->affected_rows === 1) {
                header("Location: produk.php?status=stok");
                exit;
            }
            $pesan = 'Stok tidak dapat ditambahkan. Periksa barang atau batas jumlah stok.';
            $jenisPesan = 'danger';
            $stmt->close();
        }
    }
}

if (($_GET['status'] ?? '') === 'ditambahkan') {
    $pesan = 'Barang baru berhasil ditambahkan ke katalog.';
} elseif (($_GET['status'] ?? '') === 'stok') {
    $pesan = 'Stok berhasil diperbarui.';
} elseif (($_GET['status'] ?? '') === 'gambar') {
    $pesan = 'Gambar barang berhasil diperbarui.';
}

$ringkasan = $conn->query(
    "SELECT COUNT(*) AS total_barang,
            COALESCE(SUM(stok), 0) AS total_stok,
            SUM(CASE WHEN stok = 0 THEN 1 ELSE 0 END) AS stok_habis
     FROM tbl_produk
     WHERE aktif = 1"
)->fetch_assoc();
$produk = $conn->query(
    "SELECT id_produk, nama_produk, harga, stok, gambar
     FROM tbl_produk
     WHERE aktif = 1
     ORDER BY nama_produk"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk & Stok - TokoKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="management-page products-page">
<?php include 'navbar.php'; ?>
<main class="management-container">
    <header class="management-heading">
        <div>
            <span class="management-eyebrow"><i class="bi bi-box-seam"></i> INVENTARIS TOKO</span>
            <h1>Produk & stok</h1>
            <p><?= $isAdmin ? 'Kelola katalog barang dan pantau persediaan di gudang.' : 'Tambahkan stok untuk barang yang sudah ada di katalog.' ?></p>
        </div>
        <span class="management-count"><i class="bi bi-boxes"></i> <?= number_format((int)$ringkasan['total_barang'], 0, ',', '.') ?> barang</span>
    </header>

    <?php if ($pesan !== ''): ?>
        <div class="management-alert <?= $jenisPesan === 'success' ? 'is-success' : '' ?>"><?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>

    <section class="transaction-stats product-stats">
        <div><span class="transaction-stat-icon"><i class="bi bi-box-seam"></i></span><span><small>Barang aktif</small><strong><?= number_format((int)$ringkasan['total_barang'], 0, ',', '.') ?></strong></span></div>
        <div><span class="transaction-stat-icon is-green"><i class="bi bi-boxes"></i></span><span><small>Total persediaan</small><strong data-total-stock><?= number_format((int)$ringkasan['total_stok'], 0, ',', '.') ?> unit</strong></span></div>
        <div><span class="transaction-stat-icon is-orange"><i class="bi bi-exclamation-triangle"></i></span><span><small>Stok habis</small><strong data-empty-stock><?= number_format((int)$ringkasan['stok_habis'], 0, ',', '.') ?> barang</strong></span></div>
    </section>

    <?php if ($isAdmin): ?>
    <section class="management-panel">
        <div class="management-panel-heading">
            <span class="management-panel-icon"><i class="bi bi-plus-square"></i></span>
            <div><h2>Tambah barang</h2><p>Masukkan detail barang dan stok awal yang tersedia di gudang.</p></div>
        </div>
        <form method="post" enctype="multipart/form-data" class="product-add-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <label><span>Nama barang</span><input class="form-control" name="nama_produk" maxlength="100" value="<?= htmlspecialchars($namaProdukForm) ?>" placeholder="Contoh: Buku Tulis" required></label>
            <label><span>Harga jual (Rp)</span><input class="form-control" type="number" name="harga" min="1" step="0.01" value="<?= htmlspecialchars($hargaProdukForm) ?>" placeholder="5000" required></label>
            <label><span>Stok awal (unit)</span><input class="form-control" type="number" name="stok" min="0" step="1" value="<?= htmlspecialchars($stokProdukForm) ?>" placeholder="0" required></label>
            <label>
                <span>Foto asli barang (opsional, maks. 5 MB)</span>
                <input class="form-control product-photo-input" type="file" name="gambar_produk" accept="image/jpeg,image/png,image/webp" data-preview-target="new-product-photo-preview">
                <span class="product-photo-preview" id="new-product-photo-preview"><i class="bi bi-image"></i> Pratinjau foto barang</span>
                <small class="product-photo-hint">Unggah foto barangnya, bukan barcode atau QR.</small>
            </label>
            <button type="submit" class="management-primary-button" name="tambah_produk" value="1"><i class="bi bi-plus-lg"></i> Simpan barang</button>
        </form>
    </section>
    <?php endif; ?>

    <section class="management-panel product-inventory-panel">
        <div class="management-panel-heading">
            <span class="management-panel-icon"><i class="bi bi-clipboard-data"></i></span>
            <div><h2>Persediaan gudang</h2><p>Tambah stok ketika barang baru masuk gudang. Stok berkurang otomatis saat pembelian berhasil.</p></div>
        </div>
        <?php if ($isAdmin): ?>
        <label class="management-search product-inventory-search" for="productInventorySearch">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input id="productInventorySearch" type="search" placeholder="Cari produk berdasarkan nama atau ID..." autocomplete="off" aria-controls="productInventoryRows">
            <button type="button" class="product-search-clear" id="clearProductSearch" aria-label="Hapus pencarian" hidden><i class="bi bi-x-lg"></i></button>
        </label>
        <p class="product-search-status" id="productSearchStatus" aria-live="polite"></p>
        <?php endif; ?>
        <div class="management-table-wrap">
            <table class="table management-table align-middle mb-0">
                <thead><tr><th>Barang</th><th>Foto barang</th><?php if ($isAdmin): ?><th>Ganti foto</th><?php endif; ?><th class="text-end">Harga</th><th class="text-center">Stok tersedia</th><th>Tambah stok</th></tr></thead>
                <tbody id="productInventoryRows">
                <?php if ($produk->num_rows > 0): ?>
                    <?php while ($item = $produk->fetch_assoc()): ?>
                    <tr data-product-row data-product-name="<?= htmlspecialchars($item['nama_produk'], ENT_QUOTES, 'UTF-8') ?>" data-product-id="<?= (int)$item['id_produk'] ?>">
                        <td><span class="management-person"><span class="management-avatar"><i class="bi bi-box"></i></span><span><strong><?= htmlspecialchars($item['nama_produk']) ?></strong><small>ID #<?= (int)$item['id_produk'] ?></small></span></span></td>
                        <td><?php if (!empty($item['gambar'])): ?><img class="inventory-product-image" src="<?= htmlspecialchars($item['gambar'], ENT_QUOTES, 'UTF-8') ?>" alt="Foto barang: <?= htmlspecialchars($item['nama_produk'], ENT_QUOTES, 'UTF-8') ?>"><?php else: ?><span class="inventory-image-placeholder" title="Foto barang belum diunggah"><i class="bi bi-image"></i></span><?php endif; ?></td>
                        <?php if ($isAdmin): ?>
                        <td>
                            <form method="post" enctype="multipart/form-data" class="product-image-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                <input type="hidden" name="id_produk" value="<?= (int)$item['id_produk'] ?>">
                                <input class="product-photo-input" type="file" name="gambar_produk" accept="image/jpeg,image/png,image/webp" aria-label="Pilih foto asli barang <?= htmlspecialchars($item['nama_produk'], ENT_QUOTES, 'UTF-8') ?>, bukan barcode" data-preview-target="product-photo-preview-<?= (int)$item['id_produk'] ?>" required>
                                <button name="ubah_gambar" value="1" class="management-action" aria-label="Simpan foto <?= htmlspecialchars($item['nama_produk'], ENT_QUOTES, 'UTF-8') ?>" title="Simpan foto"><i class="bi bi-upload"></i></button>
                                <span class="product-photo-preview" id="product-photo-preview-<?= (int)$item['id_produk'] ?>"><i class="bi bi-image"></i> Pratinjau</span>
                            </form>
                        </td>
                        <?php endif; ?>
                        <td class="text-end management-money"><?= rupiah($item['harga']) ?></td>
                        <td class="text-center"><span class="stock-badge <?= (int)$item['stok'] === 0 ? 'is-empty' : '' ?>" data-stock-display="<?= (int)$item['id_produk'] ?>"><?= number_format((int)$item['stok'], 0, ',', '.') ?> unit</span></td>
                        <td>
                            <form method="post" class="restock-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                <input type="hidden" name="id_produk" value="<?= (int)$item['id_produk'] ?>">
                                <input type="number" name="jumlah_tambah" min="1" step="1" class="form-control" placeholder="Jumlah" aria-label="Jumlah stok tambahan untuk <?= htmlspecialchars($item['nama_produk'], ENT_QUOTES, 'UTF-8') ?>" required>
                                <button name="tambah_stok" class="management-action" aria-label="Tambah stok <?= htmlspecialchars($item['nama_produk'], ENT_QUOTES, 'UTF-8') ?>" title="Tambah stok"><i class="bi bi-plus-lg"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="<?= $isAdmin ? 6 : 5 ?>"><div class="management-empty"><i class="bi bi-box"></i><strong>Belum ada barang</strong><span>Tambahkan produk untuk mulai mengisi persediaan.</span></div></td></tr>
                <?php endif; ?>
                    <?php if ($isAdmin && $produk->num_rows > 0): ?>
                    <tr id="productSearchEmpty" hidden><td colspan="6"><div class="management-empty"><i class="bi bi-search"></i><strong>Produk tidak ditemukan</strong><span>Coba nama atau ID produk lain.</span></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<script src="stock-sync.js"></script>
<script>
const productSearch = document.getElementById('productInventorySearch');
if (productSearch) {
    const productRows = [...document.querySelectorAll('[data-product-row]')];
    const emptyResult = document.getElementById('productSearchEmpty');
    const status = document.getElementById('productSearchStatus');
    const clearButton = document.getElementById('clearProductSearch');
    const updateProductResults = () => {
        const query = productSearch.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
        productRows.forEach(row => {
            const matches = `${row.dataset.productName} ${row.dataset.productId}`.toLocaleLowerCase().includes(query);
            row.hidden = !matches;
            if (matches) visibleCount++;
        });
        emptyResult.hidden = visibleCount > 0;
        clearButton.hidden = query === '';
        status.textContent = query ? `${visibleCount} produk ditemukan` : `${visibleCount} produk tersedia`;
    };
    productSearch.addEventListener('input', updateProductResults);
    clearButton.addEventListener('click', () => {
        productSearch.value = '';
        updateProductResults();
        productSearch.focus();
    });
    updateProductResults();
}

document.querySelectorAll('.product-photo-input').forEach(input => {
    input.addEventListener('change', () => {
        const preview = document.getElementById(input.dataset.previewTarget);
        const file = input.files?.[0];
        if (!preview) return;
        preview.replaceChildren();
        if (!file) {
            const icon = document.createElement('i');
            icon.className = 'bi bi-image';
            preview.append(icon, document.createTextNode(' Pratinjau foto barang'));
            return;
        }
        if (!file.type.startsWith('image/')) {
            preview.textContent = 'File harus berupa foto barang';
            return;
        }
        const image = document.createElement('img');
        image.alt = 'Pratinjau foto barang yang dipilih';
        image.src = URL.createObjectURL(file);
        image.addEventListener('load', () => URL.revokeObjectURL(image.src), { once: true });
        image.addEventListener('error', () => {
            URL.revokeObjectURL(image.src);
            preview.textContent = 'Foto tidak dapat ditampilkan';
        }, { once: true });
        preview.append(image);
    });
});
</script>
</body>
</html>
