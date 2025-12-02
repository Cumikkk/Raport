<?php
// pages/guru/data_guru_edit.php
require_once '../../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: data_guru.php?err=' . urlencode('Metode tidak diizinkan.'));
  exit;
}

$ALLOWED_JABATAN = ['Kepala Sekolah', 'Guru'];
$errors = [];

$id = isset($_POST['id_guru']) ? (int)$_POST['id_guru'] : 0;
if ($id <= 0) {
  header('Location: data_guru.php?err=' . urlencode('ID tidak valid.'));
  exit;
}

// Pastikan data ada
$sqlFind = "SELECT id_guru FROM guru WHERE id_guru = ?";
$stmtFind = $koneksi->prepare($sqlFind);
$stmtFind->bind_param('i', $id);
$stmtFind->execute();
$res = $stmtFind->get_result();
if ($res->num_rows === 0) {
  header('Location: data_guru.php?err=' . urlencode('Data guru tidak ditemukan.'));
  exit;
}

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

$sqlUpd = "UPDATE guru SET nama_guru = ?, jabatan_guru = ? WHERE id_guru = ?";
$stmtUpd = $koneksi->prepare($sqlUpd);
$stmtUpd->bind_param('ssi', $nama_guru, $jabatan_guru, $id);

if ($stmtUpd->execute()) {
  header('Location: data_guru.php?msg=' . urlencode('Data guru berhasil diperbarui.'));
  exit;
}

header('Location: data_guru.php?err=' . urlencode('Gagal memperbarui data guru: ' . $koneksi->error));
exit;
