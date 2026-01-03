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
  $m = 'Sesi tidak valid (CSRF). Silakan refresh halaman.';
  if (is_ajax()) json_out(false, $m, 'danger', 403);
  header('Location: data_kelas.php?status=danger&msg=' . urlencode($m));
  exit;
}

$id_kelas   = (int)($_POST['id_kelas'] ?? 0);
$nama_kelas = trim($_POST['nama_kelas'] ?? '');
$tingkat    = trim($_POST['tingkat_kelas'] ?? '');
$id_guru    = (int)($_POST['id_guru'] ?? 0);

$allowed = ['X', 'XI', 'XII'];
if ($id_kelas <= 0 || $nama_kelas === '' || !in_array($tingkat, $allowed, true) || $id_guru <= 0) {
  $m = 'Data tidak valid. Pastikan Nama Kelas, Tingkat, dan Wali Kelas terisi.';
  if (is_ajax()) json_out(false, $m, 'warning', 422);
  header('Location: data_kelas.php?status=danger&msg=' . urlencode($m));
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
    $m = 'Nama kelas sudah terpakai.';
    if (is_ajax()) json_out(false, $m, 'warning', 409);
    header('Location: data_kelas.php?status=danger&msg=' . urlencode($m));
    exit;
  }

  $sql = "UPDATE kelas SET id_guru = ?, tingkat_kelas = ?, nama_kelas = ? WHERE id_kelas = ?";
  $stmt = mysqli_prepare($koneksi, $sql);
  mysqli_stmt_bind_param($stmt, 'issi', $id_guru, $tingkat, $nama_kelas, $id_kelas);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  $okMsg = 'Data kelas berhasil diperbarui.';
  if (is_ajax()) json_out(true, $okMsg, 'success');
  header('Location: data_kelas.php?status=success&msg=' . urlencode($okMsg));
  exit;
} catch (Throwable $e) {
  $m = 'Gagal memperbarui data kelas.';
  if (is_ajax()) json_out(false, $m, 'danger', 500);
  header('Location: data_kelas.php?status=danger&msg=' . urlencode($m));
  exit;
}
