<?php require_once 'auth.php'; ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-shop-window"></i> TokoKu</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="pelanggan.php"><i class="bi bi-people"></i> Pelanggan</a></li>
                <li class="nav-item"><a class="nav-link" href="transaksi.php"><i class="bi bi-cart"></i> Transaksi</a></li>
                <li class="nav-item"><a class="nav-link" href="laporan.php"><i class="bi bi-file-earmark-text"></i> Laporan</a></li>

                <?php if (isLogin()): ?>
                <li class="nav-item dropdown ms-lg-3">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                        <span class="badge bg-primary rounded-circle p-2">
                            <i class="bi bi-person-fill"></i>
                        </span>
                        <span><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li class="px-3 py-2">
                            <small class="text-muted d-block">Login sebagai</small>
                            <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                            <span class="badge bg-info text-dark ms-1"><?= $_SESSION['role'] ?></span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>