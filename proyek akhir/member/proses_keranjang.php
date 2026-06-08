<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isLoggedIn() || isAdmin()) {
    header("Location: ../auth/login.php");
    exit;
}

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

if ($action === 'update') {
    $menu_id = (int)$_POST['menu_id'];
    $jumlah = (int)$_POST['jumlah'];

    if ($jumlah <= 0) {
        unset($_SESSION['cart'][$menu_id]);
        header("Location: keranjang.php?msg=Menu berhasil dihapus dari keranjang.");
        exit;
    }

    // Cek stok menu
    $stmt = $conn->prepare("SELECT nama, stok FROM menu WHERE id = ?");
    $stmt->bind_param("i", $menu_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $menu = $res->fetch_assoc();

    if ($menu) {
        if ($jumlah > $menu['stok']) {
            $_SESSION['cart'][$menu_id] = $menu['stok'];
            header("Location: keranjang.php?error=Stok " . htmlspecialchars($menu['nama']) . " hanya tersedia " . $menu['stok'] . ".");
            exit;
        } else {
            $_SESSION['cart'][$menu_id] = $jumlah;
            header("Location: keranjang.php?msg=Jumlah pesanan berhasil diperbarui.");
            exit;
        }
    } else {
        unset($_SESSION['cart'][$menu_id]);
        header("Location: keranjang.php?error=Menu tidak ditemukan.");
        exit;
    }
}

if ($action === 'delete') {
    $menu_id = (int)$_POST['menu_id'];
    unset($_SESSION['cart'][$menu_id]);
    header("Location: keranjang.php?msg=Menu berhasil dihapus dari keranjang.");
    exit;
}

if ($action === 'checkout') {
    if (empty($_SESSION['cart'])) {
        header("Location: keranjang.php?error=Keranjang belanja kosong.");
        exit;
    }

    $conn->begin_transaction();
    try {
        $total_harga = 0;
        $items_to_process = [];

        //Validasi stok dan hitung total harga
        foreach ($_SESSION['cart'] as $menu_id => $jumlah) {
            $stmt = $conn->prepare("SELECT nama, harga, stok, tersedia FROM menu WHERE id = ?");
            $stmt->bind_param("i", $menu_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $menu = $res->fetch_assoc();

            if (!$menu || $menu['tersedia'] != 1) {
                throw new Exception("Menu " . ($menu ? htmlspecialchars($menu['nama']) : "tertentu") . " tidak tersedia lagi.");
            }

            if ($menu['stok'] < $jumlah) {
                throw new Exception("Stok untuk " . htmlspecialchars($menu['nama']) . " tidak mencukupi (Tersedia: " . $menu['stok'] . ").");
            }

            $subtotal = $menu['harga'] * $jumlah;
            $total_harga += $subtotal;

            $items_to_process[] = [
                'menu_id' => $menu_id,
                'nama' => $menu['nama'],
                'harga' => $menu['harga'],
                'jumlah' => $jumlah,
                'subtotal' => $subtotal
            ];
        }

        //Buat pesanan baru
        $stmt_pesanan = $conn->prepare("INSERT INTO pesanan (user_id, total_harga, status) VALUES (?, ?, 'pending')");
        $stmt_pesanan->bind_param("id", $user_id, $total_harga);
        $stmt_pesanan->execute();
        $pesanan_id = $conn->insert_id;

        // Masukkan ke detail_pesanan & Kurangi stok menu
        $stmt_detail = $conn->prepare("INSERT INTO detail_pesanan (pesanan_id, menu_id, jumlah, harga_saat, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmt_stok = $conn->prepare("UPDATE menu SET stok = stok - ? WHERE id = ?");

        foreach ($items_to_process as $item) {
            // detail
            $stmt_detail->bind_param("iiidd", $pesanan_id, $item['menu_id'], $item['jumlah'], $item['harga'], $item['subtotal']);
            $stmt_detail->execute();

            // update stok
            $stmt_stok->bind_param("ii", $item['jumlah'], $item['menu_id']);
            $stmt_stok->execute();
        }

        $conn->commit();
        unset($_SESSION['cart']); // Kosongkan keranjang
        header("Location: pesanan.php?msg=Pesanan berhasil dibuat!");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: keranjang.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}

header("Location: keranjang.php");
exit;
