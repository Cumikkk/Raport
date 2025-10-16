<?php
include '../../koneksi.php'; // pastikan file ini berisi $koneksi = mysqli_connect(...)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tahun_ajaran = $_POST['tahun_ajaran'];
  $sistem_penilaian = $_POST['sistem_penilaian'];
  $semester_aktif = $_POST['semester_aktif'];

  $query = "INSERT INTO semester (nama_semester, tahun_ajaran, sistem_penilaian) 
            VALUES ('$semester_aktif', '$tahun_ajaran', '$sistem_penilaian')";

  if (mysqli_query($koneksi, $query)) {
    echo "<script>
            alert('Data semester berhasil disimpan!');
            window.location.href='data_semester.php'; // ubah ke halaman daftar semester kalau ada
          </script>";
  } else {
    echo "<script>
            alert('Gagal menyimpan data: " . mysqli_error($koneksi) . "');
            history.back();
          </script>";
  }
}
?>
