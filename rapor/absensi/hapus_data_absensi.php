<?php
// pages/absensi/hapus_data_absensi.php
require_once '../../koneksi.php';

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

$csrf = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
    back_with(['err' => 'Token tidak valid. Silakan coba lagi.']);
}

// Kumpulkan ID dari single (id) & bulk (ids[])
$rawIds = [];

// bulk: ids[]
if (isset($_POST['ids']) && is_array($_POST['ids'])) {
    foreach ($_POST['ids'] as $rid) {
        $id = (int)$rid;
        if ($id > 0) {
            $rawIds[] = $id;
        }
    }
}

// single: id
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
    // cek ada datanya
    $stmt = $koneksi->prepare("SELECT id_absensi FROM absensi WHERE id_absensi = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $skipped[] = "Data absensi dengan ID {$id} tidak ditemukan.";
        $stmt->close();
        continue;
    }
    $stmt->close();

    // delete
    $stmtD = $koneksi->prepare("DELETE FROM absensi WHERE id_absensi = ? LIMIT 1");
    $stmtD->bind_param('i', $id);
    if ($stmtD->execute() && $stmtD->affected_rows > 0) {
        $deleted[] = $id;
    } else {
        $skipped[] = "Gagal menghapus data absensi dengan ID {$id}.";
    }
    $stmtD->close();
}

$params = [];

if (!empty($deleted)) {
    if (count($deleted) === 1) {
        $params['msg'] = 'Data absensi berhasil dihapus.';
    } else {
        $params['msg'] = count($deleted) . ' data absensi berhasil dihapus.';
    }
}
if (!empty($skipped)) {
    $params['err'] = implode(' | ', $skipped);
}
if (empty($params)) {
    $params['err'] = 'Tidak ada perubahan data.';
}

back_with($params);
