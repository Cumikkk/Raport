<?php
include '../../koneksi.php';

/* =========================================
 *  HAPUS TERPILIH (PROSES SAJA)
 * ========================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['hapus_multiple'])) {
    // akses langsung GET atau tanpa flag -> kembalikan ke halaman utama
    header('Location: nilai_ekstra.php');
    exit;
}

if (empty($_POST['id_nilai']) || !is_array($_POST['id_nilai'])) {
    header('Location: nilai_ekstra.php?status=error&msg=' . urlencode('Tidak ada data yang dipilih.'));
    exit;
}

$ids = [];
foreach ($_POST['id_nilai'] as $raw) {
    $id = (int)$raw;
    if ($id > 0) {
        $ids[] = $id;
    }
}
$ids = array_values(array_unique($ids));

if (count($ids) === 0) {
    header('Location: nilai_ekstra.php?status=error&msg=' . urlencode('Tidak ada data valid yang bisa dihapus.'));
    exit;
}

try {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types        = str_repeat('i', count($ids));

    $sql  = "DELETE FROM nilai_ekstrakurikuler WHERE id_nilai_ekstrakurikuler IN ($placeholders)";
    $stmt = $koneksi->prepare($sql);
    if ($stmt === false) {
        throw new Exception('Prepare gagal: ' . $koneksi->error);
    }

    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $stmt->close();

    header('Location: nilai_ekstra.php?status=success&msg=' . urlencode('Data nilai ekstrakurikuler terpilih berhasil dihapus.'));
    exit;
} catch (Throwable $e) {
    // Kalau mau debug, bisa var_dump($e->getMessage());
    header('Location: nilai_ekstra.php?status=error&msg=' . urlencode('Terjadi kesalahan saat menghapus data.'));
    exit;
}
