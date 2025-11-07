<?php
include '../../koneksi.php';

// Pastikan ada ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID siswa tidak ditemukan');window.location='data_siswa.php';</script>";
    exit;
}

$id = intval($_GET['id']); // pastikan angka agar aman

// Hapus dulu data di tabel absensi yang terhubung ke siswa ini
mysqli_query($koneksi, "DELETE FROM absensi WHERE id_siswa='$id'");

// Jika ada tabel lain yang juga pakai id_siswa sebagai relasi, hapus juga di sini
// Contoh: mysqli_query($koneksi, "DELETE FROM nilai WHERE id_siswa='$id'");

// Baru hapus data siswa-nya
$hapus = mysqli_query($koneksi, "DELETE FROM siswa WHERE id_siswa='$id'");

if ($hapus) {
    echo "<script>alert('Data siswa berhasil dihapus');window.location='data_siswa.php';</script>";
} else {
    $error = mysqli_error($koneksi);
    echo "<script>alert('Gagal menghapus data: $error');window.location='data_siswa.php';</script>";
}
?>
