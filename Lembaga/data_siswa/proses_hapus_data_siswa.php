<?php
// pages/siswa/proses_hapus_data_siswa.php
require_once '../../koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// wajib POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: data_siswa.php?err=' . urlencode('Metode tidak diizinkan.'));
    exit;
}

// CSRF
$csrf = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
    header('Location: data_siswa.php?err=' . urlencode('Token tidak valid. Silakan refresh halaman.'));
    exit;
}

// ambil id single atau ids[] multiple
$ids = [];

if (isset($_POST['id']) && $_POST['id'] !== '') {
    $ids[] = (int)$_POST['id'];
} elseif (isset($_POST['ids']) && is_array($_POST['ids'])) {
    foreach ($_POST['ids'] as $v) {
        $x = (int)$v;
        if ($x > 0) $ids[] = $x;
    }
}

$ids = array_values(array_unique(array_filter($ids, fn($x) => $x > 0)));

if (count($ids) === 0) {
    header('Location: data_siswa.php?err=' . urlencode('Tidak ada data yang dipilih.'));
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    // Hapus relasi: absensi
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $sqlAbsensi = "DELETE FROM absensi WHERE id_siswa IN ($placeholders)";
    $stmt1 = mysqli_prepare($koneksi, $sqlAbsensi);
    mysqli_stmt_bind_param($stmt1, $types, ...$ids);
    mysqli_stmt_execute($stmt1);
    mysqli_stmt_close($stmt1);

    // Hapus relasi: cetak_rapor
    $sqlCetak = "DELETE FROM cetak_rapor WHERE id_siswa IN ($placeholders)";
    $stmt2 = mysqli_prepare($koneksi, $sqlCetak);
    mysqli_stmt_bind_param($stmt2, $types, ...$ids);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);

    // Hapus master: siswa
    $sqlSiswa = "DELETE FROM siswa WHERE id_siswa IN ($placeholders)";
    $stmt3 = mysqli_prepare($koneksi, $sqlSiswa);
    mysqli_stmt_bind_param($stmt3, $types, ...$ids);
    mysqli_stmt_execute($stmt3);
    $affected = mysqli_stmt_affected_rows($stmt3);
    mysqli_stmt_close($stmt3);

    mysqli_commit($koneksi);

    $msg = (count($ids) === 1)
        ? 'Data siswa berhasil dihapus.'
        : 'Berhasil menghapus ' . count($ids) . ' data siswa.';

    header('Location: data_siswa.php?msg=' . urlencode($msg));
    exit;
} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    header('Location: data_siswa.php?err=' . urlencode('Gagal menghapus data.'));
    exit;
}
