<?php
// admin/proses_penjualan.php
// Controller untuk memproses Penambahan & Penghapusan data penjualan ke Supabase

session_start();
require_once __DIR__ . '/../config/db.php';       // Memuat koneksi lokal, helper isLoggedIn() & isAdmin()
require_once __DIR__ . '/../config/supabase.php'; // Memuat helper koneksi Supabase API

// Keamanan: Cek apakah user sudah login
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

// ============================================
// 1. REFACTOR FUNGSI TAMBAH (INSERT)
// ============================================
if (isset($_POST['tambah_penjualan'])) {
    // Baik admin maupun anggota (user) diperbolehkan menambah data penjualan
    
    // Validasi & sanitasi input dengan aman
    $nama_pelanggan = trim($_POST['nama_pelanggan'] ?? '');
    $nama_menu      = trim($_POST['nama_menu'] ?? '');
    $jumlah         = filter_var($_POST['jumlah'] ?? 0, FILTER_VALIDATE_INT);
    $total_harga    = filter_var($_POST['total_harga'] ?? 0, FILTER_VALIDATE_FLOAT);
    
    // Pastikan tidak ada data yang kosong atau tidak valid
    if (empty($nama_pelanggan) || empty($nama_menu) || $jumlah <= 0 || $total_harga <= 0) {
        $_SESSION['error_msg'] = "Gagal menambah data: Input tidak lengkap atau tidak valid.";
        redirect('kelola_penjualan.php');
    }
    
    // Siapkan payload data untuk dikirim ke Supabase (PostgreSQL)
    $payload = [
        'nama_pelanggan' => htmlspecialchars($nama_pelanggan),
        'nama_menu'      => htmlspecialchars($nama_menu),
        'jumlah'         => $jumlah,
        'total_harga'    => $total_harga
    ];
    
    // Panggil helper supabaseRequest dengan method POST ke tabel 'penjualan'
    $response = supabaseRequest('POST', 'penjualan', $payload);
    
    // Supabase REST API mengembalikan kode 201 Created atau 200 OK jika berhasil
    if ($response['code'] === 201 || $response['code'] === 200) {
        $_SESSION['success_msg'] = "Berhasil mencatat data penjualan ke Supabase!";
    } else {
        $errorMessage = $response['data']['message'] ?? json_encode($response['data']);
        $_SESSION['error_msg'] = "Gagal menyimpan ke Supabase (Code: {$response['code']}): {$errorMessage}";
    }
    
    redirect('kelola_penjualan.php');
}

// ============================================
// 2. REFACTOR FUNGSI HAPUS (DELETE)
// ============================================
if (isset($_GET['action']) && $_GET['action'] === 'hapus') {
    // KEAMANAN: Hanya admin yang boleh menghapus data penjualan
    if (!isAdmin()) {
        $_SESSION['error_msg'] = "Akses ditolak: Hanya Administrator yang berhak menghapus data penjualan.";
        redirect('kelola_penjualan.php');
    }
    
    // Validasi ID penjualan yang akan dihapus
    $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
    
    if ($id <= 0) {
        $_SESSION['error_msg'] = "Gagal menghapus: ID penjualan tidak valid.";
        redirect('kelola_penjualan.php');
    }
    
    // Memanggil API Supabase dengan query filter ?id=eq.{id}
    $endpoint = 'penjualan?id=eq.' . $id;
    $response = supabaseRequest('DELETE', $endpoint);
    
    // Supabase REST API mengembalikan 200 OK atau 204 No Content untuk penghapusan yang sukses
    if ($response['code'] === 200 || $response['code'] === 204) {
        $_SESSION['success_msg'] = "Data penjualan berhasil dihapus dari Supabase!";
    } else {
        $errorMessage = $response['data']['message'] ?? json_encode($response['data']);
        $_SESSION['error_msg'] = "Gagal menghapus data di Supabase (Code: {$response['code']}): {$errorMessage}";
    }
    
    redirect('kelola_penjualan.php');
}

// Fallback redirect jika diakses secara tidak sah
redirect('kelola_penjualan.php');
?>
