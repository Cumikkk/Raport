<?php
require_once '../../koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$koneksi->set_charset('utf8mb4');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: data_semester.php?msg=error");
  exit;
}

$tahun_ajaran   = $_POST['tahun_ajaran'] ?? '';
$semester_aktif = $_POST['semester_aktif'] ?? '';

if ($tahun_ajaran === '' || $semester_aktif === '') {
  header("Location: data_semester.php?msg=error");
  exit;
}

try {
  // 🔎 cek apakah sudah ada data semester
  $cek = $koneksi->query("SELECT id_semester FROM semester ORDER BY id_semester DESC LIMIT 1");

  if ($cek->num_rows > 0) {
    // =========================
    // UPDATE (jika sudah ada)
    // =========================
    $row = $cek->fetch_assoc();
    $id  = (int)$row['id_semester'];

    $sql  = "UPDATE semester SET nama_semester = ?, tahun_ajaran = ? WHERE id_semester = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("ssi", $semester_aktif, $tahun_ajaran, $id);
    $stmt->execute();
    $stmt->close();
  } else {
    // =========================
    // INSERT (jika belum ada)
    // =========================
    $sql  = "INSERT INTO semester (nama_semester, tahun_ajaran) VALUES (?, ?)";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("ss", $semester_aktif, $tahun_ajaran);
    $stmt->execute();
    $stmt->close();
  }

  header("Location: data_semester.php?msg=saved");
  exit;
} catch (Throwable $e) {
  header("Location: data_semester.php?msg=error");
  exit;
}
