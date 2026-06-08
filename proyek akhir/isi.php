<?php
//Halaman konten publik
session_start();
require_once __DIR__ . '/config/db.php';

// Ambil menu tersedia
$menus = $conn->query("SELECT m.*, k.nama AS kategori FROM menu m
                        JOIN kategori_menu k ON m.kategori_id = k.id
                        WHERE m.tersedia = 1
                        ORDER BY k.nama, m.nama");

$kategoris = $conn->query("SELECT DISTINCT k.id, k.nama FROM kategori_menu k
                            JOIN menu m ON m.kategori_id = k.id
                            WHERE m.tersedia = 1 ORDER BY k.nama");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Kedai Kopi Sudut Senja</title>
    <link rel="stylesheet" href="kedai.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body>

    <!-- NAV -->
    <nav class="isi-nav">
        <a href="index.php" class="brand">Kedai Kopi Sudut Senja</a>
        <div class="nav-links-right">
            <a href="index.php">Beranda</a>
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="admin/index.php">Dashboard Admin</a>
                <?php else: ?>
                    <a href="member/dashboard.php">Dashboard</a>
                    <a href="member/keranjang.php">Keranjang
                        (<?= isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0 ?>)</a>
                <?php endif; ?>
                <a href="auth/logout.php">Keluar</a>
            <?php else: ?>
                <a href="auth/login.php">Login</a>
                <a href="auth/register.php" class="btn-nav">Daftar</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- HERO -->
    <div class="isi-hero">
        <h1>Menu Kami</h1>
        <p>Temukan minuman dan makanan terbaik dari Kedai Kopi Sudut Senja</p>
    </div>

    <!-- BODY -->
    <div class="isi-body">

        <?php if (isset($_GET['msg'])): ?>
            <div
                style="background: #eafaf1; color: #1e8449; padding: 12px; border-left: 4px solid #27ae60; border-radius: 8px; margin-bottom: 20px; text-align: center; max-width: 1200px; margin-left: auto; margin-right: auto;">
                ✅ <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div
                style="background: #fdecea; color: #c0392b; padding: 12px; border-left: 4px solid #e74c3c; border-radius: 8px; margin-bottom: 20px; text-align: center; max-width: 1200px; margin-left: auto; margin-right: auto;">
                ⚠️ <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <!-- Filter Kategori -->
        <div class="filter-bar" id="filterBar">
            <button class="filter-btn active" data-cat="semua" onclick="filterKat('semua', this)">Semua</button>
            <?php while ($kat = $kategoris->fetch_assoc()): ?>
                <button class="filter-btn" data-cat="<?= $kat['id'] ?>" onclick="filterKat('<?= $kat['id'] ?>', this)">
                    <?= htmlspecialchars($kat['nama']) ?>
                </button>
            <?php endwhile; ?>
        </div>

        <?php if ($menus->num_rows > 0): ?>
            <div class="isi-grid" id="isiGrid">
                <?php while ($item = $menus->fetch_assoc()): ?>
                    <div class="isi-card" data-cat="<?= $item['kategori_id'] ?>">
                        <div class="isi-card-img">
                            <?php
                            $src = '';
                            if ($item['gambar'] && file_exists($item['gambar']))
                                $src = $item['gambar'];
                            ?>
                            <?php if ($src): ?>
                                <img src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($item['nama']) ?>">
                            <?php else: ?>
                                <div class="isi-card-ph">☕</div>
                            <?php endif; ?>
                            <span class="isi-card-kat"><?= htmlspecialchars($item['kategori']) ?></span>
                        </div>
                        <div class="isi-card-body">
                            <h3><?= htmlspecialchars($item['nama']) ?></h3>
                            <p><?= htmlspecialchars(mb_substr($item['deskripsi'] ?: 'Menu pilihan Kedai Kopi Sudut Senja.', 0, 80)) ?>...
                            </p>
                            <div class="isi-card-footer">
                                <span class="isi-price"><?= formatRupiah($item['harga']) ?></span>
                                <span class="isi-stok">Stok: <?= $item['stok'] ?></span>
                            </div>
                            <?php if (isLoggedIn() && !isAdmin()): ?>
                                <div style="margin-top:12px;">
                                    <form action="member/tambah_keranjang.php" method="POST">
                                        <input type="hidden" name="menu_id" value="<?= $item['id'] ?>">
                                        <input type="hidden" name="harga" value="<?= $item['harga'] ?>">
                                        <button type="submit"
                                            style="width:100%; padding:8px; background:#6f4e37; color:#ffcc70; border:none; border-radius:8px; font-weight:bold; cursor:pointer;">Tambah
                                            ke Keranjang</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-msg">
                <p>Belum ada menu yang tersedia saat ini.</p>
                <p style="font-size:14px;margin-top:8px;">Silakan kunjungi kembali nanti.</p>
            </div>
        <?php endif; ?>

    </div>

    <script>
        function filterKat(cat, btn) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('.isi-card').forEach(card => {
                card.style.display = (cat === 'semua' || card.dataset.cat === cat) ? '' : 'none';
            });
        }
    </script>
</body>

</html>