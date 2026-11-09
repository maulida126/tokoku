<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "db_tokoku";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

function rupiah($n) {
    return "Rp " . number_format($n, 0, ',', '.');
}

/**
 * Bersihkan semua result set dari stored procedure
 * supaya tidak kena error "Commands out of sync"
 */
function clearResults($conn) {
    while ($conn->more_results() && $conn->next_result()) {;}
}

// Session & auto-protect
require_once __DIR__ . '/auth.php';

$halamanBebas = ['login.php', 'logout.php'];
$halamanIni   = basename($_SERVER['PHP_SELF']);
if (!in_array($halamanIni, $halamanBebas)) {
    cekLogin();
}
?>