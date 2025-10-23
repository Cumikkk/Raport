<?php 
$koneksi = mysqli_connect('localhost','root','','rapor');

if (!$koneksi) {
    die('Koneksi Database Gagal: ' . mysqli_connect_error());
}
?>

