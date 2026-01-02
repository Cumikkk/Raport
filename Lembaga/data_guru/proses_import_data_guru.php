<?php
// pages/guru/proses_import_data_guru.php
require_once '../../koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['ok' => false, 'type' => 'danger', 'msg' => 'Metode tidak diizinkan.']);
  exit;
}

// support name input: excel_file (punyamu) atau excelFile (contoh siswa)
$file = null;
if (isset($_FILES['excel_file'])) $file = $_FILES['excel_file'];
if (!$file && isset($_FILES['excelFile'])) $file = $_FILES['excelFile'];

if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
  echo json_encode(['ok' => false, 'type' => 'warning', 'msg' => 'File Excel belum dipilih atau terjadi kesalahan upload.']);
  exit;
}

$allowedExt = ['xls', 'xlsx'];
$filename   = $file['name'] ?? '';
$tmpPath    = $file['tmp_name'] ?? '';
$ext        = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt, true)) {
  echo json_encode(['ok' => false, 'type' => 'warning', 'msg' => 'Format file tidak didukung. Upload .xls / .xlsx.']);
  exit;
}

if (!is_uploaded_file($tmpPath)) {
  echo json_encode(['ok' => false, 'type' => 'danger', 'msg' => 'Upload file tidak valid.']);
  exit;
}

// Cari autoload PhpSpreadsheet
$autoloadCandidates = [
  __DIR__ . '/../../vendor/autoload.php',
  __DIR__ . '/../../../vendor/autoload.php',
  __DIR__ . '/../../../../vendor/autoload.php',
  __DIR__ . '/vendor/autoload.php',
];

$autoloadFound = null;
foreach ($autoloadCandidates as $p) {
  if (file_exists($p)) {
    $autoloadFound = $p;
    break;
  }
}

if (!$autoloadFound) {
  echo json_encode([
    'ok' => false,
    'type' => 'danger',
    'msg' => 'PhpSpreadsheet belum terpasang. Jalankan: composer require phpoffice/phpspreadsheet (pastikan folder vendor/ ada).'
  ]);
  exit;
}

require_once $autoloadFound;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

function norm($v): string
{
  $v = trim((string)$v);
  $v = preg_replace('/\s+/', ' ', $v);
  return $v;
}

function lower_key(string $v): string
{
  return mb_strtolower($v, 'UTF-8');
}

