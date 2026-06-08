<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isLoggedIn() || !isAdmin()) redirect('../auth/login.php');

$pageTitle = 'Kelola Pesanan';

// Handle ubah status
if (isset($_POST['update_status'])) {
    $pesanan_id = (int)$_POST['pesanan_id'];
    $status_baru = sanitize($conn, $_POST['status']);
    
    $stmt = $conn->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status_baru, $pesanan_id);
    if ($stmt->execute()) {
        $msg = "Status pesanan berhasil diperbarui!";
    } else {
        $err = "Gagal memperbarui status pesanan.";
    }
}

// Ambil semua pesanan
$pesanan = $conn->query("
    SELECT p.*, u.nama AS nama_pemesan, GROUP_CONCAT(CONCAT(m.nama, ' (x', dp.jumlah, ')') SEPARATOR '<br>') AS detail_menu
    FROM pesanan p
    JOIN users u ON p.user_id = u.id
    JOIN detail_pesanan dp ON p.id = dp.pesanan_id
    JOIN menu m ON dp.menu_id = m.id
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> – Kedai Kopi Sudut Senja</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .badge-status { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .status-pending { background: #fef9e7; color: #f39c12; }
        .status-diproses { background: #ebf5fb; color: #3498db; }
        .status-selesai { background: #eafaf1; color: #27ae60; }
        .status-dibatalkan { background: #fdecea; color: #e74c3c; }
        .action-form { display: flex; gap: 8px; align-items: center; }
        .action-form select { padding: 4px; border-radius: 4px; border: 1px solid #ccc; font-size: 12px; }
        .action-form button { padding: 4px 8px; font-size: 12px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <h3>📦 Kelola Pesanan</h3>
                <p>Manajemen transaksi dan pesanan member</p>
            </div>
            <div class="topbar-right">
                <div class="user-badge">
                    <div class="avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
                    <span><?= htmlspecialchars($_SESSION['nama']) ?></span>
                </div>
            </div>
        </div>

        <div class="page-body">
            <?php if (isset($msg)): ?>
                <div style="background: #eafaf1; color: #1e8449; padding: 12px; margin-bottom: 20px; border-left: 4px solid #27ae60;">✅ <?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>
            <?php if (isset($err)): ?>
                <div style="background: #fdecea; color: #c0392b; padding: 12px; margin-bottom: 20px; border-left: 4px solid #e74c3c;">⚠️ <?= htmlspecialchars($err) ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>Daftar Seluruh Pesanan</h3>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tanggal</th>
                                <th>Pemesan</th>
                                <th>Detail Menu</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi (Ubah Status)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($pesanan->num_rows > 0): ?>
                                <?php while($row = $pesanan->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $row['id'] ?></td>
                                    <td style="font-size:12px;"><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($row['nama_pemesan']) ?></td>
                                    <td style="font-size:12px; line-height: 1.4;"><?= $row['detail_menu'] ?></td>
                                    <td style="font-weight:bold; color:#d2691e;"><?= formatRupiah($row['total_harga']) ?></td>
                                    <td><span class="badge-status status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                                    <td>
                                        <form method="POST" class="action-form">
                                            <input type="hidden" name="pesanan_id" value="<?= $row['id'] ?>">
                                            <select name="status">
                                                <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="diproses" <?= $row['status'] == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                                <option value="selesai" <?= $row['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                                <option value="dibatalkan" <?= $row['status'] == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" style="text-align:center;">Belum ada pesanan masuk.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
