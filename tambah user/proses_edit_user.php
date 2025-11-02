<?php
require_once '../koneksi.php';

$id_user     = isset($_POST['id_user']) ? (int)$_POST['id_user'] : 0;
$role        = isset($_POST['role']) ? trim($_POST['role']) : '';
$username    = isset($_POST['username']) ? trim($_POST['username']) : '';
$passwordRaw = isset($_POST['password_user']) ? $_POST['password_user'] : '';
$id_guru     = isset($_POST['id_guru']) && $_POST['id_guru'] !== '' ? (int)$_POST['id_guru'] : null;

if ($id_user <= 0) {
    exit("<script>alert('ID tidak valid'); history.back();</script>");
}

$allowedRoles = ['Admin', 'Guru'];
if (!in_array($role, $allowedRoles, true)) {
    exit("<script>alert('Role tidak valid'); history.back();</script>");
}
if ($username === '' || strlen($username) > 50) {
    exit("<script>alert('Username wajib dan maks 50 karakter.'); history.back();</script>");
}

// Cek username unik (kecuali diri sendiri)
$sqlCheck = "SELECT 1 FROM user WHERE username=? AND id_user<>? LIMIT 1";
$stmt = mysqli_prepare($koneksi, $sqlCheck);
mysqli_stmt_bind_param($stmt, 'si', $username, $id_user);
mysqli_stmt_execute($stmt);
if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
    exit("<script>alert('Username sudah dipakai'); history.back();</script>");
}

// Build update (tanpa hash)
if ($passwordRaw !== '') {
    if ($id_guru === null) {
        $sql  = "UPDATE user SET id_guru=NULL, role_user=?, username=?, password_user=? WHERE id_user=?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, 'sssi', $role, $username, $passwordRaw, $id_user);
    } else {
        $sql  = "UPDATE user SET id_guru=?, role_user=?, username=?, password_user=? WHERE id_user=?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, 'isssi', $id_guru, $role, $username, $passwordRaw, $id_user);
    }
} else {
    if ($id_guru === null) {
        $sql  = "UPDATE user SET id_guru=NULL, role_user=?, username=? WHERE id_user=?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, 'ssi', $role, $username, $id_user);
    } else {
        $sql  = "UPDATE user SET id_guru=?, role_user=?, username=? WHERE id_user=?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, 'issi', $id_guru, $role, $username, $id_user);
    }
}

$ok = mysqli_stmt_execute($stmt);
if ($ok) {
    echo "<script>alert('Perubahan disimpan'); window.location.href='data_user.php';</script>";
} else {
    echo "<script>alert('Gagal menyimpan perubahan: " . htmlspecialchars(mysqli_error($koneksi)) . "'); history.back();</script>";
}
