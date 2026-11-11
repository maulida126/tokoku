<?php
require 'config.php';

$error = '';
$success = '';
$nama = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $error = 'Form sudah kedaluwarsa. Muat ulang halaman lalu coba kembali.';
    } elseif ($nama === '' || $username === '' || $password === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $error = 'Username harus terdiri dari 3 sampai 50 karakter.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'kasir';
        $stmt = $conn->prepare("INSERT INTO tbl_user (nama, username, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $username, $hash, $role);
        try {
            $stmt->execute();
            $success = 'Akun kasir berhasil dibuat.';
            $nama = '';
            $username = '';
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                $error = 'Username sudah digunakan. Silakan pilih username lain.';
            } else {
                throw $e;
            }
        } finally {
            $stmt->close();
        }
    }
}

$users = $conn->query("SELECT nama, username, role FROM tbl_user ORDER BY role, nama");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengguna - TokoKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<main class="container py-4">
    <h3 class="mb-3"><i class="bi bi-person-gear"></i> Pengguna</h3>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success !== ''): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Tambah akun kasir</strong></div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <div class="col-md-4">
                    <label for="nama" class="form-label">Nama lengkap</label>
                    <input id="nama" name="nama" class="form-control" maxlength="100" value="<?= htmlspecialchars($nama) ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="username" class="form-label">Username</label>
                    <input id="username" name="username" class="form-control" minlength="3" maxlength="50" value="<?= htmlspecialchars($username) ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="password" class="form-label">Password (minimal 8 karakter)</label>
                    <input id="password" type="password" name="password" class="form-control" minlength="8" required>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary"><i class="bi bi-person-plus"></i> Buat akun kasir</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Daftar pengguna</strong></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Nama</th><th>Username</th><th>Role</th></tr></thead>
                <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['nama'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($user['role']) ?></span></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
