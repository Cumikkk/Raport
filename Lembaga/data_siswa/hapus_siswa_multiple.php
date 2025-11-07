<?php
include '../../koneksi.php';

if (!isset($_POST['id_siswa'])) {
    header("Location: data_siswa.php");
    exit;
}

$ids = $_POST['id_siswa'];

if (!is_array($ids) || empty($ids)) {
    header("Location: data_siswa.php?pesan=tidak_ada_data_dipilih");
    exit;
}

$idList = implode(",", array_map('intval', $ids));

// Hapus data absensi yang terkait dulu
mysqli_query($koneksi, "DELETE FROM absensi WHERE id_siswa IN ($idList)");

// Baru hapus data siswa
mysqli_query($koneksi, "DELETE FROM siswa WHERE id_siswa IN ($idList)");

header("Location: data_siswa.php?pesan=hapus_sukses");
exit;
?>
