<?php
// pages/absensi/data_absensi.php

// ====== BACKEND SETUP ======
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);

require_once __DIR__ . '/../../koneksi.php';
mysqli_set_charset($koneksi, 'utf8mb4');

// Session + CSRF (untuk bulk delete & bulk edit)
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

// --------- SEARCH & FILTER AWAL ---------
$search  = isset($_GET['q']) ? trim($_GET['q']) : '';
$like    = "%{$search}%";
$tingkat = isset($_GET['tingkat']) ? trim($_GET['tingkat']) : '';
$idKelasFilter = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;

// valid tingkat
$allowedTingkat = ['', 'X', 'XI', 'XII'];
if (!in_array($tingkat, $allowedTingkat, true)) $tingkat = '';

// --------- PAGINATION AWAL ---------
$allowedPer = [10, 20, 50, 100];
$perPage    = isset($_GET['per']) ? (int)$_GET['per'] : 10;
if (!in_array($perPage, $allowedPer, true)) {
  $perPage = 10;
}

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
 * Build WHERE dinamis
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
 * AMBIL DATA ABSENSI UNTUK TAMPILAN AWAL
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
    a.sakit, a.izin, a.alpha
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

// Info range
if ($totalRows === 0) {
  $shown = 0;
  $pageDisplayCurrent = 0;
  $pageDisplayTotal   = 0;
} else {
  $from = $offset + 1;
  $to   = min($offset + $perPage, $totalRows);
  $shown = $to - $from + 1;
  $pageDisplayCurrent = $page;
  $pageDisplayTotal   = $totalPages;
}

