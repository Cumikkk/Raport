<?php
include '../../koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $id_mapel = $_POST['id_mapel'];
  $nama_mapel = $_POST['nama_mapel'];
  $jenis_mapel = $_POST['jenis_mapel'];

  if (empty($id_mapel) || empty($nama_mapel) || empty($jenis_mapel)) {
    echo "<script>alert('Harap isi semua field!'); window.history.back();</script>";
    exit;
  }

  $query = "UPDATE mata_pelajaran 
            SET nama_mata_pelajaran='$nama_mapel', kelompok_mata_pelajaran='$jenis_mapel' 
            WHERE id_mata_pelajaran='$id_mapel'";

  $result = mysqli_query($koneksi, $query);

  if ($result) {
    echo "<script>alert('Data berhasil diperbarui!'); window.location.href='data_mapel.php';</script>";
  } else {
    echo "<script>alert('Gagal memperbarui data: " . mysqli_error($koneksi) . "'); window.history.back();</script>";
  }
}
?>
