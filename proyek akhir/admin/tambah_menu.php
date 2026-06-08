<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isLoggedIn() || !isAdmin()) redirect('../auth/login.php');

$kategoris = $conn->query("SELECT * FROM kategori_menu ORDER BY nama");
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama        = sanitize($conn, $_POST['nama'] ?? '');
    $kategori_id = (int)($_POST['kategori_id'] ?? 0);
    $deskripsi   = sanitize($conn, $_POST['deskripsi'] ?? '');
    $harga       = (float)($_POST['harga'] ?? 0);
    $stok        = (int)($_POST['stok'] ?? 0);
    $tersedia    = isset($_POST['tersedia']) ? 1 : 0;
    $gambar_path = '';

    if (empty($nama) || $kategori_id <= 0 || $harga <= 0) {
        $error = 'Nama menu, kategori, dan harga wajib diisi.';
    } else {
        if (!empty($_FILES['gambar']['name'])) {
            $ext     = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];
            if (!in_array($ext, $allowed)) {
                $error = 'Format gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.';
            } elseif ($_FILES['gambar']['size'] > 3 * 1024 * 1024) {
                $error = 'Ukuran gambar maksimal 3MB.';
            } else {
                $filename    = 'img_' . time() . '_' . rand(100,999) . '.' . $ext;
                $gambar_path = 'menu/' . $filename;
                if (!move_uploaded_file($_FILES['gambar']['tmp_name'], '../menu/' . $filename)) {
                    $error       = 'Gagal mengupload gambar.';
                    $gambar_path = '';
                }
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO menu (kategori_id, nama, deskripsi, harga, gambar, stok, tersedia) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("issdsis", $kategori_id, $nama, $deskripsi, $harga, $gambar_path, $stok, $tersedia);
            if ($stmt->execute()) redirect('kelola_menu.php?success=tambah');
            else $error = 'Gagal menyimpan menu. Coba lagi.';
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
    <title>Tambah Menu – Admin Kedai Kopi</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">

        <div class="topbar">
            <div class="topbar-left">
                <h3>➕ Tambah Menu Baru</h3>
                <p>Isi form untuk menambahkan menu ke kedai</p>
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
                <div class="card-header"><h3>📋 Informasi Menu</h3></div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div>
                                <div class="form-group">
                                    <label>Nama Menu <span style="color:#e74c3c">*</span></label>
                                    <input type="text" name="nama" placeholder="Contoh: Espresso, Latte..."
                                           value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Kategori <span style="color:#e74c3c">*</span></label>
                                    <select name="kategori_id" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        <?php while ($kat = $kategoris->fetch_assoc()): ?>
                                        <option value="<?= $kat['id'] ?>"
                                            <?= (isset($_POST['kategori_id']) && $_POST['kategori_id'] == $kat['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kat['nama']) ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Harga (Rp) <span style="color:#e74c3c">*</span></label>
                                        <input type="number" name="harga" min="0" placeholder="25000"
                                               value="<?= htmlspecialchars($_POST['harga'] ?? '') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Stok</label>
                                        <input type="number" name="stok" min="0"
                                               value="<?= htmlspecialchars($_POST['stok'] ?? '50') ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Deskripsi</label>
                                    <textarea name="deskripsi" placeholder="Deskripsi singkat menu..."><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="tersedia" value="1"
                                               <?= (!isset($_POST['tersedia']) || $_POST['tersedia']) ? 'checked' : '' ?>>
                                        &nbsp;Menu langsung tersedia
                                    </label>
                                </div>
                            </div>

                            <!-- Upload Gambar -->
                            <div>
                                <div class="form-group">
                                    <label>Foto Menu</label>
                                    <div style="text-align:center;">
                                        <div class="img-placeholder" id="imgPlaceholder">
                                            <span>🖼️</span>
                                            <small>Pratinjau foto</small>
                                        </div>
                                        <img id="imgPreview" src="" alt=""
                                             style="display:none;max-width:200px;max-height:160px;-webkit-border-radius: 12px; border-radius: 12px;object-fit:cover;border:3px solid #f4ede8;-webkit-box-shadow: 0 4px 15px rgba(0,0,0,0.15); box-shadow: 0 4px 15px rgba(0,0,0,0.15);margin-bottom:10px;">
                                    </div>
                                    <input type="file" name="gambar" accept="image/*" onchange="previewImg(this)" style="margin-top:10px;">
                                    <p class="form-hint">Format: JPG, PNG, WEBP. Maks 3MB.</p>
                                    <p class="form-hint" style="margin-top:4px;">Foto bisa ditambahkan/diubah nanti melalui fitur Edit Menu.</p>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex;gap:12px;margin-top:8px;">
                            <button type="submit" class="btn btn-primary">💾 Simpan Menu</button>
                            <a href="kelola_menu.php" class="btn btn-outline">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function previewImg(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('imgPlaceholder').style.display = 'none';
        const img = document.getElementById('imgPreview');
        img.src   = e.target.result;
        img.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
</body>
</html>
