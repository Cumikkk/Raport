<?php
require_once '../koneksi.php';

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function json_response(bool $success, string $message): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
    ]);
    exit;
}

function redirect_to_modal(string $modal, string $message, ?int $idUser = null): void
{
    $params = [
        'modal' => $modal,
        'msg'   => $message,
    ];
    if ($idUser !== null && $idUser > 0) {
        $params['id'] = $idUser;
    }
    $location = 'data_user.php?' . http_build_query($params);
    header("Location: {$location}");
    exit;
}

$id_user     = isset($_POST['id_user']) ? (int)$_POST['id_user'] : 0;
$role        = isset($_POST['role']) ? trim($_POST['role']) : '';
$username    = isset($_POST['username']) ? trim($_POST['username']) : '';
$passwordRaw = isset($_POST['password_user']) ? $_POST['password_user'] : '';
$id_guru     = isset($_POST['id_guru']) && $_POST['id_guru'] !== '' ? (int)$_POST['id_guru'] : null;

if ($id_user <= 0) {
    $msg = 'ID tidak valid.';
    if ($isAjax) json_response(false, $msg);
    redirect_to_modal('edit', $msg, $id_user);
}

$allowedRoles = ['Admin', 'Guru'];
if (!in_array($role, $allowedRoles, true)) {
    $msg = 'Role tidak valid.';
    if ($isAjax) json_response(false, $msg);
    redirect_to_modal('edit', $msg, $id_user);
}

if ($username === '' || strlen($username) > 50) {
    $msg = 'Username wajib diisi dan maksimal 50 karakter.';
    if ($isAjax) json_response(false, $msg);
    redirect_to_modal('edit', $msg, $id_user);
}

// Cek username unik (kecuali diri sendiri)
$sqlCheck = "SELECT 1 FROM user WHERE username = ? AND id_user <> ? LIMIT 1";
$stmt = mysqli_prepare($koneksi, $sqlCheck);
mysqli_stmt_bind_param($stmt, 'si', $username, $id_user);
mysqli_stmt_execute($stmt);
if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
    $msg = 'Username sudah dipakai.';
    if ($isAjax) json_response(false, $msg);
    redirect_to_modal('edit', $msg, $id_user);
}

// Build update (tanpa hash)
if ($passwordRaw !== '') {
    if ($id_guru === null) {
        $sql  = "UPDATE user SET id_guru = NULL, role_user = ?, username = ?, password_user = ? WHERE id_user = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, 'sssi', $role, $username, $passwordRaw, $id_user);
    } else {
        $sql  = "UPDATE user SET id_guru = ?, role_user = ?, username = ?, password_user = ? WHERE id_user = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, 'isssi', $id_guru, $role, $username, $passwordRaw, $id_user);
    }
} else {
    if ($id_guru === null) {
        $sql  = "UPDATE user SET id_guru = NULL, role_user = ?, username = ? WHERE id_user = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, 'ssi', $role, $username, $id_user);
    } else {
        $sql  = "UPDATE user SET id_guru = ?, role_user = ?, username = ? WHERE id_user = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, 'issi', $id_guru, $role, $username, $id_user);
    }
}

$ok = mysqli_stmt_execute($stmt);

if ($ok) {
    $msg = 'Data user berhasil diperbarui.';
    if ($isAjax) {
        json_response(true, $msg);
    }
    header('Location: data_user.php?status=success&msg=' . urlencode($msg));
    exit;
} else {
    $msg = 'Gagal memperbarui data user: ' . mysqli_error($koneksi);
    if ($isAjax) {
        json_response(false, $msg);
    }
    header('Location: data_user.php?status=error&msg=' . urlencode($msg));
    exit;
}
