<?php
// edit_siswa.php
include '../../koneksi.php';

// Jika bukan POST atau tidak ada id_siswa → kembali ke data_siswa
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_siswa'])) {
    header('Location: data_siswa.php');
    exit;
}

$id = (int)$_POST['id_siswa'];

// Ambil data dari form (modal)
$nama     = mysqli_real_escape_string($koneksi, $_POST['nama_siswa'] ?? '');
$nisn     = mysqli_real_escape_string($koneksi, $_POST['no_induk_siswa'] ?? '');
$absen    = mysqli_real_escape_string($koneksi, $_POST['no_absen_siswa'] ?? '');
$id_kelas = mysqli_real_escape_string($koneksi, $_POST['id_kelas'] ?? '');
$catatan  = mysqli_real_escape_string($koneksi, $_POST['catatan_wali_kelas'] ?? '');

// Mulai transaksi
mysqli_begin_transaction($koneksi);

try {
    // Update tabel siswa
    mysqli_query($koneksi, "
        UPDATE siswa SET 
          nama_siswa      = '$nama',
          no_induk_siswa  = '$nisn',
          no_absen_siswa  = '$absen',
          id_kelas        = '$id_kelas'
        WHERE id_siswa     = '$id'
    ");

    // Simpan / update catatan wali kelas (mengikuti logika file lama)
    mysqli_query($koneksi, "
        INSERT INTO cetak_rapor (id_siswa, catatan_wali_kelas)
        VALUES ('$id', '$catatan')
        ON DUPLICATE KEY UPDATE catatan_wali_kelas = '$catatan'
    ");

    mysqli_commit($koneksi);
    echo "<script>
            alert('Data berhasil diperbarui!');
            window.location.href = 'data_siswa.php';
          </script>";
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo "<script>
            alert('Gagal memperbarui data!');
            window.history.back();
          </script>";
}

exit;
