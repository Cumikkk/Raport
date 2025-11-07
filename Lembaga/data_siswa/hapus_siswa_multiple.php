<?php
include '../../koneksi.php';

if (!isset($_POST['id_siswa'])) {
    header("Location: data_siswa.php");
    exit;
}

$ids = $_POST['id_siswa'];
$idList = implode(",", array_map('intval', $ids));

$query = "DELETE FROM siswa WHERE id_siswa IN ($idList)";
mysqli_query($koneksi, $query);

header("Location: data_siswa.php?pesan=hapus_sukses");
exit;
