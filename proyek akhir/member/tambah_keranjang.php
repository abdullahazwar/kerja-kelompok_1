<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isLoggedIn() || isAdmin()) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu_id = (int)$_POST['menu_id'];
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
    if ($qty < 1) $qty = 1;

    // Cek ketersediaan menu di DB
    $stmt = $conn->prepare("SELECT nama, stok, tersedia FROM menu WHERE id = ?");
    $stmt->bind_param("i", $menu_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $menu = $res->fetch_assoc();

    if ($menu && $menu['tersedia'] == 1) {
        // Inisialisasi cart jika belum ada
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Cek total qty jika ditambah
        $current_qty = isset($_SESSION['cart'][$menu_id]) ? $_SESSION['cart'][$menu_id] : 0;
        $new_qty = $current_qty + $qty;

        if ($new_qty > $menu['stok']) {
            $msg = "Stok tidak mencukupi. Hanya bisa memesan maksimal " . $menu['stok'] . " porsi.";
            $type = "error";
        } else {
            $_SESSION['cart'][$menu_id] = $new_qty;
            $msg = "Berhasil menambahkan " . htmlspecialchars($menu['nama']) . " ke keranjang.";
            $type = "msg";
        }
    } else {
        $msg = "Menu tidak tersedia.";
        $type = "error";
    }

    // Redirect back to referrer or dashboard
    $redirect = $_SERVER['HTTP_REFERER'] ?? 'dashboard.php';
    $redirect_url = strtok($redirect, '?');
    header("Location: " . $redirect_url . "?" . $type . "=" . urlencode($msg));
    exit;
} else {
    header("Location: dashboard.php");
    exit;
}
