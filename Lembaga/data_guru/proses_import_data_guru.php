<?php
// pages/guru/proses_import_data_guru.php
require_once '../../koneksi.php';
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

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
  $msg = 'Metode tidak diizinkan.';
  if (is_ajax_request()) json_out(['ok' => false, 'type' => 'danger', 'msg' => $msg], 405);
  header('Location: data_guru.php?err=' . urlencode($msg));
  exit;
}

if (!isset($_FILES['excel_file']) || !is_uploaded_file($_FILES['excel_file']['tmp_name'])) {
  $msg = 'Silakan pilih file Excel terlebih dahulu.';
  if (is_ajax_request()) json_out(['ok' => false, 'type' => 'danger', 'msg' => $msg], 422);
  header('Location: data_guru.php?err=' . urlencode($msg));
  exit;
}

$ALLOWED_JABATAN = ['Kepala Sekolah', 'Guru'];

$success   = 0;
$skipped   = 0;
$emptyRows = 0;

$skippedReasons = [
  'duplikat' => 0,
  'jabatan'  => 0,
  'kepsek'   => 0,
  'invalid'  => 0,
];

try {
  // cek apakah sudah ada kepala sekolah (sekali saja)
  $stmtKS = $koneksi->prepare("SELECT COUNT(*) AS cnt FROM guru WHERE jabatan_guru = 'Kepala Sekolah'");
  $stmtKS->execute();
  $kepsekExists = ((int)$stmtKS->get_result()->fetch_assoc()['cnt'] > 0);
  $stmtKS->close();

  $spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
  $sheet       = $spreadsheet->getActiveSheet();
  $highestRow  = $sheet->getHighestRow();

  // prepared untuk cek duplikat (case-insensitive)
  $stmtDup = $koneksi->prepare("SELECT COUNT(*) AS cnt FROM guru WHERE LOWER(nama_guru) = LOWER(?)");
  // prepared untuk insert
  $stmtIns = $koneksi->prepare("INSERT INTO guru (nama_guru, jabatan_guru) VALUES (?, ?)");

  for ($row = 2; $row <= $highestRow; $row++) {
    $nama    = trim((string)$sheet->getCell('B' . $row)->getValue());
    $jabatan = trim((string)$sheet->getCell('C' . $row)->getValue());

    if ($nama === '' && $jabatan === '') {
      $emptyRows++;
      continue;
    }

    if ($nama === '') {
      $skipped++;
      $skippedReasons['invalid']++;
      continue;
    }

    $jabatanNormalized = ucwords(strtolower($jabatan));
    if (!in_array($jabatanNormalized, $ALLOWED_JABATAN, true)) {
      $skipped++;
      $skippedReasons['jabatan']++;
      continue;
    }

    // cek duplikat nama
    $stmtDup->bind_param('s', $nama);
    $stmtDup->execute();
    $cntDup = (int)$stmtDup->get_result()->fetch_assoc()['cnt'];
    if ($cntDup > 0) {
      $skipped++;
      $skippedReasons['duplikat']++;
      continue;
    }

    // kepala sekolah cuma 1 (import: kalau sudah ada → skip)
    if ($jabatanNormalized === 'Kepala Sekolah' && $kepsekExists) {
      $skipped++;
      $skippedReasons['kepsek']++;
      continue;
    }

    // insert
    $stmtIns->bind_param('ss', $nama, $jabatanNormalized);
    if ($stmtIns->execute()) {
      $success++;
      if ($jabatanNormalized === 'Kepala Sekolah') {
        $kepsekExists = true; // setelah sukses insert kepsek, baris kepsek berikutnya skip
      }
    } else {
      $skipped++;
      $skippedReasons['invalid']++;
    }
  }

  $stmtDup->close();
  $stmtIns->close();

  // Susun pesan akhir
  $parts = [];
  $parts[] = "Import selesai.";
  $parts[] = "Berhasil: $success baris.";

  if ($skipped > 0) {
    $detail = [];
    if ($skippedReasons['duplikat'] > 0) $detail[] = "duplikat nama: {$skippedReasons['duplikat']}";
    if ($skippedReasons['kepsek'] > 0)   $detail[] = "kepala sekolah lebih dari 1: {$skippedReasons['kepsek']}";
    if ($skippedReasons['jabatan'] > 0)  $detail[] = "jabatan tidak valid: {$skippedReasons['jabatan']}";
    if ($skippedReasons['invalid'] > 0)  $detail[] = "data tidak valid: {$skippedReasons['invalid']}";
    $parts[] = "Dilewati: $skipped baris (" . implode(', ', $detail) . ").";
  }

  if ($emptyRows > 0) {
    $parts[] = "Baris kosong: $emptyRows baris (diabaikan).";
  }

  $msg = implode(' ', $parts);

  // kalau ada yang di-skip, tipe warning biar kerasa “ada yang dilewati”
  $type = ($skipped > 0) ? 'warning' : 'success';

  if (is_ajax_request()) json_out(['ok' => true, 'type' => $type, 'msg' => $msg]);
  header('Location: data_guru.php?msg=' . urlencode($msg));
  exit;
} catch (Throwable $e) {
  $msg = 'Gagal memproses file Excel: ' . $e->getMessage();
  if (is_ajax_request()) json_out(['ok' => false, 'type' => 'danger', 'msg' => $msg], 500);
  header('Location: data_guru.php?err=' . urlencode($msg));
  exit;
}
