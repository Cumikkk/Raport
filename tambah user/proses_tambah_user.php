<?php
require_once '../koneksi.php';

$role        = isset($_POST['role']) ? trim($_POST['role']) : '';
$username    = isset($_POST['username']) ? trim($_POST['username']) : '';
$passwordRaw = isset($_POST['password_user']) ? $_POST['password_user'] : '';
$id_guru     = isset($_POST['id_guru']) && $_POST['id_guru'] !== '' ? (int)$_POST['id_guru'] : null;

// Validasi dasar
$allowedRoles = ['Admin', 'Guru'];
if (!in_array($role, $allowedRoles, true)) {
    exit("<script>alert('Role tidak valid.'); history.back();</script>");
}
if ($username === '' || strlen($username) > 50) {
    exit("<script>alert('Username wajib dan maks 50 karakter.'); history.back();</script>");
}
if ($passwordRaw === '') {
    exit("<script>alert('Password wajib diisi.'); history.back();</script>");
}

// Cek username unik
$sqlCheck = "SELECT 1 FROM user WHERE username = ? LIMIT 1";
$stmt = mysqli_prepare($koneksi, $sqlCheck);
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($res) > 0) {
    exit("<script>alert('Username sudah digunakan.'); history.back();</script>");
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
    echo "<script>alert('User berhasil ditambahkan'); window.location.href='data_user.php';</script>";
} else {
    echo "<script>alert('Gagal menambahkan user: " . htmlspecialchars(mysqli_error($koneksi)) . "'); history.back();</script>";
}
