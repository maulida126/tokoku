<?php
require 'config.php';

if (isLogin()) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === '' || $password === '') {
        $error = "Username dan password wajib diisi!";
    } else {
        $stmt = $conn->prepare("SELECT * FROM tbl_user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']      = $user['id_user'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role']         = $user['role'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Username atau password salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TokoKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0,0,0,.25);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 30px;
            text-align: center;
        }
        .login-header i { font-size: 3rem; }
        .login-body { padding: 30px; background: #fff; }
        .form-control { border-radius: 10px; padding: 12px; }
        .btn-login {
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-login:hover { opacity: .9; }
    </style>
</head>
<body>

<div class="card login-card">
    <div class="login-header">
        <i class="bi bi-shop-window"></i>
        <h3 class="mt-2 mb-0 fw-bold">TokoKu</h3>
        <p class="mb-0 small opacity-75">Silakan login untuk melanjutkan</p>
    </div>
    <div class="login-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle"></i> <?= $error ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['logout'])): ?>
            <div class="alert alert-success py-2"><i class="bi bi-check-circle"></i> Anda berhasil logout.</div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" autofocus required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="pass" class="form-control" placeholder="Masukkan password" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePass()">
                        <i class="bi bi-eye" id="eye"></i>
                    </button>
                </div>
            </div>
            <button class="btn btn-primary btn-login w-100 text-white">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
        </form>

        <hr class="my-4">
        <p class="text-center text-muted small mb-0">
            <i class="bi bi-info-circle"></i> Default: <code>admin</code> / <code>admin123</code>
        </p>
    </div>
</div>

<script>
function togglePass() {
    const p = document.getElementById('pass');
    const eye = document.getElementById('eye');
    if (p.type === 'password') {
        p.type = 'text';
        eye.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        p.type = 'password';
        eye.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>
</body>
</html>