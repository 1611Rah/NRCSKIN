<?php
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once __DIR__ . '/config/koneksi.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    $conn = @mysqli_connect("localhost", "root", "", "nrcskin_db");
    if (!$conn) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }
    mysqli_set_charset($conn, "utf8mb4");
}

if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        return "Rp " . number_format((float)$angka, 0, ',', '.');
    }
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = (int)$_SESSION['user_id'];
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$q_p = mysqli_query($conn, "SELECT * FROM produk WHERE id = $id");
$p = ($q_p && mysqli_num_rows($q_p) > 0) ? mysqli_fetch_assoc($q_p) : null;

if (!$p) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['add_to_cart'])) {
    $cek = mysqli_query($conn, "SELECT id, qty FROM keranjang WHERE user_id = $uid AND produk_id = $id");
    if ($cek && mysqli_num_rows($cek) > 0) {
        mysqli_query($conn, "UPDATE keranjang SET qty = qty + 1 WHERE user_id = $uid AND produk_id = $id");
    } else {
        mysqli_query($conn, "INSERT INTO keranjang (user_id, produk_id, qty) VALUES ($uid, $id, 1)");
    }
    header("Location: detail.php?id=$id&added=1");
    exit;
}

$q_cart = mysqli_query($conn, "SELECT SUM(qty) AS total FROM keranjang WHERE user_id = $uid");
$cart_count = ($q_cart && ($r_cart = mysqli_fetch_assoc($q_cart))) ? (int)($r_cart['total'] ?? 0) : 0;

function ambilFotoProduk(array $produk): ?string {
    $nama_produk = strtolower(trim((string)($produk['nama_produk'] ?? '')));
    $kolom_gambar = trim((string)($produk['gambar'] ?? ''));

    // Pemetaan kata kunci nama produk ke file di folder assets/
    $mapping = [
        'clay mask'   => 'assets/clay mask.jpeg',
        'facial wash' => 'assets/facial wash.jpeg',
        'moist'       => 'assets/mousturizer.jpeg',
        'moisturizer' => 'assets/mousturizer.jpeg',
        'mousturizer' => 'assets/mousturizer.jpeg',
        'serum'       => 'assets/serum.jpeg',
        'sunscreen'   => 'assets/sunscreen.jpeg',
        'toner'       => 'assets/toner.jpeg',
    ];

    $kandidat = [];

    if ($kolom_gambar !== '' && $kolom_gambar !== 'default.png') {
        $kandidat[] = "assets/" . $kolom_gambar;
        $kandidat[] = "assets/products/" . $kolom_gambar;
    }

    foreach ($mapping as $kata_kunci => $path_foto) {
        if ($kata_kunci !== '' && strpos($nama_produk, $kata_kunci) !== false) {
            $kandidat[] = $path_foto;
        }
    }

    foreach ($kandidat as $rel_path) {
        if (is_file(__DIR__ . "/" . $rel_path)) {
            return $rel_path;
        }
    }

    return null;
}

$foto_produk = ambilFotoProduk($p);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($p['nama_produk'] ?? 'Detail Produk') ?> - NRCSKIN</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="mobile-container">
    <div class="top-nav">
        <a href="index.php" style="color:#7a4250; font-size:20px;"><i class="fa-solid fa-arrow-left"></i></a>
        <div class="brand-title" style="font-size:18px;">✦ NRCSKIN</div>
        <div class="nav-icons">
            <a href="keranjang.php"><i class="fa-solid fa-cart-shopping"></i>
                <?php if ($cart_count > 0): ?><span class="badge"><?= $cart_count ?></span><?php endif; ?>
            </a>
        </div>
    </div>

    <?php if (isset($_GET['added'])): ?>
        <div style="background:#8ce99a; color:#1b4332; padding:8px 12px; border-radius:10px; font-size:12px; margin-bottom:10px; text-align:center;">
            ✓ Produk berhasil ditambahkan ke keranjang!
        </div>
    <?php endif; ?>

    <!-- Foto Produk -->
    <div class="product-img-box" style="height:220px; background:rgba(255,255,255,0.65); border-radius:20px; margin-bottom:15px;">
        <?php if ($foto_produk !== null): ?>
            <img src="<?= htmlspecialchars($foto_produk) ?>" alt="<?= htmlspecialchars($p['nama_produk'] ?? '') ?>" style="width:100%; height:100%; object-fit:contain; border-radius:16px;">
        <?php else: ?>
            <i class="fa-solid fa-pump-soap" style="font-size:80px; color:#c77d8e;"></i>
        <?php endif; ?>
    </div>

    <!-- Nama & Harga -->
    <h2 style="font-size:22px; color:#5c333d;"><?= htmlspecialchars($p['nama_produk'] ?? '') ?></h2>
    <div style="margin:8px 0 15px;">
        <span style="font-size:20px; font-weight:700; color:#7a4250;"><?= formatRupiah($p['harga'] ?? 0) ?></span>
        <?php if (!empty($p['harga_coret']) && (int)$p['harga_coret'] > 0): ?>
            <span class="product-old-price" style="font-size:14px;"><?= formatRupiah($p['harga_coret']) ?></span>
        <?php endif; ?>
    </div>

    <!-- Description Box -->
    <span style="background:#b57c8a; color:#fff; padding:4px 12px; border-radius:8px; font-size:12px;">Description</span>
    <div class="glass-card" style="margin-top:8px; padding:15px; font-size:12px; line-height:1.6;">
        <p><?= nl2br(htmlspecialchars($p['deskripsi'] ?? '')) ?></p>
        <p style="margin-top:10px;"><strong>Ukuran:</strong> <?= htmlspecialchars($p['ukuran'] ?? '-') ?></p>
        <p><strong>Stok tersedia:</strong> <?= (int)($p['stok'] ?? 0) ?></p>
    </div>

    <form method="POST" style="margin-top:20px;">
        <button type="submit" name="add_to_cart" class="btn-primary">Add To Cart</button>
    </form>
</div>
</body>
</html>