<?php
// pages/ekstra/proses_edit_data_ekstra.php
require_once '../../koneksi.php';

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function respond_json(array $data)
{
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  if ($isAjax) {
    respond_json([
      'success' => false,
      'message' => 'Metode tidak diizinkan.'
    ]);
  } else {
    header('Location: data_ekstra.php?err=' . urlencode('Metode tidak diizinkan.'));
    exit;
  }
}

$id = isset($_POST['id_ekstra']) ? (int)$_POST['id_ekstra'] : 0;
$nama_ekstra = isset($_POST['nama_ekstra']) ? trim($_POST['nama_ekstra']) : '';

$errors = [];

if ($id <= 0) {
  $errors[] = 'ID tidak valid.';
}

if ($nama_ekstra === '') {
  $errors[] = 'Nama ekstrakurikuler wajib diisi.';
}

// Pastikan data ada
if ($id > 0) {
  $sqlFind = "SELECT id_ekstrakurikuler FROM ekstrakurikuler WHERE id_ekstrakurikuler = ?";
  $stmtFind = $koneksi->prepare($sqlFind);
  $stmtFind->bind_param('i', $id);
  $stmtFind->execute();
  $resFind = $stmtFind->get_result();
  if ($resFind->num_rows === 0) {
    $errors[] = 'Data ekstrakurikuler tidak ditemukan.';
  }
}

// Cek duplikat (kecuali dirinya sendiri)
if ($nama_ekstra !== '' && $id > 0) {
  $sqlCheck = "
      SELECT COUNT(*) AS cnt
      FROM ekstrakurikuler
      WHERE nama_ekstrakurikuler = ?
        AND id_ekstrakurikuler <> ?
    ";
  $stmtCheck = $koneksi->prepare($sqlCheck);
  $stmtCheck->bind_param('si', $nama_ekstra, $id);
  $stmtCheck->execute();
  $rowCheck = $stmtCheck->get_result()->fetch_assoc();
  $cnt = (int)($rowCheck['cnt'] ?? 0);

  if ($cnt > 0) {
    $errors[] = 'Nama ekstrakurikuler sudah ada.';
  }
}

if (!empty($errors)) {
  if ($isAjax) {
    respond_json([
      'success' => false,
      'errors'  => $errors
    ]);
  } else {
    header('Location: data_ekstra.php?edit_err=' . urlencode(implode(' ', $errors)) . '&edit_id=' . $id);
    exit;
  }
}

// UPDATE
$sqlUpd = "UPDATE ekstrakurikuler SET nama_ekstrakurikuler = ? WHERE id_ekstrakurikuler = ?";
$stmtUpd = $koneksi->prepare($sqlUpd);
$stmtUpd->bind_param('si', $nama_ekstra, $id);

if ($stmtUpd->execute()) {
  if ($isAjax) {
    respond_json([
      'success' => true,
      'message' => 'Data ekstrakurikuler berhasil diperbarui.'
    ]);
  } else {
    header('Location: data_ekstra.php?msg=' . urlencode('Data ekstrakurikuler berhasil diperbarui.'));
    exit;
  }
}

// gagal eksekusi
if ($isAjax) {
  respond_json([
    'success' => false,
    'message' => 'Gagal memperbarui data.'
  ]);
} else {
  header('Location: data_ekstra.php?edit_err=' . urlencode('Gagal memperbarui data.') . '&edit_id=' . $id);
  exit;
}