try {
  $spreadsheet = IOFactory::load($tmpPath);
  $sheet = $spreadsheet->getActiveSheet();

  $highestRow = (int)$sheet->getHighestDataRow();
  $highestCol = (string)$sheet->getHighestDataColumn();
  $highestColIndex = (int)Coordinate::columnIndexFromString($highestCol);

  // Template guru: A No, B Nama Guru, C Jabatan
  if ($highestRow < 2 || $highestColIndex < 3) {
    echo json_encode([
      'ok' => false,
      'type' => 'warning',
      'msg' => 'File Excel tidak sesuai. Pastikan kolom sampai C (No, Nama Guru, Jabatan).'
    ]);
    exit;
  }

  // cek apakah sudah ada kepala sekolah di DB
  $kepsekExists = false;
  $qKS = mysqli_query($koneksi, "SELECT COUNT(*) AS cnt FROM guru WHERE jabatan_guru='Kepala Sekolah'");
  if ($qKS) {
    $rowKS = mysqli_fetch_assoc($qKS);
    $kepsekExists = ((int)($rowKS['cnt'] ?? 0) > 0);
  }

  mysqli_begin_transaction($koneksi);

  $inserted = 0;
  $skipped_empty = 0;
  $skipped_invalid = 0;
  $duplicates_db = 0;
  $duplicates_file = 0;
  $errors = [];

  $allowedJabatan = ['Kepala Sekolah', 'Guru'];

  // prepared: cek duplikat di DB berdasarkan (nama|jabatan) case-insensitive
  $stmtFind = mysqli_prepare(
    $koneksi,
    "SELECT id_guru FROM guru WHERE LOWER(nama_guru)=LOWER(?) AND jabatan_guru=? LIMIT 1"
  );

  // prepared: insert
  $stmtIns  = mysqli_prepare(
    $koneksi,
    "INSERT INTO guru (nama_guru, jabatan_guru) VALUES (?, ?)"
  );

  // deteksi duplikat di dalam file (nama|jabatan)
  $seen = [];

  for ($r = 2; $r <= $highestRow; $r++) {
    // A: No (abaikan)
    // B: Nama Guru
    // C: Jabatan
    $namaRaw    = $sheet->getCell("B{$r}")->getFormattedValue();
    $jabatanRaw = $sheet->getCell("C{$r}")->getFormattedValue();

    $nama    = norm($namaRaw);
    $jabatan = norm($jabatanRaw);

    if ($nama === '' && $jabatan === '') {
      $skipped_empty++;
      continue;
    }

    if ($nama === '' || $jabatan === '') {
      $skipped_invalid++;
      $errors[] = "Baris {$r}: data belum lengkap (Nama/Jabatan wajib).";
      continue;
    }

    // Normalisasi jabatan agar konsisten
    $jabKey = lower_key($jabatan);
    if ($jabKey === 'kepala sekolah' || $jabKey === 'kepalasekolah') $jabatan = 'Kepala Sekolah';
    else if ($jabKey === 'guru') $jabatan = 'Guru';

    if (!in_array($jabatan, $allowedJabatan, true)) {
      $skipped_invalid++;
      $errors[] = "Baris {$r}: Jabatan \"{$jabatanRaw}\" tidak valid (hanya Kepala Sekolah / Guru).";
      continue;
    }

    // duplikat di file (pakai nama|jabatan)
    $key = lower_key($nama . '|' . $jabatan);
    if (isset($seen[$key])) {
      $duplicates_file++;
      $errors[] = "Baris {$r}: Duplikat di file ({$nama} - {$jabatan}).";
      continue;
    }
    $seen[$key] = true;

    // aturan kepsek hanya 1 (di DB atau sudah masuk pada baris sebelumnya)
    if ($jabatan === 'Kepala Sekolah' && $kepsekExists) {
      $skipped_invalid++;
      $errors[] = "Baris {$r}: Kepala Sekolah sudah ada. Baris di-skip.";
      continue;
    }

    // duplikat di DB (nama|jabatan)
    mysqli_stmt_bind_param($stmtFind, 'ss', $nama, $jabatan);
    mysqli_stmt_execute($stmtFind);
    $resFind = mysqli_stmt_get_result($stmtFind);
    $found = mysqli_fetch_assoc($resFind);

    if (!empty($found)) {
      $duplicates_db++;
      $errors[] = "Baris {$r}: {$nama} ({$jabatan}) sudah ada di database. Baris di-skip.";
      continue;
    }

    // insert
    mysqli_stmt_bind_param($stmtIns, 'ss', $nama, $jabatan);
    mysqli_stmt_execute($stmtIns);
    $inserted++;

    if ($jabatan === 'Kepala Sekolah') {
      $kepsekExists = true;
    }
  }

  mysqli_stmt_close($stmtFind);
  mysqli_stmt_close($stmtIns);

  mysqli_commit($koneksi);

  $msg = "Import selesai. Data masuk: {$inserted}, Data duplikat dalam sistem: {$duplicates_db}, Data duplikat dalam file excel: {$duplicates_file}, Baris kosong dilewati: {$skipped_empty}, Data tidak valid: {$skipped_invalid}.";
  if (!empty($errors)) $msg .= " Ada " . count($errors) . " catatan.";

  echo json_encode([
    'ok' => true,
    'type' => ($inserted > 0 ? 'success' : 'warning'),
    'msg' => $msg,
    'detail' => [
      'inserted' => $inserted,
      'duplicates_db' => $duplicates_db,
      'duplicates_file' => $duplicates_file,
      'skipped_empty' => $skipped_empty,
      'skipped_invalid' => $skipped_invalid,
      'errors' => $errors
    ]
  ]);
  exit;
} catch (Throwable $e) {
  try {
    mysqli_rollback($koneksi);
  } catch (Throwable $e2) {
  }

  echo json_encode([
    'ok' => false,
    'type' => 'danger',
    'msg' => 'Gagal import. Pastikan file sesuai template dan PhpSpreadsheet terpasang.'
  ]);
  exit;
}
