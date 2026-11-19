<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Koneksi database langsung agar tidak error pada $conn
$conn = mysqli_connect("localhost", "root", "", "nrcskin_db");
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        return "Rp " . number_format((float)$angka, 0, ',', '.');
    }
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['tambah_kat'])) {
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama_kategori'] ?? ''));
    if ($nama !== '') {
        mysqli_query($conn, "INSERT INTO kategori (nama_kategori) VALUES ('$nama')");
    }
    header("Location: kategori.php");
    exit;
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id > 0) {
        mysqli_query($conn, "DELETE FROM kategori WHERE id = $id");
    }
    header("Location: kategori.php");
    exit;
}

$kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="mobile-container">
    <div class="top-nav">
        <a href="index.php" style="color:#7a4250; font-size:20px;"><i class="fa-solid fa-arrow-left"></i></a>
        <h3 style="color:#7a4250;">Kelola Kategori</h3>
        <div></div>
    </div>

    <form method="POST" style="display:flex; gap:8px; margin-bottom:15px;">
        <input type="text" name="nama_kategori" class="form-control" placeholder="Nama kategori baru..." required>
        <button type="submit" name="tambah_kat" class="btn-primary btn-sm">+ Tambah</button>
    </form>

    <?php if (!$kategori): ?>
        <div class="glass-card" style="text-align:center; color:#c93b4b;">
            Query gagal: <?= htmlspecialchars(mysqli_error($conn)) ?>
        </div>
    <?php elseif (mysqli_num_rows($kategori) === 0): ?>
        <div class="glass-card" style="text-align:center;">
            Belum ada kategori.
        </div>
    <?php else: ?>
        <?php while ($k = mysqli_fetch_assoc($kategori)): ?>
        <div class="cart-item" style="justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:12px;">
                <i class="fa-solid fa-tag" style="font-size:24px; color:#c93b4b;"></i>
                <div>
                    <div style="font-weight:600;"><?= htmlspecialchars($k['nama_kategori'] ?? '') ?></div>
                </div>
            </div>
            <a href="?hapus=<?= (int)$k['id'] ?>" onclick="return confirm('Apakah kamu yakin ingin menghapus Kategori ini?')" style="color:#c93b4b;">
                <i class="fa-solid fa-trash-can"></i>
            </a>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>
</body>
</html>