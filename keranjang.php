<?php
require 'config/koneksi.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$uid = $_SESSION['user_id'];

// Update Qty
if (isset($_GET['aksi']) && isset($_GET['cid'])) {
    $cid = (int)$_GET['cid'];
    if ($_GET['aksi'] == 'plus') {
        mysqli_query($conn, "UPDATE keranjang SET qty = qty + 1 WHERE id='$cid' AND user_id='$uid'");
    } elseif ($_GET['aksi'] == 'minus') {
        $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT qty FROM keranjang WHERE id='$cid'"));
        if ($cek['qty'] > 1) {
            mysqli_query($conn, "UPDATE keranjang SET qty = qty - 1 WHERE id='$cid'");
        } else {
            mysqli_query($conn, "DELETE FROM keranjang WHERE id='$cid'");
        }
    }
    header("Location: keranjang.php");
    exit;
}

$items = mysqli_query($conn, "SELECT k.id as cid, k.qty, p.* FROM keranjang k JOIN produk p ON k.produk_id = p.id WHERE k.user_id='$uid'");
$total_harga = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Saya - NRCSKIN</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="mobile-container">
    <div class="top-nav">
        <a href="index.php" style="color:#7a4250; font-size:20px;"><i class="fa-solid fa-arrow-left"></i></a>
        <div class="brand-title" style="font-size:18px;">✦ NRCSKIN</div>
        <div></div>
    </div>

    <h3 style="text-align:center; letter-spacing:2px; margin-bottom:20px; color:#6e3b47;">KERANJANG SAYA</h3>

    <?php if(mysqli_num_rows($items) == 0): ?>
        <div class="glass-card" style="text-align:center;">
            <p>Keranjang belanja masih kosong.</p>
            <a href="index.php" class="btn-primary btn-sm" style="margin-top:10px;">Belanja Sekarang</a>
        </div>
    <?php else: ?>
        <?php while($row = mysqli_fetch_assoc($items)): 
            $subtotal = $row['harga'] * $row['qty'];
            $total_harga += $subtotal;
        ?>
        <div class="cart-item">
            <div class="cart-thumb" style="display:flex; align-items:center; justify-content:center;">
                <i class="fa-solid fa-pump-soap" style="font-size:28px; color:#c77d8e;"></i>
            </div>
            <div style="flex:1;">
                <div style="font-weight:600; font-size:14px;"><?= $row['nama_produk'] ?></div>
                <div style="font-size:13px; color:#8c4b5b; font-weight:700;"><?= formatRupiah($row['harga']) ?></div>
                <div style="margin-top:6px; display:flex; align-items:center; gap:10px;">
                    <a href="?aksi=minus&cid=<?= $row['cid'] ?>" style="background:#9e5b6b; color:#fff; width:22px; height:22px; border-radius:5px; text-align:center; text-decoration:none; font-size:14px;">-</a>
                    <span style="font-size:13px; font-weight:600;"><?= $row['qty'] ?></span>
                    <a href="?aksi=plus&cid=<?= $row['cid'] ?>" style="background:#9e5b6b; color:#fff; width:22px; height:22px; border-radius:5px; text-align:center; text-decoration:none; font-size:14px;">+</a>
                </div>
            </div>
            <input type="checkbox" checked style="accent-color:#c93b4b; width:18px; height:18px;">
        </div>
        <?php endwhile; ?>

        <div class="bottom-bar">
            <div>
                <small style="font-size:11px;">Total</small>
                <div style="font-weight:700; font-size:16px; color:#7a4250;"><?= formatRupiah($total_harga) ?></div>
            </div>
            <a href="checkout.php" class="btn-primary btn-sm" style="padding:10px 24px;">Checkout</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>