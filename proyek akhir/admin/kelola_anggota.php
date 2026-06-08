<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isLoggedIn() || !isAdmin()) redirect('../auth/login.php');

// Hapus anggota
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM users WHERE id=$id AND role='anggota'");
    redirect('kelola_anggota.php?success=hapus');
}

$search  = isset($_GET['q']) ? sanitize($conn, $_GET['q']) : '';
$where   = "WHERE role='anggota'";
if ($search) $where .= " AND (nama LIKE '%$search%' OR email LIKE '%$search%')";

$anggota = $conn->query("SELECT * FROM users $where ORDER BY created_at DESC");
$total   = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='anggota'")->fetch_assoc()['c'];

$msg = '';
if (isset($_GET['success']) && $_GET['success'] === 'hapus') $msg = '🗑️ Anggota berhasil dihapus.';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Anggota – Admin Kedai Kopi</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <h3>👥 Data Anggota</h3>
                <p>Total <?= $total ?> anggota terdaftar</p>
            </div>
        </div>

        <div class="page-body">
            <?php if ($msg): ?>
            <div class="alert alert-success"><?= $msg ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body" style="padding:16px 24px;">
                    <form method="GET" style="display:flex;gap:12px;-webkit-align-items: flex-end; align-items: flex-end;">
                        <div style="flex:1;">
                            <label style="font-size:13px;font-weight:600;color:#6f4e37;display:block;margin-bottom:6px;">Cari Anggota</label>
                            <input type="text" name="q" placeholder="Nama atau email..." value="<?= htmlspecialchars($search) ?>"
                                   style="width:100%;padding:10px 14px;border:2px solid #e8ddd6;-webkit-border-radius: 10px; border-radius: 10px;font-size:14px;">
                        </div>
                        <button type="submit" class="btn btn-primary">🔍 Cari</button>
                        <a href="kelola_anggota.php" class="btn btn-outline">Reset</a>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Daftar Anggota (<?= $anggota->num_rows ?> hasil)</h3>
                </div>
                <div class="table-wrapper">
                    <?php if ($anggota->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Telepon</th>
                                <th>Tanggal Daftar</th>
                                <th style="text-align:center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $no = 1; while ($row = $anggota->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <div style="display:flex;-webkit-align-items: center; align-items: center;gap:10px;">
                                        <div style="width:34px;height:34px;background:#6f4e37;color:#ffcc70;-webkit-border-radius: 50%; border-radius: 50%;
                                                    display:flex;-webkit-align-items: center; align-items: center;-webkit-justify-content: center; justify-content: center;font-weight:700;font-size:14px;">
                                            <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                                        </div>
                                        <strong><?= htmlspecialchars($row['nama']) ?></strong>
                                    </div>
                                </td>
                                <td style="font-size:13.5px;"><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['telepon'] ?: '-') ?></td>
                                <td style="font-size:13px;color:#777"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
                                <td style="text-align:center">
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
                        <div class="icon">👥</div>
                        <h4>Belum ada anggota</h4>
                        <p>Belum ada anggota yang mendaftar.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalHapus">
    <div class="modal">
        <div style="font-size:50px;margin-bottom:12px;">⚠️</div>
        <h3>Hapus Anggota?</h3>
        <p id="modalMsg">Yakin ingin menghapus anggota ini?</p>
        <div class="modal-btns">
            <button class="btn btn-outline" onclick="closeModal()">Batal</button>
            <a href="#" id="btnHapusConfirm" class="btn btn-danger">Ya, Hapus</a>
        </div>
    </div>
</div>

<script>
function confirmHapus(id, nama) {
    document.getElementById('modalMsg').textContent = `Hapus anggota "${nama}"? Data tidak bisa dikembalikan.`;
    document.getElementById('btnHapusConfirm').href = `kelola_anggota.php?hapus=${id}`;
    document.getElementById('modalHapus').classList.add('open');
}
function closeModal() { document.getElementById('modalHapus').classList.remove('open'); }
document.getElementById('modalHapus').addEventListener('click', e => { if (e.target === document.getElementById('modalHapus')) closeModal(); });
</script>
</body>
</html>
