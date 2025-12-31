<?php
// pages/kelas/ajax_kelas_list.php
require_once '../../koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

$search  = isset($_GET['q']) ? trim($_GET['q']) : '';
$tingkat = isset($_GET['tingkat']) ? trim($_GET['tingkat']) : '';

$allowedTingkat = ['', 'X', 'XI', 'XII'];
if (!in_array($tingkat, $allowedTingkat, true)) $tingkat = '';

$like = "%{$search}%";
$tingkatParam = $tingkat ?: '';

$sql = "
  SELECT
    k.id_kelas, k.id_guru, k.nama_kelas, k.tingkat_kelas,
    g.nama_guru AS wali_kelas,
    (SELECT COUNT(*) FROM siswa s WHERE s.id_kelas = k.id_kelas) AS jumlah_siswa
  FROM kelas k
  LEFT JOIN guru g ON g.id_guru = k.id_guru
  WHERE (k.nama_kelas LIKE ? OR COALESCE(g.nama_guru,'') LIKE ?)
    AND (? = '' OR k.tingkat_kelas = ?)
  ORDER BY FIELD(k.tingkat_kelas,'X','XI','XII'), k.nama_kelas
";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'ssss', $like, $like, $tingkatParam, $tingkatParam);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$no = 1;
$rowClass = ($search !== '') ? 'highlight-row' : '';

$has = false;
while ($row = mysqli_fetch_assoc($result)) {
  $has = true;

  $id_kelas = (int)$row['id_kelas'];
  $id_guru  = (int)($row['id_guru'] ?? 0);
  $namaRaw  = (string)($row['nama_kelas'] ?? '');
  $nama     = htmlspecialchars($namaRaw, ENT_QUOTES, 'UTF-8');
  $ting     = htmlspecialchars((string)($row['tingkat_kelas'] ?? ''), ENT_QUOTES, 'UTF-8');
  $wali     = htmlspecialchars((string)(($row['wali_kelas'] ?? '-') ?: '-'), ENT_QUOTES, 'UTF-8');
  $jumlah   = (int)($row['jumlah_siswa'] ?? 0);

  echo '<tr class="' . $rowClass . '">';
  echo '  <td class="text-center" data-label="Pilih"><input type="checkbox" class="row-check" value="' . $id_kelas . '"></td>';
  echo '  <td data-label="No">' . ($no++) . '</td>';
  echo '  <td data-label="Nama Kelas">' . $nama . '</td>';
  echo '  <td data-label="Jumlah Siswa" class="text-center">' . $jumlah . '</td>';
  echo '  <td data-label="Wali Kelas">' . $wali . '</td>';
  echo '  <td data-label="Tingkat" class="text-center">' . $ting . '</td>';
  echo '  <td data-label="Aksi">';
  echo '    <div class="d-flex gap-2 justify-content-center flex-wrap">';
  echo '      <button type="button" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-edit-kelas"
              data-id="' . $id_kelas . '"
              data-nama="' . $nama . '"
              data-tingkat="' . $ting . '"
              data-idguru="' . $id_guru . '"><i class="bi bi-pencil-square"></i> Edit</button>';
  echo '      <button type="button" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-delete-single"
              data-id="' . $id_kelas . '"
              data-label="' . $nama . '"><i class="bi bi-trash"></i> Hapus</button>';
  echo '    </div>';
  echo '  </td>';
  echo '</tr>';
}

if (!$has) {
  echo '<tr><td colspan="7">Tidak ada data yang cocok.</td></tr>';
}

mysqli_stmt_close($stmt);
