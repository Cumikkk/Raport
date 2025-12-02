<?php
// pages/guru/data_guru_import.php
require_once '../../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: data_guru.php?err=' . urlencode('Metode tidak diizinkan.'));
  exit;
}

if (!isset($_FILES['excel_file']) || !is_uploaded_file($_FILES['excel_file']['tmp_name'])) {
  header('Location: data_guru.php?err=' . urlencode('Silakan pilih file Excel terlebih dahulu.'));
  exit;
}

// TODO: proses file Excel di sini (PhpSpreadsheet, dsb).
// Untuk sementara, hanya redirect sukses sebagai placeholder.

header('Location: data_guru.php?msg=' . urlencode('File Excel diterima. Silakan lengkapi logika import di data_guru_import.php.'));
exit;
