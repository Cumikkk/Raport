<?php
// pages/semester/proses_simpan_data_semester.php

require_once '../../koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

// ==========================
// VALIDASI METODE
// ==========================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: data_semester.php?msg=error');
  exit;
}

// ==========================
// AMBIL & VALIDASI INPUT
// ==========================
$tahun_ajaran   = trim($_POST['tahun_ajaran'] ?? '');
$semester_aktif = trim($_POST['semester_aktif'] ?? '');

if ($tahun_ajaran === '' || $semester_aktif === '') {
  header('Location: data_semester.php?msg=error');
  exit;
}

// validasi semester harus 1 atau 2
if ($semester_aktif !== '1' && $semester_aktif !== '2') {
  header('Location: data_semester.php?msg=error');
  exit;
}

try {
  // ==========================
  // CEK DATA SEMESTER TERAKHIR
  // ==========================
  $cekSql  = "SELECT id_semester FROM semester ORDER BY id_semester DESC LIMIT 1";
  $cekStmt = mysqli_prepare($koneksi, $cekSql);
  mysqli_stmt_execute($cekStmt);
  $res = mysqli_stmt_get_result($cekStmt);

  if ($row = mysqli_fetch_assoc($res)) {
    // ==========================
    // UPDATE JIKA SUDAH ADA
    // ==========================
    $id = (int)$row['id_semester'];

    $updateSql = "
      UPDATE semester
      SET nama_semester = ?, tahun_ajaran = ?
      WHERE id_semester = ?
    ";
    $stmt = mysqli_prepare($koneksi, $updateSql);
    mysqli_stmt_bind_param($stmt, 'ssi', $semester_aktif, $tahun_ajaran, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  } else {
    // ==========================
    // INSERT JIKA BELUM ADA
    // ==========================
    $insertSql = "
      INSERT INTO semester (nama_semester, tahun_ajaran)
      VALUES (?, ?)
    ";
    $stmt = mysqli_prepare($koneksi, $insertSql);
    mysqli_stmt_bind_param($stmt, 'ss', $semester_aktif, $tahun_ajaran);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }

  mysqli_stmt_close($cekStmt);

  // ==========================
  // REDIRECT SUKSES
  // ==========================
  header('Location: data_semester.php?msg=saved');
  exit;
} catch (Throwable $e) {
  // ==========================
  // REDIRECT ERROR
  // ==========================
  header('Location: data_semester.php?msg=error');
  exit;
}
