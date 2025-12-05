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

// Ambil id_semester (pakai yang pertama/aktif)
$id_semester = null;
$res = $koneksi->query("SELECT id_semester FROM semester ORDER BY id_semester ASC LIMIT 1");
if ($res && $res->num_rows) {
  $id_semester = (int)$res->fetch_assoc()['id_semester'];
} else {
  $id_semester = 0; // fallback (kalau ada FK, pastikan ada row id_semester=0 atau ubah logika ini)
}

$success        = 0;
$skipped        = 0;
$emptyRows      = 0;
$skippedNoNIS   = 0;
$skippedNoSiswa = 0;

try {
  $spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
  $sheet       = $spreadsheet->getActiveSheet();
  $highestRow  = $sheet->getHighestRow();

  // Loop mulai dari baris 2 (baris 1 = header)
  for ($row = 2; $row <= $highestRow; $row++) {
    $no      = trim((string)$sheet->getCell('A' . $row)->getValue());
    $namaXls = trim((string)$sheet->getCell('B' . $row)->getValue());
    $nisXls  = trim((string)$sheet->getCell('C' . $row)->getValue());
    $waliXls = trim((string)$sheet->getCell('D' . $row)->getValue());
    $sakitX  = $sheet->getCell('E' . $row)->getValue();
    $izinX   = $sheet->getCell('F' . $row)->getValue();
    $alphaX  = $sheet->getCell('G' . $row)->getValue();

    // Cek benar-benar kosong
    if ($namaXls === '' && $nisXls === '' && $waliXls === '' && $sakitX === null && $izinX === null && $alphaX === null) {
      $emptyRows++;
      continue;
    }

    // NIS wajib diisi sebagai kunci mapping ke siswa
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

    // Cari siswa berdasar NIS
    $stmtFind = $koneksi->prepare("
      SELECT s.id_siswa
      FROM siswa s
      WHERE s.no_induk_siswa = ?
      LIMIT 1
    ");
    $stmtFind->bind_param('s', $nisXls);
    $stmtFind->execute();
    $rowS = $stmtFind->get_result()->fetch_assoc();
    $stmtFind->close();

    if (!$rowS) {
      // siswa tidak ditemukan berdasarkan NIS
      $skippedNoSiswa++;
      $skipped++;
      continue;
    }

    $id_siswa = (int)$rowS['id_siswa'];

    // Insert ke absensi (pakai INNER JOIN saat read)
    $stmtIns = $koneksi->prepare("
      INSERT INTO absensi (id_semester, id_siswa, sakit, izin, alpha)
      VALUES (?, ?, ?, ?, ?)
    ");
    $stmtIns->bind_param('iiiii', $id_semester, $id_siswa, $sakit, $izin, $alpha);

    if ($stmtIns->execute()) {
      $success++;
    } else {
      $skipped++;
    }
    $stmtIns->close();
  }

  // Susun pesan
  $msgParts = [];
  $msgParts[] = "Import selesai.";
  $msgParts[] = "Berhasil: {$success} baris.";
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

  $msg = implode(' ', $msgParts);
  header('Location: data_absensi.php?msg=' . urlencode($msg));
  exit;
} catch (Throwable $e) {
  header('Location: data_absensi.php?err=' . urlencode('Gagal memproses file Excel: ' . $e->getMessage()));
  exit;
}
