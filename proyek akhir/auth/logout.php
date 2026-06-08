<?php
// baca data login aktif
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role'];

if (isset($_POST['confirm_logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

if (isset($_POST['cancel_logout'])) {
    if ($user_role === 'admin') {
        header("Location: ../admin/index.php");
    } else {
        header("Location: ../member/dashboard.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi logout</title>
    <link rel="stylesheet" href="auth.css">

</head>

<body>

    <div class="logout-box">
        <!-- Visual pendukung bertema kopi -->
        <div class="cofee-icon"></div>
        <p>Apakah Anda yakin ingin keluar?</p>

        <!-- Form untuk memproses keputusan ke backend PHP -->
        <form method="POST" action="">
            <div class="button-group">
                <button type="submit" name="cancel_logout" class="btn btn-cancel">Batal</button>
                <button type="submit" name="confirm_logout" class="btn btn-confirm">Ya, Keluar</button>
            </div>
        </form>
    </div>

</body>

</html>