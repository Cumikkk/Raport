<?php
include '../../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_siswa = $_POST['id_siswa'];
    $id_ekstra = $_POST['id_ekstra'];
    $nilai = $_POST['nilai_ekstrakurikuler'];
    $semester = $_POST['id_semester'];

    $query = "INSERT INTO nilai_ekstrakurikuler (id_siswa, id_semester, id_ekstrakurikuler, nilai_ekstrakurikuler)
              VALUES ('$id_siswa', '$semester', '$id_ekstra', '$nilai')";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>
                alert('✅ Data berhasil ditambahkan!');
                window.location.href = 'nilai_ekstra.php';
              </script>";
    } else {
        echo "<script>
                alert('❌ Gagal menambahkan data!');
                window.history.back();
              </script>";
    }
}
?>
