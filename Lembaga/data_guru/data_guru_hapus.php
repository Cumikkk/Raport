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

$id   = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$csrf = $_POST['csrf'] ?? '';

if ($id <= 0) {
    back_with(['err' => 'ID tidak valid.']);
}
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
    back_with(['err' => 'Token tidak valid. Silakan coba lagi.']);
}

// Cek apakah guru ada
$stmt = $koneksi->prepare("SELECT id_guru, nama_guru FROM guru WHERE id_guru = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    back_with(['err' => 'Data guru tidak ditemukan.']);
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
    back_with(['err' => 'Tidak dapat menghapus karena ' . implode(' dan ', $reason) . '. Putuskan relasi terlebih dahulu.']);
}

// Lakukan delete
$stmtD = $koneksi->prepare("DELETE FROM guru WHERE id_guru = ? LIMIT 1");
$stmtD->bind_param('i', $id);
if ($stmtD->execute() && $stmtD->affected_rows > 0) {
    back_with(['msg' => 'Data guru "' . $guru['nama_guru'] . '" berhasil dihapus.']);
} else {
    back_with(['err' => 'Gagal menghapus data.']);
}
