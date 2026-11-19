<?php
require 'config/koneksi.php';

if (isset($_POST['daftar'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $alamat   = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_hp    = mysqli_real_escape_string($conn, $_POST['no_hp']);

    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        $err = "ID sudah terdaftar, gunakan ID lain.";
    } else {
        mysqli_query($conn, "INSERT INTO users (username, password, role, alamat, no_hp) VALUES ('$username', '$password', 'user', '$alamat', '$no_hp')");
        echo "<script>alert('Akun berhasil dibuat! Silakan Login.'); window.location='login.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - NRCSKIN</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="mobile-container" style="display:flex; flex-direction:column; justify-content:center;">
    <div class="glass-card">
        <h3 style="text-align:center; margin-bottom:20px; color:#7a4250;">Silahkan Membuat akun</h3>
        <?php if(isset($err)) echo "<p style='color:red; font-size:12px; text-align:center;'>$err</p>"; ?>
        <form method="POST">
            <div class="form-group">
                <label>ID</label>
                <input type="text" name="username" class="form-control" minlength="6" required>
                <small style="font-size:10px; color:#8c4b5b;">**Isi ID dengan Huruf dan Angka minimal 6 karakter</small>
            </div>
            <div class="form-group">
                <label>PASSWORD</label>
                <input type="password" name="password" class="form-control" required>
                <small style="font-size:10px; color:#8c4b5b;">**Buat Password nya yang kuat yaa</small>
            </div>
            <div class="form-group">
                <label>No. HP</label>
                <input type="text" name="no_hp" class="form-control" placeholder="0812xxxx" required>
            </div>
            <div class="form-group">
                <label>Alamat Pengiriman</label>
                <textarea name="alamat" class="form-control" rows="2" placeholder="Jl. Gedung No XX..." required></textarea>
            </div>
            <button type="submit" name="daftar" class="btn-primary" style="margin-top:10px;">Daftar</button>
        </form>
        <p style="text-align:center; font-size:12px; margin-top:15px;">
            Sudah punya akun? <a href="login.php" style="color:#8c4b5b; font-weight:600;">Login</a>
        </p>
    </div>
</div>
</body>
</html>