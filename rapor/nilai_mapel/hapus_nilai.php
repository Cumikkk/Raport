<?php
// ===== HAPUS NILAI (AMAN & SIMPLE) =====
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
require_once __DIR__ . '/../../koneksi.php';
mysqli_set_charset($koneksi, 'utf8mb4');

// Ambil parameter dari URL
$id_siswa    = isset($_GET['id_siswa'])    ? (int)$_GET['id_siswa']    : 0;
$id_mapel    = isset($_GET['id_mapel'])    ? (int)$_GET['id_mapel']    : 0;
$id_semester = isset($_GET['id_semester']) ? (int)$_GET['id_semester'] : 0;

// Validasi
if ($id_siswa <= 0 || $id_mapel <= 0 || $id_semester <= 0) {
  echo "<script>alert('Parameter tidak lengkap.');history.back();</script>";
  exit;
}

try {
  // Hapus data berdasarkan kombinasi 3 kunci unik
  $stmt = $koneksi->prepare("
    DELETE FROM nilai_mata_pelajaran
    WHERE id_siswa = ? AND id_mata_pelajaran = ? AND id_semester = ?
  ");
  $stmt->bind_param('iii', $id_siswa, $id_mapel, $id_semester);
  $stmt->execute();
  $stmt->close();

  // Redirect kembali ke halaman nilai_mapel.php + FLAG NOTIFIKASI
  header(
    "Location: nilai_mapel.php?id=" . urlencode($id_mapel) .
    "&id_semester=" . urlencode($id_semester) .
    "&msg=delete_success"             // <= ini yang memicu alert merah
  );
  exit;

} catch (Throwable $e) {
  echo "<script>alert('Gagal menghapus data: " . addslashes($e->getMessage()) . "');history.back();</script>";
  exit;
}
