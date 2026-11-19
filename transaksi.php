<?php
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once __DIR__ . '/../config/koneksi.php';

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

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ubah status pesanan menjadi Selesai
if (isset($_GET['selesai'])) {
    $pid = (int)$_GET['selesai'];
    if ($pid > 0) {
        mysqli_query($conn, "UPDATE pesanan SET status_pesanan = 'Selesai' WHERE id = $pid");
    }
    header("Location: transaksi.php");
    exit;
}

$detail_id = isset($_GET['detail']) ? (int)$_GET['detail'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Transaksi - NRCSKIN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #2b2023;
            display: flex;
            justify-content: center;
            min-height: 100vh;
        }

        .mobile-screen {
    width: 100%;
    max-width: 412px;
    min-height: 100vh;
    background-image: url('../assets/bg.jpeg');
    background-repeat: no-repeat;
    background-position: center center;
    background-size: cover;
    background-attachment: fixed;
    padding: 22px 18px 36px;
    position: relative;
    overflow-x: hidden;
    box-shadow: 0 0 30px rgba(0, 0, 0, 0.45);
}

        /* Header Atas: <- Kelola Transaksi */
        .trx-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 22px;
        }
        .trx-back {
            color: #5a313b;
            text-decoration: none;
            font-size: 20px;
            line-height: 1;
        }
        .trx-title {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: #7c3f4e;
        }

        /* Kartu Daftar Transaksi */
        .trx-row-card {
            background: #9c5969;
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(90, 49, 59, 0.18);
        }
        .trx-user-left {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ffffff;
        }
        .trx-avatar-icon {
            font-size: 38px;
            color: #ffffff;
        }
        .trx-customer-name {
            font-size: 16px;
            font-weight: 600;
            line-height: 1.25;
            color: #ffffff;
        }
        .trx-item-count {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.9);
            margin-top: 2px;
        }
        .trx-btn-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            min-width: 82px;
        }
        .btn-pill-selesai {
            background: #7fe08e;
            color: #1f3f25;
            font-size: 10px;
            font-weight: 600;
            padding: 3px 14px;
            border-radius: 10px;
            text-decoration: none;
            width: 100%;
            text-align: center;
        }
        .btn-pill-selesai.done {
            background: #c9f2cf;
            color: #264e2d;
        }
        .btn-pill-detail {
            background: #ffffff;
            color: #4a2830;
            font-size: 9px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 10px;
            text-decoration: none;
            width: 100%;
            text-align: center;
            white-space: nowrap;
        }

        /* Kartu Detail Pesanan */
        .detail-card {
            background: #9c5969;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 14px;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(90, 49, 59, 0.16);
        }
        .detail-card-title {
            font-size: 12px;
            font-weight: 600;
            padding-bottom: 6px;
            margin-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.45);
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .info-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .info-label {
            width: 44%;
            color: rgba(255, 255, 255, 0.92);
        }
        .info-val {
            color: #ffffff;
            font-weight: 500;
        }

        /* Tabel Rincian Pesanan */
        .rincian-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            background: rgba(255, 240, 244, 0.92);
            color: #4a2830;
        }
        .rincian-table th,
        .rincian-table td {
            border: 1px solid rgba(156, 89, 105, 0.45);
            padding: 6px 8px;
        }
        .rincian-table th {
            background: rgba(247, 217, 224, 0.95);
            font-weight: 600;
            text-align: center;
        }
        .rincian-table th:first-child {
            text-align: left;
        }

        /* Stepper Status Pesanan */
        .status-stepper {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            position: relative;
            padding-top: 4px;
        }
        .status-stepper::before {
            content: "";
            position: absolute;
            top: 15px;
            left: 14%;
            right: 14%;
            height: 2px;
            background: rgba(255, 255, 255, 0.55);
            z-index: 0;
        }
        .status-step {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 25%;
            text-align: center;
            font-size: 9px;
            line-height: 1.25;
        }
        .step-circle {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 5px;
            font-size: 11px;
        }
        .step-done {
            background: #7fe08e;
            color: #1f3f25;
        }
        .step-active-pink {
            background: #f48fb1;
            color: #ffffff;
        }
        .step-pending {
            background: #f5dce2;
            color: transparent;
        }

        /* Box Catatan Pesanan */
        .catatan-box {
            background: #f5dce2;
            color: #4a2830;
            border-radius: 10px;
            min-height: 44px;
            padding: 8px 10px;
            font-size: 11px;
        }

        .empty-state {
            background: rgba(255, 244, 246, 0.85);
            color: #5a313b;
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            font-size: 13px;
        }
    </style>
