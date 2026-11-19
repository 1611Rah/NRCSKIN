<?php
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "nrcskin_db";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        return "Rp " . number_format((float)$angka, 0, ',', '.');
    }
}
?>