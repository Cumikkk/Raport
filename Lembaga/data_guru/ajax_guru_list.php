<?php
// pages/guru/ajax_guru_list.php
require_once '../../koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$koneksi->set_charset('utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$like   = "%{$search}%";

// ✅ perPage dukung 0 = Semua
$perPage = isset($_GET['per']) ? (int)$_GET['per'] : 10;
if ($perPage < 0) $perPage = 10;     // jangan minus
if ($perPage > 100) $perPage = 100;  // batas max, kecuali 0 (semua)

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// ==========================
// Hitung total data
// ==========================
if ($search !== '') {
  $countSql = "SELECT COUNT(*) AS total FROM guru WHERE nama_guru LIKE ? OR npk_guru LIKE ?";
  $stmtCount = mysqli_prepare($koneksi, $countSql);
  mysqli_stmt_bind_param($stmtCount, 'ss', $like, $like);
} else {
  $countSql = "SELECT COUNT(*) AS total FROM guru";
  $stmtCount = mysqli_prepare($koneksi, $countSql);
}
mysqli_stmt_execute($stmtCount);
$resCount  = mysqli_stmt_get_result($stmtCount);
$rowCount  = mysqli_fetch_assoc($resCount);
$totalRows = (int)($rowCount['total'] ?? 0);

// ✅ kalau perPage=0 (Semua) → page=1, offset=0
if ($perPage === 0) {
  $page = 1;
  $offset = 0;
} else {
  $totalPages = max(1, (int)ceil($totalRows / $perPage));
  if ($page > $totalPages) $page = $totalPages;
  $offset = ($page - 1) * $perPage;
}

// ==========================
// Ambil data
// ==========================
if ($perPage === 0) {
  // ✅ Semua data: tanpa LIMIT/OFFSET
  if ($search !== '') {
    $sql = "
      SELECT id_guru, npk_guru, nama_guru, jabatan_guru
      FROM guru
      WHERE nama_guru LIKE ? OR npk_guru LIKE ?
      ORDER BY nama_guru ASC
    ";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
  } else {
    $sql = "
      SELECT id_guru, npk_guru, nama_guru, jabatan_guru
      FROM guru
      ORDER BY nama_guru ASC
    ";
    $stmt = mysqli_prepare($koneksi, $sql);
  }
} else {
  // ✅ Normal: pakai LIMIT/OFFSET
  if ($search !== '') {
    $sql = "
      SELECT id_guru, npk_guru, nama_guru, jabatan_guru
      FROM guru
      WHERE nama_guru LIKE ? OR npk_guru LIKE ?
      ORDER BY nama_guru ASC
      LIMIT ? OFFSET ?
    ";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'ssii', $like, $like, $perPage, $offset);
  } else {
    $sql = "
      SELECT id_guru, npk_guru, nama_guru, jabatan_guru
      FROM guru
      ORDER BY nama_guru ASC
      LIMIT ? OFFSET ?
    ";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $perPage, $offset);
  }
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

header('Content-Type: text/html; charset=utf-8');

$rowClass = ($search !== '') ? 'highlight-row' : '';
$no = $offset + 1;

if ($totalRows === 0) {
  echo '<tr><td colspan="6">Tidak ada data yang cocok.</td></tr>';
} else {
  while ($row = mysqli_fetch_assoc($result)) {
    $id = (int)$row['id_guru'];
    $npk = htmlspecialchars((string)($row['npk_guru'] ?? ''), ENT_QUOTES, 'UTF-8');
    $nama = htmlspecialchars((string)($row['nama_guru'] ?? ''), ENT_QUOTES, 'UTF-8');
    $jabatan = htmlspecialchars((string)($row['jabatan_guru'] ?? ''), ENT_QUOTES, 'UTF-8');

    echo '<tr class="' . $rowClass . '">';
    echo '<td class="text-center" data-label="Pilih">
            <input type="checkbox" class="row-check" value="' . $id . '">
          </td>';
    echo '<td data-label="No">' . $no++ . '</td>';
    echo '<td data-label="NPK">' . $npk . '</td>';
    echo '<td data-label="Nama Guru">' . $nama . '</td>';
    echo '<td data-label="Jabatan" class="text-center">' . $jabatan . '</td>';
    echo '<td data-label="Aksi">
            <div class="d-flex gap-2 justify-content-center flex-wrap">
              <button type="button"
                class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-edit-guru"
                data-id="' . $id . '"
                data-npk="' . $npk . '"
                data-nama="' . $nama . '"
                data-jabatan="' . $jabatan . '">
                <i class="bi bi-pencil-square"></i> Edit
              </button>
              <button type="button"
                class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-delete-single"
                data-id="' . $id . '"
                data-label="' . $nama . '">
                <i class="bi bi-trash"></i> Hapus
              </button>
            </div>
          </td>';
    echo '</tr>';
  }
}

// meta-row untuk pagination JS
echo '<tr class="meta-row" data-total="' . $totalRows . '" data-page="' . (int)$page . '" data-per="' . (int)$perPage . '"></tr>';
