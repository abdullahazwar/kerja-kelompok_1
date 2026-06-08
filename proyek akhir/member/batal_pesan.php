<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isLoggedIn() || isAdmin()) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesanan_id'])) {
    $pesanan_id = (int)$_POST['pesanan_id'];
    $user_id = $_SESSION['user_id'];

    // Ambil detail pesanan untuk memverifikasi kepemilikan dan status
    $stmt = $conn->prepare("SELECT status FROM pesanan WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $pesanan_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $pesanan = $res->fetch_assoc();

    if ($pesanan) {
        if ($pesanan['status'] !== 'pending') {
            header("Location: pesanan.php?error=" . urlencode("Hanya pesanan berstatus Pending yang dapat dibatalkan."));
            exit;
        }

        $conn->begin_transaction();
        try {
            // Update status pesanan ke dibatalkan
            $stmt_update = $conn->prepare("UPDATE pesanan SET status = 'dibatalkan' WHERE id = ?");
            $stmt_update->bind_param("i", $pesanan_id);
            $stmt_update->execute();

            // Kembalikan stok menu
            $stmt_items = $conn->prepare("SELECT menu_id, jumlah FROM detail_pesanan WHERE pesanan_id = ?");
            $stmt_items->bind_param("i", $pesanan_id);
            $stmt_items->execute();
            $res_items = $stmt_items->get_result();

            $stmt_restor = $conn->prepare("UPDATE menu SET stok = stok + ? WHERE id = ?");
            while ($item = $res_items->fetch_assoc()) {
                $stmt_restor->bind_param("ii", $item['jumlah'], $item['menu_id']);
                $stmt_restor->execute();
            }

            $conn->commit();
            header("Location: pesanan.php?msg=" . urlencode("Pesanan berhasil dibatalkan dan stok dikembalikan."));
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            header("Location: pesanan.php?error=" . urlencode("Gagal membatalkan pesanan."));
            exit;
        }
    } else {
        header("Location: pesanan.php?error=" . urlencode("Pesanan tidak ditemukan."));
        exit;
    }
} else {
    header("Location: pesanan.php");
    exit;
}
