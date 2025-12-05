<?php
// pages/absensi/ajax_absensi_list.php
require_once __DIR__ . '/../../koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$like   = "%{$search}%";

$perPage = isset($_GET['per']) ? (int)$_GET['per'] : 10;
if ($perPage < 1) $perPage = 10;
if ($perPage > 100) $perPage = 100;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

/* ==========================
 * HITUNG TOTAL DATA
 * ========================== */
if ($search !== '') {
  $countSql = "
    SELECT COUNT(*) AS total
    FROM absensi a
    INNER JOIN siswa s ON s.id_siswa = a.id_siswa
    LEFT JOIN kelas k ON k.id_kelas = s.id_kelas
    LEFT JOIN guru  g ON g.id_guru  = k.id_guru
    WHERE (
      s.nama_siswa LIKE ?
      OR s.no_induk_siswa LIKE ?
      OR COALESCE(g.nama_guru,'') LIKE ?
  ";
  $params = [$like, $like, $like];
  $types  = 'sss';

  if (ctype_digit($search)) {
    $countSql .= " OR a.id_absensi = ? OR a.sakit = ? OR a.izin = ? OR a.alpha = ? ";
    $val = (int)$search;
    $params[] = $val;
    $params[] = $val;
    $params[] = $val;
    $params[] = $val;
    $types   .= 'iiii';
  }

  $countSql .= " ) ";

  $stmtCount = mysqli_prepare($koneksi, $countSql);
  mysqli_stmt_bind_param($stmtCount, $types, ...$params);
} else {
  $countSql = "SELECT COUNT(*) AS total FROM absensi";
  $stmtCount = mysqli_prepare($koneksi, $countSql);
}

mysqli_stmt_execute($stmtCount);
$resCount  = mysqli_stmt_get_result($stmtCount);
$rowCount  = mysqli_fetch_assoc($resCount);
$totalRows = (int)($rowCount['total'] ?? 0);

$totalPages = max(1, (int)ceil($totalRows / $perPage));
if ($page > $totalPages) {
  $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

/* ==========================
 * AMBIL DATA (INNER JOIN)
 * ========================== */
if ($search !== '') {
  $sql = "
    SELECT
      a.id_absensi,
      a.id_siswa,
      s.nama_siswa,
      s.no_induk_siswa AS nis,
      COALESCE(g.nama_guru, '-') AS wali_kelas,
      a.sakit,
      a.izin,
      a.alpha
    FROM absensi a
    INNER JOIN siswa s ON s.id_siswa = a.id_siswa
    LEFT JOIN kelas k ON k.id_kelas = s.id_kelas
    LEFT JOIN guru  g ON g.id_guru  = k.id_guru
    WHERE (
      s.nama_siswa LIKE ?
      OR s.no_induk_siswa LIKE ?
      OR COALESCE(g.nama_guru,'') LIKE ?
  ";
  $params = [$like, $like, $like];
  $types  = 'sss';

  if (ctype_digit($search)) {
    $sql .= " OR a.id_absensi = ? OR a.sakit = ? OR a.izin = ? OR a.alpha = ? ";
    $val = (int)$search;
    $params[] = $val;
    $params[] = $val;
    $params[] = $val;
    $params[] = $val;
    $types   .= 'iiii';
  }

  $sql .= " ) 
            ORDER BY s.nama_siswa ASC 
            LIMIT ? OFFSET ? ";

  $params[] = $perPage;
  $params[] = $offset;
  $types   .= 'ii';

  $stmt = mysqli_prepare($koneksi, $sql);
  mysqli_stmt_bind_param($stmt, $types, ...$params);
} else {
  $sql = "
    SELECT
      a.id_absensi,
      a.id_siswa,
      s.nama_siswa,
      s.no_induk_siswa AS nis,
      COALESCE(g.nama_guru, '-') AS wali_kelas,
      a.sakit,
      a.izin,
      a.alpha
    FROM absensi a
    INNER JOIN siswa s ON s.id_siswa = a.id_siswa
    LEFT JOIN kelas k ON k.id_kelas = s.id_kelas
    LEFT JOIN guru  g ON g.id_guru  = k.id_guru
    ORDER BY s.nama_siswa ASC
    LIMIT ? OFFSET ?
  ";
  $stmt = mysqli_prepare($koneksi, $sql);
  mysqli_stmt_bind_param($stmt, 'ii', $perPage, $offset);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

header('Content-Type: text/html; charset=utf-8');

$rowClass = ($search !== '') ? 'highlight-row' : '';
$no       = $offset + 1;

if ($totalRows === 0) {
  echo '<tr><td colspan="9">Tidak ada data yang cocok.</td></tr>';
} else {
  while ($row = mysqli_fetch_assoc($result)) {
    $id        = (int)$row['id_absensi'];
    $id_siswa  = (int)($row['id_siswa'] ?? 0);
    $nama      = htmlspecialchars($row['nama_siswa'] ?? '-', ENT_QUOTES, 'UTF-8');
    $nis       = htmlspecialchars($row['nis'] ?? '-', ENT_QUOTES, 'UTF-8');
    $wali      = htmlspecialchars($row['wali_kelas'] ?? '-', ENT_QUOTES, 'UTF-8');
    $sakit     = (int)($row['sakit'] ?? 0);
    $izin      = (int)($row['izin'] ?? 0);
    $alpha     = (int)($row['alpha'] ?? 0);

    echo '<tr class="' . $rowClass . '" data-id="' . $id . '">';

    echo '<td class="text-center" data-label="Pilih">
            <input type="checkbox" class="row-check" value="' . $id . '">
            <input type="hidden" name="id_absensi[]" value="' . $id . '">
          </td>';

    echo '<td data-label="Absen">' . $no++ . '</td>';
    echo '<td data-label="Nama Siswa">' . $nama . '</td>';
    echo '<td data-label="NIS">' . $nis . '</td>';
    echo '<td data-label="Wali Kelas">' . $wali . '</td>';

    echo '<td data-label="Sakit">
            <span class="cell-view">' . $sakit . '</span>
            <input type="number"
                   class="form-control form-control-sm cell-input d-none"
                   name="sakit[]"
                   min="0"
                   step="1"
                   value="' . $sakit . '">
          </td>';

    echo '<td data-label="Izin">
            <span class="cell-view">' . $izin . '</span>
            <input type="number"
                   class="form-control form-control-sm cell-input d-none"
                   name="izin[]"
                   min="0"
                   step="1"
                   value="' . $izin . '">
          </td>';

    echo '<td data-label="Alpha">
            <span class="cell-view">' . $alpha . '</span>
            <input type="number"
                   class="form-control form-control-sm cell-input d-none"
                   name="alpha[]"
                   min="0"
                   step="1"
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
