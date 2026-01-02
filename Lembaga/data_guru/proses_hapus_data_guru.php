<?php
// pages/guru/hapus_data_guru.php
require_once '../../koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function back_with(string $successMsg = '', string $errorMsg = ''): void
{
    $_SESSION['dk_alert_guru'] = [
        'success' => $successMsg,
        'error'   => $errorMsg,
    ];
    header('Location: data_guru.php?alert=1');
    exit;
}

function safe_ident(string $s): bool
{
    return (bool)preg_match('/^[A-Za-z0-9_]+$/', $s);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    back_with('', 'Metode tidak diizinkan.');
}

$csrf = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string)$csrf)) {
    back_with('', 'Token keamanan tidak valid. Silakan refresh halaman dan coba lagi.');
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
    back_with('', 'Tidak ada data guru yang dipilih.');
}

// =======================
// Ambil nama guru
// =======================
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

$stmtNames = mysqli_prepare(
    $koneksi,
    "SELECT id_guru, nama_guru FROM guru WHERE id_guru IN ($placeholders)"
);
mysqli_stmt_bind_param($stmtNames, $types, ...$ids);
mysqli_stmt_execute($stmtNames);
$resNames = mysqli_stmt_get_result($stmtNames);

$nameMap = [];
while ($r = mysqli_fetch_assoc($resNames)) {
    $nameMap[(int)$r['id_guru']] = (string)$r['nama_guru'];
}
mysqli_stmt_close($stmtNames);

// ======================================================
// DETEKSI RELASI DINAMIS (INFORMATION_SCHEMA)
// ======================================================
$dbName = '';
$resDb = mysqli_query($koneksi, "SELECT DATABASE() AS db");
if ($resDb) {
    $rowDb = mysqli_fetch_assoc($resDb);
    $dbName = (string)($rowDb['db'] ?? '');
}

$refCols = [];

if ($dbName !== '') {
    $sqlFk = "
        SELECT TABLE_NAME, COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE CONSTRAINT_SCHEMA = ?
          AND REFERENCED_TABLE_NAME = 'guru'
          AND REFERENCED_COLUMN_NAME = 'id_guru'
    ";
    $stmtFk = mysqli_prepare($koneksi, $sqlFk);
    mysqli_stmt_bind_param($stmtFk, 's', $dbName);
    mysqli_stmt_execute($stmtFk);
    $resFk = mysqli_stmt_get_result($stmtFk);

    while ($fk = mysqli_fetch_assoc($resFk)) {
        $t = (string)$fk['TABLE_NAME'];
        $c = (string)$fk['COLUMN_NAME'];
        if ($t && $c && safe_ident($t) && safe_ident($c) && $t !== 'guru') {
            $refCols[] = ['table' => $t, 'col' => $c];
        }
    }
    mysqli_stmt_close($stmtFk);
}

// =======================
// PROSES DELETE
// =======================
$deletedNames     = [];
$blockedRelMap    = [];
$failedOtherNames = [];

$stmtDel = mysqli_prepare($koneksi, "DELETE FROM guru WHERE id_guru = ?");

// helper cek relasi
$getRelationTables = function (int $id) use ($koneksi, $refCols): array {
    $tables = [];
    foreach ($refCols as $rc) {
        $sql = "SELECT 1 FROM `{$rc['table']}` WHERE `{$rc['col']}` = ? LIMIT 1";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if (mysqli_fetch_row($res)) {
            $tables[] = $rc['table'];
        }
        mysqli_stmt_close($stmt);
    }
    $tables = array_values(array_unique($tables));
    sort($tables);
    return $tables;
};

foreach ($ids as $id) {
    $label = $nameMap[$id] ?? ('ID ' . $id);

    try {
        $relTables = $getRelationTables($id);
        if (!empty($relTables)) {
            $blockedRelMap[$label] = $relTables;
            continue;
        }
    } catch (Throwable $e) {
        $blockedRelMap[$label] = ['cek_relasi_gagal'];
        continue;
    }

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
mysqli_stmt_close($stmtDel);

// =======================
// BENTUK PESAN ALERT
// =======================
$successMsg = '';
$errorMsg   = '';

if ($deletedNames) {
    $successMsg = count($deletedNames) === 1
        ? 'Data guru "' . $deletedNames[0] . '" berhasil dihapus.'
        : 'Berhasil menghapus ' . count($deletedNames) . ' data guru: ' . implode(', ', $deletedNames) . '.';
}

if (!empty($blockedRelMap)) {
    if (count($blockedRelMap) === 1) {
        foreach ($blockedRelMap as $nama => $tables) {
            $tablesQuoted = array_map(fn($t) => '"' . $t . '"', $tables);
            $errorMsg =
                'Data guru "' . $nama . '" tidak bisa dihapus karena masih ada relasi di: ' .
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
            ' data guru tidak bisa dihapus karena masih ada relasi di: ' .
            implode(' | ', $parts) . '.';
    }
}

if ($errorMsg === '' && $failedOtherNames) {
    $errorMsg = count($failedOtherNames) === 1
        ? 'Gagal menghapus data guru "' . $failedOtherNames[0] . '".'
        : 'Gagal menghapus ' . count($failedOtherNames) . ' data guru: ' . implode(', ', $failedOtherNames) . '.';
}

back_with($successMsg, $errorMsg);
