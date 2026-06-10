<?php
// admin/kelola_penjualan.php
// Halaman Manajemen Data Penjualan Terkoneksi dengan Supabase REST API

session_start();
require_once __DIR__ . '/../config/db.php';       // Memuat koneksi lokal, helper login & role
require_once __DIR__ . '/../config/supabase.php'; // Memuat helper API Supabase

// Keamanan: Cek apakah user sudah login
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$pageTitle = 'Kelola Penjualan (Supabase)';

// 1. Ambil data penjualan dari Supabase (GET request)
// Data diurutkan dari yang terbaru (created_at descending)
$response = supabaseRequest('GET', 'penjualan?select=*&order=created_at.desc');
$penjualan_list = [];
$fetch_error = null;

if ($response['code'] === 200) {
    $penjualan_list = $response['data'];
} else {
    // Jika Supabase belum dikonfigurasi atau tabel belum siap
    $fetch_error = "Gagal memuat data dari Supabase (Code {$response['code']}).";
    if (isset($response['data']['error'])) {
        $fetch_error .= " Detail: " . $response['data']['error'];
    } elseif (isset($response['data']['message'])) {
        $fetch_error .= " Detail: " . $response['data']['message'];
    }
}

// 2. Ambil data menu lokal (MySQL) untuk mempermudah pilihan input form
$menus = $conn->query("SELECT nama, harga FROM menu WHERE tersedia = 1 ORDER BY nama ASC");
$menu_list = [];
if ($menus) {
    while ($m = $menus->fetch_assoc()) {
        $menu_list[] = $m;
    }
}

// 3. Ambil pesan notifikasi dari session
$success_msg = $_SESSION['success_msg'] ?? null;
$error_msg = $_SESSION['error_msg'] ?? null;
unset($_SESSION['success_msg'], $_SESSION['error_msg']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> – Kedai Kopi Sudut Senja</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .penjualan-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }
        @media (min-width: 992px) {
            .penjualan-container {
                grid-template-columns: 320px 1fr;
            }
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 13.5px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 13.5px;
            box-sizing: border-box;
        }
        .form-group input[readonly] {
            background-color: #f5f5f5;
            cursor: not-allowed;
            border: 1px solid #ddd;
        }
        .alert-box {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13.5px;
        }
        .alert-success {
            background-color: #eafaf1;
            color: #1e8449;
            border-left: 4px solid #27ae60;
        }
        .alert-error {
            background-color: #fdecea;
            color: #c0392b;
            border-left: 4px solid #e74c3c;
        }
        .badge-role {
            font-size: 11px;
            background: #eee;
            padding: 2px 6px;
            border-radius: 3px;
            color: #555;
            margin-left: 5px;
        }
    </style>
