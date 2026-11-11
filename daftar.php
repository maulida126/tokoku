<?php
require 'config.php';

if (isLogin()) {
    header("Location: " . ($_SESSION['role'] === 'pelanggan' ? 'belanja.php' : 'index.php'));
    exit;
}

$error = '';
$nama = '';
$kota = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $kota = trim($_POST['kota'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? null;

    if (!validCsrfToken($token)) {
        http_response_code(400);
        $error = 'Form sudah kedaluwarsa. Muat ulang halaman lalu coba kembali.';
    } elseif ($nama === '' || $kota === '' || $username === '' || $password === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $error = 'Username harus terdiri dari 3 sampai 50 karakter.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } else {
        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare("INSERT INTO tbl_pelanggan (nama_pelanggan, kota) VALUES (?, ?)");
            $stmt->bind_param("ss", $nama, $kota);
            $stmt->execute();
            $idPelanggan = $conn->insert_id;
            $stmt->close();

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'pelanggan';
            $stmt = $conn->prepare("INSERT INTO tbl_user (username, password, nama, role, id_pelanggan) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $username, $hash, $nama, $role, $idPelanggan);
            $stmt->execute();
            $idUser = $conn->insert_id;
            $stmt->close();

            $conn->commit();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $idUser;
            $_SESSION['username'] = $username;
            $_SESSION['nama_lengkap'] = $nama;
            $_SESSION['role'] = $role;
            $_SESSION['id_pelanggan'] = $idPelanggan;

            header("Location: belanja.php");
            exit;
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            if ($e->getCode() === 1062) {
                $error = 'Username sudah digunakan. Silakan pilih username lain.';
            } else {
                throw $e;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pelanggan - TokoKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<div class="card auth-card register-card">
    <div class="card-header auth-header text-center py-4">
        <i class="bi bi-person-plus fs-1"></i>
        <h3 class="mb-0">Daftar Pelanggan</h3>
    </div>
    <div class="card-body p-4">
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama lengkap</label>
                <input id="nama" name="nama" class="form-control" maxlength="100" value="<?= htmlspecialchars($nama) ?>" required>
            </div>
            <div class="mb-3">
                <label for="kota" class="form-label">Kota</label>
                <input id="kota" name="kota" class="form-control" maxlength="50" value="<?= htmlspecialchars($kota) ?>" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input id="username" name="username" class="form-control" minlength="3" maxlength="50" value="<?= htmlspecialchars($username) ?>" autocomplete="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password (minimal 8 karakter)</label>
                <input id="password" type="password" name="password" class="form-control" minlength="8" autocomplete="new-password" required>
            </div>
            <button class="btn auth-submit w-100"><i class="bi bi-person-check"></i> Daftar dan mulai belanja</button>
        </form>
        <p class="text-center mt-3 mb-0">Sudah punya akun? <a href="login.php">Login</a></p>
    </div>
</div>
</body>
</html>
