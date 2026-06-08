<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isLoggedIn() || !isAdmin()) redirect('../auth/login.php');

$error = $success = '';

// Hapus kategori
if (isset($_GET['hapus'])) {
    $id  = (int)$_GET['hapus'];
    $cek = $conn->query("SELECT COUNT(*) AS c FROM menu WHERE kategori_id=$id")->fetch_assoc()['c'];
    if ($cek > 0) {
        $error = 'Kategori tidak bisa dihapus karena masih memiliki menu.';
    } else {
        $conn->query("DELETE FROM kategori_menu WHERE id=$id");
        redirect('kelola_kategori.php?success=hapus');
    }
}

// Tambah kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $nama      = sanitize($conn, $_POST['nama'] ?? '');
    $deskripsi = sanitize($conn, $_POST['deskripsi'] ?? '');
    if (empty($nama)) {
        $error = 'Nama kategori wajib diisi.';
    } else {
        $stmt = $conn->prepare("INSERT INTO kategori_menu (nama, deskripsi) VALUES (?,?)");
        $stmt->bind_param("ss", $nama, $deskripsi);
        $stmt->execute() ? redirect('kelola_kategori.php?success=tambah') : ($error = 'Gagal menambah kategori.');
        $stmt->close();
    }
}

$msg = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'tambah') $msg = '✅ Kategori berhasil ditambahkan!';
    if ($_GET['success'] === 'hapus')  $msg = '🗑️ Kategori berhasil dihapus.';
}

$kategoris = $conn->query("SELECT k.*, (SELECT COUNT(*) FROM menu WHERE kategori_id=k.id) AS jml_menu
                            FROM kategori_menu k ORDER BY k.nama");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Menu – Admin Kedai Kopi</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <h3>📋 Kategori Menu</h3>
                <p>Kelola kategori untuk pengelompokan menu</p>
            </div>
        </div>

        <div class="page-body">
            <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:24px;-webkit-align-items: start; align-items: start;">

                <!-- Form Tambah -->
                <div class="card">
                    <div class="card-header"><h3>➕ Tambah Kategori</h3></div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="aksi" value="tambah">
                            <div class="form-group">
                                <label>Nama Kategori *</label>
                                <input type="text" name="nama" placeholder="Contoh: Kopi Panas" required>
                            </div>
                            <div class="form-group">
                                <label>Deskripsi</label>
                                <textarea name="deskripsi" placeholder="Deskripsi singkat..." style="min-height:80px;"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">💾 Simpan</button>
                        </form>
                    </div>
                </div>

                <!-- Tabel Kategori -->
                <div class="card">
                    <div class="card-header"><h3>Daftar Kategori</h3></div>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr><th>#</th><th>Nama Kategori</th><th>Deskripsi</th><th>Jumlah Menu</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                            <?php $no=1; while($kat=$kategoris->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= htmlspecialchars($kat['nama']) ?></strong></td>
                                    <td style="font-size:13px;color:#777"><?= htmlspecialchars($kat['deskripsi'] ?: '-') ?></td>
                                    <td><span class="badge badge-kopi"><?= $kat['jml_menu'] ?> menu</span></td>
                                    <td>
                                        <?php if ($kat['jml_menu'] == 0): ?>
                                        <button class="btn btn-danger btn-sm"
                                                onclick="if(confirm('Hapus kategori \'<?= addslashes($kat['nama']) ?>\'?')) window.location='kelola_kategori.php?hapus=<?= $kat['id'] ?>'">
                                            🗑️
                                        </button>
                                        <?php else: ?>
                                        <span style="font-size:12px;color:#999">Ada menu</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
