<?php
// pages/guru/proses_tambah_data_guru.php
require_once '../../koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$koneksi->set_charset('utf8mb4');

function is_ajax_request(): bool
{
  return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}

function json_out(array $payload, int $code = 200): void
{
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($payload);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  if (is_ajax_request()) json_out(['ok' => false, 'type' => 'danger', 'msg' => 'Metode tidak diizinkan.'], 405);
  header('Location: data_guru.php?err=' . urlencode('Metode tidak diizinkan.'));
  exit;
}

$ALLOWED_JABATAN = ['Kepala Sekolah', 'Guru'];
$errors = [];

$nama_guru    = isset($_POST['nama_guru']) ? trim($_POST['nama_guru']) : '';
$jabatan_guru = isset($_POST['jabatan_guru']) ? trim($_POST['jabatan_guru']) : '';

if ($nama_guru === '') {
  $errors[] = 'Nama Guru wajib diisi.';
}
if (!in_array($jabatan_guru, $ALLOWED_JABATAN, true)) {
  $errors[] = 'Jabatan tidak valid. Pilih "Kepala Sekolah" atau "Guru".';
}

if (!empty($errors)) {
  $msg = implode(' | ', $errors);
  if (is_ajax_request()) json_out(['ok' => false, 'type' => 'danger', 'msg' => $msg], 422);
  header('Location: data_guru.php?err=' . urlencode($msg));
  exit;
}

// ===========================
// ✅ CEK DUPLIKAT NAMA (case-insensitive)
// ===========================
$stmtDup = $koneksi->prepare("SELECT COUNT(*) AS cnt FROM guru WHERE LOWER(nama_guru) = LOWER(?)");
$stmtDup->bind_param('s', $nama_guru);
$stmtDup->execute();
$cntDup = (int)$stmtDup->get_result()->fetch_assoc()['cnt'];
$stmtDup->close();

if ($cntDup > 0) {
  $msg = 'Nama guru sudah ada. Silakan gunakan nama lain.';
  if (is_ajax_request()) json_out(['ok' => false, 'type' => 'danger', 'msg' => $msg], 409);
  header('Location: data_guru.php?err=' . urlencode($msg));
  exit;
}

// ===========================
// ✅ KEPALA SEKOLAH CUMA 1
// ===========================
if ($jabatan_guru === 'Kepala Sekolah') {
  $stmtKS = $koneksi->prepare("SELECT COUNT(*) AS cnt FROM guru WHERE jabatan_guru = 'Kepala Sekolah'");
  $stmtKS->execute();
  $cntKS = (int)$stmtKS->get_result()->fetch_assoc()['cnt'];
  $stmtKS->close();

  if ($cntKS > 0) {
    $msg = 'Kepala Sekolah hanya boleh 1. Sudah ada data Kepala Sekolah.';
    if (is_ajax_request()) json_out(['ok' => false, 'type' => 'danger', 'msg' => $msg], 409);
    header('Location: data_guru.php?err=' . urlencode($msg));
    exit;
  }
}

// Insert
$sql = "INSERT INTO guru (nama_guru, jabatan_guru) VALUES (?, ?)";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param('ss', $nama_guru, $jabatan_guru);

if ($stmt->execute()) {
  $msg = 'Data guru berhasil ditambahkan.';
  if (is_ajax_request()) json_out(['ok' => true, 'type' => 'success', 'msg' => $msg]);
  header('Location: data_guru.php?msg=' . urlencode($msg));
  exit;
}

$msg = 'Gagal menambahkan data guru.';
if (is_ajax_request()) json_out(['ok' => false, 'type' => 'danger', 'msg' => $msg], 500);
header('Location: data_guru.php?err=' . urlencode($msg));
exit;
