<?php
// pages/ekstra/ajax_ekstra_list.php
require_once '../../koneksi.php';

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

// Hitung total
if ($search !== '') {
    $countSql = "SELECT COUNT(*) AS total FROM ekstrakurikuler WHERE nama_ekstrakurikuler LIKE ?";
    $stmtCount = mysqli_prepare($koneksi, $countSql);
    mysqli_stmt_bind_param($stmtCount, 's', $like);
} else {
    $countSql = "SELECT COUNT(*) AS total FROM ekstrakurikuler";
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

// Ambil data
if ($search !== '') {
    $sql = "
    SELECT id_ekstrakurikuler, nama_ekstrakurikuler
    FROM ekstrakurikuler
    WHERE nama_ekstrakurikuler LIKE ?
    ORDER BY nama_ekstrakurikuler ASC
    LIMIT ? OFFSET ?
  ";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'sii', $like, $perPage, $offset);
} else {
    $sql = "
    SELECT id_ekstrakurikuler, nama_ekstrakurikuler
    FROM ekstrakurikuler
    ORDER BY nama_ekstrakurikuler ASC
    LIMIT ? OFFSET ?
  ";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $perPage, $offset);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

header('Content-Type: text/html; charset=utf-8');

$rowClass = ($search !== '') ? 'highlight-row' : '';
$no = $offset + 1;

if ($totalRows === 0) {
    echo '<tr><td colspan="4">Tidak ada data yang cocok.</td></tr>';
} else {
    while ($row = mysqli_fetch_assoc($result)) {
        $id   = (int)$row['id_ekstrakurikuler'];
        $nama = htmlspecialchars($row['nama_ekstrakurikuler'], ENT_QUOTES, 'UTF-8');

        echo '<tr class="' . $rowClass . '">';
        echo '<td class="text-center" data-label="Pilih">
            <input type="checkbox" class="row-check" value="' . $id . '">
          </td>';
        echo '<td data-label="No">' . $no++ . '</td>';
        echo '<td data-label="Nama Ekstrakurikuler">' . $nama . '</td>';
        echo '<td data-label="Aksi">
            <div class="d-flex gap-2 justify-content-center flex-wrap">
              <button type="button"
                class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-edit-ekstra"
                data-id="' . $id . '"
                data-nama="' . $nama . '">
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
echo '<tr class="meta-row" data-total="' . $totalRows . '" data-page="' . $page . '" data-per="' . $perPage . '"></tr>';
