<?php
require_once 'auth.php';
$role = $_SESSION['role'] ?? '';
$halamanAktif = basename($_SERVER['PHP_SELF']);
$namaPengguna = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Pengguna';

if ($role === 'pelanggan') {
    $tautanNavigasi = [
        ['belanja.php', 'bi-bag', 'Belanja & Pesanan'],
    ];
    $tautanUtama = 'belanja.php';
} elseif ($role === 'kasir') {
    $tautanNavigasi = [
        ['transaksi.php', 'bi-credit-card', 'Pembayaran'],
        ['produk.php', 'bi-box-seam', 'Produk & Stok'],
        ['pelanggan.php', 'bi-person-plus', 'Tambah Pelanggan'],
    ];
    $tautanUtama = 'transaksi.php';
} else {
    $tautanNavigasi = [
        ['index.php', 'bi-speedometer2', 'Dashboard'],
        ['produk.php', 'bi-box-seam', 'Produk & Stok'],
        ['pelanggan.php', 'bi-people', 'Pelanggan'],
        ['transaksi.php', 'bi-cart', 'Transaksi'],
        ['laporan.php', 'bi-file-earmark-text', 'Laporan'],
        ['pengguna.php', 'bi-person-gear', 'Pengguna'],
    ];
    $tautanUtama = 'index.php';
}
?>
<button class="sidebar-toggle" type="button" aria-label="Buka menu" aria-controls="appSidebar" aria-expanded="false">
    <i class="bi bi-list"></i>
</button>
<button class="sidebar-backdrop" type="button" aria-label="Tutup menu"></button>
<aside class="app-sidebar" id="appSidebar">
    <a class="sidebar-brand" href="<?= htmlspecialchars($tautanUtama) ?>">
        <span class="sidebar-brand-icon"><i class="bi bi-shop-window"></i></span>
        <span>TokoKu<small>Manajemen Toko</small></span>
    </a>

    <div class="sidebar-section-label">MENU</div>
    <nav class="sidebar-navigation" aria-label="Navigasi utama">
        <?php foreach ($tautanNavigasi as [$url, $ikon, $label]): ?>
            <a class="sidebar-link <?= $halamanAktif === $url ? 'is-active' : '' ?>" href="<?= htmlspecialchars($url) ?>" <?= $halamanAktif === $url ? 'aria-current="page"' : '' ?>>
                <i class="bi <?= htmlspecialchars($ikon) ?>"></i><span><?= htmlspecialchars($label) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php if (isLogin()): ?>
    <div class="sidebar-account">
        <span class="sidebar-account-icon"><i class="bi bi-person-fill"></i></span>
        <span class="sidebar-account-info"><strong><?= htmlspecialchars($namaPengguna) ?></strong><small><?= htmlspecialchars(ucfirst($role)) ?></small></span>
        <a class="sidebar-logout" href="logout.php" aria-label="Logout" title="Logout"><i class="bi bi-box-arrow-right"></i></a>
    </div>
    <?php endif; ?>
</aside>
<script>
(() => {
    const body = document.body;
    const toggle = document.querySelector('.sidebar-toggle');
    const backdrop = document.querySelector('.sidebar-backdrop');
    const closeMenu = () => {
        body.classList.remove('sidebar-open');
        toggle.setAttribute('aria-expanded', 'false');
    };

    toggle.addEventListener('click', () => {
        const isOpen = body.classList.toggle('sidebar-open');
        toggle.setAttribute('aria-expanded', String(isOpen));
    });
    backdrop.addEventListener('click', closeMenu);
    document.querySelectorAll('.sidebar-link').forEach((link) => link.addEventListener('click', closeMenu));
})();
</script>
