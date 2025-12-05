<?php
// pages/absensi/proses_bulk_edit_absensi.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
require_once __DIR__ . '/../../koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function back_with(array $params)
{
    $qs = http_build_query($params);
    header('Location: data_absensi.php?' . $qs);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    back_with(['err' => 'Metode tidak diizinkan.']);
}

// CSRF
$csrf = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
    back_with(['err' => 'Token tidak valid. Silakan coba lagi.']);
}

$idArr    = isset($_POST['id_absensi']) && is_array($_POST['id_absensi']) ? $_POST['id_absensi'] : [];
$sakitArr = isset($_POST['sakit'])      && is_array($_POST['sakit'])      ? $_POST['sakit']      : [];
$izinArr  = isset($_POST['izin'])       && is_array($_POST['izin'])       ? $_POST['izin']       : [];
$alphaArr = isset($_POST['alpha'])      && is_array($_POST['alpha'])      ? $_POST['alpha']      : [];

$countIds = count($idArr);
if ($countIds === 0) {
    back_with(['err' => 'Tidak ada data yang dikirim.']);
}

$updated = 0;
$skipped = 0;

for ($i = 0; $i < $countIds; $i++) {
    $id     = (int)($idArr[$i] ?? 0);
    $sakit  = isset($sakitArr[$i]) ? (int)$sakitArr[$i] : 0;
    $izin   = isset($izinArr[$i])  ? (int)$izinArr[$i]  : 0;
    $alpha  = isset($alphaArr[$i]) ? (int)$alphaArr[$i] : 0;

    if ($id <= 0) {
        $skipped++;
        continue;
    }

    if ($sakit < 0 || $izin < 0 || $alpha < 0) {
        $skipped++;
        continue;
    }

    $stmt = $koneksi->prepare("
      UPDATE absensi
      SET sakit = ?, izin = ?, alpha = ?
      WHERE id_absensi = ?
    ");
    $stmt->bind_param('iiii', $sakit, $izin, $alpha, $id);
    $stmt->execute();

    if ($stmt->affected_rows >= 0) {
        $updated++;
    } else {
        $skipped++;
    }

    $stmt->close();
}

if ($updated === 0 && $skipped === 0) {
    back_with(['err' => 'Tidak ada perubahan yang dilakukan.']);
}

$msgParts = [];
$msgParts[] = "Perubahan absensi berhasil disimpan.";
$msgParts[] = "Diperbarui: {$updated} baris.";
if ($skipped > 0) {
    $msgParts[] = "Dilewati: {$skipped} baris (data tidak valid).";
}

back_with(['msg' => implode(' ', $msgParts)]);
