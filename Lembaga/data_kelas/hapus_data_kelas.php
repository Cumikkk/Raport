<?php
// pages/kelas/hapus_data_kelas.php
require_once '../../koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function back_with(string $successMsg = '', string $errorMsg = ''): void
{
    $_SESSION['dk_alert_kelas'] = [
        'success' => $successMsg,
        'error'   => $errorMsg,
    ];
    header('Location: data_kelas.php?alert=1');
    exit;
}

function is_ajax(): bool
{
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}

function json_out(bool $ok, string $msg, string $type = 'success', int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => $ok, 'msg' => $msg, 'type' => $type], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (is_ajax()) json_out(false, 'Metode tidak diizinkan.', 'danger', 405);
    back_with('', 'Metode tidak diizinkan.');
}

$csrf = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string)$csrf)) {
    $m = 'Token keamanan tidak valid. Silakan refresh halaman dan coba lagi.';
    if (is_ajax()) json_out(false, $m, 'danger', 403);
    back_with('', $m);
}

// =======================
// Ambil ID (single / bulk)
// =======================
$ids = [];
if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    if ($id > 0) $ids[] = $id;
} elseif (!empty($_POST['ids']) && is_array($_POST['ids'])) {
    foreach ($_POST['ids'] as $v) {
        $id = (int)$v;
        if ($id > 0) $ids[] = $id;
    }
}

$ids = array_values(array_unique($ids));
if (count($ids) === 0) {
    $m = 'Tidak ada data kelas yang dipilih.';
    if (is_ajax()) json_out(false, $m, 'warning', 422);
    back_with('', $m);
}

// =======================
// Ambil nama kelas
// =======================
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

$stmtNames = mysqli_prepare(
    $koneksi,
    "SELECT id_kelas, nama_kelas FROM kelas WHERE id_kelas IN ($placeholders)"
);
mysqli_stmt_bind_param($stmtNames, $types, ...$ids);
mysqli_stmt_execute($stmtNames);
$resNames = mysqli_stmt_get_result($stmtNames);

$nameMap = [];
while ($r = mysqli_fetch_assoc($resNames)) {
    $nameMap[(int)$r['id_kelas']] = (string)$r['nama_kelas'];
}
mysqli_stmt_close($stmtNames);

// =======================
// PROSES DELETE (cek relasi siswa)
// =======================
$deletedNames     = [];
$blockedRelMap    = []; // [nama_kelas => ['siswa']]
$failedOtherNames = [];

$stmtRel = mysqli_prepare($koneksi, "SELECT 1 FROM siswa WHERE id_kelas = ? LIMIT 1");
$stmtDel = mysqli_prepare($koneksi, "DELETE FROM kelas WHERE id_kelas = ?");

foreach ($ids as $id) {
    $label = $nameMap[$id] ?? ('ID ' . $id);

    // cek relasi siswa
    try {
        mysqli_stmt_bind_param($stmtRel, 'i', $id);
        mysqli_stmt_execute($stmtRel);
        $resRel = mysqli_stmt_get_result($stmtRel);
        if (mysqli_fetch_row($resRel)) {
            $blockedRelMap[$label] = ['siswa'];
            continue;
        }
    } catch (Throwable $e) {
        $blockedRelMap[$label] = ['cek_relasi_gagal'];
        continue;
    }

    // delete
    try {
        mysqli_stmt_bind_param($stmtDel, 'i', $id);
        mysqli_stmt_execute($stmtDel);

        if (mysqli_stmt_affected_rows($stmtDel) > 0) {
            $deletedNames[] = $label;
        } else {
            $failedOtherNames[] = $label;
        }
    } catch (mysqli_sql_exception $e) {
        $blockedRelMap[$label] = ['foreign_key'];
    }
}

mysqli_stmt_close($stmtRel);
mysqli_stmt_close($stmtDel);

// =======================
// BENTUK PESAN ALERT
// =======================
$successMsg = '';
$errorMsg   = '';

if ($deletedNames) {
    $successMsg = count($deletedNames) === 1
        ? 'Data kelas "' . $deletedNames[0] . '" berhasil dihapus.'
        : 'Berhasil menghapus ' . count($deletedNames) . ' data kelas: ' . implode(', ', $deletedNames) . '.';
}

if (!empty($blockedRelMap)) {
    if (count($blockedRelMap) === 1) {
        foreach ($blockedRelMap as $nama => $tables) {
            $tablesQuoted = array_map(fn($t) => '"' . $t . '"', $tables);
            $errorMsg =
                'Data kelas "' . $nama . '" tidak bisa dihapus karena masih ada relasi di: ' .
                implode(', ', $tablesQuoted) . '.';
        }
    } else {
        $parts = [];
        foreach ($blockedRelMap as $nama => $tables) {
            $tablesQuoted = array_map(fn($t) => '"' . $t . '"', $tables);
            $parts[] = '"' . $nama . '" (' . implode(', ', $tablesQuoted) . ')';
        }
        $errorMsg =
            'Ada ' . count($parts) .
            ' data kelas tidak bisa dihapus karena masih ada relasi di: ' .
            implode(' | ', $parts) . '.';
    }
}

if ($errorMsg === '' && $failedOtherNames) {
    $errorMsg = count($failedOtherNames) === 1
        ? 'Gagal menghapus data kelas "' . $failedOtherNames[0] . '".'
        : 'Gagal menghapus ' . count($failedOtherNames) . ' data kelas: ' . implode(', ', $failedOtherNames) . '.';
}

if (is_ajax()) {
    if ($successMsg !== '' && $errorMsg !== '') {
        json_out(true, $successMsg . ' ' . $errorMsg, 'success', 200);
    }
    if ($successMsg !== '') json_out(true, $successMsg, 'success', 200);
    json_out(false, $errorMsg !== '' ? $errorMsg : 'Terjadi kesalahan.', 'danger', 409);
}

back_with($successMsg, $errorMsg);
