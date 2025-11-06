<?php
include '../../koneksi.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    // Hapus data berdasarkan id
    $query = "DELETE FROM nilai_ekstrakurikuler WHERE id_nilai_ekstrakurikuler = '$id'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        echo "<script>
                alert('Data berhasil dihapus!');
                window.location.href='nilai_ekstra.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus data!');
                window.location.href='nilai_ekstra.php';
              </script>";
    }
} else {
    echo "<script>
            alert('ID tidak ditemukan!');
            window.location.href='nilai_ekstra.php';
          </script>";
}
?>