</head>
<body>
<div class="admin-layout">

    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <h3>📊 Data Penjualan (Supabase PostgreSQL)</h3>
                <p>Migrasi Pencatatan Penjualan Terintegrasi Cloud Database</p>
            </div>
            <div class="topbar-right">
                <div class="user-badge">
                    <div class="avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
                    <span><?= htmlspecialchars($_SESSION['nama']) ?></span>
                    <span class="badge-role"><?= htmlspecialchars($_SESSION['role']) ?></span>
                </div>
            </div>
        </div>

        <!-- PAGE BODY -->
        <div class="page-body">
            
            <!-- Notifikasi Aksi -->
            <?php if ($success_msg): ?>
                <div class="alert-box alert-success">✅ <?= htmlspecialchars($success_msg) ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert-box alert-error">⚠️ <?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>

            <div class="penjualan-container">
                
                <!-- KOLOM KIRI: Form Input (Tambah Penjualan) -->
                <div class="card">
                    <div class="card-header">
                        <h3>✍️ Catat Penjualan</h3>
                    </div>
                    <div class="card-body">
                        <form action="proses_penjualan.php" method="POST">
                            
                            <div class="form-group">
                                <label for="nama_pelanggan">Nama Pelanggan</label>
                                <input type="text" id="nama_pelanggan" name="nama_pelanggan" required placeholder="Nama Pembeli / No. Meja">
                            </div>

                            <div class="form-group">
                                <label for="nama_menu">Menu Terjual</label>
                                <select id="nama_menu" name="nama_menu" required onchange="handleMenuChange()">
                                    <option value="" data-price="0">-- Pilih Menu --</option>
                                    <?php foreach ($menu_list as $menu): ?>
                                        <option value="<?= htmlspecialchars($menu['nama']) ?>" data-price="<?= $menu['harga'] ?>">
                                            <?= htmlspecialchars($menu['nama']) ?> (<?= formatRupiah($menu['harga']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="jumlah">Jumlah Porsi</label>
                                <input type="number" id="jumlah" name="jumlah" min="1" value="1" required oninput="calculateTotal()">
                            </div>

                            <div class="form-group">
                                <label for="total_harga">Total Harga (Otomatis)</label>
                                <input type="number" id="total_harga" name="total_harga" readonly required placeholder="0">
                            </div>

                            <button type="submit" name="tambah_penjualan" class="btn btn-primary" style="width: 100%; margin-top: 10px; padding: 10px;">
                                💾 Simpan Penjualan
                            </button>
                        </form>
                    </div>
                </div>

                <!-- KOLOM KANAN: Daftar Penjualan (Supabase) -->
                <div class="card">
                    <div class="card-header">
                        <h3>📋 Riwayat Penjualan di Supabase</h3>
                    </div>
                    
                    <?php if ($fetch_error): ?>
                        <div class="alert-box alert-error" style="margin: 15px;"><?= htmlspecialchars($fetch_error) ?></div>
                        <div style="padding: 20px; font-size: 13.5px;">
                            <p><strong>Panduan untuk Pengguna:</strong></p>
                            <ol>
                                <li>Pastikan variabel <code>SUPABASE_URL</code> dan <code>SUPABASE_KEY</code> telah ditambahkan ke file <code>.env</code> lokal Anda (atau Vercel Environment Variables).</li>
                                <li>Pastikan Anda telah membuat tabel <code>penjualan</code> di editor SQL Supabase Anda dengan struktur berikut:</li>
                            </ol>
                            <pre style="background: #272822; color: #f8f8f2; padding: 15px; border-radius: 4px; overflow-x: auto; font-family: monospace; font-size: 12px;">
CREATE TABLE penjualan (
    id SERIAL PRIMARY KEY,
    nama_pelanggan VARCHAR(100) NOT NULL,
    nama_menu VARCHAR(150) NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,
    total_harga NUMERIC(10, 2) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);</pre>
                        </div>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tanggal</th>
                                        <th>Pelanggan</th>
                                        <th>Menu</th>
                                        <th>Jumlah</th>
                                        <th>Total Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($penjualan_list)): ?>
                                        <?php foreach ($penjualan_list as $item): ?>
                                            <tr>
                                                <td>#<?= htmlspecialchars($item['id'] ?? '-') ?></td>
                                                <td style="font-size: 12px; color: #666;">
                                                    <?= isset($item['created_at']) ? date('d M Y H:i', strtotime($item['created_at'])) : '-' ?>
                                                </td>
                                                <td><?= htmlspecialchars($item['nama_pelanggan'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($item['nama_menu'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($item['jumlah'] ?? '-') ?> porsi</td>
                                                <td style="font-weight: bold; color: #d2691e;">
                                                    <?= formatRupiah($item['total_harga'] ?? 0) ?>
                                                </td>
                                                <td>
                                                    <?php if (isAdmin()): ?>
                                                        <a href="proses_penjualan.php?action=hapus&id=<?= $item['id'] ?>" 
                                                           class="btn btn-danger btn-sm"
                                                           onclick="return confirm('Apakah Anda yakin ingin menghapus data penjualan ID #<?= $item['id'] ?> dari Supabase?')">
                                                           🗑️ Hapus
                                                        </a>
                                                    <?php else: ?>
                                                        <span style="color: #999; font-size: 11px; font-style: italic;">Hanya Admin</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" style="text-align: center; color: #777;">Belum ada data penjualan tercatat di Supabase.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </div><!-- /page-body -->
    </div><!-- /main-content -->

</div><!-- /admin-layout -->

<script>
// Menghitung total harga secara real-time berdasarkan menu dan jumlah
function handleMenuChange() {
    calculateTotal();
}

function calculateTotal() {
    const select = document.getElementById('nama_menu');
    const selectedOption = select.options[select.selectedIndex];
    const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
    const qty = parseInt(document.getElementById('jumlah').value) || 1;
    
    const total = price * qty;
    document.getElementById('total_harga').value = total;
}
</script>
</body>
</html>
