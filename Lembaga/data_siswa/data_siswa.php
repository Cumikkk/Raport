<?php
// pages/siswa/data_siswa.php
require_once '../../koneksi.php';
include '../../includes/header.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

// Session + CSRF untuk bulk delete
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

// Search & filter awal
$search  = isset($_GET['q']) ? trim($_GET['q']) : '';
$like    = "%{$search}%";
$tingkat = isset($_GET['tingkat']) ? trim($_GET['tingkat']) : '';
$idKelasFilter = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;

// valid tingkat
$allowedTingkat = ['', 'X', 'XI', 'XII'];
if (!in_array($tingkat, $allowedTingkat, true)) $tingkat = '';

// ✅ tambah opsi 0 = semua
$allowedPer = [10, 20, 50, 100, 0];
$perPage = isset($_GET['per']) ? (int)$_GET['per'] : 10;
if (!in_array($perPage, $allowedPer, true)) $perPage = 10;

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
if (!empty($where)) {
  $whereSql = ' WHERE ' . implode(' AND ', $where);
}

// Hitung total data
$countSql = "
  SELECT COUNT(*) AS total
  FROM siswa s
  LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
  $whereSql
";
$stmtCount = mysqli_prepare($koneksi, $countSql);
bindParamsDynamic($stmtCount, $types, $params);
mysqli_stmt_execute($stmtCount);
$resCount  = mysqli_stmt_get_result($stmtCount);
$rowCount  = mysqli_fetch_assoc($resCount);
$totalRows = (int)($rowCount['total'] ?? 0);

// ✅ Pagination: kalau perPage=0 berarti tampil semua
if ($perPage === 0) {
  $totalPages = 1;
  $page = 1;
  $offset = 0;
} else {
  $totalPages = max(1, (int)ceil($totalRows / $perPage));
  if ($page > $totalPages) $page = $totalPages;
  $offset = ($page - 1) * $perPage;
}

// Ambil data siswa untuk tampilan awal
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
$result = mysqli_stmt_get_result($stmt);

// Info range
if ($totalRows === 0) {
  $shown = 0;
  $pageDisplayCurrent = 0;
  $pageDisplayTotal   = 0;
} else {
  if ($perPage === 0) {
    $shown = $totalRows;
    $pageDisplayCurrent = 1;
    $pageDisplayTotal   = 1;
  } else {
    $from = $offset + 1;
    $to   = min($offset + $perPage, $totalRows);
    $shown = $to - $from + 1;
    $pageDisplayCurrent = $page;
    $pageDisplayTotal   = $totalPages;
  }
}

