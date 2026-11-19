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

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Hitung pesanan yang belum selesai untuk notifikasi
$jml_pesanan = 0;
$q_pesanan = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pesanan WHERE status_pesanan != 'Selesai'");
if ($q_pesanan) {
    $row_pesanan = mysqli_fetch_assoc($q_pesanan);
    $jml_pesanan = (int)($row_pesanan['total'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NRCSKIN</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="mobile-container">
    <div class="top-nav">
        <div>
            <div class="brand-title" style="font-size:20px;">✦ NRCSKIN</div>
            <div class="brand-sub" style="font-size:8px;">ADMIN PANEL</div>
        </div>
        <a href="../logout.php" style="color:#7a4250; font-size:18px;"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>

    <div style="margin:20px 0;">
        <h2 style="font-family:'Playfair Display', serif; font-size:26px; color:#7a4250;">Selamat Datang,<br>Admin</h2>
        <p style="font-size:12px; color:#6e3b47; margin-top:5px;">Kelola produk dan transaksi dengan mudah dan cepat.</p>
    </div>

    <h4 style="color:#7a4250; margin-top:20px;">Menu Utama</h4>

    <div class="admin-grid">
        <a href="produk.php" class="admin-menu-box">
            <i class="fa-solid fa-box-open"></i>
            <div style="display:flex; justify-content:space-between; align-items:end;">
                <span>Kelola data<br>produk</span>
                <span>▶</span>
            </div>
        </a>

        <a href="kategori.php" class="admin-menu-box">
            <i class="fa-solid fa-table-cells-large"></i>
            <div style="display:flex; justify-content:space-between; align-items:end;">
                <span>Kelola kategori</span>
                <span>▶</span>
            </div>
        </a>

        <a href="produk.php" class="admin-menu-box">
            <i class="fa-solid fa-tags"></i>
            <div style="display:flex; justify-content:space-between; align-items:end;">
                <span>Kelola Stok &<br>Harga</span>
                <span>▶</span>
            </div>
        </a>

        <a href="transaksi.php" class="admin-menu-box" style="position:relative;">
            <?php if ($jml_pesanan > 0): ?>
                <span style="position:absolute; top:12px; right:12px; background:#c93b4b; color:#fff; font-size:11px; padding:2px 8px; border-radius:12px;">
                    <?= $jml_pesanan ?> Baru
                </span>
            <?php endif; ?>
            <i class="fa-solid fa-cash-register"></i>
            <div style="display:flex; justify-content:space-between; align-items:end;">
                <span>Kelola Transaksi</span>
                <span>▶</span>
            </div>
        </a>
    </div>
</div>
</body>
</html>