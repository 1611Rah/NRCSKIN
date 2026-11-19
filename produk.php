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

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id > 0) {
        $q_img = mysqli_query($conn, "SELECT gambar FROM produk WHERE id = $id");
        if ($q_img && mysqli_num_rows($q_img) > 0) {
            $img_data = mysqli_fetch_assoc($q_img);
            $file_path = __DIR__ . '/../assets/products/' . ($img_data['gambar'] ?? '');
            if (!empty($img_data['gambar']) && $img_data['gambar'] !== 'default.png' && is_file($file_path)) {
                @unlink($file_path);
            }
        }
        mysqli_query($conn, "DELETE FROM produk WHERE id = $id");
    }
    header("Location: produk.php");
    exit;
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$where = $search !== '' ? "WHERE nama_produk LIKE '%$search%'" : "";
$produk = mysqli_query($conn, "SELECT * FROM produk $where ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Produk - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="mobile-container">
    <div class="top-nav">
        <a href="index.php" style="color:#7a4250; font-size:20px;"><i class="fa-solid fa-arrow-left"></i></a>
        <h3 style="color:#7a4250;">Kelola Data Produk</h3>
        <div></div>
    </div>

    <div style="display:flex; gap:8px; margin-bottom:15px;">
        <form method="GET" style="flex:1;">
            <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($search) ?>" style="padding:8px 12px;">
        </form>
        <a href="tambah_produk.php" class="btn-primary btn-sm" style="display:flex; align-items:center;">+ Tambah Produk</a>
    </div>

    <?php if (!$produk): ?>
        <div class="glass-card" style="text-align:center; color:#c93b4b;">
            Query gagal: <?= htmlspecialchars(mysqli_error($conn)) ?>
        </div>
    <?php elseif (mysqli_num_rows($produk) === 0): ?>
        <div class="glass-card" style="text-align:center;">
            Belum ada data produk.
        </div>
    <?php else: ?>
        <?php while ($row = mysqli_fetch_assoc($produk)):
            $gambar = $row['gambar'] ?? 'default.png';
            $img_url = "../assets/products/" . rawurlencode($gambar);
            $img_disk = __DIR__ . "/../assets/products/" . $gambar;
        ?>
        <div class="cart-item" style="justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="cart-thumb" style="display:flex; align-items:center; justify-content:center; overflow:hidden;">
                    <?php if ($gambar !== 'default.png' && is_file($img_disk)): ?>
                        <img src="<?= $img_url ?>" alt="<?= htmlspecialchars($row['nama_produk'] ?? '') ?>" style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                        <i class="fa-solid fa-pump-soap" style="font-size:26px; color:#c77d8e;"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <div style="font-weight:600; font-size:14px;"><?= htmlspecialchars($row['nama_produk'] ?? '') ?></div>
                    <div style="font-size:12px; color:#8c4b5b;"><?= formatRupiah($row['harga'] ?? 0) ?></div>
                    <div style="font-size:11px; color:#6e3b47;">Stok : <?= (int)($row['stok'] ?? 0) ?></div>
                </div>
            </div>
            <div style="display:flex; gap:10px; font-size:16px;">
                <a href="?hapus=<?= (int)$row['id'] ?>" onclick="return confirm('Apakah kamu yakin ingin menghapus produk ini?')" style="color:#c93b4b;">
                    <i class="fa-solid fa-trash-can"></i>
                </a>
            </div>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>
</body>
</html>