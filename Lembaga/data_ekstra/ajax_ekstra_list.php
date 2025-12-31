<?php
// pages/ekstra/ajax_ekstra_list.php
require_once '../../koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

// ==========================
// Helper
// ==========================
function e($str)
{
  return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// ==========================
// Mode: cek nama duplikat (JSON)
// GET: action=check_name&nama=...&exclude_id=...
// ==========================
if (isset($_GET['action']) && $_GET['action'] === 'check_name') {
  header('Content-Type: application/json; charset=utf-8');

  $nama = isset($_GET['nama']) ? trim($_GET['nama']) : '';
  $excludeId = isset($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : 0;

  if ($nama === '') {
    echo json_encode(['ok' => true, 'exists' => false]);
    exit;
  }

  // cek case-insensitive (lebih aman)
  if ($excludeId > 0) {
    $sql = "SELECT COUNT(*) AS c
            FROM ekstrakurikuler
            WHERE LOWER(nama_ekstrakurikuler) = LOWER(?)
              AND id_ekstrakurikuler <> ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $nama, $excludeId);
  } else {
    $sql = "SELECT COUNT(*) AS c
            FROM ekstrakurikuler
            WHERE LOWER(nama_ekstrakurikuler) = LOWER(?)";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 's', $nama);
  }

  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($res);
  $count = (int)($row['c'] ?? 0);

  echo json_encode(['ok' => true, 'exists' => ($count > 0)]);
  exit;
}

// ==========================
// Ambil parameter pencarian
// ==========================
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$like   = "%{$search}%";

// ==========================
// Pagination params
// ==========================
$allowedPer = [10, 20, 50, 100, 0]; // ✅ 0 = semua
$perPage = isset($_GET['per']) ? (int)$_GET['per'] : 10;
if (!in_array($perPage, $allowedPer, true)) {
  $perPage = 10;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// ==========================
// Hitung total data
// ==========================
if ($search !== '') {
  $countSql = "SELECT COUNT(*) AS total
               FROM ekstrakurikuler
               WHERE nama_ekstrakurikuler LIKE ?";
  $stmtCount = mysqli_prepare($koneksi, $countSql);
  mysqli_stmt_bind_param($stmtCount, 's', $like);
} else {
  $countSql = "SELECT COUNT(*) AS total
               FROM ekstrakurikuler";
  $stmtCount = mysqli_prepare($koneksi, $countSql);
}

mysqli_stmt_execute($stmtCount);
$resCount  = mysqli_stmt_get_result($stmtCount);
$rowCount  = mysqli_fetch_assoc($resCount);
$totalRows = (int)($rowCount['total'] ?? 0);

// ==========================
// Hitung offset & totalPages
// ==========================
if ($perPage === 0) {
  $totalPages = 1;
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
  // ✅ tampil semua: tanpa LIMIT/OFFSET
  if ($search !== '') {
    $sql = "SELECT id_ekstrakurikuler, nama_ekstrakurikuler
            FROM ekstrakurikuler
            WHERE nama_ekstrakurikuler LIKE ?
            ORDER BY nama_ekstrakurikuler ASC";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 's', $like);
  } else {
    $sql = "SELECT id_ekstrakurikuler, nama_ekstrakurikuler
            FROM ekstrakurikuler
            ORDER BY nama_ekstrakurikuler ASC";
    $stmt = mysqli_prepare($koneksi, $sql);
  }
} else {
  // ✅ normal: pakai LIMIT/OFFSET
  if ($search !== '') {
    $sql = "SELECT id_ekstrakurikuler, nama_ekstrakurikuler
            FROM ekstrakurikuler
            WHERE nama_ekstrakurikuler LIKE ?
            ORDER BY nama_ekstrakurikuler ASC
            LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'sii', $like, $perPage, $offset);
  } else {
    $sql = "SELECT id_ekstrakurikuler, nama_ekstrakurikuler
            FROM ekstrakurikuler
            ORDER BY nama_ekstrakurikuler ASC
            LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $perPage, $offset);
  }
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// ==========================
// Render HTML tbody rows
// ==========================
$no = ($perPage === 0) ? 1 : ($offset + 1);
$rowClass = ($search !== '') ? 'highlight-row' : '';

if ($totalRows === 0) {
  echo '<tr><td colspan="4">Tidak ada data yang cocok.</td></tr>';
} else {
  while ($row = mysqli_fetch_assoc($result)) {
    $id = (int)$row['id_ekstrakurikuler'];
    $namaRaw = (string)($row['nama_ekstrakurikuler'] ?? '');
    $nama = e($namaRaw);

    echo '<tr class="' . $rowClass . '">';
    echo '  <td class="text-center" data-label="Pilih"><input type="checkbox" class="row-check" value="' . $id . '"></td>';
    echo '  <td data-label="No">' . $no++ . '</td>';
    echo '  <td data-label="Nama Ekstrakurikuler">' . $nama . '</td>';
    echo '  <td data-label="Aksi">';
    echo '    <div class="d-flex gap-2 justify-content-center flex-wrap">';
    echo '      <button type="button"
                class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-edit-ekstra"
                data-id="' . $id . '"
                data-nama="' . $nama . '">
                <i class="bi bi-pencil-square"></i> Edit
              </button>';
    echo '      <button type="button"
                class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-delete-single"
                data-id="' . $id . '"
                data-label="' . $nama . '">
                <i class="bi bi-trash"></i> Hapus
              </button>';
    echo '    </div>';
    echo '  </td>';
    echo '</tr>';
  }
}

// ==========================
// Meta row untuk JS (pagination/info)
// ==========================
echo '<tr class="meta-row d-none"
          data-total="' . (int)$totalRows . '"
          data-page="' . (int)$page . '"
          data-per="' . (int)$perPage . '">
      </tr>';
