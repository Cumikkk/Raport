<?php
require_once '../koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirect_with_status(string $status, string $message = '')
{
    $location = 'data_user.php?status=' . urlencode($status);
    if ($message !== '') {
        $location .= '&msg=' . urlencode($message);
    }
    header("Location: {$location}");
    exit;
}

// === MODE BULK DELETE (POST ids[]) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ids']) && is_array($_POST['ids'])) {
    $csrf = $_POST['csrf'] ?? '';
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
        redirect_with_status('error', 'Token tidak valid. Silakan coba lagi.');
    }

    $rawIds = [];
    foreach ($_POST['ids'] as $rid) {
        $id = (int)$rid;
        if ($id > 0) {
            $rawIds[] = $id;
        }
    }
    $ids = array_values(array_unique($rawIds));

    if (empty($ids)) {
        redirect_with_status('error', 'ID tidak valid.');
    }

    $deleted = [];
    $failed  = [];

    foreach ($ids as $id) {
        $stmt = mysqli_prepare($koneksi, "SELECT username FROM user WHERE id_user=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($res) === 0) {
            $failed[] = "User dengan ID $id tidak ditemukan.";
            continue;
        }
        $row = mysqli_fetch_assoc($res);
        $username = $row['username'];

        $stmtDel = mysqli_prepare($koneksi, "DELETE FROM user WHERE id_user=?");
        mysqli_stmt_bind_param($stmtDel, 'i', $id);
        $ok = mysqli_stmt_execute($stmtDel);

        if ($ok && mysqli_stmt_affected_rows($stmtDel) > 0) {
            $deleted[] = $username;
        } else {
            $failed[] = "Gagal menghapus user \"{$username}\".";
        }
    }

    $msgParts = [];
    if (!empty($deleted)) {
        if (count($deleted) === 1) {
            $msgParts[] = 'User "' . $deleted[0] . '" terhapus.';
        } else {
            $msgParts[] = count($deleted) . ' user terhapus: ' . implode(', ', $deleted) . '.';
        }
    }
    if (!empty($failed)) {
        $msgParts[] = implode(' | ', $failed);
    }
    if (empty($msgParts)) {
        $msgParts[] = 'Tidak ada perubahan data.';
    }

    redirect_with_status('success', implode(' ', $msgParts));
}

// === MODE SINGLE DELETE (GET id) ===
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    redirect_with_status('error', 'ID tidak valid.');
}

$stmt = mysqli_prepare($koneksi, "DELETE FROM user WHERE id_user=?");
mysqli_stmt_bind_param($stmt, 'i', $id);
$ok = mysqli_stmt_execute($stmt);

if ($ok && mysqli_stmt_affected_rows($stmt) > 0) {
    redirect_with_status('success', 'User terhapus.');
} else {
    redirect_with_status('error', 'Gagal menghapus: ' . mysqli_error($koneksi));
}
