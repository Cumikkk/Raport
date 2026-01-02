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
    header('Location: data_siswa.php?status=error&msg=' . urlencode('Metode tidak diizinkan.'));
    exit;
}

// CSRF
$csrf = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
    header('Location: data_siswa.php?status=error&msg=' . urlencode('Token tidak valid. Silakan refresh halaman.'));
    exit;
}

// helper bind_param dinamis (wajib pakai reference)
function bindParamsDynamic(mysqli_stmt $stmt, string $types, array $params): void
{
    if ($types === '' || empty($params)) return;

    $refs = [];
    $refs[] = &$types;
    foreach ($params as $k => $v) {
        $refs[] = &$params[$k];
    }
    call_user_func_array([$stmt, 'bind_param'], $refs);
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
    header('Location: data_siswa.php?status=error&msg=' . urlencode('Tidak ada data yang dipilih.'));
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    // =========================
    // Ambil nama siswa yang akan dihapus (untuk info alert)
    // =========================
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $sqlNames = "SELECT id_siswa, nama_siswa FROM siswa WHERE id_siswa IN ($placeholders) ORDER BY nama_siswa ASC";
    $stmtN = mysqli_prepare($koneksi, $sqlNames);
    bindParamsDynamic($stmtN, $types, $ids);
    mysqli_stmt_execute($stmtN);
    $resN = mysqli_stmt_get_result($stmtN);

    $names = [];
    while ($r = mysqli_fetch_assoc($resN)) {
        $nm = trim((string)($r['nama_siswa'] ?? ''));
        $id = (int)($r['id_siswa'] ?? 0);
        if ($nm !== '') $names[] = $nm;
        else if ($id > 0) $names[] = 'ID ' . $id;
    }
    mysqli_stmt_close($stmtN);

    // =========================
    // Hapus relasi: absensi
    // =========================
    $sqlAbsensi = "DELETE FROM absensi WHERE id_siswa IN ($placeholders)";
    $stmt1 = mysqli_prepare($koneksi, $sqlAbsensi);
    bindParamsDynamic($stmt1, $types, $ids);
    mysqli_stmt_execute($stmt1);
    mysqli_stmt_close($stmt1);

    // =========================
    // Hapus relasi: cetak_rapor
    // =========================
    $sqlCetak = "DELETE FROM cetak_rapor WHERE id_siswa IN ($placeholders)";
    $stmt2 = mysqli_prepare($koneksi, $sqlCetak);
    bindParamsDynamic($stmt2, $types, $ids);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);

    // =========================
    // Hapus master: siswa
    // =========================
    $sqlSiswa = "DELETE FROM siswa WHERE id_siswa IN ($placeholders)";
    $stmt3 = mysqli_prepare($koneksi, $sqlSiswa);
    bindParamsDynamic($stmt3, $types, $ids);
    mysqli_stmt_execute($stmt3);
    mysqli_stmt_close($stmt3);

    mysqli_commit($koneksi);

    // =========================
    // Build teks alert (sesuai format yang kamu mau)
    // - Single:
    //   Data Siswa "Nama" berhasil dihapus.
    // - Bulk:
    //   Jumlah {N} data siswa berhasil dihapus: nama1, nama2, nama3, ... (SEMUA NAMA)
    // =========================
    $total = count($ids);

    if ($total === 1) {
        $nm = $names[0] ?? ('ID ' . (int)$ids[0]);
        $msg = 'Data Siswa "' . $nm . '" berhasil dihapus.';
    } else {
        $list = $names;
        if (count($list) === 0) {
            $list = array_map(fn($x) => 'ID ' . (int)$x, $ids);
        }
        $msg = $total . ' data siswa berhasil dihapus: ' . implode(', ', $list) . '.';
    }

    // ✅ simpan ke session (biar pesan panjang tidak masuk URL)
    $_SESSION['flash_deleted_msg'] = $msg;

    header('Location: data_siswa.php?status=deleted');
    exit;
} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    header('Location: data_siswa.php?status=error&msg=' . urlencode('Gagal menghapus data.'));
    exit;
}
