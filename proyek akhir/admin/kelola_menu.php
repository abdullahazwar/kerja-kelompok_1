<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isLoggedIn() || !isAdmin()) redirect('../auth/login.php');

// Hapus menu jika ada request
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    // Hapus gambar jika ada di folder lokal
    $row = $conn->query("SELECT gambar FROM menu WHERE id=$id")->fetch_assoc();
    if ($row && $row['gambar'] && file_exists('../' . $row['gambar'])) {
        @unlink('../' . $row['gambar']);
    }
    $conn->query("DELETE FROM menu WHERE id=$id");
    redirect('kelola_menu.php?success=hapus');
}

// Toggle tersedia
if (isset($_GET['toggle'])) {
    $id  = (int)$_GET['toggle'];
    $row = $conn->query("SELECT tersedia FROM menu WHERE id=$id")->fetch_assoc();
    $new = $row['tersedia'] ? 0 : 1;
    $conn->query("UPDATE menu SET tersedia=$new WHERE id=$id");
    redirect('kelola_menu.php?success=toggle');
}

// Filter kategori
$filter_kat = isset($_GET['kat']) ? (int)$_GET['kat'] : 0;
$search     = isset($_GET['q']) ? sanitize($conn, $_GET['q']) : '';

$where = "WHERE 1";
if ($filter_kat > 0) $where .= " AND m.kategori_id=$filter_kat";
if ($search)         $where .= " AND m.nama LIKE '%$search%'";

$menus     = $conn->query("SELECT m.*, k.nama AS kategori
                            FROM menu m JOIN kategori_menu k ON m.kategori_id=k.id
                            $where ORDER BY k.nama, m.nama");
$kategoris = $conn->query("SELECT * FROM kategori_menu ORDER BY nama");

$msg = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'tambah') $msg = '✅ Menu berhasil ditambahkan!';
    if ($_GET['success'] === 'edit')   $msg = '✅ Menu berhasil diperbarui!';
    if ($_GET['success'] === 'hapus')  $msg = '🗑️ Menu berhasil dihapus.';
    if ($_GET['success'] === 'toggle') $msg = '🔄 Status menu diperbarui.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu – Admin Kedai Kopi</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">

        <div class="topbar">
            <div class="topbar-left">
                <h3>🍵 Kelola Menu</h3>
                <p>Tambah, ubah, dan hapus menu kedai</p>
            </div>
            <div class="topbar-right">
                <a href="tambah_menu.php" class="btn btn-primary">➕ Tambah Menu</a>
            </div>
        </div>

        <div class="page-body">

            <?php if ($msg): ?>
            <div class="alert alert-success"><?= $msg ?></div>
            <?php endif; ?>

            <!-- Filter & Search -->
            <div class="card">
                <div class="card-body" style="padding-top:18px;padding-bottom:18px;">
                    <form method="GET" style="display:flex;gap:14px;flex-wrap:wrap;-webkit-align-items: flex-end; align-items: flex-end;">
                        <div style="flex:1;min-width:200px;">
                            <label style="font-size:13px;font-weight:600;color:#6f4e37;display:block;margin-bottom:6px;">Cari Menu</label>
                            <input type="text" name="q" placeholder="Nama menu..." value="<?= htmlspecialchars($search) ?>"
                                   style="width:100%;padding:10px 14px;border:2px solid #e8ddd6;-webkit-border-radius: 10px; border-radius: 10px;font-size:14px;">
                        </div>
                        <div style="min-width:180px;">
                            <label style="font-size:13px;font-weight:600;color:#6f4e37;display:block;margin-bottom:6px;">Filter Kategori</label>
                            <select name="kat" style="width:100%;padding:10px 14px;border:2px solid #e8ddd6;-webkit-border-radius: 10px; border-radius: 10px;font-size:14px;background:#fdfaf8;">
                                <option value="0">Semua Kategori</option>
                                <?php
                                $kategoris->data_seek(0);
                                while ($kat = $kategoris->fetch_assoc()):
                                ?>
                                <option value="<?= $kat['id'] ?>" <?= $filter_kat == $kat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat['nama']) ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">🔍 Cari</button>
                        <a href="kelola_menu.php" class="btn btn-outline">Reset</a>
                    </form>
                </div>
            </div>

            <!-- Tabel Menu -->
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Menu (<?= $menus->num_rows ?> item)</h3>
                </div>
                <div class="table-wrapper">
                    <?php if ($menus->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Foto</th>
                                <th>Nama Menu</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th style="text-align:center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $no = 1; while ($row = $menus->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <?php
                                    $imgPath = '../' . $row['gambar'];
                                    if ($row['gambar'] && file_exists($imgPath)):
                                    ?>
                                    <img src="../<?= htmlspecialchars($row['gambar']) ?>" class="menu-thumb" alt="<?= htmlspecialchars($row['nama']) ?>">
                                    <?php else: ?>
                                    <div class="menu-thumb-placeholder">☕</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['nama']) ?></strong>
                                    <?php if ($row['deskripsi']): ?>
                                    <br><small style="color:#999;font-size:12px;"><?= mb_substr(htmlspecialchars($row['deskripsi']), 0, 50) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-kopi"><?= htmlspecialchars($row['kategori']) ?></span></td>
                                <td><strong style="color:#d2691e"><?= formatRupiah($row['harga']) ?></strong></td>
                                <td><?= $row['stok'] ?></td>
                                <td>
                                    <a href="kelola_menu.php?toggle=<?= $row['id'] ?>"
                                       class="badge <?= $row['tersedia'] ? 'badge-active' : 'badge-inactive' ?>"
                                       style="cursor:pointer;text-decoration:none;"
                                       title="Klik untuk toggle">
                                        <?= $row['tersedia'] ? '✅ Tersedia' : '❌ Habis' ?>
                                    </a>
                                </td>
                                <td style="text-align:center;white-space:nowrap;">
                                    <a href="edit_menu.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                                    <button class="btn btn-danger btn-sm"
                                            onclick="confirmHapus(<?= $row['id'] ?>, '<?= addslashes(htmlspecialchars($row['nama'])) ?>')">
                                        🗑️ Hapus
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="icon">🍵</div>
                        <h4>Belum ada menu</h4>
                        <p>Mulai tambahkan menu kedai Anda.</p>
                        <a href="tambah_menu.php" class="btn btn-primary" style="margin-top:16px;">➕ Tambah Menu Pertama</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /page-body -->

    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal-overlay" id="modalHapus">
    <div class="modal">
        <div style="font-size:50px;margin-bottom:12px;">🗑️</div>
        <h3>Hapus Menu?</h3>
        <p id="modalMsg">Yakin ingin menghapus menu ini? Tindakan ini tidak bisa dibatalkan.</p>
        <div class="modal-btns">
            <button class="btn btn-outline" onclick="closeModal()">Batal</button>
            <a href="#" id="btnHapusConfirm" class="btn btn-danger">Ya, Hapus</a>
        </div>
    </div>
</div>

<script>
function confirmHapus(id, nama) {
    document.getElementById('modalMsg').textContent = `Yakin ingin menghapus menu "${nama}"? Tindakan ini tidak bisa dibatalkan.`;
    document.getElementById('btnHapusConfirm').href = `kelola_menu.php?hapus=${id}`;
    document.getElementById('modalHapus').classList.add('open');
}
function closeModal() {
    document.getElementById('modalHapus').classList.remove('open');
}
document.getElementById('modalHapus').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
</body>
</html>
