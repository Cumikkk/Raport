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
  $m = 'Sesi tidak valid (CSRF). Silakan refresh halaman.';
  if (is_ajax()) json_out(false, $m, 'danger', [], 403);
  header('Location: data_kelas.php?status=danger&msg=' . urlencode($m));
  exit;
}

if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
  $m = 'File Excel belum dipilih atau gagal diupload.';
  if (is_ajax()) json_out(false, $m, 'warning', [], 422);
  header('Location: data_kelas.php?status=danger&msg=' . urlencode($m));
  exit;
}

$tmp = $_FILES['excel_file']['tmp_name'];
$ext = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['xlsx', 'xls'], true)) {
  $m = 'Format file tidak didukung. Upload: harus .xlsx atau .xls.';
  if (is_ajax()) json_out(false, $m, 'warning', [], 422);
  header('Location: data_kelas.php?status=danger&msg=' . urlencode($m));
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
  header('Location: data_kelas.php?status=danger&msg=' . urlencode($m));
  exit;
}

use PhpOffice\PhpSpreadsheet\IOFactory;

$allowedTingkat = ['X', 'XI', 'XII'];

// sesuai format alert yang diminta
$inserted = 0;
$duplicates_db_npk = 0;    // duplikat di sistem (nama kelas sudah ada)
$duplicates_file_npk = 0;  // duplikat di file excel (nama kelas duplikat)
$skipped_empty = 0;        // baris kosong
$skipped_invalid = 0;      // data tidak valid
$errors = [];

try {
  $spreadsheet = IOFactory::load($tmp);
  $sheet = $spreadsheet->getActiveSheet();
  $highestRow = (int)$sheet->getHighestDataRow();

  // map guru: nama -> id
  $guruMap = [];
  $resG = mysqli_query($koneksi, "SELECT id_guru, nama_guru FROM guru");
  while ($g = mysqli_fetch_assoc($resG)) {
    $guruMap[mb_strtolower(trim((string)($g['nama_guru'] ?? '')), 'UTF-8')] = (int)$g['id_guru'];
  }

  // map nama kelas existing
  $kelasExist = [];
  $resK = mysqli_query($koneksi, "SELECT id_kelas, nama_kelas FROM kelas");
  while ($k = mysqli_fetch_assoc($resK)) {
    $kelasExist[mb_strtolower(trim((string)($k['nama_kelas'] ?? '')), 'UTF-8')] = (int)$k['id_kelas'];
  }

  // duplikat dalam file
  $seenInFile = [];

  $stmtIns = mysqli_prepare($koneksi, "INSERT INTO kelas (id_guru, tingkat_kelas, nama_kelas) VALUES (?, ?, ?)");

  for ($row = 2; $row <= $highestRow; $row++) {
    // A: No., B: Nama Kelas, C: Tingkat, D: Wali Kelas
    $namaKelas = trim((string)$sheet->getCell('B' . $row)->getValue());
    $tingkat   = strtoupper(trim((string)$sheet->getCell('C' . $row)->getValue()));
    $waliNama  = trim((string)$sheet->getCell('D' . $row)->getValue());

    // baris kosong
    if ($namaKelas === '' && $tingkat === '' && $waliNama === '') {
      $skipped_empty++;
      continue;
    }

    // validasi dasar
    if ($namaKelas === '' || !in_array($tingkat, $allowedTingkat, true) || $waliNama === '') {
      $skipped_invalid++;
      if (count($errors) < 50) {
        $errors[] = "Baris {$row}: Data tidak valid (Nama Kelas/Tingkat/Wali Kelas wajib diisi dan Tingkat harus X/XI/XII).";
      }
      continue;
    }

    // wali harus ada di sistem
    $waliKey = mb_strtolower($waliNama, 'UTF-8');
    if (!isset($guruMap[$waliKey])) {
      $skipped_invalid++;
      if (count($errors) < 50) {
        $errors[] = "Baris {$row}: Wali Kelas \"{$waliNama}\" tidak ditemukan di Data Guru.";
      }
      continue;
    }
    $idGuru = (int)$guruMap[$waliKey];

    $kelasKey = mb_strtolower($namaKelas, 'UTF-8');

    // duplikat di file
    if (isset($seenInFile[$kelasKey])) {
      $duplicates_file_npk++;
      if (count($errors) < 50) {
        $errors[] = "Baris {$row}: Nama Kelas \"{$namaKelas}\" duplikat di file excel.";
      }
      continue;
    }
    $seenInFile[$kelasKey] = true;

    // duplikat di sistem
    if (isset($kelasExist[$kelasKey])) {
      $duplicates_db_npk++;
      if (count($errors) < 50) {
        $errors[] = "Baris {$row}: Nama Kelas \"{$namaKelas}\" sudah ada di sistem.";
      }
      continue;
    }

    mysqli_stmt_bind_param($stmtIns, 'iss', $idGuru, $tingkat, $namaKelas);
    mysqli_stmt_execute($stmtIns);
    $inserted++;

    $kelasExist[$kelasKey] = (int)mysqli_insert_id($koneksi);
  }

  mysqli_stmt_close($stmtIns);

  // ✅ format msg sesuai yang diminta
  $msg = "Import selesai. Data masuk: {$inserted}, Data duplikat dalam sistem: {$duplicates_db_npk}, Data duplikat dalam file excel: {$duplicates_file_npk}, Baris kosong dilewati: {$skipped_empty}, Data tidak valid: {$skipped_invalid}.";
  if (!empty($errors)) $msg .= " Ada " . count($errors) . " catatan.";

  if (is_ajax()) {
    json_out(true, $msg, 'success', [
      'errors' => $errors,
      'inserted' => $inserted,
      'duplicates_db' => $duplicates_db_npk,
      'duplicates_file' => $duplicates_file_npk,
      'skipped_empty' => $skipped_empty,
      'skipped_invalid' => $skipped_invalid
    ]);
  }

  header('Location: data_kelas.php?status=success&msg=' . urlencode($msg));
  exit;
} catch (Throwable $e) {
  $m = 'Gagal memproses file Excel. Pastikan format sesuai template.';
  if (is_ajax()) json_out(false, $m, 'danger', [], 500);
  header('Location: data_kelas.php?status=danger&msg=' . urlencode($m));
  exit;
}
