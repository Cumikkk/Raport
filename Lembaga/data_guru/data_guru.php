<?php
// pages/guru/data_guru.php
require_once '../../koneksi.php';
include '../../includes/header.php';

// Session + CSRF untuk bulk delete
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

// Search & pagination awal (server-side sebelum AJAX)
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$like   = "%{$search}%";

$allowedPer = [10, 20, 50, 100];
$perPage    = isset($_GET['per']) ? (int)$_GET['per'] : 10;
if (!in_array($perPage, $allowedPer, true)) {
  $perPage = 10;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Hitung total data
if ($search !== '') {
  $countSql = "SELECT COUNT(*) AS total FROM guru WHERE nama_guru LIKE ?";
  $stmtCount = mysqli_prepare($koneksi, $countSql);
  mysqli_stmt_bind_param($stmtCount, 's', $like);
} else {
  $countSql = "SELECT COUNT(*) AS total FROM guru";
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

// Ambil data guru untuk tampilan awal
if ($search !== '') {
  $sql = "
    SELECT id_guru, nama_guru, jabatan_guru
    FROM guru
    WHERE nama_guru LIKE ?
    ORDER BY nama_guru ASC
    LIMIT ? OFFSET ?
  ";
  $stmt = mysqli_prepare($koneksi, $sql);
  mysqli_stmt_bind_param($stmt, 'sii', $like, $perPage, $offset);
} else {
  $sql = "
    SELECT id_guru, nama_guru, jabatan_guru
    FROM guru
    ORDER BY nama_guru ASC
    LIMIT ? OFFSET ?
  ";
  $stmt = mysqli_prepare($koneksi, $sql);
  mysqli_stmt_bind_param($stmt, 'ii', $perPage, $offset);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Info range
if ($totalRows === 0) {
  $from = 0;
  $to   = 0;
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

    /* TRANSISI TABEL + OVERLAY LOADING */
    #guruTbody {
      transition:
        opacity 0.25s ease,
        transform 0.25s ease;
    }

    #guruTbody.tbody-loading {
      opacity: 0.4;
      transform: scale(0.995);
    }

    #guruTbody.tbody-loaded {
      opacity: 1;
      transform: scale(1);
    }

    #guruTableWrap {
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

    /* ALERT */
    .alert {
      padding: 12px 14px;
      border-radius: 12px;
      margin-bottom: 20px;
      font-size: 14px;
      transition:
        opacity 0.4s ease,
        transform 0.4s ease,
        max-height 0.4s ease,
        margin 0.4s ease,
        padding-top 0.4s ease,
        padding-bottom 0.4s ease;
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

    /* ================================
       ✅ PAGINATION GROUP (Referensi 2)
       ================================ */
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

      /* di mobile: separator disembunyikan biar rapih */
      .pager-sep {
        display: none;
      }

      .pager-group {
        width: 100%;
      }
    }

    .searchbox:focus-within {
      box-shadow: 0 0 0 3px rgba(10, 77, 179, .15);
    }
  </style>

  <main class="content">
    <div class="row g-3">
      <div class="col-12">

        <!-- ALERT DI LUAR CARD -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] !== ''): ?>
          <div class="alert alert-success">
            <span class="close-btn">&times;</span>
            ✅ <?= htmlspecialchars($_GET['msg'], ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_GET['err']) && $_GET['err'] !== ''): ?>
          <div class="alert alert-danger">
            <span class="close-btn">&times;</span>
            ❌ <?= htmlspecialchars($_GET['err'], ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>

        <div class="card shadow-sm">

          <!-- TOP BAR -->
          <div class="top-bar p-3 p-md-4">
            <div class="d-flex flex-column gap-3 w-100">
              <div>
                <h5 class="page-title mb-0 fw-bold fs-4">Data Guru</h5>
              </div>

              <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2">
                <!-- Search + per page -->
                <div class="d-flex search-perpage-row align-items-md-center gap-2 flex-grow-1">
                  <div class="search-wrap flex-grow-1">
                    <div class="searchbox" role="search" aria-label="Pencarian guru">
                      <i class="bi bi-search icon"></i>
                      <input type="text" id="searchInput" placeholder="Ketik untuk mencari" autofocus>
                    </div>
                  </div>

                  <!-- (perPage dipindah ke grup pagination bawah, jadi ini dikosongkan) -->
                </div>

                <!-- Tombol Tambah / Import / Export -->
                <div class="d-flex justify-content-md-end flex-wrap gap-2">
                  <button type="button"
                    class="btn btn-brand btn-sm d-inline-flex align-items-center gap-2 px-3"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTambahGuru">
                    <i class="bi bi-person-plus"></i> Tambah Guru
                  </button>

                  <button type="button"
                    class="btn btn-success btn-sm d-inline-flex align-items-center gap-2 px-3"
                    data-bs-toggle="modal"
                    data-bs-target="#modalImportGuru">
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
            <div class="table-responsive" id="guruTableWrap">
              <!-- OVERLAY LOADING -->
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
                    <th style="width:70px;">No</th>
                    <th>Nama Guru</th>
                    <th>Jabatan</th>
                    <th style="width:200px;">Aksi</th>
                  </tr>
                </thead>
                <tbody id="guruTbody" class="text-center tbody-loaded">
                  <?php if ($totalRows === 0): ?>
                    <tr>
                      <td colspan="5">Belum ada data.</td>
                    </tr>
                    <?php else:
                    $no = $offset + 1;
                    $rowClass = ($search !== '') ? 'highlight-row' : '';
                    while ($row = mysqli_fetch_assoc($result)):
                    ?>
                      <tr class="<?= $rowClass; ?>">
                        <td class="text-center" data-label="Pilih">
                          <input type="checkbox" class="row-check" value="<?= (int)$row['id_guru'] ?>">
                        </td>
                        <td data-label="No"><?= $no++; ?></td>
                        <td data-label="Nama Guru"><?= htmlspecialchars($row['nama_guru']) ?></td>
                        <td data-label="Jabatan" class="text-center"><?= htmlspecialchars($row['jabatan_guru']) ?></td>
                        <td data-label="Aksi">
                          <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <button type="button"
                              class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-edit-guru"
                              data-id="<?= (int)$row['id_guru'] ?>"
                              data-nama="<?= htmlspecialchars($row['nama_guru'], ENT_QUOTES, 'UTF-8') ?>"
                              data-jabatan="<?= htmlspecialchars($row['jabatan_guru'], ENT_QUOTES, 'UTF-8') ?>">
                              <i class="bi bi-pencil-square"></i> Edit
                            </button>

                            <button type="button"
                              class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-delete-single"
                              data-id="<?= (int)$row['id_guru'] ?>"
                              data-label="<?= htmlspecialchars($row['nama_guru'], ENT_QUOTES, 'UTF-8') ?>">
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

            <!-- HAPUS TERPILIH -->
            <div class="mt-3 d-flex justify-content-start">
              <button type="button" id="bulkDeleteBtn"
                class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1"
                disabled>
                <i class="bi bi-trash3"></i> <span>Hapus Terpilih</span>
              </button>
            </div>

            <!-- ✅ Pagination + perPage digabung (Referensi 2) -->
            <nav aria-label="Page navigation" class="mt-3">
              <div class="pager-area">
                <div class="pager-group">
                  <!-- kiri: pagination -->
                  <ul class="pagination mb-0" id="paginationWrap"></ul>

                  <!-- separator -->
                  <div class="pager-sep" aria-hidden="true"></div>

                  <!-- kanan: per halaman -->
                  <select id="perPage" class="form-select form-select-sm per-select">
                    <?php foreach ($allowedPer as $opt): ?>
                      <option value="<?= $opt ?>" <?= $perPage === $opt ? 'selected' : '' ?>>
                        <?= $opt ?>/hal
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- info bawah: center -->
                <p id="pageInfo" class="page-info-text text-muted mb-0 page-info-center">
                  Menampilkan <strong><?= $shown ?></strong> dari <strong><?= $totalRows ?></strong> data •
                  Halaman <strong><?= $pageDisplayCurrent ?></strong> / <strong><?= $pageDisplayTotal ?></strong>
                </p>
              </div>
            </nav>

          </div>
        </div><!-- /.card -->
      </div><!-- /.col-12 -->
    </div><!-- /.row -->
  </main>

  <!-- MODAL TAMBAH GURU -->
  <div class="modal fade" id="modalTambahGuru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Data Guru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <form id="formTambahGuru" action="proses_tambah_data_guru.php" method="POST" autocomplete="off">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-semibold" for="add_nama_guru">Nama Guru</label>
              <input type="text" id="add_nama_guru" name="nama_guru" class="form-control" maxlength="100" required placeholder="Nama Guru">
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold" for="add_jabatan_guru">Jabatan</label>
              <select id="add_jabatan_guru" name="jabatan_guru" class="form-select" required>
                <option value="" disabled selected>Pilih Jabatan</option>
                <option value="Kepala Sekolah">Kepala Sekolah</option>
                <option value="Guru">Guru</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" data-bs-dismiss="modal">
              <i class="bi bi-x-lg"></i> Batal
            </button>
            <button type="submit" id="btnSubmitTambahGuru" class="btn btn-brand d-inline-flex align-items-center gap-2">
              <i class="bi bi-check2-circle"></i> Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- MODAL EDIT GURU -->
  <div class="modal fade" id="modalEditGuru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Data Guru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <form id="formEditGuru" action="proses_edit_data_guru.php" method="POST" autocomplete="off">
          <input type="hidden" name="id_guru" id="edit_id_guru">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-semibold" for="edit_nama_guru">Nama Guru</label>
              <input type="text" id="edit_nama_guru" name="nama_guru" class="form-control" maxlength="100" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold" for="edit_jabatan_guru">Jabatan</label>
              <select id="edit_jabatan_guru" name="jabatan_guru" class="form-select" required>
                <option value="Kepala Sekolah">Kepala Sekolah</option>
                <option value="Guru">Guru</option>
              </select>
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

  <!-- MODAL IMPORT GURU -->
  <div class="modal fade" id="modalImportGuru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div>
            <h5 class="modal-title fw-semibold">Import Data Guru</h5>
            <p class="mb-0 text-muted" style="font-size: 13px;">
              Gunakan template resmi agar susunan kolom sesuai dengan sistem.
            </p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>

        <form id="formImportGuru"
          action="proses_import_data_guru.php"
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
                  <strong>Langkah import data guru:</strong>
                  <ol class="mb-1 ps-3" style="padding-left:18px;">
                    <li>Download template Excel terlebih dahulu.</li>
                    <li>Isi data guru sesuai kolom yang tersedia.</li>
                    <li>Upload kembali file Excel tersebut di form ini.</li>
                  </ol>
                  <span class="text-muted">
                    Struktur kolom template:
                    <strong>A: nomer</strong>,
                    <strong>B: nama guru</strong>,
                    <strong>C: jabatan</strong>
                    (<em>hanya “Kepala Sekolah” atau “Guru”</em>).
                  </span>
                </div>
              </div>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
              <span class="text-muted" style="font-size:13px;">
                Klik tombol di samping untuk mengunduh template Excel.
              </span>
              <a href="../../assets/templates/template_data_guru.xlsx"
                class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2"
                download>
                <i class="fa-solid fa-file-excel"></i>
                <span>Download Template</span>
              </a>
            </div>

            <hr class="my-2">

            <div class="mb-2">
              <label for="excelFile" class="form-label fw-semibold mb-1">Upload File Excel</label>
              <div class="position-relative d-flex align-items-center">
                <input type="file"
                  class="form-control"
                  id="excelFile"
                  name="excel_file"
                  accept=".xlsx,.xls"
                  style="padding-right:35px;"
                  required
                  onchange="toggleClearButtonGuruImport()">

                <button type="button"
                  id="clearFileBtnGuruImport"
                  onclick="clearFileGuruImport()"
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
            <button type="submit" id="btnSubmitImportGuru" class="btn btn-warning d-inline-flex align-items-center gap-2">
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
    function toggleClearButtonGuruImport() {
      const fileInput = document.getElementById('excelFile');
      const clearBtn = document.getElementById('clearFileBtnGuruImport');
      if (!fileInput || !clearBtn) return;
      clearBtn.style.display = fileInput.files.length > 0 ? 'block' : 'none';
    }

    function clearFileGuruImport() {
      const fileInput = document.getElementById('excelFile');
      const clearBtn = document.getElementById('clearFileBtnGuruImport');
      if (!fileInput || !clearBtn) return;
      fileInput.value = '';
      clearBtn.style.display = 'none';
    }
  </script>

  <script>
    (function() {
      const input = document.getElementById('searchInput');
      const tbody = document.getElementById('guruTbody');
      const tableWrap = document.getElementById('guruTableWrap');
      const loadingOverlay = document.getElementById('tableLoadingOverlay');

      const checkAll = document.getElementById('checkAll');
      const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
      const perPageSelect = document.getElementById('perPage');

      // ✅ sekarang pagination UL langsung
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

      let pendingDeleteHandler = null;

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
          const m = bootstrap.Modal.getOrCreateInstance(confirmModalEl);
          m.show();
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
        const editButtons = document.querySelectorAll('.btn-edit-guru');
        const modalEl = document.getElementById('modalEditGuru');
        if (!modalEl) return;

        const inputId = document.getElementById('edit_id_guru');
        const inputNama = document.getElementById('edit_nama_guru');
        const inputJabatan = document.getElementById('edit_jabatan_guru');

        editButtons.forEach(btn => {
          btn.addEventListener('click', (e) => {
            e.preventDefault();

            const id = btn.getAttribute('data-id') || '';
            const nama = btn.getAttribute('data-nama') || '';
            const jabatan = btn.getAttribute('data-jabatan') || '';

            if (inputId) inputId.value = id;
            if (inputNama) inputNama.value = nama;
            if (inputJabatan) inputJabatan.value = jabatan;

            if (typeof bootstrap !== 'undefined') {
              const editModal = bootstrap.Modal.getOrCreateInstance(modalEl);
              editModal.show();
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
            const label = btn.getAttribute('data-label') || 'guru ini';
            if (!id) return;

            showDeleteConfirm(`Yakin ingin menghapus guru "${label}"?`, () => {
              const form = document.createElement('form');
              form.method = 'post';
              form.action = 'hapus_data_guru.php';

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

      // ✅ Build pagination ke <ul id="paginationWrap"> (bukan wrapper div/nav)
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

        // tampilkan sekitar 5 tombol
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
            doSearch(currentQuery, target, currentPerPage, true);
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
        form.action = 'hapus_data_guru.php';

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

        showDeleteConfirm(`Yakin ingin menghapus ${count} data guru terpilih?`, () => {
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

      function doSearch(query, page, perPage, fromPaginationOrPerpage = false) {
        setLoading(fromPaginationOrPerpage);

        if (currentController) currentController.abort();
        currentController = new AbortController();

        currentQuery = query || '';
        const params = new URLSearchParams({
          q: currentQuery,
          page: page || 1,
          per: perPage || currentPerPage || 10
        });

        fetch('ajax_guru_list.php?' + params.toString(), {
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
            attachEditModalEvents();
            attachSingleDeleteEvents();
            finishLoading();
          })
          .catch(e => {
            if (e.name === 'AbortError') return;
            tbody.innerHTML = `<tr><td colspan="5">Gagal memuat data.</td></tr>`;
            finishLoading();
            console.error(e);
          });
      }

      input.addEventListener('input', () => {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
          doSearch(input.value, 1, currentPerPage, false);
        }, debounceMs);
      });

      if (perPageSelect) {
        perPageSelect.addEventListener('change', () => {
          const val = parseInt(perPageSelect.value || '10', 10);
          if (isNaN(val) || val <= 0) return;
          currentPerPage = val;
          doSearch(currentQuery, 1, currentPerPage, true);
        });
      }

      // VALIDASI modal
      const formTambah = document.getElementById('formTambahGuru');
      const btnSubmitTambah = document.getElementById('btnSubmitTambahGuru');
      if (formTambah && btnSubmitTambah) {
        btnSubmitTambah.addEventListener('click', (e) => {
          if (!formTambah.checkValidity()) {
            e.preventDefault();
            formTambah.reportValidity();
          }
        });
      }

      const formImport = document.getElementById('formImportGuru');
      const btnSubmitImport = document.getElementById('btnSubmitImportGuru');
      if (formImport && btnSubmitImport) {
        btnSubmitImport.addEventListener('click', (e) => {
          if (!formImport.checkValidity()) {
            e.preventDefault();
            formImport.reportValidity();
          }
        });
      }

      // init
      attachCheckboxEvents();
      attachEditModalEvents();
      attachSingleDeleteEvents();
      buildPagination(currentTotalRows, currentPage, currentPerPage);

      if (tbody) tbody.classList.add('tbody-loaded');
    })();
  </script>

  <script>
    // Auto-hide alert + tombol X
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