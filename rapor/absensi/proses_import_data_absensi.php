<?php
// pages/absensi/proses_import_data_absensi.php
require_once __DIR__ . '/../../koneksi.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: data_absensi.php?err=' . urlencode('Metode tidak diizinkan.'));
  exit;
}

if (!isset($_FILES['excel_file']) || !is_uploaded_file($_FILES['excel_file']['tmp_name'])) {
  header('Location: data_absensi.php?err=' . urlencode('Silakan pilih file Excel terlebih dahulu.'));
  exit;
}

// Ambil id_semester (pakai yang pertama/aktif sesuai versi kamu)
$res = $koneksi->query("SELECT id_semester FROM semester ORDER BY id_semester ASC LIMIT 1");
if (!$res || !$res->num_rows) {
  header('Location: data_absensi.php?err=' . urlencode('Data semester belum ada. Silakan isi data semester terlebih dahulu.'));
  exit;
}
$id_semester = (int)$res->fetch_assoc()['id_semester'];

$success          = 0;
$skipped          = 0;
$emptyRows        = 0;
$skippedNoNIS     = 0;
$skippedNoSiswa   = 0;
$skippedDuplicate = 0;

try {
  $spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
  $sheet       = $spreadsheet->getActiveSheet();
  $highestRow  = $sheet->getHighestRow();

  // Prepare statements (lebih cepat)
  $stmtFindSiswa = $koneksi->prepare("
    SELECT s.id_siswa
    FROM siswa s
    WHERE s.no_induk_siswa = ?
    LIMIT 1
  ");

  $stmtCheckDup = $koneksi->prepare("
    SELECT 1
    FROM absensi
    WHERE id_semester = ? AND id_siswa = ?
    LIMIT 1
  ");

  $stmtIns = $koneksi->prepare("
    INSERT INTO absensi (id_semester, id_siswa, sakit, izin, alpha)
    VALUES (?, ?, ?, ?, ?)
  ");

  // Loop mulai dari baris 2 (baris 1 = header)
  for ($row = 2; $row <= $highestRow; $row++) {
    // Template baru:
    // A No
    // B NIS
    // C Nama Siswa
    // D Kelas
    // E Absen
    // F Sakit
    // G Izin
    // H Alpha

    $noXls   = trim((string)$sheet->getCell('A' . $row)->getValue());
    $nisXls  = trim((string)$sheet->getCell('B' . $row)->getValue());
    $namaXls = trim((string)$sheet->getCell('C' . $row)->getValue());
    $kelasX  = trim((string)$sheet->getCell('D' . $row)->getValue());
    $absenX  = trim((string)$sheet->getCell('E' . $row)->getValue());

    $sakitX  = $sheet->getCell('F' . $row)->getValue();
    $izinX   = $sheet->getCell('G' . $row)->getValue();
    $alphaX  = $sheet->getCell('H' . $row)->getValue();

    // Cek benar-benar kosong
    if ($nisXls === '' && $namaXls === '' && $kelasX === '' && $absenX === '' && $sakitX === null && $izinX === null && $alphaX === null) {
      $emptyRows++;
      continue;
    }

    // NIS wajib
    if ($nisXls === '') {
      $skippedNoNIS++;
      $skipped++;
      continue;
    }

    // Normalisasi angka
    $sakit = is_numeric($sakitX) ? (int)$sakitX : 0;
    $izin  = is_numeric($izinX)  ? (int)$izinX  : 0;
    $alpha = is_numeric($alphaX) ? (int)$alphaX : 0;

    if ($sakit < 0 || $izin < 0 || $alpha < 0) {
      $skipped++;
      continue;
    }

    // Cari siswa by NIS
    $stmtFindSiswa->bind_param('s', $nisXls);
    $stmtFindSiswa->execute();
    $rowS = $stmtFindSiswa->get_result()->fetch_assoc();

    if (!$rowS) {
      $skippedNoSiswa++;
      $skipped++;
      continue;
    }

    $id_siswa = (int)$rowS['id_siswa'];

    // ==========================
    // CEK DUPLIKAT (semester + siswa/NIS)
    // ==========================
    $stmtCheckDup->bind_param('ii', $id_semester, $id_siswa);
    $stmtCheckDup->execute();
    $dupRow = $stmtCheckDup->get_result()->fetch_row();

    if ($dupRow) {
      $skippedDuplicate++;
      $skipped++;
      continue; // TOLAK, tidak insert
    }

    // Insert
    $stmtIns->bind_param('iiiii', $id_semester, $id_siswa, $sakit, $izin, $alpha);
    if ($stmtIns->execute()) {
      $success++;
    } else {
      $skipped++;
    }
  }

  $stmtFindSiswa->close();
  $stmtCheckDup->close();
  $stmtIns->close();

  // Pesan
  $msgParts = [];
  $msgParts[] = "Import selesai.";
  $msgParts[] = "Berhasil: {$success} baris.";

  if ($skippedDuplicate > 0) {
    $msgParts[] = "Duplikat ditolak: {$skippedDuplicate} baris.";
  }
  if ($skipped > 0) {
    $msgParts[] = "Dilewati (tidak valid/ tidak cocok): {$skipped} baris.";
  }
  if ($emptyRows > 0) {
    $msgParts[] = "Baris kosong: {$emptyRows} baris (diabaikan).";
  }
  if ($skippedNoNIS > 0) {
    $msgParts[] = "Tanpa NIS: {$skippedNoNIS} baris.";
  }
  if ($skippedNoSiswa > 0) {
    $msgParts[] = "NIS tidak ditemukan di data siswa: {$skippedNoSiswa} baris.";
  }

  header('Location: data_absensi.php?msg=' . urlencode(implode(' ', $msgParts)));
  exit;
} catch (Throwable $e) {
  header('Location: data_absensi.php?err=' . urlencode('Gagal memproses file Excel: ' . $e->getMessage()));
  exit;
}
