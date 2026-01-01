<?php
// pages/siswa/ajax_siswa_list.php
require_once '../../koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

$search  = isset($_GET['q']) ? trim($_GET['q']) : '';
$like    = "%{$search}%";

$tingkat = isset($_GET['tingkat']) ? trim($_GET['tingkat']) : '';
$idKelasFilter = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;

$allowedTingkat = ['', 'X', 'XI', 'XII'];
if (!in_array($tingkat, $allowedTingkat, true)) $tingkat = '';

$allowedPer = [10, 20, 50, 100, 0];
$perPage = isset($_GET['per']) ? (int)$_GET['per'] : 10;
if (!in_array($perPage, $allowedPer, true)) $perPage = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

function bindParamsDynamic(mysqli_stmt $stmt, string $types, array $params): void
{
  if ($types === '' || empty($params)) return;
  $refs = [];
  $refs[] = &$types;
  foreach ($params as $k => $v) {
    $refs[] = &$params[$k];
  }
  call_user_func_array([$stmt, 'bind_param'], $refs);
}

$where = [];
$params = [];
$types  = '';

if ($search !== '') {
  $where[] = "(s.nama_siswa LIKE ? OR s.no_absen_siswa LIKE ? OR s.no_induk_siswa LIKE ?)";
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
  $types .= 'sss';
}
if ($tingkat !== '') {
  $where[] = "k.tingkat_kelas = ?";
  $params[] = $tingkat;
  $types .= 's';
}
if ($idKelasFilter > 0) {
  $where[] = "s.id_kelas = ?";
  $params[] = $idKelasFilter;
  $types .= 'i';
}

$whereSql = '';
if (!empty($where)) $whereSql = ' WHERE ' . implode(' AND ', $where);

// total
$countSql = "
  SELECT COUNT(*) AS total
  FROM siswa s
  LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
  $whereSql
";
$stmtCount = mysqli_prepare($koneksi, $countSql);
bindParamsDynamic($stmtCount, $types, $params);
mysqli_stmt_execute($stmtCount);
$resCount = mysqli_stmt_get_result($stmtCount);
$rowCount = mysqli_fetch_assoc($resCount);
$totalRows = (int)($rowCount['total'] ?? 0);

// pagination
if ($perPage === 0) {
  $totalPages = 1;
  $page = 1;
  $offset = 0;
} else {
  $totalPages = max(1, (int)ceil($totalRows / $perPage));
  if ($page > $totalPages) $page = $totalPages;
  $offset = ($page - 1) * $perPage;
}

// data base
$baseSql = "
  SELECT s.id_siswa, s.nama_siswa, s.no_induk_siswa, s.no_absen_siswa, s.id_kelas,
         k.nama_kelas, k.tingkat_kelas
  FROM siswa s
  LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
  $whereSql
  ORDER BY CAST(s.no_absen_siswa AS UNSIGNED) ASC, s.no_absen_siswa ASC
";

if ($perPage === 0) {
  $stmt = mysqli_prepare($koneksi, $baseSql);
  bindParamsDynamic($stmt, $types, $params);
} else {
  $sql = $baseSql . " LIMIT ? OFFSET ?";
  $stmt = mysqli_prepare($koneksi, $sql);

  $params2 = $params;
  $types2  = $types . 'ii';
  $params2[] = $perPage;
  $params2[] = $offset;

  bindParamsDynamic($stmt, $types2, $params2);
}

mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if ($totalRows === 0) {
  echo '<tr><td colspan="6">Belum ada data.</td></tr>';
  echo '<tr class="meta-row" data-total="0" data-page="1" data-per="' . (int)$perPage . '"></tr>';
  exit;
}

$rowClass = ($search !== '') ? 'highlight-row' : '';

while ($row = mysqli_fetch_assoc($res)) {
  $id = (int)$row['id_siswa'];
  $nama = htmlspecialchars($row['nama_siswa']);
  $nisn = htmlspecialchars($row['no_induk_siswa']);
  $absen = htmlspecialchars($row['no_absen_siswa']);
  $nama_kelas = htmlspecialchars($row['nama_kelas'] ?? '-');
  $id_kelas = (int)($row['id_kelas'] ?? 0);

  echo '<tr class="' . $rowClass . '">';
  echo '  <td class="text-center" data-label="Pilih"><input type="checkbox" class="row-check" value="' . $id . '"></td>';
  echo '  <td data-label="NISN" class="text-center">' . $nisn . '</td>';
  echo '  <td data-label="Nama">' . $nama . '</td>';
  echo '  <td data-label="Kelas" class="text-center">' . $nama_kelas . '</td>';
  echo '  <td data-label="Absen" class="text-center">' . $absen . '</td>';
  echo '  <td data-label="Aksi">';
  echo '    <div class="d-flex gap-2 justify-content-center flex-wrap">';
  echo '      <button type="button" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-edit-siswa"
              data-id="' . $id . '"
              data-nama="' . htmlspecialchars($row['nama_siswa'], ENT_QUOTES, 'UTF-8') . '"
              data-nisn="' . htmlspecialchars($row['no_induk_siswa'], ENT_QUOTES, 'UTF-8') . '"
              data-absen="' . htmlspecialchars($row['no_absen_siswa'], ENT_QUOTES, 'UTF-8') . '"
              data-id_kelas="' . $id_kelas . '">
              <i class="bi bi-pencil-square"></i> Edit
            </button>';
  echo '      <button type="button" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-delete-single"
              data-id="' . $id . '"
              data-label="' . htmlspecialchars($row['nama_siswa'], ENT_QUOTES, 'UTF-8') . '">
              <i class="bi bi-trash"></i> Hapus
            </button>';
  echo '    </div>';
  echo '  </td>';
  echo '</tr>';
}

echo '<tr class="meta-row" data-total="' . (int)$totalRows . '" data-page="' . (int)$page . '" data-per="' . (int)$perPage . '"></tr>';
