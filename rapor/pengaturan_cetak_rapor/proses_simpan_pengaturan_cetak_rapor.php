<?php
// pages/cetak_rapor/proses_simpan_pengaturan_cetak_rapor.php
require_once '../../koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

function redirectWith($key, $message)
{
    $key = ($key === 'msg') ? 'msg' : 'err';
    header('Location: pengaturan_cetak_rapor.php?' . $key . '=' . urlencode($message));
    exit;
}

$tempat_cetak  = isset($_POST['tempat_cetak']) ? trim($_POST['tempat_cetak']) : '';
$tanggal_cetak = isset($_POST['tanggal_cetak']) ? trim($_POST['tanggal_cetak']) : '';

try {
    // cek sudah ada record belum
    $cek = mysqli_query($koneksi, "SELECT id_pengaturan_cetak_rapor FROM pengaturan_cetak_rapor LIMIT 1");

    if ($cek && mysqli_num_rows($cek) > 0) {
        $row = mysqli_fetch_assoc($cek);
        $id_existing = (int)($row['id_pengaturan_cetak_rapor'] ?? 0);

        $stmt = mysqli_prepare($koneksi, "
      UPDATE pengaturan_cetak_rapor
      SET tempat_cetak = ?, tanggal_cetak = ?
      WHERE id_pengaturan_cetak_rapor = ?
    ");
        mysqli_stmt_bind_param($stmt, "ssi", $tempat_cetak, $tanggal_cetak, $id_existing);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($ok) {
            redirectWith('msg', 'Pengaturan cetak rapor berhasil diperbarui.');
        } else {
            redirectWith('err', 'Gagal memperbarui pengaturan cetak rapor.');
        }
    } else {
        $stmt = mysqli_prepare($koneksi, "
      INSERT INTO pengaturan_cetak_rapor (tempat_cetak, tanggal_cetak)
      VALUES (?, ?)
    ");
        mysqli_stmt_bind_param($stmt, "ss", $tempat_cetak, $tanggal_cetak);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($ok) {
            redirectWith('msg', 'Pengaturan cetak rapor berhasil disimpan.');
        } else {
            redirectWith('err', 'Gagal menyimpan pengaturan cetak rapor.');
        }
    }
} catch (Throwable $e) {
    redirectWith('err', 'Terjadi kesalahan: ' . $e->getMessage());
}
