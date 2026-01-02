<?php
// pages/guru/proses_edit_data_guru.php
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

function redirect_with(string $status, string $msg): void
{
  $qs = http_build_query(['status' => $status, 'msg' => $msg]);
  header('Location: data_guru.php?' . $qs);
  exit;
}

function norm(string $v): string
{
  $v = trim($v);
  $v = preg_replace('/\s+/', ' ', $v);
  return $v;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  $msg = 'Metode tidak diizinkan.';
  if (is_ajax_request()) json_out(['ok' => false, 'type' => 'danger', 'msg' => $msg], 405);
  redirect_with('error', $msg);
}

$ALLOWED_JABATAN = ['Kepala Sekolah', 'Guru'];
$errors = [];

$id = isset($_POST['id_guru']) ? (int)$_POST['id_guru'] : 0;
if ($id <= 0) {
  $msg = 'ID tidak valid.';
  if (is_ajax_request()) json_out(['ok' => false, 'type' => 'warning', 'msg' => $msg], 422);
  redirect_with('error', $msg);
}

// Pastikan data ada
$stmtFind = $koneksi->prepare("SELECT id_guru, jabatan_guru, npk_guru FROM guru WHERE id_guru = ?");
$stmtFind->bind_param('i', $id);
$stmtFind->execute();
$current = $stmtFind->get_result()->fetch_assoc();
$stmtFind->close();

if (!$current) {
  $msg = 'Data guru tidak ditemukan.';
  if (is_ajax_request()) json_out(['ok' => false, 'type' => 'warning', 'msg' => $msg], 404);
  redirect_with('error', $msg);
}

$npk_guru     = isset($_POST['npk_guru']) ? norm((string)$_POST['npk_guru']) : '';
$nama_guru    = isset($_POST['nama_guru']) ? norm((string)$_POST['nama_guru']) : '';
$jabatan_guru = isset($_POST['jabatan_guru']) ? norm((string)$_POST['jabatan_guru']) : '';

if ($npk_guru === '') {
  $errors[] = 'NPK wajib diisi.';
} elseif (mb_strlen($npk_guru, 'UTF-8') > 50) {
  $errors[] = 'NPK maksimal 50 karakter.';
}

if ($nama_guru === '') {
  $errors[] = 'Nama Guru wajib diisi.';
} elseif (mb_strlen($nama_guru, 'UTF-8') > 100) {
  $errors[] = 'Nama Guru maksimal 100 karakter.';
}

if (!in_array($jabatan_guru, $ALLOWED_JABATAN, true)) {
  $errors[] = 'Jabatan tidak valid. Pilih "Kepala Sekolah" atau "Guru".';
}

if (!empty($errors)) {
  $msg = implode(' | ', $errors);
  if (is_ajax_request()) json_out(['ok' => false, 'type' => 'warning', 'msg' => $msg], 422);
  redirect_with('error', $msg);
}

// ✅ Aturan baru: tolak jika NPK sudah dipakai data lain (exclude dirinya)
$stmtDup = $koneksi->prepare("SELECT COUNT(*) AS cnt FROM guru WHERE npk_guru = ? AND id_guru <> ?");
$stmtDup->bind_param('si', $npk_guru, $id);
$stmtDup->execute();
$cntDup = (int)$stmtDup->get_result()->fetch_assoc()['cnt'];
$stmtDup->close();

if ($cntDup > 0) {
  $msg = 'NPK sudah terpakai.';
  if (is_ajax_request()) json_out(['ok' => false, 'type' => 'warning', 'msg' => $msg], 409);
  redirect_with('error', $msg);
}

// ✅ KEPALA SEKOLAH CUMA 1 (kecuali dirinya sendiri)
if ($jabatan_guru === 'Kepala Sekolah') {
  $stmtKS = $koneksi->prepare("SELECT COUNT(*) AS cnt FROM guru WHERE jabatan_guru='Kepala Sekolah' AND id_guru <> ?");
  $stmtKS->bind_param('i', $id);
  $stmtKS->execute();
  $cntKS = (int)$stmtKS->get_result()->fetch_assoc()['cnt'];
  $stmtKS->close();

  if ($cntKS > 0) {
    $msg = 'Kepala Sekolah sudah ada.';
    if (is_ajax_request()) json_out(['ok' => false, 'type' => 'warning', 'msg' => $msg], 409);
    redirect_with('error', $msg);
  }
}

// Update
$stmtUpd = $koneksi->prepare("UPDATE guru SET npk_guru = ?, nama_guru = ?, jabatan_guru = ? WHERE id_guru = ?");
$stmtUpd->bind_param('sssi', $npk_guru, $nama_guru, $jabatan_guru, $id);

if ($stmtUpd->execute()) {
  $stmtUpd->close();
  $msg = 'Data guru berhasil diperbarui.';
  if (is_ajax_request()) json_out(['ok' => true, 'type' => 'success', 'msg' => $msg]);
  redirect_with('success', $msg);
}

$stmtUpd->close();
$msg = 'Gagal memperbarui data guru.';
if (is_ajax_request()) json_out(['ok' => false, 'type' => 'danger', 'msg' => $msg], 500);
redirect_with('error', $msg);
