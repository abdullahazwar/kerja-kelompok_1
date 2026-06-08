<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isLoggedIn() || isAdmin())
    redirect('../auth/login.php');

$user_id = $_SESSION['user_id'];

// Ambil data pesanan
$pesanan = $conn->query("
    SELECT p.*, GROUP_CONCAT(CONCAT(m.nama, ' (x', dp.jumlah, ')') SEPARATOR ', ') AS detail_menu
    FROM pesanan p
    JOIN detail_pesanan dp ON p.id = dp.pesanan_id
    JOIN menu m ON dp.menu_id = m.id
    WHERE p.user_id = $user_id
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya</title>
    <link rel="stylesheet" href="../kedai.css">
    <link rel="stylesheet" href="member.css">
    <style>
        body {
            background: #f5f5f5;
        }

        .pesanan-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        .table-pesanan {
            width: 100%;
            border-collapse: collapse;
            background: white;
            -webkit-border-radius: 12px;
            border-radius: 12px;
            overflow: hidden;
            -webkit-box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .table-pesanan th,
        .table-pesanan td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .table-pesanan th {
            background: #6f4e37;
            color: #ffcc70;
            font-weight: 600;
        }

        .badge-status {
            padding: 6px 12px;
            -webkit-border-radius: 20px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-pending {
            background: #fef9e7;
            color: #f39c12;
        }

        .status-diproses {
            background: #ebf5fb;
            color: #3498db;
        }

        .status-selesai {
            background: #eafaf1;
            color: #27ae60;
        }

        .status-dibatalkan {
            background: #fdecea;
            color: #e74c3c;
        }

        .btn-cancel {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-cancel:hover {
            background: #c0392b;
        }
    </style>
</head>

<body>
    <nav class="member-nav">
        <div class="nav-brand">
            <span class="user-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></span>
            <span><?= htmlspecialchars($_SESSION['nama']) ?></span>
        </div>
        <ul class="nav-links">
            <li><a href="dashboard.php">Menu</a></li>
            <li><a href="pesanan.php" class="active">Pesanan Saya</a></li>
            <li><a href="keranjang.php">Keranjang
                    (<?= isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0 ?>)</a></li>
        </ul>
        <div class="nav-user">
            <a href="../auth/logout.php" class="btn-logout">Keluar</a>
        </div>
    </nav>

    <div class="pesanan-container">
        <?php if (isset($_GET['msg'])): ?>
            <div
                style="background: #eafaf1; color: #1e8449; padding: 12px; border-left: 4px solid #27ae60; border-radius: 8px; margin-bottom: 20px;">
                ✅ <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div
                style="background: #fdecea; color: #c0392b; padding: 12px; border-left: 4px solid #e74c3c; border-radius: 8px; margin-bottom: 20px;">
                ⚠️ <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <h2 style="color:#6f4e37; margin-bottom: 20px;">Riwayat Pesanan Saya</h2>
        <table class="table-pesanan">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Detail Menu</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pesanan->num_rows > 0): ?>
                    <?php while ($row = $pesanan->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                            <td><?= htmlspecialchars($row['detail_menu']) ?></td>
                            <td style="font-weight:bold; color:#d2691e;"><?= formatRupiah($row['total_harga']) ?></td>
                            <td><span class="badge-status status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span>
                            </td>
                            <td>
                                <?php if ($row['status'] === 'pending'): ?>
                                    <form action="batal_pesan.php" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                                        <input type="hidden" name="pesanan_id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn-cancel">Batalkan</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #999; font-size: 13px;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:30px; color:#888;">☕ Belum ada riwayat pesanan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
</body>

</html>