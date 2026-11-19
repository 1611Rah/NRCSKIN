<?php
require 'config/koneksi.php';

$error = '';
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $_SESSION['user_id'] = $data['id'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['role'] = $data['role'];

        if ($data['role'] == 'admin') {
            header("Location: admin/index.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "ID atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NRCSKIN</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="mobile-container" style="display:flex; flex-direction:column; justify-content:center;">
    <div class="brand-header">
        <div style="font-size:24px; color:#8c4b5b;">✦</div>
        <div class="brand-title">NRCSKIN</div>
        <div class="brand-sub">SKINCARE FOR A BRIGHTER YOU</div>
        <p style="margin-top:15px; font-size:13px; letter-spacing:1.5px;">RAWAT KULITMU, MULAI DARI SINI</p>
    </div>

    <div class="glass-card" style="margin-top:15px;">
        <h3 style="text-align:center; margin-bottom:20px; color:#7a4250;">Login Sekarang</h3>
        <?php if($error): ?>
            <p style="color:#c93b4b; font-size:12px; text-align:center; margin-bottom:10px;"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>ID</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan ID (contoh: Janjiw / admin01)" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required>
            </div>
            <button type="submit" name="login" class="btn-primary" style="margin-top:10px;">Login</button>
        </form>
        <p style="text-align:center; font-size:12px; margin-top:15px;">
            Belum punya akun? <a href="register.php" style="color:#8c4b5b; font-weight:600;">Daftar sekarang</a>
        </p>
    </div>
</div>
</body>
</html>