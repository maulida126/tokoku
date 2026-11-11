<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function cekLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function cekRole($role) {
    cekLogin();
    if ($_SESSION['role'] !== $role) {
        header("Location: index.php?error=akses");
        exit;
    }
}

function isLogin() {
    return isset($_SESSION['user_id']);
}

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

function halamanBerandaRole() {
    if (($_SESSION['role'] ?? '') === 'pelanggan') {
        return 'belanja.php';
    }

    return ($_SESSION['role'] ?? '') === 'kasir' ? 'transaksi.php' : 'index.php';
}
?>