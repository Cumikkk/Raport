<?php
// pages/nilai_ekstra/nilai_ekstra_tambah_proses.php
require_once '../../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: nilai_ekstra.php');
    exit;
}

// Ambil data dari form
$id_siswa    = isset($_POST['id_siswa']) ? (int)$_POST['id_siswa'] : 0;
$id_semester = isset($_POST['id_semester']) ? (int)$_POST['id_semester'] : 0;
$id_ekstra   = isset($_POST['id_ekstra']) ? (int)$_POST['id_ekstra'] : 0;
$nilai       = isset($_POST['nilai_ekstrakurikuler']) ? trim($_POST['nilai_ekstrakurikuler']) : '';

// Validasi sederhana
if ($id_siswa <= 0 || $id_semester <= 0 || $id_ekstra <= 0 || $nilai === '') {
    header('Location: nilai_ekstra.php?status=error&msg=' . urlencode('Data tidak lengkap.'));
    exit;
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $sql  = "INSERT INTO nilai_ekstrakurikuler 
             (id_siswa, id_semester, id_ekstrakurikuler, nilai_ekstrakurikuler)
             VALUES (?,?,?,?)";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('iiis', $id_siswa, $id_semester, $id_ekstra, $nilai);
    $stmt->execute();
    $stmt->close();

    // Redirect ke halaman utama + kirim status & pesan
    header('Location: nilai_ekstra.php?status=success&msg=' . urlencode('Data nilai ekstrakurikuler berhasil ditambahkan.'));
    exit;
} catch (Throwable $e) {
    // Bisa kamu log error-nya kalau mau
    header('Location: nilai_ekstra.php?status=error&msg=' . urlencode('Terjadi kesalahan saat menambahkan data.'));
    exit;
}
