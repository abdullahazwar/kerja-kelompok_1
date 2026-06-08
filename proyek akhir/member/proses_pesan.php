<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isLoggedIn() || isAdmin()) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $menu_id = $_POST['menu_id'];
    $harga = $_POST['harga'];
    $jumlah = 1;
    $total_harga = $harga * $jumlah;

    $conn->begin_transaction();
    try {
        // Cek stok menu
        $stmt_cek = $conn->prepare("SELECT stok FROM menu WHERE id = ?");
        $stmt_cek->bind_param("i", $menu_id);
        $stmt_cek->execute();
        $res = $stmt_cek->get_result();
        $menu = $res->fetch_assoc();

        if (!$menu || $menu['stok'] < $jumlah) {
            throw new Exception("Stok tidak mencukupi.");
        }

        // Buat pesanan baru
        $stmt = $conn->prepare("INSERT INTO pesanan (user_id, total_harga, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("id", $user_id, $total_harga);
        $stmt->execute();
        $pesanan_id = $conn->insert_id;
        
        // Masukkan ke detail_pesanan
        $stmt_detail = $conn->prepare("INSERT INTO detail_pesanan (pesanan_id, menu_id, jumlah, harga_saat, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmt_detail->bind_param("iiidd", $pesanan_id, $menu_id, $jumlah, $harga, $total_harga);
        $stmt_detail->execute();
        
        // Kurangi stok menu
        $stmt_stok = $conn->prepare("UPDATE menu SET stok = stok - ? WHERE id = ?");
        $stmt_stok->bind_param("ii", $jumlah, $menu_id);
        $stmt_stok->execute();

        $conn->commit();
        header("Location: dashboard.php?msg=Pesanan Berhasil Dibuat");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: ../isi.php?error=Gagal Membuat Pesanan");
        exit;
    }
} else {
    header("Location: ../isi.php");
    exit;
}
