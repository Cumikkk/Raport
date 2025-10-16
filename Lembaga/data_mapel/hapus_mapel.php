<?php
include '../../koneksi.php';

// pastikan ada parameter id di URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // query hapus data
    $query = "DELETE FROM mata_pelajaran WHERE id_mata_pelajaran = '$id'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        echo "<script>
                alert('Data berhasil dihapus!');
                window.location.href = 'data_mapel.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus data!');
                window.location.href = 'data_mapel.php';
              </script>";
    }
} else {
    echo "<script>
            alert('ID tidak ditemukan!');
            window.location.href = 'data_mapel.php';
          </script>";
}
?>