// --------- AMBIL LIST SISWA UNTUK MODAL TAMBAH ---------
$siswa_list = [];
$q = $koneksi->query("
  SELECT s.id_siswa, s.nama_siswa, s.no_induk_siswa, s.no_absen_siswa,
         k.nama_kelas, k.tingkat_kelas
  FROM siswa s
  LEFT JOIN kelas k ON k.id_kelas = s.id_kelas
  ORDER BY s.nama_siswa ASC
");
while ($r = $q->fetch_assoc()) {
  $siswa_list[] = $r;
}

// Data kelas untuk dropdown filter
$kelasAll = [];
$kelasQuery = mysqli_query($koneksi, "SELECT id_kelas, nama_kelas, tingkat_kelas FROM kelas ORDER BY tingkat_kelas ASC, nama_kelas ASC");
while ($k = mysqli_fetch_assoc($kelasQuery)) {
  $kelasAll[] = [
    'id_kelas' => (int)$k['id_kelas'],
    'nama_kelas' => (string)$k['nama_kelas'],
    'tingkat_kelas' => (string)$k['tingkat_kelas'],
  ];
}

// --------- TENTUKAN ALERT BERDASARKAN ?msg= / ?err= ---------
$alertMsg   = '';
$alertClass = '';

if (!empty($_GET['msg'])) {
  $alertMsgRaw = $_GET['msg'];

  if ($alertMsgRaw === 'add_success') {
    $alertMsg   = 'Data absensi berhasil ditambahkan.';
    $alertClass = 'alert-success';
  } elseif ($alertMsgRaw === 'edit_success') {
    $alertMsg   = 'Data absensi berhasil diperbarui.';
    $alertClass = 'alert-success';
  } elseif ($alertMsgRaw === 'delete_success') {
    $alertMsg   = 'Data absensi berhasil dihapus.';
    $alertClass = 'alert-danger';
  } else {
    $alertMsg   = $alertMsgRaw;
    $alertClass = 'alert-success';
  }
}

if (!empty($_GET['err'])) {
  $alertMsg   = $_GET['err'];
  $alertClass = 'alert-danger';
}

include '../../includes/header.php';
?>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <style>
    :root {
      --brand: #0a4db3;
      --brand-600: #083f93;
      --ink: #0f172a;
      --text: #111827;
      --muted: #475569;
      --ring: #cbd5e1;
      --card: #ffffff;
      --thead: #0a4db3;
      --thead-text: #ffffff;
      --card-radius: 14px;
      --success: #16a34a;
      --warn: #f59e0b;
      --danger: #dc2626;
    }

    .content {
      padding: clamp(12px, 2vw, 20px);
      color: var(--text);
    }

    .card {
      border-radius: var(--card-radius);
      border: 1px solid #e8eef6;
      background: var(--card);
    }

    .page-title {
      color: var(--ink);
    }

    .top-bar {
      gap: 12px;
    }

    .search-wrap {
      max-width: 300px;
      width: 100%;
    }

    .searchbox {
      display: flex;
      align-items: center;
      gap: 8px;
      width: 100%;
      border: 1px solid var(--ring);
      border-radius: 10px;
      background: #fff;
      padding: 6px 10px;
    }

    .searchbox .icon {
      color: var(--muted);
    }

    .searchbox input {
      border: none;
      outline: none;
      width: 100%;
      font-size: 14px;
      color: var(--ink);
    }

    .searchbox input::placeholder {
      color: #9aa3af;
    }

    .searchbox:focus-within {
      box-shadow: 0 0 0 3px rgba(10, 77, 179, .15);
    }

    .filter-select {
      border: 1px solid var(--ring);
      border-radius: 10px;
      padding: 6px 12px;
      font-size: 14px;
      color: var(--ink);
      background: #fff;
      height: 38px;
      text-align: center;
      text-align-last: center;
    }

    .filter-select:focus {
      box-shadow: 0 0 0 3px rgba(10, 77, 179, .15);
      border-color: var(--brand);
    }

    .table thead th {
      white-space: nowrap;
      background: var(--thead);
      color: var(--thead-text);
    }

    .table td,
    .table th {
      vertical-align: middle;
      color: var(--text);
    }

    .highlight-row {
      background-color: #d4edda !important;
    }

    #absensiTbody {
      transition: opacity 0.25s ease, transform 0.25s ease;
    }

    #absensiTbody.tbody-loading {
      opacity: 0.4;
      transform: scale(0.995);
    }

    #absensiTbody.tbody-loaded {
      opacity: 1;
      transform: scale(1);
    }

    #absensiTableWrap {
      position: relative;
    }

    .table-loading-overlay {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.7);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s ease;
      z-index: 2;
    }

    .table-loading-overlay.show {
      opacity: 1;
      pointer-events: auto;
    }

    .btn-brand {
      background: #0a4db3 !important;
      border-color: #0a4db3 !important;
      color: #fff !important;
      font-weight: 700;
    }

    .btn-brand:hover {
      background: #083f93 !important;
      border-color: #083f93 !important;
    }

    .btn-warning {
      background: #f0ad4e !important;
      border-color: #eea236 !important;
      color: #fff !important;
    }

    .btn-warning:hover {
      background: #d98d26 !important;
      border-color: #c77e20 !important;
      color: #fff !important;
    }

    .btn-danger {
      background: #d9534f !important;
      border-color: #d43f3a !important;
      color: #fff !important;
    }

    .btn-danger:hover {
      background: #c0392b !important;
      border-color: #a93226 !important;
      color: #fff !important;
    }

    .btn-outline-secondary {
      border-color: #6c757d !important;
      color: #6c757d !important;
      background: transparent !important;
    }

    .btn-outline-secondary:hover {
      background: #e9ecef !important;
      color: #333 !important;
    }

    #perPage {
      border: 1px solid var(--ring);
      border-radius: 10px;
      padding: 6px 30px;
      font-size: 14px;
      color: var(--ink);
      background-color: #fff;
    }

    #perPage:focus {
      box-shadow: 0 0 0 3px rgba(10, 77, 179, .15);
      border-color: var(--brand);
    }

    .page-info-text strong {
      font-size: 0.95rem;
    }

    .alert {
      padding: 12px 14px;
      border-radius: 12px;
      margin-bottom: 20px;
      font-size: 14px;
      transition: opacity 0.4s ease, transform 0.4s ease, max-height 0.4s ease, margin 0.4s ease, padding-top 0.4s ease, padding-bottom 0.4s ease;
      max-height: 200px;
      overflow: hidden;
      position: relative;
    }

    .alert-success {
      background: #e8f8ee;
      border: 1px solid #c8efd9;
      color: #166534;
    }

    .alert-danger {
      background: #fdecec;
      border: 1px solid #f5c2c2;
      color: #991b1b;
    }

    .alert-warning {
      background: #fff7ed;
      border: 1px solid #fed7aa;
      color: #9a3412;
    }

    .alert-hide {
      opacity: 0;
      transform: translateY(-4px);
      max-height: 0;
      margin: 0;
      padding-top: 0;
      padding-bottom: 0;
    }

    .alert .close-btn {
      position: absolute;
      top: 14px;
      right: 14px;
      font-weight: 700;
      cursor: pointer;
      opacity: 0.6;
      font-size: 18px;
      line-height: 1;
    }

    .alert .close-btn:hover {
      opacity: 1;
    }

    /* area alert untuk modal tambah (mirip data_guru) */
    .modal-alert-area {
      margin-bottom: 12px;
    }

    .pager-area {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
      gap: 10px;
    }

    .pager-group {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      flex-wrap: wrap;
      padding: 10px 12px;
      border: 1px solid rgba(0, 0, 0, .12);
      border-radius: 12px;
      background: #fff;
    }

    .pager-group .pagination {
      margin: 0;
      justify-content: center;
    }

    .pager-sep {
      width: 1px;
      height: 34px;
      background: rgba(0, 0, 0, .15);
    }

    .per-select {
      width: 120px;
      min-width: 120px;
    }

    .page-info-center {
      text-align: center;
      width: 100%;
    }

    .thead-sub th {
      background: var(--thead);
      color: var(--thead-text);
      border-top: 0 !important;
    }

    @media (max-width: 520px) {
      table.table thead {
        display: none;
      }

      table.table tbody tr {
        display: block;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        margin-bottom: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .04);
      }

      table.table tbody td {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 12px !important;
        border: 0 !important;
        border-bottom: 1px dashed #edf2f7 !important;
        color: var(--ink);
      }

      table.table tbody td:last-child {
        border-bottom: 0 !important;
        display: block;
      }

      table.table tbody td::before {
        content: attr(data-label);
        font-weight: 700;
        color: var(--ink);
      }

      .table .btn {
        width: 100%;
        margin-top: 6px;
      }

      .search-perpage-row {
        flex-direction: column;
        align-items: stretch !important;
      }

      .pager-sep {
        display: none;
      }

      .pager-group {
        width: 100%;
      }

      .filter-select {
        text-align: left;
        text-align-last: left;
      }
    }
  </style>

  <main class="content">
    <div class="row g-3">
      <div class="col-12">

        <!-- ALERT DI LUAR CARD -->
        <div id="alertAreaTop">
          <?php if ($alertMsg !== '' && $alertClass !== ''): ?>
            <div class="alert <?= htmlspecialchars($alertClass, ENT_QUOTES, 'UTF-8'); ?>">
              <span class="close-btn">&times;</span>
              <?= htmlspecialchars($alertMsg, ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="card shadow-sm">

          <!-- TOP BAR -->
          <div class="top-bar p-3 p-md-4">
            <div class="d-flex flex-column gap-3 w-100">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="page-title mb-0 fw-bold fs-4">Data Absensi Siswa</h5>
              </div>

              <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2">
                <div class="d-flex search-perpage-row align-items-md-center gap-2 flex-grow-1">
                  <div class="search-wrap flex-grow-1">
                    <div class="searchbox" role="search" aria-label="Pencarian absensi">
                      <i class="bi bi-search icon"></i>
                      <input type="text"
                        id="searchInput"
                        placeholder="Ketik untuk mencari"
                        value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
                        autofocus>
                    </div>
                  </div>

                  <select id="filterTingkat" class="filter-select" title="Filter Tingkat">
                    <option value="">Semua Tingkat</option>
                    <option value="X">X</option>
                    <option value="XI">XI</option>
                    <option value="XII">XII</option>
                  </select>

                  <select id="filterKelas" class="filter-select" title="Filter Kelas">
                    <option value="0">Semua Kelas</option>
                    <?php foreach ($kelasAll as $k): ?>
                      <option value="<?= (int)$k['id_kelas'] ?>"
                        data-tingkat="<?= htmlspecialchars($k['tingkat_kelas'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($k['nama_kelas'], ENT_QUOTES, 'UTF-8') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="d-flex justify-content-md-end flex-wrap gap-2 align-items-center">
                  <button type="button"
                    class="btn btn-brand btn-sm d-inline-flex align-items-center gap-2 px-3"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTambahAbsensi">
                    <i class="bi bi-plus-circle"></i> Tambah Absensi
                  </button>

                  <button type="button"
                    class="btn btn-success btn-sm d-inline-flex align-items-center gap-2 px-3"
                    data-bs-toggle="modal"
                    data-bs-target="#modalImportAbsensi">
                    <i class="fa-solid fa-file-arrow-down fa-lg"></i> Import
                  </button>

                  <button id="exportBtn"
                    class="btn btn-success btn-sm d-inline-flex align-items-center gap-2 px-3"
                    type="button">
                    <i class="fa-solid fa-file-arrow-up fa-lg"></i> Export
                  </button>
                </div>
              </div>
            </div>

            <div class="form-check form-switch mt-3 d-flex justify-content-end">
              <input class="form-check-input" type="checkbox" id="toggleEditMode">
              <label class="form-check-label fw-semibold ms-2" for="toggleEditMode">
                Mode Edit
              </label>
            </div>
          </div>

          <div class="card-body pt-0">
            <div class="table-responsive" id="absensiTableWrap">
              <div class="table-loading-overlay" id="tableLoadingOverlay">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span style="font-size:13px;">Sedang memuat data…</span>
              </div>

              <form id="bulkEditForm" action="proses_edit_data_absensi.php" method="post">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">

                <table class="table table-striped table-bordered align-middle mb-0">
                  <thead class="text-center">
                    <tr>
                      <th rowspan="2" style="width:50px;" class="text-center">
                        <input type="checkbox" id="checkAll" title="Pilih Semua">
                      </th>
                      <th rowspan="2" style="width:160px;">NIS</th>
                      <th rowspan="2">Nama Siswa</th>
                      <th rowspan="2" style="width:220px;">Kelas</th>
                      <th rowspan="2" style="width:120px;">Absen</th>
                      <th colspan="3" style="min-width:240px;">Keterangan</th>
                      <th rowspan="2" style="width:140px;">Aksi</th>
                    </tr>
                    <tr class="thead-sub">
                      <th style="width:90px;">Sakit</th>
                      <th style="width:90px;">Izin</th>
                      <th style="width:90px;">Alpha</th>
                    </tr>
                  </thead>

                  <tbody id="absensiTbody" class="text-center tbody-loaded">
                    <?php if ($totalRows === 0): ?>
                      <tr>
                        <td colspan="9">Belum ada data.</td>
                      </tr>
                      <?php else:
                      $rowClass = ($search !== '' || $tingkat !== '' || $idKelasFilter > 0) ? 'highlight-row' : '';
                      while ($row = $result->fetch_assoc()):
                        $id        = (int)$row['id_absensi'];
                        $nama      = htmlspecialchars($row['nama_siswa'] ?? '-', ENT_QUOTES, 'UTF-8');
                        $nis       = htmlspecialchars($row['nis'] ?? '-', ENT_QUOTES, 'UTF-8');
                        $absen     = htmlspecialchars($row['absen'] ?? '-', ENT_QUOTES, 'UTF-8');

                        $kelasNama = (string)($row['nama_kelas'] ?? '-');
                        $kelasTkt  = (string)($row['tingkat_kelas'] ?? '');
                        $kelasTampil = trim($kelasNama);
                        if ($kelasTkt !== '' && stripos($kelasTampil, $kelasTkt) === false) {
                          $kelasTampil = $kelasTkt . ' - ' . $kelasTampil;
                        }
                        $kelasTampil = htmlspecialchars($kelasTampil !== '' ? $kelasTampil : '-', ENT_QUOTES, 'UTF-8');

                        $sakit     = (int)($row['sakit'] ?? 0);
                        $izin      = (int)($row['izin'] ?? 0);
                        $alpha     = (int)($row['alpha'] ?? 0);
                      ?>
                        <tr class="<?= $rowClass; ?>" data-id="<?= $id; ?>">
                          <td class="text-center" data-label="Pilih">
                            <input type="checkbox" class="row-check" value="<?= $id; ?>">
                            <input type="hidden" name="id_absensi[]" value="<?= $id; ?>">
                          </td>

                          <td data-label="NIS" class="text-center"><?= $nis; ?></td>
                          <td data-label="Nama Siswa"><?= $nama; ?></td>
                          <td data-label="Kelas" class="text-center"><?= $kelasTampil; ?></td>
                          <td data-label="Absen" class="text-center"><?= $absen; ?></td>

                          <td data-label="Sakit" class="text-center">
                            <span class="cell-view"><?= $sakit; ?></span>
                            <input type="number" class="form-control form-control-sm cell-input d-none" name="sakit[]" min="0" step="1" value="<?= $sakit; ?>">
                          </td>

                          <td data-label="Izin" class="text-center">
                            <span class="cell-view"><?= $izin; ?></span>
                            <input type="number" class="form-control form-control-sm cell-input d-none" name="izin[]" min="0" step="1" value="<?= $izin; ?>">
                          </td>

                          <td data-label="Alpha" class="text-center">
                            <span class="cell-view"><?= $alpha; ?></span>
                            <input type="number" class="form-control form-control-sm cell-input d-none" name="alpha[]" min="0" step="1" value="<?= $alpha; ?>">
                          </td>

                          <td data-label="Aksi">
                            <button type="button"
                              class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-delete-single"
                              data-id="<?= $id; ?>"
                              data-label="<?= $nama; ?>">
                              <i class="bi bi-trash"></i> Hapus
                            </button>
                          </td>
                        </tr>
                    <?php endwhile;
                    endif; ?>
                  </tbody>
                </table>
              </form>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
              <button type="button" id="bulkDeleteBtn"
                class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-trash3"></i> <span>Hapus Terpilih</span>
              </button>

              <button type="button"
                id="bulkSaveBtn"
                class="btn btn-success btn-sm d-inline-flex align-items-center gap-2"
                form="bulkEditForm"
                disabled>
                <i class="bi bi-check2-circle"></i> Simpan Perubahan
              </button>
            </div>

            <nav aria-label="Page navigation" class="mt-3">
              <div class="pager-area">
                <div class="pager-group">
                  <ul class="pagination mb-0" id="paginationWrap"></ul>
                  <div class="pager-sep" aria-hidden="true"></div>

                  <select id="perPage" class="form-select form-select-sm per-select">
                    <?php foreach ($allowedPer as $opt): ?>
                      <option value="<?= $opt ?>" <?= $perPage === $opt ? 'selected' : '' ?>>
                        <?= $opt ?>/hal
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <p id="pageInfo" class="page-info-text text-muted mb-0 page-info-center">
                  Menampilkan <strong><?= $shown ?></strong> dari <strong><?= $totalRows ?></strong> data •
                  Halaman <strong><?= $pageDisplayCurrent ?></strong> / <strong><?= $pageDisplayTotal ?></strong>
                </p>
              </div>
            </nav>

          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- MODAL TAMBAH ABSENSI -->
  <div class="modal fade" id="modalTambahAbsensi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Data Absensi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>

        <form id="formTambahAbsensi" action="proses_tambah_data_absensi.php" method="POST" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">

          <div class="modal-body">
            <!-- ✅ alert di dalam modal (seperti data_guru) -->
            <div id="modalAlertTambahAbsensi" class="modal-alert-area"></div>

            <div class="mb-3">
              <label class="form-label fw-semibold" for="add_id_siswa">Nama Siswa</label>
              <select id="add_id_siswa" name="id_siswa" class="form-select" required>
                <option value="" disabled selected>-- Pilih dari daftar siswa --</option>
                <?php foreach ($siswa_list as $s):
                  $opt = $s['nama_siswa'];
                  $opt .= $s['no_induk_siswa'] ? " (NIS: {$s['no_induk_siswa']})" : "";
                  if (!empty($s['tingkat_kelas']) && !empty($s['nama_kelas'])) {
                    $opt .= " - {$s['tingkat_kelas']} - {$s['nama_kelas']}";
                  } elseif (!empty($s['nama_kelas'])) {
                    $opt .= " - {$s['nama_kelas']}";
                  }
                  if (!empty($s['no_absen_siswa'])) $opt .= " [Absen: {$s['no_absen_siswa']}]";
                ?>
                  <option value="<?= (int)$s['id_siswa']; ?>"><?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="row g-3">
              <div class="col-sm-4">
                <label class="form-label fw-semibold" for="add_sakit">Sakit</label>
                <input type="number" id="add_sakit" name="sakit" class="form-control" min="0" step="1" value="0" required>
              </div>
              <div class="col-sm-4">
                <label class="form-label fw-semibold" for="add_izin">Izin</label>
                <input type="number" id="add_izin" name="izin" class="form-control" min="0" step="1" value="0" required>
              </div>
              <div class="col-sm-4">
                <label class="form-label fw-semibold" for="add_alpha">Alpha</label>
                <input type="number" id="add_alpha" name="alpha" class="form-control" min="0" step="1" value="0" required>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" data-bs-dismiss="modal">
              <i class="bi bi-x-lg"></i> Batal
            </button>
            <button type="submit" id="btnSubmitTambahAbsensi" class="btn btn-brand d-inline-flex align-items-center gap-2">
              <i class="bi bi-check2-circle"></i> Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- MODAL IMPORT ABSENSI -->
  <div class="modal fade" id="modalImportAbsensi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div>
            <h5 class="modal-title fw-semibold">Import Data Absensi</h5>
            <p class="mb-0 text-muted" style="font-size: 13px;">
              Gunakan template resmi agar susunan kolom sesuai dengan sistem.
            </p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>

        <form id="formImportAbsensi"
          action="proses_import_data_absensi.php"
          method="POST"
          enctype="multipart/form-data"
          autocomplete="off">
          <div class="modal-body pt-3">
            <div class="mb-3 p-3 rounded-3" style="background:#f9fafb;border:1px solid #e5e7eb;">
              <div class="d-flex align-items-start gap-2">
                <div class="mt-1">
                  <i class="fa-solid fa-circle-info" style="color:#0a4db3;"></i>
                </div>
                <div style="font-size:13px;">
                  <strong>Langkah import data absensi:</strong>
                  <ol class="mb-1 ps-3" style="padding-left:18px;">
                    <li>Download template Excel terlebih dahulu.</li>
                    <li>Isi data sesuai kolom yang tersedia.</li>
                    <li>Upload kembali file Excel tersebut di form ini.</li>
                  </ol>
                  <span class="text-muted">
                    Struktur kolom template:
                    <strong>A: No</strong>,
                    <strong>B: NIS</strong>,
                    <strong>C: Nama Siswa</strong>,
                    <strong>D: Kelas</strong>,
                    <strong>E: Absen</strong>,
                    <strong>F: Sakit</strong>,
                    <strong>G: Izin</strong>,
                    <strong>H: Alpha</strong>.
                    Data akan disambungkan berdasarkan <strong>NIS</strong>, dan <strong>duplikat akan ditolak</strong>.
                  </span>
                </div>
              </div>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
              <span class="text-muted" style="font-size:13px;">Klik tombol di samping untuk mengunduh template Excel.</span>
              <a href="../../assets/templates/template_data_absensi.xlsx"
                class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2"
                download>
                <i class="fa-solid fa-file-excel"></i>
                <span>Download Template</span>
              </a>
            </div>

            <hr class="my-2">

            <div class="mb-2">
              <label for="excelFileAbsensi" class="form-label fw-semibold mb-1">Upload File Excel</label>
              <div class="position-relative d-flex align-items-center">
                <input type="file"
                  class="form-control"
                  id="excelFileAbsensi"
                  name="excel_file"
                  accept=".xlsx,.xls"
                  style="padding-right:35px;"
                  required
                  onchange="toggleClearButtonAbsensiImport()">

                <button type="button"
                  id="clearFileBtnAbsensiImport"
                  onclick="clearFileAbsensiImport()"
                  title="Hapus file"
                  style="position:absolute;right:10px;background:none;border:none;color:#6c757d;font-size:20px;line-height:1;display:none;cursor:pointer;">
                  &times;
                </button>
              </div>
              <small class="text-muted d-block mt-1" style="font-size:12px;">
                Format yang didukung: <strong>.xlsx</strong> atau <strong>.xls</strong>.
                Pastikan tidak mengubah urutan kolom di template.
              </small>
            </div>
          </div>

          <div class="modal-footer d-flex justify-content-between">
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" data-bs-dismiss="modal">
              <i class="fa fa-times"></i> Batal
            </button>
            <button type="submit" id="btnSubmitImportAbsensi" class="btn btn-warning d-inline-flex align-items-center gap-2">
              <i class="fas fa-upload"></i> Upload &amp; Proses
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- MODAL KONFIRMASI HAPUS -->
  <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-exclamation-triangle text-danger me-2"></i> Konfirmasi Hapus
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <p id="confirmDeleteBody" class="mb-0">Yakin ingin menghapus data ini?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-danger d-inline-flex align-items-center gap-2" id="confirmDeleteBtn">
            <i class="bi bi-trash"></i> Hapus
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    function toggleClearButtonAbsensiImport() {
      const fileInput = document.getElementById('excelFileAbsensi');
      const clearBtn = document.getElementById('clearFileBtnAbsensiImport');
      if (!fileInput || !clearBtn) return;
      clearBtn.style.display = fileInput.files.length > 0 ? 'block' : 'none';
    }

    function clearFileAbsensiImport() {
      const fileInput = document.getElementById('excelFileAbsensi');
      const clearBtn = document.getElementById('clearFileBtnAbsensiImport');
      if (!fileInput || !clearBtn) return;
      fileInput.value = '';
      clearBtn.style.display = 'none';
    }
  </script>

  <script>
    (function() {
      const input = document.getElementById('searchInput');
      const tbody = document.getElementById('absensiTbody');
      const tableWrap = document.getElementById('absensiTableWrap');
      const loadingOverlay = document.getElementById('tableLoadingOverlay');

      const filterTingkat = document.getElementById('filterTingkat');
      const filterKelas = document.getElementById('filterKelas');

      const checkAll = document.getElementById('checkAll');
      const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
      const perPageSelect = document.getElementById('perPage');

      const paginationUl = document.getElementById('paginationWrap');
      const pageInfo = document.getElementById('pageInfo');

      const toggleEditMode = document.getElementById('toggleEditMode');
      const bulkSaveBtn = document.getElementById('bulkSaveBtn');

      const csrfToken = '<?= htmlspecialchars($csrf, ENT_QUOTES, "UTF-8"); ?>';

      const confirmModalEl = document.getElementById('confirmDeleteModal');
      const confirmBodyEl = document.getElementById('confirmDeleteBody');
      const confirmBtn = document.getElementById('confirmDeleteBtn');

      let typingTimer;
      const debounceMs = 250;
      let currentController = null;

      let currentQuery = '<?= htmlspecialchars($search, ENT_QUOTES, "UTF-8"); ?>';
      let currentPage = <?= (int)$page ?>;
      let currentPerPage = <?= (int)$perPage ?>;
      let currentTotalRows = <?= (int)$totalRows ?>;

      let currentTingkat = '<?= htmlspecialchars($tingkat, ENT_QUOTES, "UTF-8"); ?>';
      let currentKelas = <?= (int)$idKelasFilter ?>;

      let pendingDeleteHandler = null;
      let editMode = false;

      // ========= ALERT helpers (top & modal) =========
      function escapeHtml(str) {
        return String(str ?? '')
          .replaceAll('&', '&amp;')
          .replaceAll('<', '&lt;')
          .replaceAll('>', '&gt;')
          .replaceAll('"', '&quot;')
          .replaceAll("'", "&#039;");
      }

      function wireAlert(el, autoMs = 4000) {
        if (!el) return;
        const timer = setTimeout(() => el.classList.add('alert-hide'), autoMs);
        const close = el.querySelector('.close-btn');
        if (close) {
          close.addEventListener('click', (e) => {
            e.preventDefault();
            el.classList.add('alert-hide');
            clearTimeout(timer);
          });
        }
      }

      function showTopAlert(type, message) {
        const area = document.getElementById('alertAreaTop');
        if (!area) return;

        const cls = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');

        const div = document.createElement('div');
        div.className = `alert ${cls}`;
        div.innerHTML = `<span class="close-btn">&times;</span> ${escapeHtml(message)}`;

        area.prepend(div);
        wireAlert(div, 4000);
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      }

      function showModalAlert(containerId, type, message) {
        const box = document.getElementById(containerId);
        if (!box) return;

        const cls = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');
        box.innerHTML = `
          <div class="alert ${cls}">
            <span class="close-btn">&times;</span>
            ${escapeHtml(message)}
          </div>
        `;
        const alertEl = box.querySelector('.alert');
        wireAlert(alertEl, 5000);
      }

      function disableBtn(btn, loading) {
        if (!btn) return;
        btn.disabled = !!loading;
        if (loading) {
          btn.dataset.oldHtml = btn.innerHTML;
          btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span> Memproses...`;
        } else if (btn.dataset.oldHtml) {
          btn.innerHTML = btn.dataset.oldHtml;
          delete btn.dataset.oldHtml;
        }
      }

      async function postFormAjax(form, btn, modalAlertId, onSuccess) {
        if (!form) return;

        if (!form.checkValidity()) {
          form.reportValidity();
          return;
        }

        disableBtn(btn, true);

        try {
          const fd = new FormData(form);
          const res = await fetch(form.getAttribute('action'), {
            method: 'POST',
            body: fd,
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          });

          const data = await res.json().catch(() => null);
          if (!data) {
            showModalAlert(modalAlertId, 'danger', 'Respon server tidak valid.');
            disableBtn(btn, false);
            return;
          }

          if (data.ok) {
            onSuccess && onSuccess(data);
          } else {
            showModalAlert(modalAlertId, data.type || 'danger', data.msg || 'Terjadi kesalahan.');
          }
        } catch (err) {
          showModalAlert(modalAlertId, 'danger', 'Gagal terhubung ke server.');
          console.error(err);
        } finally {
          disableBtn(btn, false);
        }
      }

      function scrollToTable() {
        if (!tableWrap) return;
        tableWrap.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }

      function showDeleteConfirm(message, handler) {
        if (!confirmModalEl || !confirmBodyEl || !confirmBtn) {
          if (confirm(message)) handler();
          return;
        }

        confirmBodyEl.textContent = message;
        pendingDeleteHandler = handler;

        confirmBtn.onclick = function() {
          if (pendingDeleteHandler) pendingDeleteHandler();
          if (typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(confirmModalEl).hide();
          }
        };

        if (typeof bootstrap !== 'undefined') {
          bootstrap.Modal.getOrCreateInstance(confirmModalEl).show();
        } else {
          if (confirm(message)) handler();
        }
      }

      function getRowCheckboxes() {
        return Array.from(document.querySelectorAll('.row-check'));
      }

      function updateBulkUI() {
        const boxes = getRowCheckboxes();
        const total = boxes.length;
        const checked = boxes.filter(b => b.checked).length;

        bulkDeleteBtn.disabled = (checked === 0);

        if (total === 0) {
          checkAll.checked = false;
          checkAll.indeterminate = false;
        } else if (checked === 0) {
          checkAll.checked = false;
          checkAll.indeterminate = false;
        } else if (checked === total) {
          checkAll.checked = true;
          checkAll.indeterminate = false;
        } else {
          checkAll.checked = false;
          checkAll.indeterminate = true;
        }
      }

      function attachCheckboxEvents() {
        const boxes = getRowCheckboxes();
        boxes.forEach(box => box.addEventListener('change', updateBulkUI));
        updateBulkUI();
      }

      function applyEditModeToTable() {
        const rows = document.querySelectorAll('#absensiTbody tr[data-id]');
        rows.forEach(row => {
          const views = row.querySelectorAll('.cell-view');
          const inputs = row.querySelectorAll('.cell-input');
          if (editMode) {
            views.forEach(v => v.classList.add('d-none'));
            inputs.forEach(i => i.classList.remove('d-none'));
          } else {
            views.forEach(v => v.classList.remove('d-none'));
            inputs.forEach(i => i.classList.add('d-none'));
          }
        });
      }

      function attachSingleDeleteEvents() {
        const buttons = document.querySelectorAll('.btn-delete-single');
        buttons.forEach(btn => {
          btn.addEventListener('click', (e) => {
            e.preventDefault();
            const id = btn.getAttribute('data-id');
            const label = btn.getAttribute('data-label') || 'data ini';
            if (!id) return;

            showDeleteConfirm(`Yakin ingin menghapus absensi "${label}"?`, () => {
              const form = document.createElement('form');
              form.method = 'post';
              form.action = 'hapus_data_absensi.php';

              const csrfInput = document.createElement('input');
              csrfInput.type = 'hidden';
              csrfInput.name = 'csrf';
              csrfInput.value = csrfToken;
              form.appendChild(csrfInput);

              const idInput = document.createElement('input');
              idInput.type = 'hidden';
              idInput.name = 'id';
              idInput.value = id;
              form.appendChild(idInput);

              document.body.appendChild(form);
              form.submit();
            });
          });
        });
      }

      function filterKelasOptionsByTingkat(tingkatVal) {
        if (!filterKelas) return;

        const opts = Array.from(filterKelas.querySelectorAll('option'));
        opts.forEach(opt => {
          const v = parseInt(opt.value || '0', 10);
          if (v === 0) {
            opt.hidden = false;
            return;
          }
          const t = opt.getAttribute('data-tingkat') || '';
          opt.hidden = (tingkatVal !== '' && t !== tingkatVal);
        });

        const selectedOpt = filterKelas.querySelector('option:checked');
        if (selectedOpt && selectedOpt.hidden) {
          filterKelas.value = '0';
          currentKelas = 0;
        }
      }

      function buildPagination(totalRows, page, perPage) {
        currentTotalRows = totalRows;
        currentPage = page;
        currentPerPage = perPage;

        const totalPages = Math.max(1, Math.ceil(totalRows / perPage));
        if (page > totalPages) page = totalPages;

        let shown;
        if (totalRows === 0) {
          shown = 0;
        } else {
          const from = (page - 1) * perPage + 1;
          const to = Math.min(page * perPage, totalRows);
          shown = to - from + 1;
        }

        const pageDisplayCurrent = totalRows === 0 ? 0 : page;
        const pageDisplayTotal = totalRows === 0 ? 0 : totalPages;

        pageInfo.innerHTML =
          `Menampilkan <strong>${shown}</strong> dari <strong>${totalRows}</strong> data • Halaman <strong>${pageDisplayCurrent}</strong> / <strong>${pageDisplayTotal}</strong>`;

        const makeLi = (disabled, target, text, active = false) => {
          const cls = ['page-item', disabled ? 'disabled' : '', active ? 'active' : ''].filter(Boolean).join(' ');
          const aAttr = disabled ? 'tabindex="-1"' : `data-page="${target}"`;
          return `<li class="${cls}"><a class="page-link" href="#" ${aAttr}>${text}</a></li>`;
        };

        let html = '';
        const isFirst = (page <= 1);
        const isLast = (page >= totalPages);

        html += makeLi(isFirst, 1, '« First');
        html += makeLi(isFirst, Math.max(1, page - 1), '‹ Prev');

        const start = Math.max(1, page - 2);
        const end = Math.min(totalPages, page + 2);
        for (let i = start; i <= end; i++) {
          html += makeLi(false, i, String(i), i === page);
        }

        html += makeLi(isLast, Math.min(totalPages, page + 1), 'Next ›');
        html += makeLi(isLast, totalPages, 'Last »');

        paginationUl.innerHTML = html;

        paginationUl.querySelectorAll('a[data-page]').forEach(a => {
          a.addEventListener('click', (e) => {
            e.preventDefault();
            const target = parseInt(a.getAttribute('data-page') || '1', 10);
            if (isNaN(target) || target < 1 || target === currentPage) return;
            doSearch(currentQuery, target, currentPerPage, currentTingkat, currentKelas, true);
          });
        });

        if (perPageSelect) perPageSelect.value = String(perPage);
      }

      checkAll.addEventListener('change', () => {
        const boxes = getRowCheckboxes();
        boxes.forEach(b => {
          b.checked = checkAll.checked;
        });
        updateBulkUI();
      });

      bulkDeleteBtn.addEventListener('click', () => {
        const boxes = getRowCheckboxes().filter(b => b.checked);
        if (boxes.length === 0) return;

        const count = boxes.length;
        const form = document.createElement('form');
        form.method = 'post';
        form.action = 'hapus_data_absensi.php';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        boxes.forEach(box => {
          const inp = document.createElement('input');
          inp.type = 'hidden';
          inp.name = 'ids[]';
          inp.value = box.value;
          form.appendChild(inp);
        });

        showDeleteConfirm(`Yakin ingin menghapus ${count} data absensi terpilih?`, () => {
          document.body.appendChild(form);
          form.submit();
        });
      });

      toggleEditMode.addEventListener('change', () => {
        editMode = toggleEditMode.checked;
        applyEditModeToTable();
        bulkSaveBtn.disabled = !editMode;
      });

      bulkSaveBtn.addEventListener('click', (e) => {
        if (!editMode) {
          e.preventDefault();
          return;
        }
        const form = document.getElementById('bulkEditForm');
        if (!form) return;
        form.submit();
      });

      function setLoading(useScroll) {
        if (useScroll) scrollToTable();
        if (tbody) {
          tbody.classList.remove('tbody-loaded');
          tbody.classList.add('tbody-loading');
        }
        if (loadingOverlay) loadingOverlay.classList.add('show');
      }

      function finishLoading() {
        if (loadingOverlay) loadingOverlay.classList.remove('show');
        if (!tbody) return;
        tbody.classList.remove('tbody-loading');
        void tbody.offsetHeight;
        tbody.classList.add('tbody-loaded');
      }

      function doSearch(query, page, perPage, tingkat, kelas, fromPaginationOrPerpage = false) {
        setLoading(fromPaginationOrPerpage);

        if (currentController) currentController.abort();
        currentController = new AbortController();

        currentQuery = query || '';
        currentTingkat = tingkat || '';
        currentKelas = parseInt(kelas || 0, 10) || 0;

        const params = new URLSearchParams({
          q: currentQuery,
          page: page || 1,
          per: perPage || currentPerPage || 10,
          tingkat: currentTingkat,
          kelas: String(currentKelas || 0),
        });

        fetch('ajax_absensi_list.php?' + params.toString(), {
            method: 'GET',
            signal: currentController.signal,
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          })
          .then(r => {
            if (!r.ok) throw new Error(r.status);
            return r.text();
          })
          .then(html => {
            tbody.innerHTML = html;

            const metaRow = tbody.querySelector('.meta-row');
            if (metaRow) {
              const total = parseInt(metaRow.getAttribute('data-total') || '0', 10);
              const pg = parseInt(metaRow.getAttribute('data-page') || '1', 10);
              const pp = parseInt(metaRow.getAttribute('data-per') || String(currentPerPage), 10);
              metaRow.parentNode.removeChild(metaRow);

              buildPagination(
                isNaN(total) ? 0 : total,
                isNaN(pg) ? 1 : pg,
                isNaN(pp) ? currentPerPage : pp
              );
            }

            attachCheckboxEvents();
            attachSingleDeleteEvents();
            applyEditModeToTable();
            finishLoading();
          })
          .catch(e => {
            if (e.name === 'AbortError') return;
            tbody.innerHTML = `<tr><td colspan="9">Gagal memuat data.</td></tr>`;
            finishLoading();
            console.error(e);
          });
      }

      input.addEventListener('input', () => {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
          doSearch(input.value, 1, currentPerPage, currentTingkat, currentKelas, false);
        }, debounceMs);
      });

      if (perPageSelect) {
        perPageSelect.addEventListener('change', () => {
          const val = parseInt(perPageSelect.value || '10', 10);
          if (isNaN(val) || val <= 0) return;
          currentPerPage = val;
          doSearch(currentQuery, 1, currentPerPage, currentTingkat, currentKelas, true);
        });
      }

      if (filterTingkat) {
        filterTingkat.addEventListener('change', () => {
          const v = filterTingkat.value || '';
          currentTingkat = v;
          filterKelasOptionsByTingkat(currentTingkat);
          doSearch(currentQuery, 1, currentPerPage, currentTingkat, currentKelas, true);
        });
      }

      if (filterKelas) {
        filterKelas.addEventListener('change', () => {
          const v = parseInt(filterKelas.value || '0', 10) || 0;
          currentKelas = v;
          doSearch(currentQuery, 1, currentPerPage, currentTingkat, currentKelas, true);
        });
      }

      // ========= AJAX submit untuk modal tambah =========
      const formTambah = document.getElementById('formTambahAbsensi');
      const btnTambah = document.getElementById('btnSubmitTambahAbsensi');
      const modalTambahEl = document.getElementById('modalTambahAbsensi');

      if (formTambah) {
        formTambah.addEventListener('submit', (e) => {
          e.preventDefault();
          postFormAjax(formTambah, btnTambah, 'modalAlertTambahAbsensi', (data) => {
            // sukses -> tutup modal, reset, reload tabel, alert di atas
            if (typeof bootstrap !== 'undefined' && modalTambahEl) {
              bootstrap.Modal.getOrCreateInstance(modalTambahEl).hide();
            }
            formTambah.reset();
            document.getElementById('modalAlertTambahAbsensi').innerHTML = '';
            doSearch(currentQuery, 1, currentPerPage, currentTingkat, currentKelas, true);
            showTopAlert(data.type || 'success', data.msg || 'Berhasil.');
          });
        });
      }

      // init
      attachCheckboxEvents();
      attachSingleDeleteEvents();
      buildPagination(currentTotalRows, currentPage, currentPerPage);

      if (input) input.value = currentQuery;

      if (filterTingkat) filterTingkat.value = currentTingkat;
      if (filterKelas) filterKelas.value = String(currentKelas || 0);
      filterKelasOptionsByTingkat(currentTingkat);

      applyEditModeToTable();
      if (tbody) tbody.classList.add('tbody-loaded');
    })();
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const alerts = document.querySelectorAll('.alert');
      if (!alerts.length) return;

      alerts.forEach(alert => {
        const timer = setTimeout(() => {
          alert.classList.add('alert-hide');
        }, 4000);

        const close = alert.querySelector('.close-btn');
        if (close) {
          close.addEventListener('click', (e) => {
            e.preventDefault();
            alert.classList.add('alert-hide');
            clearTimeout(timer);
          });
        }
      });
    });
  </script>

  <?php include '../../includes/footer.php'; ?>
</body>