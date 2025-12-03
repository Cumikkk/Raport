<?php
// pages/guru/proses_import_data_guru.php
require_once '../../koneksi.php';

// LOAD PhpSpreadsheet (sesuaikan path jika perlu)
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: data_guru.php?err=' . urlencode('Metode tidak diizinkan.'));
  exit;
}

if (!isset($_FILES['excel_file']) || !is_uploaded_file($_FILES['excel_file']['tmp_name'])) {
  header('Location: data_guru.php?err=' . urlencode('Silakan pilih file Excel terlebih dahulu.'));
  exit;
}

// Hanya izinkan jabatan ini
$ALLOWED_JABATAN = ['Kepala Sekolah', 'Guru'];

$success   = 0; // jumlah baris yang berhasil di-insert
$skipped   = 0; // jumlah baris yang dilewati karena tidak valid
$emptyRows = 0; // jumlah baris kosong (nama & jabatan kosong)

try {
  // Baca file Excel yang diupload
  $spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
  $sheet       = $spreadsheet->getActiveSheet();
  $highestRow  = $sheet->getHighestRow();

  // Loop mulai dari baris 2 (baris 1 adalah header: nomer, nama guru, jabatan)
  for ($row = 2; $row <= $highestRow; $row++) {

    // Kolom A = nomer (tidak dipakai untuk insert, hanya untuk referensi kalau dibutuhkan)
    $no      = trim((string)$sheet->getCell('A' . $row)->getValue());
    // Kolom B = nama guru
    $nama    = trim((string)$sheet->getCell('B' . $row)->getValue());
    // Kolom C = jabatan
    $jabatan = trim((string)$sheet->getCell('C' . $row)->getValue());

    // Jika baris benar-benar kosong (nama & jabatan kosong) → lewati tanpa dihitung error
    if ($nama === '' && $jabatan === '') {
      $emptyRows++;
      continue;
    }

    // Validasi nama guru tidak boleh kosong
    if ($nama === '') {
      $skipped++;
      continue;
    }

    // Normalisasi jabatan (misal user isi "kepala sekolah" semua huruf kecil)
    $jabatanNormalized = ucwords(strtolower($jabatan));

    if (!in_array($jabatanNormalized, $ALLOWED_JABATAN, true)) {
      // Jika tidak valid, baris ini dilewati
      $skipped++;
      continue;
    }

    // Insert ke DB
    $stmt = $koneksi->prepare("
      INSERT INTO guru (nama_guru, jabatan_guru)
      VALUES (?, ?)
    ");
    if (!$stmt) {
      // Jika gagal prepare, anggap baris ini dilewati dan lanjut
      $skipped++;
      continue;
    }

    $stmt->bind_param('ss', $nama, $jabatanNormalized);

    if ($stmt->execute()) {
      $success++;
    } else {
      $skipped++;
    }

    $stmt->close();
  }

  // Susun pesan akhir
  $msgParts = [];
  $msgParts[] = "Import selesai.";
  $msgParts[] = "Berhasil: $success baris.";
  if ($skipped > 0) {
    $msgParts[] = "Dilewati (tidak valid): $skipped baris.";
  }
  if ($emptyRows > 0) {
    $msgParts[] = "Baris kosong: $emptyRows baris (diabaikan).";
  }

  $msg = implode(' ', $msgParts);

  header('Location: data_guru.php?msg=' . urlencode($msg));
  exit;
} catch (Throwable $e) {
  // Jika terjadi error saat baca/parse Excel
  header('Location: data_guru.php?err=' . urlencode('Gagal memproses file Excel: ' . $e->getMessage()));
  exit;
}
