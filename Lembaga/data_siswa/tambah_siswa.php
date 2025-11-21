<?php
// tambah_siswa.php (backend only)
include '../../koneksi.php';

header('Content-Type: application/json; charset=utf-8');

// hanya POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['status' => 'error', 'msg' => 'method']);
  exit;
}

// ambil input
$nama     = trim($_POST['nama_siswa'] ?? '');
$nisn     = trim($_POST['no_induk_siswa'] ?? '');
$absen    = trim($_POST['no_absen_siswa'] ?? '');
$id_kelas = trim($_POST['id_kelas'] ?? '');
$catatan  = trim($_POST['catatan_wali_kelas'] ?? '');

if ($nama === '' || $nisn === '' || $absen === '' || $id_kelas === '') {
  echo json_encode(['status' => 'error', 'msg' => 'valid']);
  exit;
}

mysqli_begin_transaction($koneksi);

try {
  $stmt = mysqli_prepare($koneksi, "INSERT INTO siswa (nama_siswa, no_induk_siswa, no_absen_siswa, id_kelas) VALUES (?, ?, ?, ?)");
  if ($stmt === false) throw new Exception('prepare1: ' . $koneksi->error);
  mysqli_stmt_bind_param($stmt, 'sssi', $nama, $nisn, $absen, $id_kelas);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  $id_siswa = mysqli_insert_id($koneksi);

  $stmt2 = mysqli_prepare($koneksi, "INSERT INTO cetak_rapor (id_siswa, catatan_wali_kelas) VALUES (?, ?)");
  if ($stmt2 === false) throw new Exception('prepare2: ' . $koneksi->error);
  mysqli_stmt_bind_param($stmt2, 'is', $id_siswa, $catatan);
  mysqli_stmt_execute($stmt2);
  mysqli_stmt_close($stmt2);

  mysqli_commit($koneksi);

  echo json_encode(['status' => 'success']);
  exit;
} catch (Exception $e) {
  mysqli_rollback($koneksi);
  error_log('tambah_siswa error: ' . $e->getMessage());
  echo json_encode(['status' => 'error', 'msg' => 'failed']);
  exit;
}
