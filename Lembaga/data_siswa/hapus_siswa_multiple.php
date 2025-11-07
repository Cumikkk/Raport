<?php
include '../../koneksi.php';

if (!isset($_POST['id_siswa'])) {
    header("Location: data_siswa.php");
    exit;
}

$ids = $_POST['id_siswa'];
if (!is_array($ids) || empty($ids)) {
    header("Location: data_siswa.php?pesan=tidak_ada_data_dipilih");
    exit;
}

// Sanitisasi: pastikan semua angka
$idList = implode(',', array_map('intval', $ids));

mysqli_begin_transaction($koneksi);

try {
    // Hapus tabel relasi terlebih dulu (agar FK tidak menghalangi)
    // 1) Absensi
    $q1 = mysqli_query($koneksi, "DELETE FROM absensi WHERE id_siswa IN ($idList)");
    if ($q1 === false) { throw new Exception(mysqli_error($koneksi)); }

    // 2) Cetak Rapor (wadah catatan_wali_kelas)
    $q2 = mysqli_query($koneksi, "DELETE FROM cetak_rapor WHERE id_siswa IN ($idList)");
    if ($q2 === false) { throw new Exception(mysqli_error($koneksi)); }

    // Tambah di sini jika ada tabel lain yang refer id_siswa:
    // $qX = mysqli_query($koneksi, "DELETE FROM nilai WHERE id_siswa IN ($idList)");
    // if ($qX === false) { throw new Exception(mysqli_error($koneksi)); }

    // 3) Baru hapus data master siswa
    $qMain = mysqli_query($koneksi, "DELETE FROM siswa WHERE id_siswa IN ($idList)");
    if ($qMain === false) { throw new Exception(mysqli_error($koneksi)); }

    mysqli_commit($koneksi);
    header("Location: data_siswa.php?pesan=hapus_sukses");
    exit;

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    $msg = urlencode($e->getMessage());
    header("Location: data_siswa.php?pesan=hapus_gagal&err=$msg");
    exit;
}
