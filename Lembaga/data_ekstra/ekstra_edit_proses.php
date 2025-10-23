<?php
include '../../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama_ekstra = mysqli_real_escape_string($koneksi, $_POST['nama_ekstra']);
  $id = $_POST['id'];

  $update = "UPDATE ekstrakurikuler SET nama_ekstrakurikuler = '$nama_ekstra' WHERE id_ekstrakurikuler = '$id'";
  $hasil = mysqli_query($koneksi, $update);

  if ($hasil) {
    echo "<script>
            alert('Data berhasil diperbarui!');
            window.location='data_ekstra.php';
          </script>";
  } else {
    echo "<script>
            alert('Gagal memperbarui data!');
            window.location='data_ekstra.php';
          </script>";
  }
}
?>