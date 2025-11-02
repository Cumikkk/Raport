<?php
ob_start();
require_once '../../koneksi.php';

// ===== VALIDASI ID =====
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: datakelas.php?msg=invalid');
    exit;
}

// ===== CEK APA ADA SISWA TERKAIT (AGAR AMAN DARI FK ERROR) =====
$stmtCheck = mysqli_prepare($koneksi, "SELECT COUNT(*) FROM siswa WHERE id_kelas = ?");
mysqli_stmt_bind_param($stmtCheck, 'i', $id);
mysqli_stmt_execute($stmtCheck);
mysqli_stmt_bind_result($stmtCheck, $jumlah);
mysqli_stmt_fetch($stmtCheck);
mysqli_stmt_close($stmtCheck);

if ($jumlah > 0) {
    // Gagal hapus karena masih ada siswa
    header('Location: datakelas.php?msg=gagal_hapus_fk');
    exit;
}

// ===== EKSEKUSI DELETE =====
$stmt = mysqli_prepare($koneksi, "DELETE FROM kelas WHERE id_kelas = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    header('Location: datakelas.php?msg=deleted');
} else {
    header('Location: datakelas.php?msg=error');
}
mysqli_stmt_close($stmt);
exit;
