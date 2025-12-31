<?php
// pages/kelas/proses_import_data_kelas.php
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
function json_out(bool $ok, string $msg, string $type = 'success', array $extra = [], int $code = 200): void
{
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(array_merge(['ok' => $ok, 'msg' => $msg, 'type' => $type], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: data_kelas.php');
  exit;
}

if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
  if (is_ajax()) json_out(false, 'Sesi tidak valid (CSRF). Silakan refresh halaman.', 'danger', [], 403);
  header('Location: data_kelas.php?err=' . urlencode('Sesi tidak valid (CSRF).'));
  exit;
}

if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
  if (is_ajax()) json_out(false, 'File Excel belum dipilih atau gagal diupload.', 'warning', [], 422);
  header('Location: data_kelas.php?err=' . urlencode('File Excel belum dipilih atau gagal diupload.'));
  exit;
}

$tmp = $_FILES['excel_file']['tmp_name'];
$ext = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['xlsx', 'xls'], true)) {
  if (is_ajax()) json_out(false, 'Format file harus .xlsx atau .xls', 'warning', [], 422);
  header('Location: data_kelas.php?err=' . urlencode('Format file harus .xlsx atau .xls'));
  exit;
}

// PhpSpreadsheet
$autoload1 = __DIR__ . '/../../vendor/autoload.php';
$autoload2 = __DIR__ . '/../../../vendor/autoload.php';
if (file_exists($autoload1)) {
  require_once $autoload1;
} elseif (file_exists($autoload2)) {
  require_once $autoload2;
} else {
  $m = 'Library PhpSpreadsheet tidak ditemukan. Pastikan sudah install via Composer (phpoffice/phpspreadsheet).';
  if (is_ajax()) json_out(false, $m, 'danger', [], 500);
  header('Location: data_kelas.php?err=' . urlencode($m));
  exit;
}

use PhpOffice\PhpSpreadsheet\IOFactory;

$allowedTingkat = ['X', 'XI', 'XII'];

$inserted = 0;
$skipped  = 0;
$dupName  = 0;
$waliMissing = 0;
$waliNotFound = 0;
$notes    = [];

try {
  $spreadsheet = IOFactory::load($tmp);
  $sheet = $spreadsheet->getActiveSheet();
  $highestRow = (int)$sheet->getHighestDataRow();

  // map guru: nama -> id
  $guruMap = [];
  $resG = mysqli_query($koneksi, "SELECT id_guru, nama_guru FROM guru");
  while ($g = mysqli_fetch_assoc($resG)) {
    $guruMap[mb_strtolower(trim($g['nama_guru'] ?? ''), 'UTF-8')] = (int)$g['id_guru'];
  }

  // map nama kelas existing
  $kelasExist = [];
  $resK = mysqli_query($koneksi, "SELECT id_kelas, nama_kelas FROM kelas");
  while ($k = mysqli_fetch_assoc($resK)) {
    $kelasExist[mb_strtolower(trim($k['nama_kelas'] ?? ''), 'UTF-8')] = (int)$k['id_kelas'];
  }

  // duplikat dalam file
  $seenInFile = [];

  $stmtIns = mysqli_prepare($koneksi, "INSERT INTO kelas (id_guru, tingkat_kelas, nama_kelas) VALUES (?, ?, ?)");

  for ($row = 2; $row <= $highestRow; $row++) {
    // A: No., B: Nama Kelas, C: Tingkat, D: Wali Kelas
    $namaKelas = trim((string)$sheet->getCell('B' . $row)->getValue());
    $tingkat   = strtoupper(trim((string)$sheet->getCell('C' . $row)->getValue()));
    $waliNama  = trim((string)$sheet->getCell('D' . $row)->getValue());

    if ($namaKelas === '' && $tingkat === '' && $waliNama === '') continue;

    if ($namaKelas === '' || !in_array($tingkat, $allowedTingkat, true)) {
      $skipped++;
      if (count($notes) < 10) $notes[] = "Baris {$row}: Nama Kelas kosong atau Tingkat tidak valid.";
      continue;
    }

    if ($waliNama === '') {
      $skipped++;
      $waliMissing++;
      if (count($notes) < 10) $notes[] = "Baris {$row}: Wali Kelas wajib diisi.";
      continue;
    }

    $waliKey = mb_strtolower($waliNama, 'UTF-8');
    if (!isset($guruMap[$waliKey])) {
      $skipped++;
      $waliNotFound++;
      if (count($notes) < 10) $notes[] = "Baris {$row}: Wali Kelas \"{$waliNama}\" tidak ditemukan di Data Guru.";
      continue;
    }
    $idGuru = (int)$guruMap[$waliKey];

    $kelasKey = mb_strtolower($namaKelas, 'UTF-8');

    // duplikat dalam file
    if (isset($seenInFile[$kelasKey])) {
      $skipped++;
      $dupName++;
      if (count($notes) < 10) $notes[] = "Baris {$row}: Nama Kelas \"{$namaKelas}\" duplikat di file (ditolak).";
      continue;
    }
    $seenInFile[$kelasKey] = true;

    // aturan: kalau nama kelas sudah digunakan di DB -> ditolak
    if (isset($kelasExist[$kelasKey])) {
      $skipped++;
      $dupName++;
      if (count($notes) < 10) $notes[] = "Baris {$row}: Nama Kelas \"{$namaKelas}\" sudah digunakan (ditolak).";
      continue;
    }

    mysqli_stmt_bind_param($stmtIns, 'iss', $idGuru, $tingkat, $namaKelas);
    mysqli_stmt_execute($stmtIns);
    $inserted++;

    $kelasExist[$kelasKey] = (int)mysqli_insert_id($koneksi);
  }

  mysqli_stmt_close($stmtIns);

  $ringkas = "Import selesai. Ditambah: {$inserted}. Dilewati: {$skipped}.";
  if ($dupName > 0) $ringkas .= " Ditolak duplikat nama: {$dupName}.";
  if ($waliMissing > 0) $ringkas .= " Wali kosong: {$waliMissing}.";
  if ($waliNotFound > 0) $ringkas .= " Wali tidak ditemukan: {$waliNotFound}.";

  $type = ($dupName > 0 || $waliMissing > 0 || $waliNotFound > 0) ? 'warning' : 'success';

  if (is_ajax()) {
    json_out(true, $ringkas, $type, ['notes' => $notes, 'inserted' => $inserted, 'skipped' => $skipped]);
  }

  if ($type === 'success') {
    header('Location: data_kelas.php?msg=' . urlencode($ringkas));
  } else {
    header('Location: data_kelas.php?err=' . urlencode($ringkas));
  }
  exit;
} catch (Throwable $e) {
  $m = 'Gagal memproses file Excel. Pastikan format sesuai template.';
  if (is_ajax()) json_out(false, $m, 'danger', [], 500);
  header('Location: data_kelas.php?err=' . urlencode($m));
  exit;
}
