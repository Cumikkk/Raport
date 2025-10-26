<?php
$host     = 'localhost';
$username = 'root';
$password = '';      // biarkan kosong kalau kamu pakai XAMPP/Laragon default
$database = 'rapor';

$koneksi = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$koneksi) {
    die('Koneksi ke database gagal: ' . mysqli_connect_error());
}

// Optional: set karakter ke UTF-8 biar aman dari error karakter
mysqli_set_charset($koneksi, 'utf8');
?>
