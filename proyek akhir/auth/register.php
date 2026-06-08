<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (isLoggedIn()) redirect('../member/dashboard.php');

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = sanitize($conn, $_POST['nama'] ?? '');
    $email    = sanitize($conn, $_POST['email'] ?? '');
    $telepon  = sanitize($conn, $_POST['telepon'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirm  = $_POST['konfirmasi'] ?? '';

    if (empty($nama) || empty($email) || empty($password)) {
        $error = 'Nama, email, dan password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        // Cek email sudah ada
        $cek = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $cek->bind_param("s", $email);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $error = 'Email sudah terdaftar. Gunakan email lain.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $ins  = $conn->prepare("INSERT INTO users (nama, email, telepon, password, role) VALUES (?, ?, ?, ?, 'anggota')");
            $ins->bind_param("ssss", $nama, $email, $telepon, $hash);
            if ($ins->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Terjadi kesalahan. Coba lagi.';
            }
            $ins->close();
        }
        $cek->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun – Kedai Kopi Sudut Senja</title>
    <link rel="stylesheet" href="../kedai.css">
    <link rel="stylesheet" href="auth.css">
</head>
<body class="auth-body">

    <div class="auth-wrapper">

        <div class="auth-brand">
            <a href="../index.php" class="brand-link">
                <span class="brand-icon">☕</span>
                <span class="brand-name">Kedai Kopi Sudut Senja</span>
            </a>
        </div>

        <div class="auth-card">
            <h2 class="auth-title">Buat Akun Baru</h2>
            <p class="auth-sub">Daftar untuk menikmati layanan kami</p>

            <?php if ($error): ?>
                <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?>
                    <a href="login.php">Login sekarang →</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">

                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama"
                           placeholder="Nama lengkap Anda"
                           value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           placeholder="contoh@email.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="telepon">No. Telepon <span class="optional">(opsional)</span></label>
                    <input type="text" id="telepon" name="telepon"
                           placeholder="0812-xxxx-xxxx"
                           value="<?= htmlspecialchars($_POST['telepon'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password"
                               placeholder="Minimal 6 karakter" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('password')">👁</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="konfirmasi">Konfirmasi Password</label>
                    <div class="input-wrap">
                        <input type="password" id="konfirmasi" name="konfirmasi"
                               placeholder="Ulangi password" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('konfirmasi')">👁</button>
                    </div>
                </div>

                <!-- Password strength bar -->
                <div class="pw-strength" id="pwStrength">
                    <div class="pw-bar" id="pwBar"></div>
                </div>
                <small id="pwLabel" style="color:gray;font-size:12px;"></small>

                <button type="submit" class="btn-auth">Daftar Sekarang</button>
            </form>

            <div class="auth-footer">
                <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
                <p><a href="../index.php">← Kembali ke Beranda</a></p>
            </div>
        </div>
    </div>

    <script>
        function togglePw(id) {
            const pw = document.getElementById(id);
            pw.type = pw.type === 'password' ? 'text' : 'password';
        }

        document.getElementById('password').addEventListener('input', function () {
            const val = this.value;
            const bar = document.getElementById('pwBar');
            const label = document.getElementById('pwLabel');
            let strength = 0;
            if (val.length >= 6)  strength++;
            if (val.length >= 10) strength++;
            if (/[A-Z]/.test(val))  strength++;
            if (/[0-9]/.test(val))  strength++;
            if (/[^A-Za-z0-9]/.test(val)) strength++;

            const pct   = (strength / 5) * 100;
            const color = strength <= 2 ? '#e74c3c' : strength <= 3 ? '#f39c12' : '#27ae60';
            const text  = strength <= 2 ? 'Lemah' : strength <= 3 ? 'Sedang' : 'Kuat';
            bar.style.width = pct + '%';
            bar.style.background = color;
            label.textContent = val ? `Kekuatan password: ${text}` : '';
        });
    </script>
</body>
</html>
