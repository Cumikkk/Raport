<?php
require_once '../koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    exit("<script>alert('ID tidak valid'); window.location.href='data_user.php';</script>");
}

$stmt = mysqli_prepare($koneksi, "DELETE FROM user WHERE id_user=?");
mysqli_stmt_bind_param($stmt, 'i', $id);
$ok = mysqli_stmt_execute($stmt);

if ($ok) {
    echo "<script>alert('User terhapus'); window.location.href='data_user.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus: " . htmlspecialchars(mysqli_error($koneksi)) . "'); window.location.href='data_user.php';</script>";
}
