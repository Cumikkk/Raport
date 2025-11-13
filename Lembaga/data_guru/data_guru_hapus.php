<?php
require_once '../../koneksi.php';

// Start session untuk verifikasi CSRF
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function back_with($params)
{
    $qs = http_build_query($params);
    header('Location: data_guru.php?' . $qs);
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
    // Cek apakah guru ada
    $stmt = $koneksi->prepare("SELECT id_guru, nama_guru FROM guru WHERE id_guru = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $skipped[] = "Data guru dengan ID $id tidak ditemukan.";
        continue;
    }

    $guru = $res->fetch_assoc();

    // Cek dependensi di tabel user (fk_user_relations_guru)
    $stmtU = $koneksi->prepare("SELECT COUNT(*) AS cnt FROM user WHERE id_guru = ?");
    $stmtU->bind_param('i', $id);
    $stmtU->execute();
    $cntU = (int)$stmtU->get_result()->fetch_assoc()['cnt'];

    // Cek dependensi di tabel kelas (fk_kelas_relations_guru)
    $stmtK = $koneksi->prepare("SELECT COUNT(*) AS cnt FROM kelas WHERE id_guru = ?");
    $stmtK->bind_param('i', $id);
    $stmtK->execute();
    $cntK = (int)$stmtK->get_result()->fetch_assoc()['cnt'];

    if ($cntU > 0 || $cntK > 0) {
        $reason = [];
        if ($cntU > 0) $reason[] = "dipakai di tabel user ($cntU)";
        if ($cntK > 0) $reason[] = "dipakai di tabel kelas ($cntK)";
        $skipped[] = 'Guru "' . $guru['nama_guru'] . '" tidak dapat dihapus karena ' . implode(' dan ', $reason) . '. Putuskan relasi terlebih dahulu.';
        continue;
    }

    // Lakukan delete
    $stmtD = $koneksi->prepare("DELETE FROM guru WHERE id_guru = ? LIMIT 1");
    $stmtD->bind_param('i', $id);
    if ($stmtD->execute() && $stmtD->affected_rows > 0) {
        $deleted[] = $guru['nama_guru'];
    } else {
        $skipped[] = 'Gagal menghapus data guru "' . $guru['nama_guru'] . '".';
    }
}

// Susun pesan
$params = [];

if (!empty($deleted)) {
    if (count($deleted) === 1) {
        $params['msg'] = 'Data guru "' . $deleted[0] . '" berhasil dihapus.';
    } else {
        $params['msg'] = count($deleted) . ' data guru berhasil dihapus: ' . implode(', ', $deleted) . '.';
    }
}

if (!empty($skipped)) {
    $params['err'] = implode(' | ', $skipped);
}

if (empty($params)) {
    $params['err'] = 'Tidak ada perubahan data.';
}

back_with($params);
