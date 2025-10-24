<?php
include '../../koneksi.php';

if (isset($_GET['id'])) {
  $id = intval($_GET['id']); // sanitasi id biar aman

  // Cek dulu apakah datanya ada
  $cek = mysqli_query($koneksi, "SELECT * FROM mata_pelajaran WHERE id_mata_pelajaran='$id'");
  if (mysqli_num_rows($cek) > 0) {
    $hapus = mysqli_query($koneksi, "DELETE FROM mata_pelajaran WHERE id_mata_pelajaran='$id'");
    if ($hapus) {
      echo "<script>alert('Data mata pelajaran berhasil dihapus!'); window.location='data_mapel.php';</script>";
    } else {
      echo "<script>alert('Gagal menghapus data!'); window.location='data_mapel.php';</script>";
    }
  } else {
    echo "<script>alert('Data tidak ditemukan!'); window.location='data_mapel.php';</script>";
  }
} else {
  echo "<script>alert('ID tidak ditemukan!'); window.location='data_mapel.php';</script>";
}
?>
