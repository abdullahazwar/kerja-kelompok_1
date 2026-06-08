<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isLoggedIn() || isAdmin())
    redirect('../auth/login.php');

// Ambil menu tersedia dari DB
$menus = $conn->query("SELECT m.*, k.nama AS kategori FROM menu m
                            JOIN kategori_menu k ON m.kategori_id=k.id
                            WHERE m.tersedia=1 ORDER BY k.nama, m.nama");
$kategoris = $conn->query("SELECT DISTINCT k.id, k.nama FROM kategori_menu k
                            JOIN menu m ON m.kategori_id=k.id WHERE m.tersedia=1");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Anggota – Kedai Kopi Sudut Senja</title>
    <link rel="stylesheet" href="../kedai.css">
    <link rel="stylesheet" href="member.css">
</head>

<body>

    <!-- NAVBAR MEMBER -->
    <nav class="member-nav">
        <div class="nav-brand">
            <span class="user-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></span>
            <span><?= htmlspecialchars($_SESSION['nama']) ?></span>
        </div>
        <ul class="nav-links">
            <li><a href="dashboard.php" class="active">Menu</a></li>
            <li><a href="pesanan.php">Pesanan Saya</a></li>
            <li><a href="keranjang.php">Keranjang
                    (<?= isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0 ?>)</a></li>
        </ul>
        <div class="nav-user">
            <a href="../auth/logout.php" class="btn-logout">Keluar</a>
        </div>
    </nav>

    <!-- HERO MEMBER -->
    <section class="member-hero">
        <div class="member-hero-inner">
            <h1>Halo, <?= htmlspecialchars(explode(' ', $_SESSION['nama'])[0]) ?>!</h1>
            <p>Selamat datang di Kedai Kopi Sudut Senja.</p>
        </div>
    </section>

    <!-- MENU SECTION -->
    <section class="member-menu" id="menu">
        <div class="container">
            <div class="section-title">
                <h2>Menu Kami</h2>
                <p>Pilih menu favoritmu hari ini.</p>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div
                    style="background: #eafaf1; color: #1e8449; padding: 12px; border-left: 4px solid #27ae60; -webkit-border-radius: 8px; border-radius: 8px; margin-bottom: 20px;">
                    ✅ <?= htmlspecialchars($_GET['msg']) ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div
                    style="background: #fdecea; color: #c0392b; padding: 12px; border-left: 4px solid #e74c3c; -webkit-border-radius: 8px; border-radius: 8px; margin-bottom: 20px;">
                    ⚠️ <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <!-- Filter Kategori -->
            <div class="cat-filter" id="catFilter">
                <button class="cat-btn active" data-cat="semua" onclick="filterMenu('semua', this)">Semua</button>
                <?php while ($kat = $kategoris->fetch_assoc()): ?>
                    <button class="cat-btn" data-cat="<?= $kat['id'] ?>" onclick="filterMenu('<?= $kat['id'] ?>', this)">
                        <?= htmlspecialchars($kat['nama']) ?>
                    </button>
                <?php endwhile; ?>
            </div>

            <div class="menu-grid" id="menuGrid">
                <?php $menus->data_seek(0);
                while ($item = $menus->fetch_assoc()): ?>
                    <div class="menu-item" data-cat="<?= $item['kategori_id'] ?>">
                        <div class="menu-img-wrap">
                            <?php
                            $src = '';
                            if ($item['gambar'] && file_exists('../' . $item['gambar']))
                                $src = '../' . $item['gambar'];
                            ?>
                            <?php if ($src): ?>
                                <img src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($item['nama']) ?>">
                            <?php else: ?>
                                <div class="menu-img-ph">☕</div>
                            <?php endif; ?>
                            <span class="menu-badge"><?= htmlspecialchars($item['kategori']) ?></span>
                        </div>
                        <div class="menu-info">
                            <h3><?= htmlspecialchars($item['nama']) ?></h3>
                            <p><?= htmlspecialchars($item['deskripsi'] ?: 'Menu pilihan Kedai Kopi Sudut Senja.') ?></p>
                            <div class="menu-footer">
                                <span class="menu-price"><?= formatRupiah($item['harga']) ?></span>
                                <span class="stok-info">Stok: <?= $item['stok'] ?></span>
                            </div>
                            <div style="margin-top:12px;">
                                <form action="tambah_keranjang.php" method="POST">
                                    <input type="hidden" name="menu_id" value="<?= $item['id'] ?>">
                                    <input type="hidden" name="harga" value="<?= $item['harga'] ?>">
                                    <button type="submit"
                                        style="width:100%; padding:8px; background:#6f4e37; color:#ffcc70; border:none; -webkit-border-radius:8px; border-radius:8px; font-weight:bold; cursor:pointer;">Tambah
                                        ke Keranjang</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <script>
        function filterMenu(cat, btn) {
            document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('.menu-item').forEach(item => {
                item.style.display = (cat === 'semua' || item.dataset.cat === cat) ? '' : 'none';
            });
        }
    </script>
</body>

</html>
</body>

</html>