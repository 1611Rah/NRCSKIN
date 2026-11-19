<?php
require 'config/koneksi.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$uid = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$uid'"));
$items = mysqli_query($conn, "SELECT k.qty, p.* FROM keranjang k JOIN produk p ON k.produk_id = p.id WHERE k.user_id='$uid'");

$list_items = [];
$subtotal = 0;
while ($r = mysqli_fetch_assoc($items)) {
    $list_items[] = $r;
    $subtotal += ($r['harga'] * $r['qty']);
}

if (empty($list_items)) { header("Location: index.php"); exit; }

$ongkir = 6000;
$total_bayar = $subtotal + $ongkir;

if (isset($_POST['buat_pesanan'])) {
    $metode  = mysqli_real_escape_string($conn, $_POST['metode']);
    $alamat  = mysqli_real_escape_string($conn, $_POST['alamat']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
    $nama_pelanggan = $user['username'];

    // Simpan ke tabel pesanan (Terhubung ke Admin)
    mysqli_query($conn, "INSERT INTO pesanan (user_id, nama_pelanggan, alamat, metode_pembayaran, subtotal, ongkir, total_bayar, catatan) 
                         VALUES ('$uid', '$nama_pelanggan', '$alamat', '$metode', '$subtotal', '$ongkir', '$total_bayar', '$catatan')");
    $pesanan_id = mysqli_insert_id($conn);

    // Simpan rincian produk & kurangi stok
    foreach ($list_items as $item) {
        $pid = $item['id'];
        $p_nama = $item['nama_produk'];
        $p_harga = $item['harga'];
        $p_qty = $item['qty'];

        mysqli_query($conn, "INSERT INTO pesanan_detail (pesanan_id, produk_id, nama_produk, harga, qty) 
                             VALUES ('$pesanan_id', '$pid', '$p_nama', '$p_harga', '$p_qty')");
        mysqli_query($conn, "UPDATE produk SET stok = stok - $p_qty WHERE id='$pid'");
    }

    // Kosongkan keranjang user
    mysqli_query($conn, "DELETE FROM keranjang WHERE user_id='$uid'");

    header("Location: sukses.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - NRCSKIN</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="mobile-container">
    <div class="top-nav">
        <a href="keranjang.php" style="color:#7a4250; font-size:20px;"><i class="fa-solid fa-arrow-left"></i></a>
        <div class="brand-title" style="font-size:18px;">✦ NRCSKIN</div>
        <div></div>
    </div>

    <h3 style="text-align:center; margin-bottom:15px; color:#6e3b47;">Checkout</h3>

    <!-- Daftar Item -->
    <?php foreach($list_items as $item): ?>
    <div class="cart-item" style="padding:10px;">
        <div class="cart-thumb" style="width:45px; height:45px; display:flex; align-items:center; justify-content:center;">
            <i class="fa-solid fa-pump-soap" style="color:#c77d8e;"></i>
        </div>
        <div style="flex:1;">
            <div style="font-size:13px; font-weight:600;"><?= $item['nama_produk'] ?></div>
            <div style="font-size:12px; color:#8c4b5b;"><?= formatRupiah($item['harga']) ?> x <?= $item['qty'] ?></div>
        </div>
    </div>
    <?php endforeach; ?>

    <form method="POST">
        <!-- Alamat -->
        <div class="glass-card" style="padding:14px; margin-bottom:12px;">
            <div style="font-size:12px; font-weight:600; margin-bottom:5px;">
                <i class="fa-solid fa-location-dot" style="color:#c93b4b;"></i> <?= $user['username'] ?> (<?= $user['no_hp'] ?>)
            </div>
            <textarea name="alamat" class="form-control" rows="2" style="font-size:12px;"><?= htmlspecialchars($user['alamat']) ?></textarea>
        </div>

        <!-- Metode Pembayaran -->
        <div class="glass-card" style="padding:14px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:12px; font-weight:600;">Metode Pembayaran</span>
            <div style="font-size:12px;">
                <label style="margin-right:10px;"><input type="radio" name="metode" value="QRIS" checked> QRIS</label>
                <label><input type="radio" name="metode" value="COD"> COD</label>
            </div>
        </div>

        <!-- Catatan -->
        <div class="glass-card" style="padding:14px; margin-bottom:12px;">
            <input type="text" name="catatan" class="form-control" placeholder="Catatan pesanan (opsional)..." style="font-size:12px;">
        </div>

        <!-- Rincian Pembayaran -->
        <div class="glass-card" style="padding:14px; font-size:12px;">
            <div style="font-weight:600; margin-bottom:8px;">RINCIAN PEMBAYARAN</div>
            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                <span>Subtotal Pesanan</span><span><?= formatRupiah($subtotal) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span>Subtotal Pengiriman</span><span><?= formatRupiah($ongkir) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; font-weight:700; font-size:14px; border-top:1px dashed #b57c8a; padding-top:8px;">
                <span>TOTAL PEMBAYARAN</span><span><?= formatRupiah($total_bayar) ?></span>
            </div>
        </div>

        <div class="bottom-bar">
            <div style="font-weight:700; font-size:14px;">Total <?= formatRupiah($total_bayar) ?></div>
            <button type="submit" name="buat_pesanan" class="btn-primary btn-sm" style="padding:10px 20px;">Buat Pesanan</button>
        </div>
    </form>
</div>
</body>
</html>