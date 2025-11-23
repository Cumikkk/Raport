<?php
include '../../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $tahun_ajaran   = $_POST['tahun_ajaran'] ?? '';
  $semester_aktif = $_POST['semester_aktif'] ?? '';

  if ($tahun_ajaran === '' || $semester_aktif === '') {
    header("Location: data_semester.php?msg=error");
    exit;
  }

  // Gunakan prepared statement supaya lebih aman
  $sql = "INSERT INTO semester (nama_semester, tahun_ajaran) VALUES (?, ?)";
  $stmt = $koneksi->prepare($sql);

  if (!$stmt) {
    header("Location: data_semester.php?msg=error");
    exit;
  }

  $stmt->bind_param("ss", $semester_aktif, $tahun_ajaran);

  if ($stmt->execute()) {
    // Jika sukses → kirim notifikasi ke data_semester.php
    header("Location: data_semester.php?msg=saved");
    exit;
  } else {
    // Jika gagal → tampilkan alert error
    header("Location: data_semester.php?msg=error");
    exit;
  }
}
?>
