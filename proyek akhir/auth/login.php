<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// login, arahkan ke halaman sesuai role
if (isLoggedIn()) {
    if (isAdmin()) redirect('../admin/index.php');
    else redirect('../member/dashboard.php');
}

$error = '';
$success = '';

// sesi pilih login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_req = $_POST['role'] ?? 'anggota';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = $conn->prepare("SELECT id, nama, password, role FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param("ss", $email, $role_req);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama']    = $user['nama'];
                $_SESSION['email']   = $email;
                $_SESSION['role']    = $user['role'];

                if ($user['role'] === 'admin') redirect('../admin/index.php');
                else redirect('../member/dashboard.php');
            } else {
                $error = 'Password salah. Silakan coba lagi.';
            }
        } else {
            $error = 'Akun tidak ditemukan.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Kedai Kopi Sudut Senja</title>
    <link rel="stylesheet" href="../kedai.css">
    <link rel="stylesheet" href="auth.css">
</head>
<body class="auth-body">

    <div class="auth-wrapper">

        <!-- Logo / Branding -->
        <div class="auth-brand">
            <a href="../index.php" class="brand-link">
                <span class="brand-icon">☕</span>
                <span class="brand-name">Kedai Kopi Sudut Senja</span>
            </a>
        </div>

        <div class="auth-card">
            <h2 class="auth-title">Selamat Datang</h2>
            <p class="auth-sub">Silakan masuk untuk melanjutkan</p>

            <!-- Tab Pilih Role -->
            <div class="role-tabs" id="roleTabs">
                <button type="button" class="role-tab active" data-role="anggota" onclick="setRole('anggota')">
                    👤 Anggota
                </button>
                <button type="button" class="role-tab" data-role="admin" onclick="setRole('admin')">
                    🔐 Admin
                </button>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" id="loginForm">
                <input type="hidden" name="role" id="roleInput" value="anggota">

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                        placeholder="contoh@email.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password"
                            placeholder="Masukkan password" required>
                        <button type="button" class="toggle-pw" onclick="togglePw()" title="Tampilkan password">👁</button>
                    </div>
                </div>

                <button type="submit" class="btn-auth" id="submitBtn">Masuk sebagai Anggota</button>
            </form>

            <div class="auth-footer">
                <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                <p><a href="../index.php">← Kembali ke Beranda</a></p>
            </div>
        </div>
    </div>

    <script>
        function setRole(role) {
            document.getElementById('roleInput').value = role;
            const tabs = document.querySelectorAll('.role-tab');
            tabs.forEach(t => t.classList.remove('active'));
            document.querySelector(`[data-role="${role}"]`).classList.add('active');
            const label = role === 'admin' ? 'Admin' : 'Anggota';
            document.getElementById('submitBtn').textContent = `Masuk sebagai ${label}`;
        }

        function togglePw() {
            const pw = document.getElementById('password');
            pw.type = pw.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
