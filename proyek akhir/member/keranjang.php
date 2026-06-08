<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isLoggedIn() || isAdmin())
    redirect('../auth/login.php');

$user_id = $_SESSION['user_id'];
$cart_items = [];
$total_harga = 0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
    if (!empty($ids)) {
        $query = $conn->query("SELECT m.*, k.nama AS kategori FROM menu m JOIN kategori_menu k ON m.kategori_id=k.id WHERE m.id IN ($ids)");
        if ($query) {
            while ($row = $query->fetch_assoc()) {
                $menu_id = $row['id'];
                $qty = $_SESSION['cart'][$menu_id];

                // Pastikan qty tidak melebihi stok yang ada
                if ($qty > $row['stok']) {
                    $qty = $row['stok'];
                    $_SESSION['cart'][$menu_id] = $qty;
                }

                if ($qty > 0) {
                    $subtotal = $row['harga'] * $qty;
                    $total_harga += $subtotal;
                    $cart_items[] = [
                        'id' => $menu_id,
                        'nama' => $row['nama'],
                        'harga' => $row['harga'],
                        'gambar' => $row['gambar'],
                        'stok' => $row['stok'],
                        'kategori' => $row['kategori'],
                        'qty' => $qty,
                        'subtotal' => $subtotal
                    ];
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Kedai Kopi Sudut Senja</title>
    <link rel="stylesheet" href="../kedai.css">
    <link rel="stylesheet" href="member.css">
    <style>
        body {
            background: #fff8f0;
        }

        .cart-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .cart-title {
            color: #3e2723;
            margin-bottom: 24px;
            font-weight: 700;
            font-size: 28px;
        }

        .cart-wrapper {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        @media (max-width: 768px) {
            .cart-wrapper {
                grid-template-columns: 1fr;
            }
        }

        .cart-items-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #f1ece8;
            gap: 16px;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-img {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            object-fit: cover;
            background: #fff3e0;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-info h4 {
            color: #3e2723;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .cart-item-info span {
            color: #d2691e;
            font-weight: 600;
            font-size: 14px;
        }

        .cart-item-qty {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .qty-btn {
            background: #f1ece8;
            color: #6f4e37;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .qty-btn:hover:not(:disabled) {
            background: #6f4e37;
            color: #ffcc70;
        }

        .qty-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .qty-input {
            width: 32px;
            text-align: center;
            border: none;
            background: transparent;
            font-weight: 600;
            font-size: 15px;
            color: #3e2723;
        }

        .cart-item-subtotal {
            min-width: 100px;
            text-align: right;
            font-weight: 700;
            color: #3e2723;
        }

        .cart-item-delete {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .cart-item-delete:hover {
            background: #e74c3c;
            color: white;
        }

        .summary-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            height: fit-content;
        }

        .summary-card h3 {
            color: #3e2723;
            margin-bottom: 20px;
            border-bottom: 2px solid #f1ece8;
            padding-bottom: 12px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
            font-size: 15px;
        }

        .summary-total {
            font-size: 18px;
            font-weight: 700;
            color: #d2691e;
            border-top: 2px dashed #f1ece8;
            padding-top: 16px;
            margin-top: 16px;
        }

        .btn-checkout {
            display: block;
            width: 100%;
            background: #6f4e37;
            color: #ffcc70;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            text-align: center;
            transition: all 0.25s;
            margin-top: 24px;
        }

        .btn-checkout:hover {
            background: #3e2723;
            transform: translateY(-2px);
        }

        .empty-cart-state {
            background: white;
            border-radius: 16px;
            padding: 50px 20px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .empty-cart-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }

        .empty-cart-state h3 {
            color: #3e2723;
            font-size: 20px;
            margin-bottom: 8px;
        }

        .empty-cart-state p {
            color: #777;
            margin-bottom: 24px;
        }

        .btn-shop {
            display: inline-block;
            background: #6f4e37;
            color: #ffcc70;
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-shop:hover {
            background: #3e2723;
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
            <li><a href="pesanan.php">Pesanan Saya</a></li>
            <li><a href="keranjang.php" class="active">Keranjang
                    (<?= isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0 ?>)</a></li>
        </ul>
        <div class="nav-user">
            <a href="../auth/logout.php" class="btn-logout">Keluar</a>
        </div>
    </nav>

    <div class="cart-container">

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

        <h2 class="cart-title">Keranjang Belanja</h2>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart-state">
                <div class="empty-cart-icon">🛒</div>
                <h3>Keranjangmu masih kosong nih</h3>
                <p>Sepertinya kamu belum memilih kopi atau makanan lezat kami.</p>
                <a href="dashboard.php" class="btn-shop">Pilih Menu Sekarang</a>
            </div>
        <?php else: ?>
            <div class="cart-wrapper">
                <div class="cart-items-card">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <?php
                            $src = '';
                            if ($item['gambar'] && file_exists('../' . $item['gambar']))
                                $src = '../' . $item['gambar'];
                            ?>
                            <?php if ($src): ?>
                                <img src="<?= htmlspecialchars($src) ?>" class="cart-item-img"
                                    alt="<?= htmlspecialchars($item['nama']) ?>">
                            <?php else: ?>
                                <div class="cart-item-img"
                                    style="display:flex;align-items:center;justify-content:center;font-size:28px;">☕</div>
                            <?php endif; ?>

                            <div class="cart-item-info">
                                <h4><?= htmlspecialchars($item['nama']) ?></h4>
                                <span><?= formatRupiah($item['harga']) ?></span>
                            </div>

                            <div class="cart-item-qty">
                                <form action="proses_keranjang.php" method="POST" style="display:flex; align-items:center;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="menu_id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="jumlah" value="<?= $item['qty'] - 1 ?>" class="qty-btn"
                                        <?= $item['qty'] <= 1 ? 'disabled' : '' ?>>-</button>
                                    <span class="qty-input"><?= $item['qty'] ?></span>
                                    <button type="submit" name="jumlah" value="<?= $item['qty'] + 1 ?>" class="qty-btn"
                                        <?= $item['qty'] >= $item['stok'] ? 'disabled' : '' ?>>+</button>
                                </form>
                            </div>

                            <div class="cart-item-subtotal">
                                <?= formatRupiah($item['subtotal']) ?>
                            </div>

                            <div>
                                <form action="proses_keranjang.php" method="POST"
                                    onsubmit="return confirm('Hapus menu ini dari keranjang?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="menu_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="cart-item-delete" title="Hapus">🗑️</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-card">
                    <h3>Ringkasan Belanja</h3>
                    <div class="summary-row">
                        <span>Total Item</span>
                        <span style="font-weight:600;"><?= array_sum(array_column($cart_items, 'qty')) ?> porsi</span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total Harga</span>
                        <span><?= formatRupiah($total_harga) ?></span>
                    </div>

                    <form action="proses_keranjang.php" method="POST"
                        onsubmit="return confirm('Apakah Anda yakin ingin melakukan pemesanan ini?');">
                        <input type="hidden" name="action" value="checkout">
                        <button type="submit" class="btn-checkout">Pesan Sekarang</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>

</body>

</html>
</body>

</html>