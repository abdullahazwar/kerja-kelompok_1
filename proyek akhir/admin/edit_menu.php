<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isLoggedIn() || !isAdmin()) redirect('../auth/login.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) redirect('kelola_menu.php');

$menu      = $conn->query("SELECT * FROM menu WHERE id=$id")->fetch_assoc();
if (!$menu) redirect('kelola_menu.php');

$kategoris = $conn->query("SELECT * FROM kategori_menu ORDER BY nama");
$error     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama        = sanitize($conn, $_POST['nama'] ?? '');
    $kategori_id = (int)($_POST['kategori_id'] ?? 0);
    $deskripsi   = sanitize($conn, $_POST['deskripsi'] ?? '');
    $harga       = (float)($_POST['harga'] ?? 0);
    $stok        = (int)($_POST['stok'] ?? 0);
    $tersedia    = isset($_POST['tersedia']) ? 1 : 0;
    $gambar_path = $menu['gambar'];

    if (empty($nama) || $kategori_id <= 0 || $harga <= 0) {
        $error = 'Nama menu, kategori, dan harga wajib diisi.';
    } else {
        if (!empty($_FILES['gambar']['name'])) {
            $ext     = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];
            if (!in_array($ext, $allowed)) {
                $error = 'Format gambar tidak didukung.';
            } elseif ($_FILES['gambar']['size'] > 10 * 1024 * 1024) {
                $error = 'Ukuran gambar maksimal 10MB.';
            } else {
                $filename = 'img_' . time() . '_' . rand(100,999) . '.' . $ext;
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], '../menu/cache_foto_menu/' . $filename)) {
                    if ($menu['gambar'] && strpos($menu['gambar'], 'menu/cache_foto_menu/') !== false && file_exists('../' . $menu['gambar'])) {
                        @unlink('../' . $menu['gambar']);
                    }
                    $gambar_path = 'menu/cache_foto_menu/' . $filename;
                } else {
                    $error = 'Gagal mengupload gambar.';
                }
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("UPDATE menu SET kategori_id=?, nama=?, deskripsi=?, harga=?, gambar=?, stok=?, tersedia=? WHERE id=?");
            $stmt->bind_param("issdssii", $kategori_id, $nama, $deskripsi, $harga, $gambar_path, $stok, $tersedia, $id);
            if ($stmt->execute()) redirect('kelola_menu.php?success=edit');
            else $error = 'Gagal memperbarui menu.';
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu – Admin Kedai Kopi</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <h3>✏️ Edit Menu</h3>
                <p>Perbarui informasi menu: <strong><?= htmlspecialchars($menu['nama']) ?></strong></p>
            </div>
            <div class="topbar-right">
                <a href="kelola_menu.php" class="btn btn-outline">← Kembali</a>
            </div>
        </div>

        <div class="page-body">
            <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header"><h3>📋 Edit Informasi Menu</h3></div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div>
                                <div class="form-group">
                                    <label>Nama Menu *</label>
                                    <input type="text" name="nama" required value="<?= htmlspecialchars($_POST['nama'] ?? $menu['nama']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Kategori *</label>
                                    <select name="kategori_id" required>
                                        <option value="">-- Pilih --</option>
                                        <?php while ($kat = $kategoris->fetch_assoc()): ?>
                                        <option value="<?= $kat['id'] ?>"
                                            <?= (($_POST['kategori_id'] ?? $menu['kategori_id']) == $kat['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kat['nama']) ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Harga (Rp) *</label>
                                        <input type="number" name="harga" min="0" required
                                               value="<?= htmlspecialchars($_POST['harga'] ?? $menu['harga']) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Stok</label>
                                        <input type="number" name="stok" min="0"
                                               value="<?= htmlspecialchars($_POST['stok'] ?? $menu['stok']) ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Deskripsi</label>
                                    <textarea name="deskripsi"><?= htmlspecialchars($_POST['deskripsi'] ?? $menu['deskripsi']) ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="tersedia" value="1"
                                               <?= (isset($_POST['tersedia']) ? isset($_POST['tersedia']) : $menu['tersedia']) ? 'checked' : '' ?>>
                                        &nbsp;Menu tersedia
                                    </label>
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label>Foto Menu</label>
                                    <div class="img-preview-wrap">
                                        <?php
                                        $imgSrc = '';
                                        if ($menu['gambar'] && file_exists('../' . $menu['gambar'])) {
                                            $imgSrc = '../' . $menu['gambar'];
                                        }
                                        ?>
                                        <?php if ($imgSrc): ?>
                                        <img id="imgPreview" src="<?= htmlspecialchars($imgSrc) ?>" alt="Foto menu"
                                             style="max-width:200px;max-height:160px;-webkit-border-radius: 12px; border-radius: 12px;object-fit:cover;border:3px solid #f4ede8;margin-bottom:10px;">
                                        <?php else: ?>
                                        <div class="img-placeholder" id="imgPlaceholder">
                                            <span>☕</span><small>Belum ada foto</small>
                                        </div>
                                        <img id="imgPreview" src="" alt="" style="display:none;max-width:200px;max-height:160px;-webkit-border-radius: 12px; border-radius: 12px;object-fit:cover;border:3px solid #f4ede8;margin-bottom:10px;">
                                        <?php endif; ?>
                                    </div>
                                    <input type="file" name="gambar" accept="image/*" onchange="previewImg(this)">
                                    <p class="form-hint">Kosongkan jika tidak ingin mengganti foto.</p>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex;gap:12px;margin-top:8px;">
                            <button type="submit" class="btn btn-primary">💾 Perbarui Menu</button>
                            <a href="kelola_menu.php" class="btn btn-outline">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="admin-footer">© 2026 Kedai Kopi Sudut Senja – Panel Admin</div>
    </div>
</div>
<script>
function previewImg(input) {
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('imgPreview');
        const ph  = document.getElementById('imgPlaceholder');
        if (ph) ph.style.display = 'none';
        img.src = e.target.result;
        img.style.display = 'block';
    };
    if (input.files[0]) reader.readAsDataURL(input.files[0]);
}
</script>
</body>
</html>
