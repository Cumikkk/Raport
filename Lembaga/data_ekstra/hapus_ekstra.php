<?php
include '../../koneksi.php'; 

$id_ekstra = $_GET['id'];

$query = "DELETE FROM ekstrakurikuler WHERE id_ekstrakurikuler = '$id_ekstra'";

if (mysqli_query($koneksi, $query)) {
  echo "<script>
    alert('Data berhasil dihapus!');
    window.location='data_ekstra.php';
  </script>";
} else {
  echo "<script>
    alert('Data gagal dihapus!');
    window.location='data_ekstra.php';
  </script>";
}
?>
