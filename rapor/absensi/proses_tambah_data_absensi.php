<?php
// pages/absensi/proses_tambah_data_absensi.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
require_once __DIR__ . '/../../koneksi.php';

// Self-healing kolom snapshot
$koneksi->query("
  ALTER TABLE absensi
    ADD COLUMN IF NOT EXISTS nama_siswa_text VARCHAR(100) NOT NULL DEFAULT '-' AFTER id_absensi,
    ADD COLUMN IF NOT EXISTS nis_text        VARCHAR(50)  NOT NULL DEFAULT '-' AFTER nama_siswa_text,
    ADD COLUMN IF NOT EXISTS wali_kelas_text VARCHAR(100) NOT NULL DEFAULT '-' AFTER nis_text
");

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

// Ambil snapshot dari relasi siswa → kelas → guru
$stmt = $koneksi->prepare("
  SELECT s.nama_siswa, s.no_induk_siswa, g.nama_guru
  FROM siswa s
  LEFT JOIN kelas k ON k.id_kelas = s.id_kelas
  LEFT JOIN guru  g ON g.id_guru  = k.id_guru
  WHERE s.id_siswa = ?
  LIMIT 1
");
$stmt->bind_param('i', $id_siswa);
$stmt->execute();
$snap = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$snap) {
  header('Location: data_absensi.php?err=' . urlencode('Data siswa tidak ditemukan.'));
  exit;
}

$nama_snap = $snap['nama_siswa']     !== null && $snap['nama_siswa']     !== '' ? $snap['nama_siswa']     : '-';
$nis_snap  = $snap['no_induk_siswa'] !== null && $snap['no_induk_siswa'] !== '' ? $snap['no_induk_siswa'] : '-';
$wali_snap = $snap['nama_guru']      !== null && $snap['nama_guru']      !== '' ? $snap['nama_guru']      : '-';

// Ambil id_semester (pakai yang pertama/aktif)
$id_semester = null;
$res = $koneksi->query("SELECT id_semester FROM semester ORDER BY id_semester ASC LIMIT 1");
if ($res && $res->num_rows) {
  $id_semester = (int)$res->fetch_assoc()['id_semester'];
}

// Insert ke absensi
if ($id_semester !== null) {
  $stmt = $koneksi->prepare("
    INSERT INTO absensi (id_semester, id_siswa, nama_siswa_text, nis_text, wali_kelas_text, sakit, izin, alpha)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
  ");
  $stmt->bind_param('iisssiii', $id_semester, $id_siswa, $nama_snap, $nis_snap, $wali_snap, $sakit, $izin, $alpha);
  $stmt->execute();
  $stmt->close();
} else {
  // Tidak ada semester → isi 0 sementara
  $koneksi->query("SET FOREIGN_KEY_CHECKS=0");
  $dummy = 0;
  $stmt = $koneksi->prepare("
    INSERT INTO absensi (id_semester, id_siswa, nama_siswa_text, nis_text, wali_kelas_text, sakit, izin, alpha)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
  ");
  $stmt->bind_param('iisssiii', $dummy, $id_siswa, $nama_snap, $nis_snap, $wali_snap, $sakit, $izin, $alpha);
  $stmt->execute();
  $stmt->close();
  $koneksi->query("SET FOREIGN_KEY_CHECKS=1");
}

header('Location: data_absensi.php?msg=add_success');
exit;
