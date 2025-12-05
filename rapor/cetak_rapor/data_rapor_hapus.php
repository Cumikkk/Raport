<?php
require '../../koneksi.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: data_rapor.php?msg=invalid");
    exit;
}

$id_siswa = (int)$_GET['id'];

// hapus cetak_rapor terbaru siswa ini
$sql = "
    DELETE cr FROM cetak_rapor cr
    INNER JOIN (
        SELECT id_siswa, MAX(id_cetak_rapor) AS max_id
        FROM cetak_rapor
        WHERE id_siswa = ?
    ) last
    ON last.max_id = cr.id_cetak_rapor
";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $id_siswa);

if ($stmt->execute()) {
    header("Location: data_rapor.php?msg=deleted");
} else {
    header("Location: data_rapor.php?msg=error");
}
exit;
