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
?>