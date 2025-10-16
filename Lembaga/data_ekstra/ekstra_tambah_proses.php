<?php
include '../../koneksi.php'; // sesuaikan path-nya

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_ekstra = mysqli_real_escape_string($koneksi, $_POST['nama_ekstra']);

    $query = "INSERT INTO ekstrakurikuler (nama_ekstrakurikuler) VALUES ('$nama_ekstra')";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        header("Location: data_ekstra.php");
        exit;
    } else {
        echo "Gagal menambahkan data: " . mysqli_error($koneksi);
    }
}
?>