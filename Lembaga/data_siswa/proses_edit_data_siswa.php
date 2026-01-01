<?php
// pages/siswa/proses_edit_data_siswa.php
require_once '../../koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['ok' => false, 'type' => 'danger', 'msg' => 'Metode tidak diizinkan.']);
  exit;
}

$id       = (int)($_POST['id_siswa'] ?? 0);
$nama     = trim($_POST['nama_siswa'] ?? '');
$nisn     = trim($_POST['no_induk_siswa'] ?? '');
$absen    = trim($_POST['no_absen_siswa'] ?? '');
$id_kelas = (int)($_POST['id_kelas'] ?? 0);

// (catatan wali kelas sudah tidak dipakai di UI; aman diabaikan)
if ($id <= 0 || $nama === '' || $nisn === '' || $absen === '' || $id_kelas <= 0) {
  echo json_encode(['ok' => false, 'type' => 'warning', 'msg' => 'Data belum lengkap atau ID tidak valid.']);
  exit;
}

mysqli_begin_transaction($koneksi);

try {
  // ✅ CEK DUPLIKAT NISN, kecuali milik diri sendiri
  $stmtDup = mysqli_prepare($koneksi, "
    SELECT id_siswa
    FROM siswa
    WHERE no_induk_siswa = ?
      AND id_siswa <> ?
    LIMIT 1
  ");
  mysqli_stmt_bind_param($stmtDup, 'si', $nisn, $id);
  mysqli_stmt_execute($stmtDup);
  $resDup = mysqli_stmt_get_result($stmtDup);
  $dupRow = mysqli_fetch_assoc($resDup);
  mysqli_stmt_close($stmtDup);

  if (!empty($dupRow)) {
    mysqli_rollback($koneksi);
    echo json_encode([
      'ok' => false,
      'type' => 'warning',
      'msg' => 'NISN sudah digunakan oleh siswa lain. Perubahan ditolak.'
    ]);
    exit;
  }

  $stmt = mysqli_prepare($koneksi, "
    UPDATE siswa SET
      nama_siswa = ?,
      no_induk_siswa = ?,
      no_absen_siswa = ?,
      id_kelas = ?
    WHERE id_siswa = ?
  ");
  mysqli_stmt_bind_param($stmt, 'sssii', $nama, $nisn, $absen, $id_kelas, $id);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  mysqli_commit($koneksi);

  echo json_encode(['ok' => true, 'type' => 'success', 'msg' => 'Data siswa berhasil diperbarui.']);
  exit;
} catch (Throwable $e) {
  mysqli_rollback($koneksi);
  echo json_encode(['ok' => false, 'type' => 'danger', 'msg' => 'Gagal memperbarui data siswa.']);
  exit;
}
