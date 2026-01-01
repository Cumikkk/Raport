<?php
// pages/absensi/ajax_absensi_list.php
require_once __DIR__ . '/../../koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

$search  = isset($_GET['q']) ? trim($_GET['q']) : '';
$like    = "%{$search}%";

$tingkat = isset($_GET['tingkat']) ? trim($_GET['tingkat']) : '';
$idKelasFilter = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;

// valid tingkat
$allowedTingkat = ['', 'X', 'XI', 'XII'];
if (!in_array($tingkat, $allowedTingkat, true)) $tingkat = '';

$perPage = isset($_GET['per']) ? (int)$_GET['per'] : 10;
if ($perPage < 1) $perPage = 10;
if ($perPage > 100) $perPage = 100;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

/**
 * Helper bind_param dinamis
 */
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

/**
 * Build WHERE dinamis (mirip data_siswa)
 */
$where = [];
$params = [];
$types  = '';

if ($search !== '') {
  $where[] = "(
    s.nama_siswa LIKE ?
    OR s.no_induk_siswa LIKE ?
    OR COALESCE(k.nama_kelas,'') LIKE ?
    OR COALESCE(k.tingkat_kelas,'') LIKE ?
  )";
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
  $types .= 'ssss';

  if (ctype_digit($search)) {
    // biar bisa cari angka sakit/izin/alpha/id_absensi
    $where[] = "(a.id_absensi = ? OR a.sakit = ? OR a.izin = ? OR a.alpha = ?)";
    $val = (int)$search;
    $params[] = $val;
    $params[] = $val;
    $params[] = $val;
    $params[] = $val;
    $types   .= 'iiii';
  }
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
if (!empty($where)) {
  $whereSql = ' WHERE ' . implode(' AND ', $where);
}

/* ==========================
 * HITUNG TOTAL DATA
 * ========================== */
$countSql = "
  SELECT COUNT(*) AS total
  FROM absensi a
  INNER JOIN siswa s ON s.id_siswa = a.id_siswa
  LEFT JOIN kelas k ON k.id_kelas = s.id_kelas
  $whereSql
";
$stmtCount = mysqli_prepare($koneksi, $countSql);
bindParamsDynamic($stmtCount, $types, $params);

mysqli_stmt_execute($stmtCount);
$resCount  = mysqli_stmt_get_result($stmtCount);
$rowCount  = mysqli_fetch_assoc($resCount);
$totalRows = (int)($rowCount['total'] ?? 0);

$totalPages = max(1, (int)ceil($totalRows / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

/* ==========================
 * AMBIL DATA
 * ========================== */
$baseSql = "
  SELECT
    a.id_absensi,
    a.id_siswa,
    s.nama_siswa,
    s.no_induk_siswa AS nis,
    s.no_absen_siswa AS absen,
    COALESCE(k.nama_kelas, '-') AS nama_kelas,
    COALESCE(k.tingkat_kelas, '') AS tingkat_kelas,
    a.sakit,
    a.izin,
    a.alpha
  FROM absensi a
  INNER JOIN siswa s ON s.id_siswa = a.id_siswa
  LEFT JOIN kelas k ON k.id_kelas = s.id_kelas
  $whereSql
  ORDER BY s.nama_siswa ASC
";

$sql = $baseSql . " LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($koneksi, $sql);

$params2 = $params;
$types2  = $types . 'ii';
$params2[] = $perPage;
$params2[] = $offset;

bindParamsDynamic($stmt, $types2, $params2);

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

header('Content-Type: text/html; charset=utf-8');

$rowClass = ($search !== '' || $tingkat !== '' || $idKelasFilter > 0) ? 'highlight-row' : '';

if ($totalRows === 0) {
  echo '<tr><td colspan="9">Tidak ada data yang cocok.</td></tr>';
} else {
  while ($row = mysqli_fetch_assoc($result)) {
    $id        = (int)$row['id_absensi'];
    $nama      = htmlspecialchars($row['nama_siswa'] ?? '-', ENT_QUOTES, 'UTF-8');
    $nis       = htmlspecialchars($row['nis'] ?? '-', ENT_QUOTES, 'UTF-8');
    $absen     = htmlspecialchars($row['absen'] ?? '-', ENT_QUOTES, 'UTF-8');

    $kelasNama = (string)($row['nama_kelas'] ?? '-');
    $kelasTkt  = (string)($row['tingkat_kelas'] ?? '');
    $kelasTampil = trim($kelasNama);
    // Kalau tingkat ada tapi nama_kelas tidak mengandung tingkat, tambahkan prefix tingkat agar informatif
    if ($kelasTkt !== '' && stripos($kelasTampil, $kelasTkt) === false) {
      $kelasTampil = $kelasTkt . ' - ' . $kelasTampil;
    }
    $kelasTampil = htmlspecialchars($kelasTampil !== '' ? $kelasTampil : '-', ENT_QUOTES, 'UTF-8');

    $sakit     = (int)($row['sakit'] ?? 0);
    $izin      = (int)($row['izin'] ?? 0);
    $alpha     = (int)($row['alpha'] ?? 0);

    echo '<tr class="' . $rowClass . '" data-id="' . $id . '">';

    echo '<td class="text-center" data-label="Pilih">
            <input type="checkbox" class="row-check" value="' . $id . '">
            <input type="hidden" name="id_absensi[]" value="' . $id . '">
          </td>';

    echo '<td data-label="NIS" class="text-center">' . $nis . '</td>';
    echo '<td data-label="Nama Siswa">' . $nama . '</td>';
    echo '<td data-label="Kelas" class="text-center">' . $kelasTampil . '</td>';
    echo '<td data-label="Absen" class="text-center">' . $absen . '</td>';

    echo '<td data-label="Sakit" class="text-center">
            <span class="cell-view">' . $sakit . '</span>
            <input type="number"
                   class="form-control form-control-sm cell-input d-none"
                   name="sakit[]"
                   min="0" step="1"
                   value="' . $sakit . '">
          </td>';

    echo '<td data-label="Izin" class="text-center">
            <span class="cell-view">' . $izin . '</span>
            <input type="number"
                   class="form-control form-control-sm cell-input d-none"
                   name="izin[]"
                   min="0" step="1"
                   value="' . $izin . '">
          </td>';

    echo '<td data-label="Alpha" class="text-center">
            <span class="cell-view">' . $alpha . '</span>
            <input type="number"
                   class="form-control form-control-sm cell-input d-none"
                   name="alpha[]"
                   min="0" step="1"
                   value="' . $alpha . '">
          </td>';

    echo '<td data-label="Aksi">
            <button type="button"
              class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-delete-single"
              data-id="' . $id . '"
              data-label="' . $nama . '">
              <i class="bi bi-trash"></i> Hapus
            </button>
          </td>';

    echo '</tr>';
  }
}

// meta-row untuk pagination JS
echo '<tr class="meta-row" data-total="' . $totalRows . '" data-page="' . $page . '" data-per="' . $perPage . '"></tr>';
