<?php
// pages/kelas/proses_edit_data_kelas.php
require_once '../../koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function is_ajax(): bool
{
  return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}
function json_out(bool $ok, string $msg, string $type = 'success', int $code = 200): void
{
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok' => $ok, 'msg' => $msg, 'type' => $type], JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: data_kelas.php');
  exit;
}

if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
  if (is_ajax()) json_out(false, 'Sesi tidak valid (CSRF). Silakan refresh halaman.', 'danger', 403);
  header('Location: data_kelas.php?err=' . urlencode('Sesi tidak valid (CSRF).'));
  exit;
}

$id_kelas   = (int)($_POST['id_kelas'] ?? 0);
$nama_kelas = trim($_POST['nama_kelas'] ?? '');
$tingkat    = trim($_POST['tingkat_kelas'] ?? '');
$id_guru    = (int)($_POST['id_guru'] ?? 0);

$allowed = ['X', 'XI', 'XII'];
if ($id_kelas <= 0 || $nama_kelas === '' || !in_array($tingkat, $allowed, true) || $id_guru <= 0) {
  if (is_ajax()) json_out(false, 'Data tidak valid. Pastikan Nama Kelas, Tingkat, dan Wali Kelas terisi.', 'warning', 422);
  header('Location: data_kelas.php?err=' . urlencode('Data tidak valid.'));
  exit;
}

try {
  // duplikat nama_kelas (case-insensitive), kecuali dirinya -> ditolak
  $sqlDup = "SELECT id_kelas FROM kelas WHERE LOWER(nama_kelas) = LOWER(?) AND id_kelas <> ? LIMIT 1";
  $stmtDup = mysqli_prepare($koneksi, $sqlDup);
  mysqli_stmt_bind_param($stmtDup, 'si', $nama_kelas, $id_kelas);
  mysqli_stmt_execute($stmtDup);
  $resDup = mysqli_stmt_get_result($stmtDup);
  $dup = mysqli_fetch_assoc($resDup);
  mysqli_stmt_close($stmtDup);

  if (!empty($dup['id_kelas'])) {
    if (is_ajax()) json_out(false, 'Nama kelas sudah digunakan. Silakan pakai nama lain.', 'warning', 409);
    header('Location: data_kelas.php?err=' . urlencode('Nama kelas sudah digunakan.'));
    exit;
  }

  $sql = "UPDATE kelas SET id_guru = ?, tingkat_kelas = ?, nama_kelas = ? WHERE id_kelas = ?";
  $stmt = mysqli_prepare($koneksi, $sql);
  mysqli_stmt_bind_param($stmt, 'issi', $id_guru, $tingkat, $nama_kelas, $id_kelas);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  if (is_ajax()) json_out(true, 'Data kelas berhasil diperbarui.', 'success');
  header('Location: data_kelas.php?msg=' . urlencode('Data kelas berhasil diperbarui.'));
  exit;
} catch (Throwable $e) {
  if (is_ajax()) json_out(false, 'Terjadi kesalahan saat memperbarui data.', 'danger', 500);
  header('Location: data_kelas.php?err=' . urlencode('Terjadi kesalahan saat memperbarui data.'));
  exit;
}
