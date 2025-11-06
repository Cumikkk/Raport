<?php
include '../../koneksi.php';

$tempat_cetak  = isset($_POST['tempat_cetak']) ? trim($_POST['tempat_cetak']) : '';
$tanggal_cetak = isset($_POST['tanggal_cetak']) ? $_POST['tanggal_cetak'] : '';

$cek = mysqli_query($koneksi, "SELECT id_pengaturan_cetak_rapor FROM pengaturan_cetak_rapor LIMIT 1");

if ($cek && mysqli_num_rows($cek) > 0) {
    // Sudah ada → update
    $row = mysqli_fetch_assoc($cek);
    $id_existing = $row['id_pengaturan_cetak_rapor'];

    $stmt = mysqli_prepare($koneksi, "UPDATE pengaturan_cetak_rapor 
                                     SET tempat_cetak=?, tanggal_cetak=?
                                     WHERE id_pengaturan_cetak_rapor=?");
    mysqli_stmt_bind_param($stmt, "ssi", $tempat_cetak, $tanggal_cetak, $id_existing);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        echo "<script>alert('Data berhasil diperbarui!'); window.location='pengaturan_cetak_rapor.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data!'); window.location='pengaturan_cetak_rapor.php';</script>";
    }

} else {
    // Belum ada → insert
    $stmt = mysqli_prepare($koneksi, "INSERT INTO pengaturan_cetak_rapor (tempat_cetak, tanggal_cetak)
                                      VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ss", $tempat_cetak, $tanggal_cetak);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        echo "<script>alert('Data berhasil ditambahkan!'); window.location='pengaturan_cetak_rapor.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan data!'); window.location='pengaturan_cetak_rapor.php';</script>";
    }
}
?>
