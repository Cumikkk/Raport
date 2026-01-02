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

// Ambil id (single atau bulk)
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

// Ambil nama untuk label
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

$stmtNames = mysqli_prepare($koneksi, "SELECT id_guru, nama_guru FROM guru WHERE id_guru IN ($placeholders)");
mysqli_stmt_bind_param($stmtNames, $types, ...$ids);
mysqli_stmt_execute($stmtNames);
$resNames = mysqli_stmt_get_result($stmtNames);

$nameMap = [];
while ($r = mysqli_fetch_assoc($resNames)) {
    $nameMap[(int)$r['id_guru']] = (string)$r['nama_guru'];
}
mysqli_stmt_close($stmtNames);

// ======================================================
// DETEKSI RELASI SECARA DINAMIS (INFORMATION_SCHEMA)
// Ambil semua FK yang mengarah ke guru(id_guru)
// ======================================================
$dbName = '';
$resDb = mysqli_query($koneksi, "SELECT DATABASE() AS db");
if ($resDb) {
    $rowDb = mysqli_fetch_assoc($resDb);
    $dbName = (string)($rowDb['db'] ?? '');
}

$refCols = []; // [ ['table'=>'kelas','col'=>'id_guru'], ... ]

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
        $t = (string)($fk['TABLE_NAME'] ?? '');
        $c = (string)($fk['COLUMN_NAME'] ?? '');
        if ($t === '' || $c === '') continue;
        if (!safe_ident($t) || !safe_ident($c)) continue;
        if ($t === 'guru') continue;
        $refCols[] = ['table' => $t, 'col' => $c];
    }
    mysqli_stmt_close($stmtFk);
}

// ======================================================
// Proses:
// 1) Cek relasi dulu -> jika ada relasi, SKIP (tidak dihapus)
//    + catat berelasi di tabel apa saja
// 2) Jika aman, baru DELETE
// 3) Pesan dipisah: success & error(relasi/failed)
// ======================================================
$deletedNames        = [];
$blockedRelMap       = []; // [ 'Nama Guru' => ['kelas','user'] ]
$failedOtherNames    = [];

$stmtDel = mysqli_prepare($koneksi, "DELETE FROM guru WHERE id_guru = ?");

// helper: cek relasi & return list tabel yang masih memakai id_guru
$getRelationTables = function (int $id) use ($koneksi, $refCols): array {
    $tables = [];
    if (empty($refCols)) return $tables;

    foreach ($refCols as $rc) {
        $t = $rc['table'];
        $c = $rc['col'];

        // query aman karena identifier sudah divalidasi regex
        $sql = "SELECT 1 FROM `{$t}` WHERE `{$c}` = ? LIMIT 1";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $found = mysqli_fetch_row($res);
        mysqli_stmt_close($stmt);

        if (!empty($found)) {
            $tables[] = $t;
        }
    }

    $tables = array_values(array_unique($tables));
    sort($tables);
    return $tables;
};

foreach ($ids as $id) {
    $label = $nameMap[$id] ?? ('ID ' . $id);

    // ✅ Jangan hapus jika ada relasi
    try {
        $relTables = $getRelationTables($id);
        if (!empty($relTables)) {
            $blockedRelMap[$label] = $relTables;
            continue;
        }
    } catch (Throwable $e) {
        // jika cek relasi gagal, anggap tidak aman -> jangan hapus
        $blockedRelMap[$label] = ['(cek relasi gagal)'];
        continue;
    }

    // ✅ Aman -> baru delete
    try {
        mysqli_stmt_bind_param($stmtDel, 'i', $id);
        mysqli_stmt_execute($stmtDel);

        if (mysqli_stmt_affected_rows($stmtDel) > 0) {
            $deletedNames[] = $label;
        } else {
            $failedOtherNames[] = $label;
        }
    } catch (mysqli_sql_exception $e) {
        // fallback: kalau tetap ada FK error, masukkan relasi + tabel unknown
        $msg  = (string)$e->getMessage();
        $code = (int)$e->getCode();

        $isRel =
            ($code === 1451 || $code === 1452) ||
            (stripos($msg, 'foreign key') !== false) ||
            (stripos($msg, 'a foreign key constraint fails') !== false);

        if ($isRel) {
            $blockedRelMap[$label] = ['(foreign key)'];
        } else {
            $failedOtherNames[] = $label;
        }
    }
}

mysqli_stmt_close($stmtDel);

// Pesan success & error dipisah (beda alert)
$successMsg = '';
$errorMsg   = '';

if (count($deletedNames) > 0) {
    if (count($deletedNames) === 1) {
        $successMsg = 'Data guru "' . $deletedNames[0] . '" berhasil dihapus.';
    } else {
        $successMsg = 'Berhasil menghapus ' . count($deletedNames) . ' data guru: ' . implode(', ', $deletedNames) . '.';
    }
}

if (!empty($blockedRelMap)) {
    // Bentuk detail: Nama (tabel1, tabel2)
    $parts = [];
    foreach ($blockedRelMap as $nama => $tables) {
        $tblText = implode(', ', $tables);
        $parts[] = '"' . $nama . '" (relasi: ' . $tblText . ')';
    }

    if (count($parts) === 1) {
        $errorMsg = 'Data guru ' . $parts[0] . ' tidak bisa dihapus karena masih ada relasi.';
    } else {
        $errorMsg = 'Ada ' . count($parts) . ' data guru tidak bisa dihapus karena masih ada relasi: ' . implode(' | ', $parts) . '.';
    }
}

// kalau tidak ada relasi tapi ada gagal lain
if ($errorMsg === '' && count($failedOtherNames) > 0) {
    if (count($failedOtherNames) === 1) {
        $errorMsg = 'Gagal menghapus data guru "' . $failedOtherNames[0] . '".';
    } else {
        $errorMsg = 'Gagal menghapus ' . count($failedOtherNames) . ' data guru: ' . implode(', ', $failedOtherNames) . '.';
    }
}

back_with($successMsg, $errorMsg);
