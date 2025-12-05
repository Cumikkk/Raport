<?php
// import_siswa.php
include '../../koneksi.php';

// Hanya boleh diakses via POST dari modal
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: data_siswa.php');
    exit;
}

// Ambil data dari form
$tingkat = $_POST['tingkat'] ?? '';
$kelas   = $_POST['kelas'] ?? '';

// Validasi dasar
if ($tingkat === '' || $tingkat === '--Pilih--' || $kelas === '' || $kelas === '--Pilih--') {
    echo "<script>
            alert('Silakan pilih tingkat dan kelas terlebih dahulu.');
            window.location.href = 'data_siswa.php';
          </script>";
    exit;
}

if (!isset($_FILES['excelFile']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
    echo "<script>
            alert('File Excel belum dipilih atau terjadi kesalahan upload.');
            window.location.href = 'data_siswa.php';
          </script>";
    exit;
}

// ====== DI SINI NANTI LOGIKA IMPORT ASLI ======
// Kamu bisa ganti bagian ini dengan proses PhpSpreadsheet, dsb.
// Sekarang hanya dummy sukses supaya alur modal → fungsi → redirect sudah jalan.

echo "<script>
        alert('Import data siswa berhasil diproses (contoh dummy).\\nSilakan lengkapi logika import di import_siswa.php.');
        window.location.href = 'data_siswa.php';
      </script>";
exit;
