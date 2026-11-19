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

if (isset($_POST['simpan'])) {
    $nama      = mysqli_real_escape_string($conn, $_POST['nama_produk'] ?? '');
    $harga     = (int)($_POST['harga'] ?? 0);
    $stok      = (int)($_POST['stok'] ?? 0);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');
    $status    = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Tersedia');

    $upload_dir = __DIR__ . '/../assets/products/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $gambar = 'default.png';
    if (!empty($_FILES['foto']['name']) && ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (in_array($ext, $allowed, true)) {
            $nama_bersih = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['foto']['name']));
            $gambar = time() . '_' . $nama_bersih;
            move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $gambar);
        }
    }

    $sql = "INSERT INTO produk (kategori_id, nama_produk, harga, stok, deskripsi, gambar, status)
            VALUES (1, '$nama', $harga, $stok, '$deskripsi', '$gambar', '$status')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Produk berhasil diperbarui & disimpan!'); window.location='produk.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menyimpan produk: " . addslashes(mysqli_error($conn)) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="mobile-container">
    <div class="top-nav">
        <a href="produk.php" style="color:#7a4250; font-size:20px;"><i class="fa-solid fa-arrow-left"></i></a>
        <h3 style="color:#7a4250;">Tambah Produk</h3>
        <div></div>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Foto Produk</label>
            <input type="file" name="foto" class="form-control" accept="image/*">
        </div>
        <div class="form-group">
            <label>Nama produk</label>
            <input type="text" name="nama_produk" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Harga (Rp)</label>
            <input type="number" name="harga" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Stok</label>
            <input type="number" name="stok" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label>Status Produk</label>
            <select name="status" class="form-control">
                <option value="Tersedia">✓ Tersedia</option>
                <option value="Stok menipis">Stok menipis</option>
                <option value="Habis">Habis</option>
            </select>
        </div>
        <button type="submit" name="simpan" class="btn-primary">Simpan</button>
    </form>
</div>
</body>
</html>