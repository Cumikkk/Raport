<?php
// pages/siswa/proses_import_data_siswa.php
require_once '../../koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['ok' => false, 'type' => 'danger', 'msg' => 'Metode tidak diizinkan.']);
  exit;
}

if (!isset($_FILES['excelFile']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
  echo json_encode(['ok' => false, 'type' => 'warning', 'msg' => 'File Excel belum dipilih atau terjadi kesalahan upload.']);
  exit;
}

$allowedExt = ['xls', 'xlsx'];
$filename   = $_FILES['excelFile']['name'] ?? '';
$tmpPath    = $_FILES['excelFile']['tmp_name'] ?? '';
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

try {
  $spreadsheet = IOFactory::load($tmpPath);
  $sheet = $spreadsheet->getActiveSheet();

  $highestRow = (int)$sheet->getHighestDataRow();
  $highestCol = (string)$sheet->getHighestDataColumn();
  $highestColIndex = (int)Coordinate::columnIndexFromString($highestCol);

  if ($highestRow < 2 || $highestColIndex < 5) {
    echo json_encode([
      'ok' => false,
      'type' => 'warning',
      'msg' => 'File Excel tidak sesuai. Pastikan kolom sampai E (No, NIS, Nama, Kelas, Absen).'
    ]);
    exit;
  }

  // Cache kelas: nama_kelas(lower) => id_kelas
  $kelasMap = [];
  $qKelas = mysqli_query($koneksi, "SELECT id_kelas, nama_kelas FROM kelas");
  while ($k = mysqli_fetch_assoc($qKelas)) {
    $nama = mb_strtolower(norm($k['nama_kelas'] ?? ''), 'UTF-8');
    if ($nama !== '') $kelasMap[$nama] = (int)$k['id_kelas'];
  }

  mysqli_begin_transaction($koneksi);

  $inserted = 0;
  $skipped_empty = 0;
  $skipped_invalid = 0;
  $duplicates_db = 0;
  $duplicates_file = 0;
  $errors = [];

  // prepared: cek nis ada
  $stmtFind = mysqli_prepare($koneksi, "SELECT id_siswa FROM siswa WHERE no_induk_siswa = ? LIMIT 1");
  $stmtIns  = mysqli_prepare($koneksi, "INSERT INTO siswa (nama_siswa, no_induk_siswa, no_absen_siswa, id_kelas) VALUES (?, ?, ?, ?)");

  // untuk deteksi duplikat di dalam file
  $seenNis = [];

  // Data mulai row 2 (row 1 header)
  for ($r = 2; $r <= $highestRow; $r++) {
    // Susunan excel user:
    // A: No (abaikan)
    // B: NIS
    // C: Nama Siswa
    // D: Kelas (nama_kelas)
    // E: Absen
    $nisRaw  = $sheet->getCell("B{$r}")->getFormattedValue();
    $namaRaw  = $sheet->getCell("C{$r}")->getFormattedValue();
    $kelasRaw = $sheet->getCell("D{$r}")->getFormattedValue();
    $absenRaw = $sheet->getCell("E{$r}")->getFormattedValue();

    $nis  = norm($nisRaw);
    $nama  = norm($namaRaw);
    $kelas = norm($kelasRaw);
    $absen = norm($absenRaw);

    if ($nis === '' && $nama === '' && $kelas === '' && $absen === '') {
      $skipped_empty++;
      continue;
    }

    if ($nis === '' || $nama === '' || $kelas === '' || $absen === '') {
      $skipped_invalid++;
      $errors[] = "Baris {$r}: data belum lengkap (NIS/Nama/Kelas/Absen wajib).";
      continue;
    }

    // duplikat di file
    $keyN = mb_strtolower($nis, 'UTF-8');
    if (isset($seenNis[$keyN])) {
      $duplicates_file++;
      $errors[] = "Baris {$r}: NIS duplikat di file ({$nis}).";
      continue;
    }
    $seenNis[$keyN] = true;

    // kelas harus ada di master
    $kelasKey = mb_strtolower($kelas, 'UTF-8');
    $id_kelas = $kelasMap[$kelasKey] ?? 0;
    if ($id_kelas <= 0) {
      $skipped_invalid++;
      $errors[] = "Baris {$r}: kelas \"{$kelas}\" tidak ditemukan di master kelas.";
      continue;
    }

    // ✅ duplikat di DB -> SKIP / TOLAK (tidak update)
    mysqli_stmt_bind_param($stmtFind, 's', $nis);
    mysqli_stmt_execute($stmtFind);
    $resFind = mysqli_stmt_get_result($stmtFind);
    $found = mysqli_fetch_assoc($resFind);

    if (!empty($found)) {
      $duplicates_db++;
      $errors[] = "Baris {$r}: NIS {$nis} sudah ada di database. Baris di-skip.";
      continue;
    }

    // insert
    mysqli_stmt_bind_param($stmtIns, 'sssi', $nama, $nis, $absen, $id_kelas);
    mysqli_stmt_execute($stmtIns);
    $inserted++;
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
