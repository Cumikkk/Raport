<?php
include '../../koneksi.php';

if (isset($_POST['id_kelas'])) {
    $id_kelas = $_POST['id_kelas'];
    $mapel_aktif = isset($_POST['mapel']) ? $_POST['mapel'] : [];

    // Ambil semua data kurikulum yang sudah ada untuk kelas ini
    $existingQuery = mysqli_query($koneksi, "SELECT id_mata_pelajaran, nilai_kurikulum FROM kurikulum WHERE id_kelas='$id_kelas'");
    $existing = [];
    while ($row = mysqli_fetch_assoc($existingQuery)) {
        $existing[$row['id_mata_pelajaran']] = $row['nilai_kurikulum'];
    }

    // Ambil semua mapel
    $mapelQuery = mysqli_query($koneksi, "SELECT id_mata_pelajaran FROM mata_pelajaran");

    $updateCases = [];
    $updateIds = [];
    $insertValues = [];

    while ($row = mysqli_fetch_assoc($mapelQuery)) {
        $id_mapel = $row['id_mata_pelajaran'];
        $nilai = in_array($id_mapel, $mapel_aktif) ? 1 : 0;

        if (isset($existing[$id_mapel])) {
            // Sudah ada → update
            if ($existing[$id_mapel] != $nilai) { // update hanya kalau berbeda
                $updateCases[] = "WHEN id_mata_pelajaran='$id_mapel' THEN '$nilai'";
                $updateIds[] = $id_mapel;
            }
        } else {
            // Belum ada → insert hanya kalau toggle ON
            if ($nilai == 1) {
                $insertValues[] = "('$id_kelas', '$id_mapel', '$nilai')";
            }
        }
    }

    // Jalankan batch update
    if (!empty($updateCases)) {
        $ids = implode(',', $updateIds);
        $caseSQL = implode(' ', $updateCases);
        $updateSQL = "UPDATE kurikulum 
                      SET nilai_kurikulum = CASE $caseSQL END 
                      WHERE id_kelas='$id_kelas' AND id_mata_pelajaran IN ($ids)";
        mysqli_query($koneksi, $updateSQL);
    }

    // Jalankan batch insert
    if (!empty($insertValues)) {
        $valuesSQL = implode(',', $insertValues);
        $insertSQL = "INSERT INTO kurikulum (id_kelas, id_mata_pelajaran, nilai_kurikulum) VALUES $valuesSQL";
        mysqli_query($koneksi, $insertSQL);
    }

    header("Location: data_kurlum.php?id_kelas=$id_kelas&status=sukses");
    exit;
} else {
    echo "Kelas belum dipilih!";
}
