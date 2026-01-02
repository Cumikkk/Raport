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

function redirect_to_modal(string $modal, string $message): void
{
    $params = [
        'modal' => $modal,
        'msg'   => $message,
    ];
    $location = 'data_user.php?' . http_build_query($params);
    header("Location: {$location}");
    exit;
}

$role        = isset($_POST['role']) ? trim($_POST['role']) : '';
$username    = isset($_POST['username']) ? trim($_POST['username']) : '';
$passwordRaw = isset($_POST['password_user']) ? $_POST['password_user'] : '';
$id_guru     = isset($_POST['id_guru']) && $_POST['id_guru'] !== '' ? (int)$_POST['id_guru'] : null;

// Validasi dasar
$allowedRoles = ['Admin', 'Guru'];
if (!in_array($role, $allowedRoles, true)) {
    $msg = 'Role tidak valid.';
    if ($isAjax) json_response(false, $msg);
    redirect_to_modal('add', $msg);
}

if ($username === '' || strlen($username) > 50) {
    $msg = 'Username wajib diisi dan maksimal 50 karakter.';
    if ($isAjax) json_response(false, $msg);
    redirect_to_modal('add', $msg);
}

if ($passwordRaw === '') {
    $msg = 'Password wajib diisi.';
    if ($isAjax) json_response(false, $msg);
    redirect_to_modal('add', $msg);
}

// Cek username unik
$sqlCheck = "SELECT 1 FROM user WHERE username = ? LIMIT 1";
$stmt = mysqli_prepare($koneksi, $sqlCheck);
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($res) > 0) {
    $msg = 'Username sudah dipakai.';
    if ($isAjax) json_response(false, $msg);
    redirect_to_modal('add', $msg);
}

// Insert (tanpa hash password, sesuai permintaan)
if ($id_guru === null) {
    $sql = "INSERT INTO user (id_guru, role_user, username, password_user) VALUES (NULL, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'sss', $role, $username, $passwordRaw);
} else {
    $sql = "INSERT INTO user (id_guru, role_user, username, password_user) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'isss', $id_guru, $role, $username, $passwordRaw);
}

$ok = mysqli_stmt_execute($stmt);

if ($ok) {
    $msg = 'Data user berhasil ditambahkan.';
    if ($isAjax) {
        json_response(true, $msg);
    }
    header('Location: data_user.php?status=success&msg=' . urlencode($msg));
    exit;
} else {
    $msg = 'Gagal menambahkan data user: ' . mysqli_error($koneksi);
    if ($isAjax) {
        json_response(false, $msg);
    }
    header('Location: data_user.php?status=error&msg=' . urlencode($msg));
    exit;
}
