<?php
include '../../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = $_POST['id'];
    $ekstra = $_POST['nama_ekstra'];
    $nilais = $_POST['nilai'];

    for ($i = 0; $i < count($ids); $i++) {
        $id = mysqli_real_escape_string($koneksi, $ids[$i]);
        $ekstra_id = mysqli_real_escape_string($koneksi, $ekstra[$i]);
        $nilai = mysqli_real_escape_string($koneksi, $nilais[$i]);

        $query = "UPDATE nilai_ekstrakurikuler 
                  SET nilai_ekstrakurikuler = '$nilai', id_ekstrakurikuler = '$ekstra_id'
                  WHERE id_nilai_ekstrakurikuler = '$id'";
        mysqli_query($koneksi, $query);
    }

    echo "<script>
            alert('Perubahan berhasil disimpan!');
            window.location.href='nilai_ekstra.php';
          </script>";
}
?>
