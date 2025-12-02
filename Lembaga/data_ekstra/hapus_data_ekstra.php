<?php
// pages/ekstra/hapus_data_ekstra.php
require_once '../../koneksi.php';

// Start session untuk verifikasi CSRF
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function back_with($params)
{
  $qs = http_build_query($params);
  header('Location: data_ekstra.php?' . $qs);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  back_with(['err' => 'Metode tidak diizinkan.']);
}

$csrf = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
  back_with(['err' => 'Token tidak valid. Silakan coba lagi.']);
}

// Kumpulkan ID dari single delete (id) maupun bulk delete (ids[])
$rawIds = [];

// dari bulk: ids[]
if (isset($_POST['ids']) && is_array($_POST['ids'])) {
  foreach ($_POST['ids'] as $rid) {
    $id = (int)$rid;
    if ($id > 0) {
      $rawIds[] = $id;
    }
  }
}

// dari single: id
if (isset($_POST['id'])) {
  $singleId = (int)$_POST['id'];
  if ($singleId > 0) {
    $rawIds[] = $singleId;
  }
}

$ids = array_values(array_unique($rawIds));

if (empty($ids)) {
  back_with(['err' => 'ID tidak valid.']);
}

$deleted = [];
$skipped = [];

foreach ($ids as $id) {
  // Cek apakah data ada
  $stmt = $koneksi->prepare("SELECT id_ekstrakurikuler, nama_ekstrakurikuler FROM ekstrakurikuler WHERE id_ekstrakurikuler = ?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res->num_rows === 0) {
    $skipped[] = "Data ekstrakurikuler dengan ID $id tidak ditemukan.";
    continue;
  }

  $ekstra = $res->fetch_assoc();

  // TODO: jika ada tabel relasi (misal nilai_ekstrakurikuler), kamu bisa cek di sini
  // Contoh (sesuaikan nama tabel & kolom kalau ada):
  // $stmtDep = $koneksi->prepare("SELECT COUNT(*) AS cnt FROM nilai_ekstrakurikuler WHERE id_ekstrakurikuler = ?");
  // $stmtDep->bind_param('i', $id);
  // $stmtDep->execute();
  // $cntDep = (int)$stmtDep->get_result()->fetch_assoc()['cnt'];
  // if ($cntDep > 0) { ... $skipped[] = ...; continue; }

  // Lakukan delete
  $stmtD = $koneksi->prepare("DELETE FROM ekstrakurikuler WHERE id_ekstrakurikuler = ? LIMIT 1");
  $stmtD->bind_param('i', $id);
  if ($stmtD->execute() && $stmtD->affected_rows > 0) {
    $deleted[] = $ekstra['nama_ekstrakurikuler'];
  } else {
    $skipped[] = 'Gagal menghapus data ekstrakurikuler "' . $ekstra['nama_ekstrakurikuler'] . '".';
  }
}

// Susun pesan
$params = [];

if (!empty($deleted)) {
  if (count($deleted) === 1) {
    $params['msg'] = 'Data ekstrakurikuler "' . $deleted[0] . '" berhasil dihapus.';
  } else {
    $params['msg'] = count($deleted) . ' Data ekstrakurikuler berhasil dihapus: ' . implode(', ', $deleted) . '.';
  }
}

if (!empty($skipped)) {
  $params['err'] = implode(' | ', $skipped);
}

if (empty($params)) {
  $params['err'] = 'Tidak ada perubahan data.';
}

back_with($params);
