<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "db_tokoku";

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log("TokoKu database connection failed: " . $e->getMessage());
    http_response_code(503);
    exit(
        '<!DOCTYPE html><html lang="id"><meta charset="UTF-8">' .
        '<meta name="viewport" content="width=device-width, initial-scale=1.0">' .
        '<title>Database tidak tersedia</title>' .
        '<body style="font-family: sans-serif; max-width: 640px; margin: 3rem auto; padding: 0 1rem;">' .
        '<h1>Database belum tersedia</h1>' .
        '<p>TokoKu tidak dapat terhubung ke MySQL di ' . htmlspecialchars($host, ENT_QUOTES, 'UTF-8') .
        ':3306. Buka XAMPP Control Panel, lalu klik <strong>Start</strong> pada MySQL.</p>' .
        '<p>Jika MySQL sudah berjalan, pastikan port MySQL di <code>config.php</code> sama dengan port pada XAMPP.</p>' .
        '</body></html>'
    );
}

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

$halamanBebas = ['login.php', 'logout.php', 'daftar.php'];
$halamanIni   = basename($_SERVER['PHP_SELF']);
if (!in_array($halamanIni, $halamanBebas)) {
    cekLogin();

    $role = $_SESSION['role'] ?? '';
    if ($role === '' || $role === 'user') {
        $role = 'kasir';
        $_SESSION['role'] = $role;
    }

    $aksesRole = [
        'admin' => null,
        'kasir' => ['pelanggan.php', 'transaksi.php', 'detail_transaksi.php', 'produk.php', 'stok_produk.php'],
        'pelanggan' => ['belanja.php', 'detail_transaksi.php', 'stok_produk.php'],
    ];

    if (!array_key_exists($role, $aksesRole)) {
        http_response_code(403);
        exit('Role akun tidak valid. Hubungi admin.');
    }

    if ($aksesRole[$role] !== null && !in_array($halamanIni, $aksesRole[$role], true)) {
        header("Location: " . halamanBerandaRole() . "?error=akses");
        exit;
    }

    if ($role === 'pelanggan' && empty($_SESSION['id_pelanggan'])) {
        http_response_code(403);
        exit('Akun pelanggan belum terhubung ke data pelanggan. Hubungi admin.');
    }
}
?>