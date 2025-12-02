<?php
// pages/guru/data_guru_tambah.php
require_once '../../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: data_guru.php?err=' . urlencode('Metode tidak diizinkan.'));
  exit;
}

$ALLOWED_JABATAN = ['Kepala Sekolah', 'Guru'];
$errors = [];

$nama_guru    = isset($_POST['nama_guru']) ? trim($_POST['nama_guru']) : '';
$jabatan_guru = isset($_POST['jabatan_guru']) ? trim($_POST['jabatan_guru']) : '';

if ($nama_guru === '') {
  $errors[] = 'Nama Guru wajib diisi.';
}
if (!in_array($jabatan_guru, $ALLOWED_JABATAN, true)) {
  $errors[] = 'Jabatan tidak valid. Pilih "Kepala Sekolah" atau "Guru".';
}

if (!empty($errors)) {
  header('Location: data_guru.php?err=' . urlencode(implode(' | ', $errors)));
  exit;
}

$sql = "INSERT INTO guru (nama_guru, jabatan_guru) VALUES (?, ?)";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param('ss', $nama_guru, $jabatan_guru);

if ($stmt->execute()) {
  header('Location: data_guru.php?msg=' . urlencode('Data guru berhasil ditambahkan.'));
  exit;
}

header('Location: data_guru.php?err=' . urlencode('Gagal menambahkan data guru: ' . $koneksi->error));
exit;