</head>
<body>
<div class="mobile-screen">
    <div class="trx-header">
        <a href="<?= $detail_id > 0 ? 'transaksi.php' : 'index.php' ?>" class="trx-back" title="Kembali">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1 class="trx-title">Kelola Transaksi</h1>
    </div>

    <?php if ($detail_id > 0):
        $q_trx = mysqli_query($conn, "SELECT * FROM pesanan WHERE id = $detail_id");
        $trx = ($q_trx && mysqli_num_rows($q_trx) > 0) ? mysqli_fetch_assoc($q_trx) : null;
    ?>
        <?php if (!$trx): ?>
            <div class="empty-state">
                Pesanan tidak ditemukan.<br>
                <a href="transaksi.php" style="display:inline-block; margin-top:10px; color:#9c5969; font-weight:600;">Kembali ke Daftar</a>
            </div>
        <?php else:
            $items = mysqli_query($conn, "SELECT * FROM pesanan_detail WHERE pesanan_id = $detail_id");
            $status_now = $trx['status_pesanan'] ?? 'Diproses';
            $is_selesai = ($status_now === 'Selesai');
        ?>
            <div class="detail-card">
                <div class="detail-card-title">
                    <i class="fa-solid fa-user"></i>
                    <span>Informasi Pelanggan</span>
                </div>
                <table class="info-table">
                    <tr>
                        <td class="info-label">Nama</td>
                        <td class="info-val">: <?= htmlspecialchars($trx['nama_pelanggan'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Metode Pembayaran</td>
                        <td class="info-val">: <?= htmlspecialchars($trx['metode_pembayaran'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal Pesan</td>
                        <td class="info-val">: <?= !empty($trx['tanggal_pesan']) ? date('d F Y', strtotime($trx['tanggal_pesan'])) : '-' ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Alamat</td>
                        <td class="info-val">: <?= htmlspecialchars($trx['alamat'] ?? '-') ?></td>
                    </tr>
                </table>
            </div>

            <div class="detail-card">
                <div class="detail-card-title">
                    <i class="fa-solid fa-box"></i>
                    <span>Rincian Pesanan</span>
                </div>
                <table class="rincian-table">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th style="width:65px;">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($items && mysqli_num_rows($items) > 0): ?>
                            <?php while ($it = mysqli_fetch_assoc($items)): ?>
                            <tr>
                                <td><?= htmlspecialchars($it['nama_produk'] ?? '-') ?></td>
                                <td style="text-align:center;"><?= (int)($it['qty'] ?? 0) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" style="text-align:center;">Belum ada rincian barang</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="detail-card">
                <div class="detail-card-title">
                    <span>Status Pesanan</span>
                </div>
                <div class="status-stepper">
                    <div class="status-step">
                        <div class="step-circle step-done"><i class="fa-solid fa-check"></i></div>
                        <span>Pesanan<br>Dibuat</span>
                    </div>
                    <div class="status-step">
                        <div class="step-circle step-done"><i class="fa-solid fa-box"></i></div>
                        <span>Pesanan<br>Dikemas</span>
                    </div>
                    <div class="status-step">
                        <div class="step-circle <?= $is_selesai ? 'step-done' : 'step-active-pink' ?>">
                            <i class="fa-solid fa-truck"></i>
                        </div>
                        <span>Pesanan<br>Dikirim</span>
                    </div>
                    <div class="status-step">
                        <div class="step-circle <?= $is_selesai ? 'step-done' : 'step-pending' ?>">
                            <?php if ($is_selesai): ?><i class="fa-solid fa-check"></i><?php endif; ?>
                        </div>
                        <span>Selesai</span>
                    </div>
                </div>
            </div>

            <div class="detail-card" style="margin-bottom:0;">
                <div class="detail-card-title">
                    <span>Catatan Pesanan</span>
                </div>
                <div class="catatan-box">
                    <?= !empty($trx['catatan']) ? htmlspecialchars($trx['catatan']) : '' ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else:
        $query_list = "
            SELECT p.*, COALESCE(SUM(pd.qty), 0) AS total_item
            FROM pesanan p
            LEFT JOIN pesanan_detail pd ON pd.pesanan_id = p.id
            GROUP BY p.id
            ORDER BY p.id DESC
        ";
        $pesanan = mysqli_query($conn, $query_list);
    ?>
        <?php if (!$pesanan): ?>
            <div class="empty-state" style="color:#b83b4c;">
                Query gagal: <?= htmlspecialchars(mysqli_error($conn)) ?>
            </div>
        <?php elseif (mysqli_num_rows($pesanan) === 0): ?>
            <div class="empty-state">
                Belum ada pesanan masuk dari user.
            </div>
        <?php else: ?>
            <?php while ($p = mysqli_fetch_assoc($pesanan)):
                $status_p = $p['status_pesanan'] ?? 'Diproses';
            ?>
            <div class="trx-row-card">
                <div class="trx-user-left">
                    <i class="fa-solid fa-circle-user trx-avatar-icon"></i>
                    <div>
                        <div class="trx-customer-name"><?= htmlspecialchars($p['nama_pelanggan'] ?? 'User') ?></div>
                        <div class="trx-item-count"><?= (int)($p['total_item'] ?? 0) ?> Item</div>
                    </div>
                </div>
                <div class="trx-btn-group">
                    <?php if ($status_p !== 'Selesai'): ?>
                        <a href="?selesai=<?= (int)$p['id'] ?>" class="btn-pill-selesai">Selesai</a>
                    <?php else: ?>
                        <span class="btn-pill-selesai done">Selesai</span>
                    <?php endif; ?>
                    <a href="?detail=<?= (int)$p['id'] ?>" class="btn-pill-detail">Detail Pesanan</a>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>