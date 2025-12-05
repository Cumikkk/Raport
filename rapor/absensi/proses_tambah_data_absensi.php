<?php
// pages/absensi/proses_tambah_data_absensi.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
require_once __DIR__ . '/../../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: data_absensi.php?err=' . urlencode('Metode tidak diizinkan.'));
  exit;
}

// Ambil input & validasi
$id_siswa = isset($_POST['id_siswa']) ? (int)$_POST['id_siswa'] : 0;
$sakit    = isset($_POST['sakit']) ? (int)$_POST['sakit'] : 0;
$izin     = isset($_POST['izin']) ? (int)$_POST['izin'] : 0;
$alpha    = isset($_POST['alpha']) ? (int)$_POST['alpha'] : 0;

if ($id_siswa <= 0) {
  header('Location: data_absensi.php?err=' . urlencode('Silakan pilih Nama Siswa.'));
  exit;
}
if ($sakit < 0 || $izin < 0 || $alpha < 0) {
  header('Location: data_absensi.php?err=' . urlencode('Input Sakit/Izin/Alpha tidak boleh bernilai negatif.'));
  exit;
}

// Cek apakah siswa ada
$stmt = $koneksi->prepare("SELECT 1 FROM siswa WHERE id_siswa = ? LIMIT 1");
$stmt->bind_param('i', $id_siswa);
$stmt->execute();
$exists = $stmt->get_result()->fetch_row();
$stmt->close();

if (!$exists) {
  header('Location: data_absensi.php?err=' . urlencode('Data siswa tidak ditemukan.'));
  exit;
}

// Ambil id_semester (pakai yang pertama/aktif)
$id_semester = null;
$res = $koneksi->query("SELECT id_semester FROM semester ORDER BY id_semester ASC LIMIT 1");
if ($res && $res->num_rows) {
  $id_semester = (int)$res->fetch_assoc()['id_semester'];
}

// Insert ke absensi
if ($id_semester !== null) {
  $stmt = $koneksi->prepare("
    INSERT INTO absensi (id_semester, id_siswa, sakit, izin, alpha)
    VALUES (?, ?, ?, ?, ?)
  ");
  $stmt->bind_param('iiiii', $id_semester, $id_siswa, $sakit, $izin, $alpha);
  $stmt->execute();
  $stmt->close();
} else {
  // Tidak ada semester → isi 0 sementara (kalau tidak pakai FK ketat)
  $koneksi->query("SET FOREIGN_KEY_CHECKS=0");
  $dummy = 0;
  $stmt = $koneksi->prepare("
    INSERT INTO absensi (id_semester, id_siswa, sakit, izin, alpha)
    VALUES (?, ?, ?, ?, ?)
  ");
  $stmt->bind_param('iiiii', $dummy, $id_siswa, $sakit, $izin, $alpha);
  $stmt->execute();
  $stmt->close();
  $koneksi->query("SET FOREIGN_KEY_CHECKS=1");
}

header('Location: data_absensi.php?msg=add_success');
exit;
