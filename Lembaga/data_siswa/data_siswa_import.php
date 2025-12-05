<?php
// import_siswa.php
include '../../koneksi.php';

// Hanya boleh diakses via POST dari modal
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: data_siswa.php');
    exit;
}

// =======================
// VALIDASI FILE EXCEL
// =======================

// Pastikan ada file yang di-upload
if (!isset($_FILES['excelFile']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
    echo "<script>
            alert('File Excel belum dipilih atau terjadi kesalahan upload.');
            window.location.href = 'data_siswa.php';
          </script>";
    exit;
}

// (Opsional) Cek ekstensi file, hanya izinkan .xls / .xlsx
$allowedExt = ['xls', 'xlsx'];
$filename   = $_FILES['excelFile']['name'] ?? '';
$ext        = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt, true)) {
    echo "<script>
            alert('Format file tidak didukung. Silakan upload file Excel (.xls / .xlsx).');
            window.location.href = 'data_siswa.php';
          </script>";
    exit;
}

// =======================
// DI SINI NANTI LOGIKA IMPORT ASLI
// =======================
// Contoh alur nantinya:
// 1. Pindahkan file ke folder tmp
// 2. Buka dengan PhpSpreadsheet
// 3. Loop tiap baris → insert ke tabel siswa
// 4. Hapus file tmp

// Sementara ini hanya dummy sukses supaya alur modal → fungsi → redirect sudah jalan.
echo "<script>
        alert('Import data siswa berhasil diproses (contoh dummy).\\nSilakan lengkapi logika import di import_siswa.php.');
        window.location.href = 'data_siswa.php';
      </script>";
exit;
