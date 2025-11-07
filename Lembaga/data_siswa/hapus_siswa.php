<?php
include '../../koneksi.php';

// Pastikan ada ID yang valid
if (!isset($_GET['id']) || $_GET['id'] === '' ) {
    echo "<script>alert('ID siswa tidak ditemukan');window.location='data_siswa.php';</script>";
    exit;
}

$id = (int) $_GET['id']; // sanitasi dasar

// Mulai transaksi agar penghapusan konsisten
mysqli_begin_transaction($koneksi);

try {
    // 1) Hapus data turunan/relasi yang bergantung pada siswa ini terlebih dulu

    // Absensi
    $q1 = mysqli_query($koneksi, "DELETE FROM absensi WHERE id_siswa = $id");
    if ($q1 === false) { throw new Exception(mysqli_error($koneksi)); }

    // Cetak Rapor (berisi catatan_wali_kelas)
    $q2 = mysqli_query($koneksi, "DELETE FROM cetak_rapor WHERE id_siswa = $id");
    if ($q2 === false) { throw new Exception(mysqli_error($koneksi)); }

    // ================================
    // Jika ada tabel lain yang juga pakai id_siswa sebagai FK, hapus di sini.
    // Contoh:
    // $q3 = mysqli_query($koneksi, "DELETE FROM nilai WHERE id_siswa = $id");
    // if ($q3 === false) { throw new Exception(mysqli_error($koneksi)); }
    // ================================

    // 2) Baru hapus master: siswa
    $qMain = mysqli_query($koneksi, "DELETE FROM siswa WHERE id_siswa = $id");
    if ($qMain === false) { throw new Exception(mysqli_error($koneksi)); }

    // Commit kalau semua langkah sukses
    mysqli_commit($koneksi);

    echo "<script>alert('Data siswa beserta relasinya berhasil dihapus');window.location='data_siswa.php';</script>";
    exit;

} catch (Exception $e) {
    // Gagal pada salah satu langkah → batalkan semua
    mysqli_rollback($koneksi);

    // Tampilkan pesan error yang aman
    $msg = addslashes($e->getMessage());
    echo "<script>alert('Gagal menghapus data: $msg');window.location='data_siswa.php';</script>";
    exit;
}
