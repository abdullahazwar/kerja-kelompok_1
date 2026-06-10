<?php
// admin/sidebar.php – komponen sidebar yang dipakai ulang di semua halaman admin
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">

    <div class="sidebar-brand">
        <span class="icon">☕</span>
        <h2>Kedai Kopi<br>Sudut Senja</h2>
        <p>Panel Administrator</p>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Utama</div>
        <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
            <span class="nav-icon">📊</span> Dashboard
        </a>

        <div class="nav-section-title">Manajemen Menu</div>
        <a href="kelola_menu.php" class="<?= $currentPage === 'kelola_menu.php' ? 'active' : '' ?>">
            <span class="nav-icon">🍵</span> Daftar Menu
        </a>
        <a href="tambah_menu.php" class="<?= $currentPage === 'tambah_menu.php' ? 'active' : '' ?>">
            <span class="nav-icon">➕</span> Tambah Menu
        </a>
        <a href="kelola_kategori.php" class="<?= $currentPage === 'kelola_kategori.php' ? 'active' : '' ?>">
            <span class="nav-icon">📋</span> Kategori Menu
        </a>

        <div class="nav-section-title">Penjualan</div>
        <a href="kelola_pesanan.php" class="<?= $currentPage === 'kelola_pesanan.php' ? 'active' : '' ?>">
            <span class="nav-icon">📦</span> Pesanan / Transaksi
        </a>
        <a href="kelola_penjualan.php" class="<?= $currentPage === 'kelola_penjualan.php' ? 'active' : '' ?>">
            <span class="nav-icon">📊</span> Data Penjualan (Supabase)
        </a>

        <div class="nav-section-title">Anggota</div>
        <a href="kelola_anggota.php" class="<?= $currentPage === 'kelola_anggota.php' ? 'active' : '' ?>">
            <span class="nav-icon">👥</span> Data Anggota
        </a>

        <div class="nav-section-title">Website</div>
        <a href="../index.php" target="_blank">
            <span class="nav-icon">🌐</span> Lihat Website
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="../auth/logout.php">
            <span>🚪</span> Keluar
        </a>
    </div>
</aside>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const topbar = document.querySelector('.topbar');
        if (topbar) {
            if (!document.getElementById('sidebarToggle')) {
                const toggleBtn = document.createElement('button');
                toggleBtn.id = 'sidebarToggle';
                toggleBtn.className = 'sidebar-toggle-btn';
                toggleBtn.innerHTML = '☰';

                topbar.insertBefore(toggleBtn, topbar.firstChild);

                const sidebar = document.getElementById('sidebar');

                toggleBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('open');
                });

                document.addEventListener('click', function (e) {
                    if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== toggleBtn) {
                        sidebar.classList.remove('open');
                    }
                });
            }
        }
    });
</script>