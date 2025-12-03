<?php
// pages/ekstra/proses_import_data_ekstra.php
require_once '../../koneksi.php';

// LOAD PhpSpreadsheet (sesuaikan path jika perlu)
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: data_ekstra.php?err=' . urlencode('Metode tidak diizinkan.'));
  exit;
}

if (!isset($_FILES['excel_file']) || !is_uploaded_file($_FILES['excel_file']['tmp_name'])) {
  header('Location: data_ekstra.php?err=' . urlencode('Silakan pilih file Excel terlebih dahulu.'));
  exit;
}

// Statistik import
$success      = 0; // jumlah baris yang berhasil di-insert
$skipped      = 0; // jumlah baris yang dilewati (tidak valid)
$emptyRows    = 0; // jumlah baris kosong (nomor & nama kosong)
$duplicateRows = 0; // jumlah baris yang dilewati karena nama sudah ada di database

try {
  // Baca file Excel yang diupload
  $spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
  $sheet       = $spreadsheet->getActiveSheet();
  $highestRow  = $sheet->getHighestRow();

  // Siapkan prepared statement untuk cek duplikat & insert
  // Cek apakah nama_ekstrakurikuler sudah ada
  $stmtCheck = $koneksi->prepare("
    SELECT id_ekstrakurikuler
    FROM ekstrakurikuler
    WHERE nama_ekstrakurikuler = ?
    LIMIT 1
  ");

  if (!$stmtCheck) {
    throw new Exception('Gagal menyiapkan statement cek duplikat.');
  }

  // Statement insert
  $stmtInsert = $koneksi->prepare("
    INSERT INTO ekstrakurikuler (nama_ekstrakurikuler)
    VALUES (?)
  ");

  if (!$stmtInsert) {
    $stmtCheck->close();
    throw new Exception('Gagal menyiapkan statement insert.');
  }

  // Loop mulai dari baris 2 (baris 1 adalah header: nomor, nama ekstrakurikuler)
  for ($row = 2; $row <= $highestRow; $row++) {

    // Kolom A = nomor (tidak dipakai untuk insert, hanya untuk referensi kalau dibutuhkan)
    $no = trim((string)$sheet->getCell('A' . $row)->getValue());
    // Kolom B = nama ekstrakurikuler
    $nama = trim((string)$sheet->getCell('B' . $row)->getValue());

    // Jika baris benar-benar kosong (nomor & nama kosong) → lewati tanpa dihitung error
    if ($no === '' && $nama === '') {
      $emptyRows++;
      continue;
    }

    // Validasi nama ekstrakurikuler tidak boleh kosong
    if ($nama === '') {
      $skipped++;
      continue;
    }

    // Cek apakah nama sudah ada di database (hindari duplikat)
    $stmtCheck->bind_param('s', $nama);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
      // Nama sudah ada → lewati sebagai duplikat
      $duplicateRows++;
      continue;
    }

    // Insert ke DB
    $stmtInsert->bind_param('s', $nama);

    if ($stmtInsert->execute()) {
      $success++;
    } else {
      // Jika gagal insert (selain duplikat), anggap baris ini dilewati
      $skipped++;
    }
  }

  // Tutup statement
  $stmtCheck->close();
  $stmtInsert->close();

  // Susun pesan akhir
  $msgParts = [];
  $msgParts[] = "Import selesai.";
  $msgParts[] = "Berhasil: $success baris.";
  if ($duplicateRows > 0) {
    $msgParts[] = "Dilewati (nama sudah ada): $duplicateRows baris.";
  }
  if ($skipped > 0) {
    $msgParts[] = "Dilewati (tidak valid): $skipped baris.";
  }
  if ($emptyRows > 0) {
    $msgParts[] = "Baris kosong: $emptyRows baris (diabaikan).";
  }

  $msg = implode(' ', $msgParts);

  header('Location: data_ekstra.php?msg=' . urlencode($msg));
  exit;
} catch (Throwable $e) {
  // Jika terjadi error saat baca/parse Excel atau proses DB
  header('Location: data_ekstra.php?err=' . urlencode('Gagal memproses file Excel: ' . $e->getMessage()));
  exit;
}
