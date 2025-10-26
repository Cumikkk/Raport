<?php
include '../koneksi.php';

$nama_lengkap = $_POST['nama_lengkap'];
$email_user = $_POST['email_user'];
$no_telepon_user = $_POST['no_telepon_user'];
$username = $_POST['username'];
$password = $_POST['password_user'];
$role = $_POST['role'];

$query = "INSERT INTO user (nama_lengkap_user, email_user, no_telepon_user, username, password_user, role)
          VALUES ('$nama_lengkap', '$email_user', '$no_telepon_user', '$username', '$password', '$role')";


if (mysqli_query($koneksi, $query)) {
    echo "<script>alert('Data user berhasil ditambahkan!'); window.location.href='../index.php';</script>";
} else {
    echo "Error: " . mysqli_error($koneksi);
}

mysqli_close($koneksi);
?>
