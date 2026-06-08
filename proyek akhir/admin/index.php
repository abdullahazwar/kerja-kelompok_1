<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Guard: hanya admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

// ---- Ambil statistik ----
$total_menu     = $conn->query("SELECT COUNT(*) AS c FROM menu")->fetch_assoc()['c'];
$total_anggota  = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='anggota'")->fetch_assoc()['c'];
$total_kategori = $conn->query("SELECT COUNT(*) AS c FROM kategori_menu")->fetch_assoc()['c'];
$menu_tersedia  = $conn->query("SELECT COUNT(*) AS c FROM menu WHERE tersedia=1")->fetch_assoc()['c'];

// ---- Ambil Statistik Penjualan ----
$total_pesanan    = $conn->query("SELECT COUNT(*) AS c FROM pesanan")->fetch_assoc()['c'];
$pesanan_selesai  = $conn->query("SELECT COUNT(*) AS c FROM pesanan WHERE status='selesai'")->fetch_assoc()['c'];
$total_pendapatan = $conn->query("SELECT SUM(total_harga) AS s FROM pesanan WHERE status='selesai'")->fetch_assoc()['s'] ?? 0;

// ---- Menu terbaru ----
$recent_menu = $conn->query("SELECT m.nama, m.harga, m.tersedia, k.nama AS kategori
                              FROM menu m JOIN kategori_menu k ON m.kategori_id=k.id
                              ORDER BY m.created_at DESC LIMIT 5");

// ---- Anggota terbaru ----
$recent_anggota = $conn->query("SELECT nama, email, created_at FROM users
                                WHERE role='anggota' ORDER BY created_at DESC LIMIT 5");

$pageTitle = 'Dashboard Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> – Kedai Kopi Sudut Senja</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-layout">

    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <!-- MAIN -->
    <div class="main-content">

        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <h3>📊 Dashboard</h3>
                <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?></p>
            </div>
            <div class="topbar-right">
                <div class="user-badge">
                    <div class="avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
                    <span><?= htmlspecialchars($_SESSION['nama']) ?></span>
                </div>
            </div>
        </div>

        <!-- PAGE BODY -->
        <div class="page-body">

            <!-- STATS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🍵</div>
                    <div class="stat-info">
                        <h3><?= $total_menu ?></h3>
                        <p>Total Menu</p>
                    </div>
                </div>
                <div class="stat-card gold">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <h3><?= $menu_tersedia ?></h3>
                        <p>Menu Tersedia</p>
                    </div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3><?= $total_anggota ?></h3>
                        <p>Total Anggota</p>
                    </div>
                </div>
                <div class="stat-card red">
                    <div class="stat-icon">📋</div>
                    <div class="stat-info">
                        <h3><?= $total_kategori ?></h3>
                        <p>Kategori Menu</p>
                    </div>
                </div>
                <div class="stat-card" style="border-left:4px solid #3498db;">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <h3><?= $total_pesanan ?></h3>
                        <p>Total Pesanan</p>
                    </div>
                </div>
                <div class="stat-card" style="border-left:4px solid #2ecc71;">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3><?= formatRupiah($total_pendapatan) ?></h3>
                        <p>Pendapatan</p>
                    </div>
                </div>
            </div>

            <!-- GRID: Menu Terbaru + Anggota Terbaru -->
            <div class="dashboard-grid">

                <!-- Menu Terbaru -->
                <div class="card">
                    <div class="card-header">
                        <h3>🍵 Menu Terbaru</h3>
                        <a href="kelola_menu.php" class="btn btn-outline btn-sm">Lihat Semua</a>
                    </div>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama Menu</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $recent_menu->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td><?= htmlspecialchars($row['kategori']) ?></td>
                                    <td><?= formatRupiah($row['harga']) ?></td>
                                    <td>
                                        <span class="badge <?= $row['tersedia'] ? 'badge-active' : 'badge-inactive' ?>">
                                            <?= $row['tersedia'] ? 'Tersedia' : 'Habis' ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Anggota Terbaru -->
                <div class="card">
                    <div class="card-header">
                        <h3>👥 Anggota Terbaru</h3>
                        <a href="kelola_anggota.php" class="btn btn-outline btn-sm">Lihat Semua</a>
                    </div>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Daftar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $recent_anggota->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td style="font-size:12.5px"><?= htmlspecialchars($row['email']) ?></td>
                                    <td style="font-size:12px;color:#777">
                                        <?= date('d M Y', strtotime($row['created_at'])) ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div><!-- /grid -->

            <!-- Quick Actions -->
            <div class="card" style="margin-top:0">
                <div class="card-header"><h3>⚡ Aksi Cepat</h3></div>
                <div class="card-body" style="display:flex;gap:14px;flex-wrap:wrap;">
                    <a href="tambah_menu.php" class="btn btn-primary">➕ Tambah Menu Baru</a>
                    <a href="kelola_menu.php" class="btn btn-warning">🍵 Kelola Menu</a>
                    <a href="kelola_anggota.php" class="btn btn-success">👥 Data Anggota</a>
                    <a href="../index.php" class="btn btn-outline" target="_blank">🌐 Lihat Website</a>
                </div>
            </div>

        </div><!-- /page-body -->
    </div><!-- /main-content -->

</div><!-- /admin-layout -->
</body>
</html>
