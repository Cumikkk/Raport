<?php
// hapus_rapor_multiple.php
require_once '../../koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirect_with_status(string $status, string $message = '')
{
    $location = 'data_rapor.php?status=' . urlencode($status);
    if ($message !== '') {
        $location .= '&msg=' . urlencode($message);
    }
    header("Location: {$location}");
    exit;
}

// Pastikan request POST + ada id_siswa[]
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_siswa']) || !is_array($_POST['id_siswa'])) {
    redirect_with_status('error', 'Tidak ada data yang dipilih.');
}

// Bersihkan ID
$ids = [];
foreach ($_POST['id_siswa'] as $raw) {
    $id = (int)$raw;
    if ($id > 0) {
        $ids[] = $id;
    }
}
$ids = array_values(array_unique($ids));

if (count($ids) === 0) {
    redirect_with_status('error', 'Tidak ada data valid yang bisa dihapus.');
}

try {
    // Hapus semua CETAK RAPOR berdasarkan id_siswa
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types       = str_repeat('i', count($ids));

    $sql  = "DELETE FROM cetak_rapor WHERE id_siswa IN ($placeholders)";
    $stmt = $koneksi->prepare($sql);
    if ($stmt === false) {
        throw new Exception('Prepare gagal: ' . $koneksi->error);
    }

    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $stmt->close();

    redirect_with_status('success', 'Data rapor terpilih berhasil dihapus.');
} catch (Throwable $e) {
    redirect_with_status('error', 'Terjadi kesalahan saat menghapus data.');
}
