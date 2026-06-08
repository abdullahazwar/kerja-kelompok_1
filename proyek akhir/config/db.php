<?php
// ============================================
// KONFIGURASI DATABASE
// Kedai Kopi Sudut Senja
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kedaikopi_db');
define('DB_CHARSET', 'utf8mb4');

// Buat koneksi MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die('<div style="font-family:Arial;padding:30px;color:#c0392b;background:#fdecea;border:1px solid #e74c3c;-webkit-border-radius: 8px; border-radius: 8px;margin:20px;">
        <h3>⚠️ Koneksi Database Gagal</h3>
        <p>Error: ' . $conn->connect_error . '</p>
        <p>Pastikan XAMPP/MySQL sudah berjalan dan database <strong>kedaikopi_db</strong> sudah di-import.</p>
    </div>');
}

// Set charset
$conn->set_charset(DB_CHARSET);

// Helper: sanitasi input
function sanitize($conn, $data) {
    return $conn->real_escape_string(htmlspecialchars(trim($data)));
}

// Helper: redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper: cek session login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper: cek role admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Helper: format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>