// Data kelas untuk dropdown + filter
$kelasAll = [];
$kelasQuery = mysqli_query($koneksi, "SELECT id_kelas, nama_kelas, tingkat_kelas FROM kelas ORDER BY tingkat_kelas ASC, nama_kelas ASC");
while ($k = mysqli_fetch_assoc($kelasQuery)) {
  $kelasAll[] = [
    'id_kelas' => (int)$k['id_kelas'],
    'nama_kelas' => (string)$k['nama_kelas'],
    'tingkat_kelas' => (string)$k['tingkat_kelas'],
  ];
}
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
      padding-bottom: 260px;
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

    #siswaTbody {
      transition: opacity .25s ease, transform .25s ease;
    }

    #siswaTbody.tbody-loading {
      opacity: .4;
      transform: scale(.995);
    }

    #siswaTbody.tbody-loaded {
      opacity: 1;
      transform: scale(1);
    }

    #siswaTableWrap {
      position: relative;
    }

    .table-loading-overlay {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, .7);
      opacity: 0;
      pointer-events: none;
      transition: opacity .2s ease;
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
      font-size: .95rem;
    }

    /* =========================
      [ALERT PACK TEMPLATE] (DATA SISWA)
    ========================= */
    .dk-alert {
      padding: 12px 14px;
      border-radius: 12px;
      margin-bottom: 12px;
      font-size: 14px;
      max-height: 220px;
      overflow: hidden;
      position: relative;
      opacity: 0;
      transform: translateY(-10px);
      transition: opacity .35s ease, transform .35s ease,
        max-height .35s ease, margin .35s ease, padding .35s ease;
    }

    .dk-alert.dk-show {
      opacity: 1;
      transform: translateY(0)
    }

    .dk-alert.dk-hide {
      opacity: 0;
      transform: translateY(-6px);
      max-height: 0;
      margin: 0;
      padding-top: 0;
      padding-bottom: 0;
    }

    .dk-alert-success {
      background: #e8f8ee;
      border: 1px solid #c8efd9;
      color: #166534;
    }

    .dk-alert-danger {
      background: #fdecec;
      border: 1px solid #f5c2c2;
      color: #991b1b;
    }

    .dk-alert-warning {
      background: #fff7ed;
      border: 1px solid #fed7aa;
      color: #9a3412;
    }

    .dk-alert .close-btn {
      position: absolute;
      top: 14px;
      right: 14px;
      font-weight: 800;
      cursor: pointer;
      opacity: .6;
      font-size: 18px;
      line-height: 1;
      user-select: none;
    }

    .dk-alert .close-btn:hover {
      opacity: 1;
    }

    @keyframes dkPulseIn {
      0% {
        transform: translateY(-10px);
        opacity: 0
      }

      70% {
        transform: translateY(2px);
        opacity: 1
      }

      100% {
        transform: translateY(0);
        opacity: 1
      }
    }

    .dk-alert.dk-pulse {
      animation: dkPulseIn .28s ease;
    }

    #alertAreaTop {
      position: relative;
    }

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

    @media (max-width:520px) {
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
        font-weight: 800;
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

        <!-- ========== ALERT TOP AREA (TEMPLATE) ========== -->
        <div id="alertAreaTop">
          <?php
          // ✅ PESAN HAPUS DISIMPAN DI SESSION & BOLEH MUNCUL LAGI SAAT REFRESH
          $flashDeleted = '';
          if (!empty($_SESSION['flash_deleted_msg'])) {
            $flashDeleted = (string)$_SESSION['flash_deleted_msg'];
            // ❌ JANGAN di-unset, biar refresh tetap muncul pesan yang benar
            // unset($_SESSION['flash_deleted_msg']);
          }
          ?>

          <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] === 'success'): ?>
              <div class="dk-alert dk-alert-success" data-auto-hide="4000">
                <span class="close-btn">&times;</span>
                <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>
                <?= htmlspecialchars($_GET['msg'] ?? 'Operasi berhasil.', ENT_QUOTES, 'UTF-8'); ?>
              </div>

            <?php elseif ($_GET['status'] === 'deleted'): ?>
              <div class="dk-alert dk-alert-success" data-auto-hide="4000">
                <span class="close-btn">&times;</span>
                <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>
                <?= htmlspecialchars($flashDeleted !== '' ? $flashDeleted : ($_GET['msg'] ?? 'Data berhasil dihapus.'), ENT_QUOTES, 'UTF-8'); ?>
              </div>

            <?php else: ?>
              <div class="dk-alert dk-alert-danger" data-auto-hide="4000">
                <span class="close-btn">&times;</span>
                <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
                <?= htmlspecialchars($_GET['msg'] ?? 'Terjadi kesalahan.', ENT_QUOTES, 'UTF-8'); ?>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>

        <div class="card shadow-sm">
          <div class="top-bar p-3 p-md-4">
            <div class="d-flex flex-column gap-3 w-100">
              <div>
                <h5 class="page-title mb-0 fw-bold fs-4">Data Siswa</h5>
              </div>

              <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2">
                <div class="d-flex search-perpage-row align-items-md-center gap-2 flex-grow-1">
                  <div class="search-wrap flex-grow-1">
                    <div class="searchbox" role="search" aria-label="Pencarian siswa">
                      <i class="bi bi-search icon"></i>
                      <input type="text" id="searchInput" placeholder="Ketik untuk mencari" autofocus>
                    </div>
                  </div>

                  <!-- ✅ Filter Tingkat -->
                  <select id="filterTingkat" class="filter-select" title="Filter Tingkat">
                    <option value="">Semua Tingkat</option>
                    <option value="X">X</option>
                    <option value="XI">XI</option>
                    <option value="XII">XII</option>
                  </select>

                  <!-- ✅ Filter Kelas -->
                  <select id="filterKelas" class="filter-select" title="Filter Kelas">
                    <option value="0">Semua Kelas</option>
                    <?php foreach ($kelasAll as $k): ?>
                      <option value="<?= (int)$k['id_kelas'] ?>" data-tingkat="<?= htmlspecialchars($k['tingkat_kelas'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($k['nama_kelas'], ENT_QUOTES, 'UTF-8') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="d-flex justify-content-md-end flex-wrap gap-2">
                  <button type="button"
                    class="btn btn-brand btn-sm d-inline-flex align-items-center gap-2 px-3"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTambahSiswa">
                    <i class="bi bi-person-plus"></i> Tambah Siswa
                  </button>

                  <button type="button"
                    class="btn btn-success btn-sm d-inline-flex align-items-center gap-2 px-3"
                    data-bs-toggle="modal"
                    data-bs-target="#modalImportSiswa">
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
          </div>

          <div class="card-body pt-0">
            <div class="table-responsive" id="siswaTableWrap">
              <div class="table-loading-overlay" id="tableLoadingOverlay">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span style="font-size:13px;">Sedang memuat data…</span>
              </div>

              <table class="table table-striped table-bordered align-middle mb-0">
                <thead class="text-center">
                  <tr>
                    <th style="width:50px;" class="text-center">
                      <input type="checkbox" id="checkAll" title="Pilih Semua">
                    </th>
                    <th style="width:160px;">NIS</th>
                    <th>Nama Siswa</th>
                    <th style="width:220px;">Kelas</th>
                    <th style="width:120px;">Absen</th>
                    <th style="width:220px;">Aksi</th>
                  </tr>
                </thead>

                <tbody id="siswaTbody" class="text-center tbody-loaded">
                  <?php if ($totalRows === 0): ?>
                    <tr>
                      <td colspan="6">Belum ada data.</td>
                    </tr>
                    <?php else:
                    $rowClass = ($search !== '') ? 'highlight-row' : '';
                    while ($row = mysqli_fetch_assoc($result)):
                    ?>
                      <tr class="<?= $rowClass; ?>">
                        <td class="text-center" data-label="Pilih">
                          <input type="checkbox" class="row-check" value="<?= (int)$row['id_siswa'] ?>">
                        </td>

                        <td data-label="NIS" class="text-center"><?= htmlspecialchars($row['no_induk_siswa']) ?></td>
                        <td data-label="Nama"><?= htmlspecialchars($row['nama_siswa']) ?></td>
                        <td data-label="Kelas" class="text-center"><?= htmlspecialchars($row['nama_kelas'] ?? '-') ?></td>
                        <td data-label="Absen" class="text-center"><?= htmlspecialchars($row['no_absen_siswa']) ?></td>

                        <td data-label="Aksi">
                          <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <button type="button"
                              class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-edit-siswa"
                              data-id="<?= (int)$row['id_siswa'] ?>"
                              data-nama="<?= htmlspecialchars($row['nama_siswa'], ENT_QUOTES, 'UTF-8') ?>"
                              data-nis="<?= htmlspecialchars($row['no_induk_siswa'], ENT_QUOTES, 'UTF-8') ?>"
                              data-absen="<?= htmlspecialchars($row['no_absen_siswa'], ENT_QUOTES, 'UTF-8') ?>"
                              data-id_kelas="<?= (int)($row['id_kelas'] ?? 0) ?>">
                              <i class="bi bi-pencil-square"></i> Edit
                            </button>

                            <button type="button"
                              class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-delete-single"
                              data-id="<?= (int)$row['id_siswa'] ?>"
                              data-label="<?= htmlspecialchars($row['nama_siswa'], ENT_QUOTES, 'UTF-8') ?>">
                              <i class="bi bi-trash"></i> Hapus
                            </button>
                          </div>
                        </td>
                      </tr>
                  <?php endwhile;
                  endif; ?>
                </tbody>
              </table>
            </div>

            <div class="mt-3 d-flex justify-content-start">
              <button type="button" id="bulkDeleteBtn"
                class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1"
                disabled>
                <i class="bi bi-trash3"></i> <span>Hapus Terpilih</span>
              </button>
            </div>

            <nav aria-label="Page navigation" class="mt-3">
              <div class="pager-area">
                <div class="pager-group">
                  <ul class="pagination mb-0" id="paginationWrap"></ul>
                  <div class="pager-sep" aria-hidden="true"></div>

                  <select id="perPage" class="form-select form-select-sm per-select">
                    <?php foreach ($allowedPer as $opt): ?>
                      <?php if ($opt === 0): ?>
                        <option value="0" <?= $perPage === 0 ? 'selected' : '' ?>>Semua</option>
                      <?php else: ?>
                        <option value="<?= $opt ?>" <?= $perPage === $opt ? 'selected' : '' ?>><?= $opt ?>/hal</option>
                      <?php endif; ?>
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

  <!-- MODAL TAMBAH SISWA -->
  <div class="modal fade" id="modalTambahSiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Data Siswa</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>

        <form id="formTambahSiswa" action="proses_tambah_data_siswa.php" method="POST" autocomplete="off">
          <div class="modal-body">
            <div id="modalAlertTambah" class="modal-alert-area"></div>

            <div class="mb-3">
              <label class="form-label fw-semibold" for="add_nis">NIS</label>
              <input type="text" id="add_nis" name="no_induk_siswa" class="form-control" maxlength="50" required placeholder="NIS">
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold" for="add_nama_siswa">Nama Siswa</label>
              <input type="text" id="add_nama_siswa" name="nama_siswa" class="form-control" maxlength="120" required placeholder="Nama Siswa">
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold" for="add_id_kelas">Kelas</label>
              <select id="add_id_kelas" name="id_kelas" class="form-select" required>
                <option value="" disabled selected>Pilih Kelas</option>
                <?php foreach ($kelasAll as $k): ?>
                  <option value="<?= (int)$k['id_kelas'] ?>">
                    <?= htmlspecialchars($k['nama_kelas'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-0">
              <label class="form-label fw-semibold" for="add_absen">Absen</label>
              <input type="text" id="add_absen" name="no_absen_siswa" class="form-control" maxlength="20" required placeholder="Nomor Absen">
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" data-bs-dismiss="modal">
              <i class="bi bi-x-lg"></i> Batal
            </button>
            <button type="submit" id="btnSubmitTambahSiswa" class="btn btn-brand d-inline-flex align-items-center gap-2">
              <i class="bi bi-check2-circle"></i> Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- MODAL EDIT SISWA -->
  <div class="modal fade" id="modalEditSiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Data Siswa</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>

        <form id="formEditSiswa" action="proses_edit_data_siswa.php" method="POST" autocomplete="off">
          <input type="hidden" name="id_siswa" id="edit_id_siswa">

          <div class="modal-body">
            <div id="modalAlertEdit" class="modal-alert-area"></div>

            <div class="mb-3">
              <label class="form-label fw-semibold" for="edit_nis">NIS</label>
              <input type="text" id="edit_nis" name="no_induk_siswa" class="form-control" maxlength="50" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold" for="edit_nama_siswa">Nama Siswa</label>
              <input type="text" id="edit_nama_siswa" name="nama_siswa" class="form-control" maxlength="120" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold" for="edit_id_kelas">Kelas</label>
              <select id="edit_id_kelas" name="id_kelas" class="form-select" required>
                <option value="" disabled>Pilih Kelas</option>
                <?php foreach ($kelasAll as $k): ?>
                  <option value="<?= (int)$k['id_kelas'] ?>">
                    <?= htmlspecialchars($k['nama_kelas'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-0">
              <label class="form-label fw-semibold" for="edit_absen">Absen</label>
              <input type="text" id="edit_absen" name="no_absen_siswa" class="form-control" maxlength="20" required>
            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" data-bs-dismiss="modal">
              <i class="bi bi-x-lg"></i> Batal
            </button>
            <button type="submit" class="btn btn-brand d-inline-flex align-items-center gap-2">
              <i class="bi bi-save"></i> Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- MODAL IMPORT SISWA -->
  <div class="modal fade" id="modalImportSiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div>
            <h5 class="modal-title fw-semibold">Import Data Siswa</h5>
            <p class="mb-0 text-muted" style="font-size: 13px;">Gunakan template resmi agar susunan kolom sesuai dengan sistem.</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>

        <form id="formImportSiswa" action="proses_import_data_siswa.php" method="POST" enctype="multipart/form-data" autocomplete="off">
          <div class="modal-body pt-3">
            <div id="modalAlertImport" class="modal-alert-area"></div>

            <div class="mb-3 p-3 rounded-3" style="background:#f9fafb;border:1px solid #e5e7eb;">
              <div class="d-flex align-items-start gap-2">
                <div class="mt-1"><i class="fa-solid fa-circle-info" style="color:#0a4db3;"></i></div>
                <div style="font-size:13px;">
                  <strong>Langkah import data siswa:</strong>
                  <ol class="mb-1 ps-3" style="padding-left:18px;">
                    <li>Download template Excel terlebih dahulu.</li>
                    <li>Isi data siswa sesuai kolom yang tersedia.</li>
                    <li>Upload kembali file Excel tersebut di form ini.</li>
                  </ol>
                  <span class="text-muted">
                    Struktur kolom template:
                    <strong>A: Nomor</strong>, <strong>B: Nama Siswa</strong>, <strong>C: NIS</strong>, <strong>D: Absen</strong>, <strong>E: Kelas</strong>.
                  </span>
                </div>
              </div>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
              <span class="text-muted" style="font-size:13px;">Klik tombol di samping untuk mengunduh template Excel.</span>
              <a href="../../assets/templates/template_data_siswa.xlsx" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2" download>
                <i class="fa-solid fa-file-excel"></i><span>Download Template</span>
              </a>
            </div>

            <hr class="my-2">

            <div class="mb-2">
              <label for="excelFile" class="form-label fw-semibold mb-1">Upload File Excel</label>
              <div class="position-relative d-flex align-items-center">
                <input type="file" class="form-control" id="excelFile" name="excelFile" accept=".xlsx,.xls"
                  style="padding-right:35px;" required onchange="toggleClearButtonSiswaImport()">
                <button type="button" id="clearFileBtnSiswaImport" onclick="clearFileSiswaImport()" title="Hapus file"
                  style="position:absolute;right:10px;background:none;border:none;color:#6c757d;font-size:20px;line-height:1;display:none;cursor:pointer;">&times;</button>
              </div>
              <small class="text-muted d-block mt-1" style="font-size:12px;">
                Format yang didukung: <strong>.xlsx</strong> atau <strong>.xls</strong>. Pastikan tidak mengubah urutan kolom di template.
              </small>
            </div>
          </div>

          <div class="modal-footer d-flex justify-content-between">
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" data-bs-dismiss="modal">
              <i class="fa fa-times"></i> Batal
            </button>
            <button type="submit" id="btnSubmitImportSiswa" class="btn btn-warning d-inline-flex align-items-center gap-2">
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
          <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-danger me-2"></i> Konfirmasi Hapus</h5>
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
    function toggleClearButtonSiswaImport() {
      const fileInput = document.getElementById('excelFile');
      const clearBtn = document.getElementById('clearFileBtnSiswaImport');
      if (!fileInput || !clearBtn) return;
      clearBtn.style.display = fileInput.files.length > 0 ? 'block' : 'none';
    }

    function clearFileSiswaImport() {
      const fileInput = document.getElementById('excelFile');
      const clearBtn = document.getElementById('clearFileBtnSiswaImport');
      if (!fileInput || !clearBtn) return;
      fileInput.value = '';
      clearBtn.style.display = 'none';
    }
  </script>

  <!-- =========================
    [ALERT PACK TEMPLATE] JS
  ========================= -->
  <script>
    (function() {
      const ALERT_DURATION = 4000;

      function escapeHtml(str) {
        return String(str ?? '')
          .replaceAll('&', '&amp;')
          .replaceAll('<', '&lt;')
          .replaceAll('>', '&gt;')
          .replaceAll('"', '&quot;')
          .replaceAll("'", "&#039;");
      }

      function animateAlertIn(el) {
        if (!el) return;
        requestAnimationFrame(() => el.classList.add('dk-show'));
      }

      function animateAlertOut(el) {
        if (!el) return;
        el.classList.add('dk-hide');
        setTimeout(() => {
          if (el && el.parentNode) el.parentNode.removeChild(el);
        }, 450);
      }

      function wireAlert(el) {
        if (!el) return;
        animateAlertIn(el);

        const ms = parseInt(el.getAttribute('data-auto-hide') || String(ALERT_DURATION), 10);
        const timer = setTimeout(() => animateAlertOut(el), ms);
        el.dataset.timerId = String(timer);

        const close = el.querySelector('.close-btn');
        if (close && !close.dataset.bound) {
          close.dataset.bound = '1';
          close.addEventListener('click', (e) => {
            e.preventDefault();
            const t = el.dataset.timerId ? parseInt(el.dataset.timerId, 10) : 0;
            if (t) clearTimeout(t);
            animateAlertOut(el);
          });
        }
      }

      function pulseAlert(el) {
        if (!el) return;
        el.classList.remove('dk-pulse');
        void el.offsetWidth;
        el.classList.add('dk-pulse');
      }

      // ✅ update URL supaya saat refresh alert tetap muncul lagi
      window.dkPersistAlertToUrl = function(status, message) {
        try {
          const url = new URL(window.location.href);
          const sp = url.searchParams;

          sp.set('status', String(status || 'success'));
          sp.set('msg', String(message || ''));

          // biar tidak bentrok dengan pagination yang sedang berjalan,
          // kita tetap pertahankan q/per/page/tingkat/kelas yang sudah ada.
          url.search = sp.toString();
          window.history.replaceState({}, '', url.toString());
        } catch (e) {}
      };

      // ✅ Top alert (success/danger)
      window.dkShowTopAlert = function(type, message) {
        const area = document.getElementById('alertAreaTop');
        if (!area) return;

        const ok = (type === 'success');
        const cls = ok ? 'dk-alert-success' : 'dk-alert-danger';
        const icon = ok ?
          '<i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>' :
          '<i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>';

        const div = document.createElement('div');
        div.className = `dk-alert ${cls}`;
        div.setAttribute('data-auto-hide', String(ALERT_DURATION));
        div.innerHTML = `<span class="close-btn">&times;</span>${icon}${escapeHtml(message)}`;

        area.prepend(div);
        wireAlert(div);

        try {
          window.scrollTo({
            top: 0,
            behavior: 'smooth'
          });
        } catch (e) {
          window.scrollTo(0, 0);
        }
      };

      // ✅ Modal warning (kuning) + spam pulse
      window.dkShowModalWarning = function(containerId, message) {
        const box = document.getElementById(containerId);
        if (!box) return;

        const icon = '<i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>';
        const existing = box.querySelector('.dk-alert');

        if (existing) {
          const oldTimer = existing.dataset.timerId ? parseInt(existing.dataset.timerId, 10) : 0;
          if (oldTimer) clearTimeout(oldTimer);

          const msgSpan = existing.querySelector('.dk-msg');
          if (msgSpan) {
            msgSpan.textContent = String(message ?? '');
          } else {
            existing.innerHTML = `<span class="close-btn">&times;</span>${icon}<span class="dk-msg"></span>`;
            existing.querySelector('.dk-msg').textContent = String(message ?? '');
          }

          existing.classList.remove('dk-hide');
          existing.classList.add('dk-show');

          pulseAlert(existing);

          const timer = setTimeout(() => animateAlertOut(existing), ALERT_DURATION);
          existing.dataset.timerId = String(timer);

          const close = existing.querySelector('.close-btn');
          if (close && !close.dataset.bound) {
            close.dataset.bound = '1';
            close.addEventListener('click', (e) => {
              e.preventDefault();
              const t = existing.dataset.timerId ? parseInt(existing.dataset.timerId, 10) : 0;
              if (t) clearTimeout(t);
              animateAlertOut(existing);
            });
          }
          return;
        }

        const div = document.createElement('div');
        div.className = 'dk-alert dk-alert-warning';
        div.setAttribute('data-auto-hide', String(ALERT_DURATION));
        div.innerHTML = `<span class="close-btn">&times;</span>${icon}<span class="dk-msg">${escapeHtml(message)}</span>`;
        box.appendChild(div);
        wireAlert(div);
        pulseAlert(div);
      };

      // auto wire alert dari PHP
      document.querySelectorAll('#alertAreaTop .dk-alert').forEach(wireAlert);
    })();
  </script>

  <script>
    (function() {
      const input = document.getElementById('searchInput');
      const tbody = document.getElementById('siswaTbody');
      const tableWrap = document.getElementById('siswaTableWrap');
      const loadingOverlay = document.getElementById('tableLoadingOverlay');

      const filterTingkat = document.getElementById('filterTingkat');
      const filterKelas = document.getElementById('filterKelas');

      const checkAll = document.getElementById('checkAll');
      const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
      const perPageSelect = document.getElementById('perPage');

      const paginationUl = document.getElementById('paginationWrap');
      const pageInfo = document.getElementById('pageInfo');

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

      function showTop(type, msg) {
        if (type === 'success') {
          window.dkShowTopAlert('success', msg);
          // ✅ simpan ke URL agar refresh tetap muncul
          window.dkPersistAlertToUrl('success', msg);
          return;
        }
        // warning/danger => red top
        window.dkShowTopAlert('danger', msg);
        window.dkPersistAlertToUrl('error', msg);
      }

      function showModalWarn(containerId, msg) {
        window.dkShowModalWarning(containerId, msg);
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
            const m = bootstrap.Modal.getOrCreateInstance(confirmModalEl);
            m.hide();
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

      function attachEditModalEvents() {
        const editButtons = document.querySelectorAll('.btn-edit-siswa');
        const modalEl = document.getElementById('modalEditSiswa');
        if (!modalEl) return;

        const inputId = document.getElementById('edit_id_siswa');
        const inputNama = document.getElementById('edit_nama_siswa');
        const inputNis = document.getElementById('edit_nis');
        const inputAbsen = document.getElementById('edit_absen');
        const inputKelas = document.getElementById('edit_id_kelas');

        editButtons.forEach(btn => {
          btn.addEventListener('click', (e) => {
            e.preventDefault();
            const id = btn.getAttribute('data-id') || '';
            const nama = btn.getAttribute('data-nama') || '';
            const nis = btn.getAttribute('data-nis') || '';
            const absen = btn.getAttribute('data-absen') || '';
            const idKelas = btn.getAttribute('data-id_kelas') || '';

            if (inputId) inputId.value = id;
            if (inputNis) inputNis.value = nis;
            if (inputNama) inputNama.value = nama;
            if (inputKelas) inputKelas.value = idKelas;
            if (inputAbsen) inputAbsen.value = absen;

            const editAlertBox = document.getElementById('modalAlertEdit');
            if (editAlertBox) editAlertBox.innerHTML = '';

            if (typeof bootstrap !== 'undefined') {
              bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
          });
        });
      }

      function attachSingleDeleteEvents() {
        const buttons = document.querySelectorAll('.btn-delete-single');
        buttons.forEach(btn => {
          btn.addEventListener('click', (e) => {
            e.preventDefault();
            const id = btn.getAttribute('data-id');
            const label = btn.getAttribute('data-label') || 'siswa ini';
            if (!id) return;

            showDeleteConfirm(`Yakin ingin menghapus siswa "${label}"?`, () => {
              const form = document.createElement('form');
              form.method = 'post';
              form.action = 'proses_hapus_data_siswa.php';

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

      function buildPagination(totalRows, page, perPage) {
        currentTotalRows = totalRows;
        currentPage = page;
        currentPerPage = perPage;

        const allMode = (parseInt(perPage, 10) === 0);
        const totalPages = allMode ? 1 : Math.max(1, Math.ceil(totalRows / perPage));

        let shown;
        if (totalRows === 0) {
          shown = 0;
        } else if (allMode) {
          shown = totalRows;
          page = 1;
        } else {
          if (page > totalPages) page = totalPages;
          const from = (page - 1) * perPage + 1;
          const to = Math.min(page * perPage, totalRows);
          shown = to - from + 1;
        }

        const pageDisplayCurrent = totalRows === 0 ? 0 : (allMode ? 1 : page);
        const pageDisplayTotal = totalRows === 0 ? 0 : totalPages;

        pageInfo.innerHTML =
          `Menampilkan <strong>${shown}</strong> dari <strong>${totalRows}</strong> data • Halaman <strong>${pageDisplayCurrent}</strong> / <strong>${pageDisplayTotal}</strong>`;

        const makeLi = (disabled, target, text, active = false) => {
          const cls = ['page-item', disabled ? 'disabled' : '', active ? 'active' : ''].filter(Boolean).join(' ');
          const aAttr = disabled ? 'tabindex="-1"' : `data-page="${target}"`;
          return `<li class="${cls}"><a class="page-link" href="#" ${aAttr}>${text}</a></li>`;
        };

        let html = '';
        const isFirst = allMode ? true : (page <= 1);
        const isLast = allMode ? true : (page >= totalPages);

        html += makeLi(isFirst, 1, '« First');
        html += makeLi(isFirst, Math.max(1, (allMode ? 1 : page - 1)), '‹ Prev');

        if (allMode) {
          html += makeLi(false, 1, '1', true);
        } else {
          const start = Math.max(1, page - 2);
          const end = Math.min(totalPages, page + 2);
          for (let i = start; i <= end; i++) html += makeLi(false, i, String(i), i === page);
        }

        html += makeLi(isLast, Math.min(totalPages, (allMode ? 1 : page + 1)), 'Next ›');
        html += makeLi(isLast, totalPages, 'Last »');

        paginationUl.innerHTML = html;

        paginationUl.querySelectorAll('a[data-page]').forEach(a => {
          a.addEventListener('click', (e) => {
            e.preventDefault();
            if (allMode) return;
            const target = parseInt(a.getAttribute('data-page') || '1', 10);
            if (isNaN(target) || target < 1 || target === currentPage) return;
            doSearch(currentQuery, target, currentPerPage, currentTingkat, currentKelas, true);
          });
        });

        if (perPageSelect) perPageSelect.value = String(perPage);
      }

      checkAll.addEventListener('change', () => {
        const boxes = getRowCheckboxes();
        boxes.forEach(b => b.checked = checkAll.checked);
        updateBulkUI();
      });

      bulkDeleteBtn.addEventListener('click', () => {
        const boxes = getRowCheckboxes().filter(b => b.checked);
        if (boxes.length === 0) return;

        const count = boxes.length;
        const form = document.createElement('form');
        form.method = 'post';
        form.action = 'proses_hapus_data_siswa.php';

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

        showDeleteConfirm(`Yakin ingin menghapus ${count} siswa terpilih?`, () => {
          document.body.appendChild(form);
          form.submit();
        });
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
          per: perPage ?? currentPerPage ?? 10,
          tingkat: currentTingkat,
          kelas: String(currentKelas || 0),
        });

        fetch('ajax_siswa_list.php?' + params.toString(), {
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

              buildPagination(isNaN(total) ? 0 : total, isNaN(pg) ? 1 : pg, isNaN(pp) ? currentPerPage : pp);
            }

            attachCheckboxEvents();
            attachEditModalEvents();
            attachSingleDeleteEvents();
            finishLoading();
          })
          .catch(e => {
            if (e.name === 'AbortError') return;
            tbody.innerHTML = `<tr><td colspan="6">Gagal memuat data.</td></tr>`;
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
          if (isNaN(val) || val < 0) return;
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
            showModalWarn(modalAlertId, 'Respon server tidak valid.');
            disableBtn(btn, false);
            return;
          }

          if (data.ok) {
            onSuccess && onSuccess(data);
          } else {
            showModalWarn(modalAlertId, data.msg || 'Terjadi kesalahan.');
          }
        } catch (err) {
          showModalWarn(modalAlertId, 'Gagal terhubung ke server.');
          console.error(err);
        } finally {
          disableBtn(btn, false);
        }
      }

      // Tambah
      const formTambah = document.getElementById('formTambahSiswa');
      const btnTambah = document.getElementById('btnSubmitTambahSiswa');
      const modalTambahEl = document.getElementById('modalTambahSiswa');

      if (formTambah) {
        formTambah.addEventListener('submit', (e) => {
          e.preventDefault();
          postFormAjax(formTambah, btnTambah, 'modalAlertTambah', (data) => {
            if (typeof bootstrap !== 'undefined' && modalTambahEl) {
              bootstrap.Modal.getOrCreateInstance(modalTambahEl).hide();
            }
            formTambah.reset();
            doSearch(currentQuery, 1, currentPerPage, currentTingkat, currentKelas, true);
            showTop('success', data.msg || 'Berhasil menambah data.');
          });
        });
      }

      // Edit
      const formEdit = document.getElementById('formEditSiswa');
      const modalEditEl = document.getElementById('modalEditSiswa');

      if (formEdit) {
        formEdit.addEventListener('submit', (e) => {
          e.preventDefault();
          const submitBtn = formEdit.querySelector('button[type="submit"]');
          postFormAjax(formEdit, submitBtn, 'modalAlertEdit', (data) => {
            if (typeof bootstrap !== 'undefined' && modalEditEl) {
              bootstrap.Modal.getOrCreateInstance(modalEditEl).hide();
            }
            doSearch(currentQuery, 1, currentPerPage, currentTingkat, currentKelas, true);
            showTop('success', data.msg || 'Berhasil mengedit data.');
          });
        });
      }

      // Import
      const formImport = document.getElementById('formImportSiswa');
      const btnImport = document.getElementById('btnSubmitImportSiswa');
      const modalImportEl = document.getElementById('modalImportSiswa');

      if (formImport) {
        formImport.addEventListener('submit', (e) => {
          e.preventDefault();
          postFormAjax(formImport, btnImport, 'modalAlertImport', (data) => {
            if (typeof bootstrap !== 'undefined' && modalImportEl) {
              bootstrap.Modal.getOrCreateInstance(modalImportEl).hide();
            }
            formImport.reset();
            toggleClearButtonSiswaImport();

            doSearch(currentQuery, 1, currentPerPage, currentTingkat, currentKelas, true);
            showTop('success', data.msg || 'Import selesai.');
          });
        });
      }

      // Export (placeholder)
      const exportBtn = document.getElementById('exportBtn');
      if (exportBtn) {
        exportBtn.addEventListener('click', () => {
          showTop('danger', 'Fitur export belum dihubungkan ke file export siswa.');
        });
      }

      // init
      attachCheckboxEvents();
      attachEditModalEvents();
      attachSingleDeleteEvents();
      buildPagination(currentTotalRows, currentPage, currentPerPage);
      if (tbody) tbody.classList.add('tbody-loaded');

      if (input) input.value = currentQuery;

      if (filterTingkat) filterTingkat.value = currentTingkat;
      if (filterKelas) filterKelas.value = String(currentKelas || 0);
      filterKelasOptionsByTingkat(currentTingkat);

    })();
  </script>

  <?php include '../../includes/footer.php'; ?>
</body>