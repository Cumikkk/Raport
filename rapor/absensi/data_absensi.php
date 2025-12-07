<?php
// pages/absensi/data_absensi.php

// ====== BACKEND SETUP ======
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);

require_once __DIR__ . '/../../koneksi.php';

// Session + CSRF (untuk bulk delete & bulk edit)
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

// --------- SEARCH & PAGINATION AWAL (server-side, sebelum AJAX) ---------
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$like   = "%{$search}%";

$allowedPer = [10, 20, 50, 100];
$perPage    = isset($_GET['per']) ? (int)$_GET['per'] : 10;
if (!in_array($perPage, $allowedPer, true)) {
  $perPage = 10;
}

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

  $stmtCount = $koneksi->prepare($countSql);
  $stmtCount->bind_param($types, ...$params);
} else {
  // Tanpa filter, cukup hitung dari tabel absensi
  $countSql = "SELECT COUNT(*) AS total FROM absensi";
  $stmtCount = $koneksi->prepare($countSql);
}

$stmtCount->execute();
$resCount  = $stmtCount->get_result();
$rowCount  = $resCount->fetch_assoc();
$totalRows = (int)($rowCount['total'] ?? 0);

$totalPages = max(1, (int)ceil($totalRows / $perPage));
if ($page > $totalPages) {
  $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

/* ==========================
 * AMBIL DATA ABSENSI UNTUK TAMPILAN AWAL (INNER JOIN)
 * ========================== */
if ($search !== '') {
  $sql = "
    SELECT
      a.id_absensi,
      a.id_siswa,
      s.nama_siswa,
      s.no_induk_siswa AS nis,
      COALESCE(g.nama_guru, '-') AS wali_kelas,
      a.sakit, a.izin, a.alpha
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

  $stmt = $koneksi->prepare($sql);
  $stmt->bind_param($types, ...$params);
} else {
  $sql = "
    SELECT
      a.id_absensi,
      a.id_siswa,
      s.nama_siswa,
      s.no_induk_siswa AS nis,
      COALESCE(g.nama_guru, '-') AS wali_kelas,
      a.sakit, a.izin, a.alpha
    FROM absensi a
    INNER JOIN siswa s ON s.id_siswa = a.id_siswa
    LEFT JOIN kelas k ON k.id_kelas = s.id_kelas
    LEFT JOIN guru  g ON g.id_guru  = k.id_guru
    ORDER BY s.nama_siswa ASC
    LIMIT ? OFFSET ?
  ";
  $stmt = $koneksi->prepare($sql);
  $stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

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

// --------- AMBIL LIST SISWA UNTUK MODAL TAMBAH ---------
$siswa_list = [];
$q = $koneksi->query("
  SELECT s.id_siswa, s.nama_siswa, s.no_induk_siswa, s.no_absen_siswa,
         k.nama_kelas, g.nama_guru
  FROM siswa s
  LEFT JOIN kelas k ON k.id_kelas = s.id_kelas
  LEFT JOIN guru  g ON g.id_guru  = k.id_guru
  ORDER BY s.nama_siswa ASC
");
while ($r = $q->fetch_assoc()) {
  $siswa_list[] = $r;
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
      transition:
        opacity 0.25s ease,
        transform 0.25s ease;
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
    }

    .searchbox:focus-within {
      box-shadow: 0 0 0 3px rgba(10, 77, 179, .15);
    }
  </style>

  <main class="content">
    <div class="row g-3">
      <div class="col-12">

        <!-- ALERT DI LUAR CARD -->
        <?php if ($alertMsg !== '' && $alertClass !== ''): ?>
          <div class="alert <?= htmlspecialchars($alertClass, ENT_QUOTES, 'UTF-8'); ?>">
            <span class="close-btn">&times;</span>
            <?= htmlspecialchars($alertMsg, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>

        <div class="card shadow-sm">

          <!-- TOP BAR -->
          <div class="top-bar p-3 p-md-4">
            <div class="d-flex flex-column gap-3 w-100">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="page-title mb-0 fw-bold fs-4">Data Absensi Siswa</h5>
              </div>

              <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2">
                <!-- Search + per page -->
                <div class="d-flex search-perpage-row align-items-md-center gap-2 flex-grow-1">
                  <div class="search-wrap flex-grow-1">
                    <div class="searchbox" role="search" aria-label="Pencarian absensi">
                      <i class="bi bi-search icon"></i>
                      <input type="text"
                        id="searchInput"
                        placeholder="Ketik untuk mencari (Nama/NIS/Wali/Sakit/Izin/Alpha)"
                        value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
                        autofocus>
                    </div>
                  </div>

                  <div class="d-flex align-items-center gap-2">
                    <select id="perPage" class="form-select form-select-sm" style="width:auto;">
                      <?php foreach ($allowedPer as $opt): ?>
                        <option value="<?= $opt ?>" <?= $perPage === $opt ? 'selected' : '' ?>>
                          <?= $opt ?>/hal
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <!-- Tombol Tambah / Import / Export -->
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
              <!-- OVERLAY LOADING -->
              <div class="table-loading-overlay" id="tableLoadingOverlay">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span style="font-size:13px;">Sedang memuat data…</span>
              </div>

              <!-- FORM BULK EDIT -->
              <form id="bulkEditForm" action="proses_bulk_edit_absensi.php" method="post">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">

                <table class="table table-striped table-bordered align-middle mb-0">
                  <thead class="text-center">
                    <tr>
                      <th style="width:50px;" class="text-center">
                        <input type="checkbox" id="checkAll" title="Pilih Semua">
                      </th>
                      <th style="width:70px;">Absen</th>
                      <th>Nama Siswa</th>
                      <th>NIS</th>
                      <th>Wali Kelas</th>
                      <th>Sakit</th>
                      <th>Izin</th>
                      <th>Alpha</th>
                      <th style="width:140px;">Aksi</th>
                    </tr>
                  </thead>
                  <tbody id="absensiTbody" class="text-center tbody-loaded">
                    <?php
                    if ($totalRows === 0):
                    ?>
                      <tr>
                        <td colspan="9">Belum ada data.</td>
                      </tr>
                      <?php
                    else:
                      $no = $offset + 1;
                      $rowClass = ($search !== '') ? 'highlight-row' : '';
                      while ($row = $result->fetch_assoc()):
                        $id        = (int)$row['id_absensi'];
                        $id_siswa  = (int)($row['id_siswa'] ?? 0);
                        $nama      = htmlspecialchars($row['nama_siswa'] ?? '-', ENT_QUOTES, 'UTF-8');
                        $nis       = htmlspecialchars($row['nis'] ?? '-', ENT_QUOTES, 'UTF-8');
                        $wali      = htmlspecialchars($row['wali_kelas'] ?? '-', ENT_QUOTES, 'UTF-8');
                        $sakit     = (int)($row['sakit'] ?? 0);
                        $izin      = (int)($row['izin'] ?? 0);
                        $alpha     = (int)($row['alpha'] ?? 0);
                      ?>
                        <tr class="<?= $rowClass; ?>"
                          data-id="<?= $id; ?>">
                          <td class="text-center" data-label="Pilih">
                            <input type="checkbox" class="row-check" value="<?= $id; ?>">
                            <input type="hidden" name="id_absensi[]" value="<?= $id; ?>">
                          </td>
                          <td data-label="Absen"><?= $no++; ?></td>
                          <td data-label="Nama Siswa"><?= $nama; ?></td>
                          <td data-label="NIS"><?= $nis; ?></td>
                          <td data-label="Wali Kelas"><?= $wali; ?></td>

                          <td data-label="Sakit">
                            <span class="cell-view"><?= $sakit; ?></span>
                            <input type="number"
                              class="form-control form-control-sm cell-input d-none"
                              name="sakit[]"
                              min="0"
                              step="1"
                              value="<?= $sakit; ?>">
                          </td>
                          <td data-label="Izin">
                            <span class="cell-view"><?= $izin; ?></span>
                            <input type="number"
                              class="form-control form-control-sm cell-input d-none"
                              name="izin[]"
                              min="0"
                              step="1"
                              value="<?= $izin; ?>">
                          </td>
                          <td data-label="Alpha">
                            <span class="cell-view"><?= $alpha; ?></span>
                            <input type="number"
                              class="form-control form-control-sm cell-input d-none"
                              name="alpha[]"
                              min="0"
                              step="1"
                              value="<?= $alpha; ?>">
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
                    <?php
                      endwhile;
                    endif;
                    ?>
                  </tbody>
                </table>
              </form>
            </div>

            <!-- HAPUS TERPILIH + SIMPAN PERUBAHAN -->
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

            <!-- Info & Pagination -->
            <div class="mt-3 d-flex flex-column align-items-center gap-1">
              <nav id="paginationWrap" class="d-flex justify-content-center"></nav>
              <div id="pageInfo" class="page-info-text text-muted text-center mt-2">
                Menampilkan <?= $shown ?> dari <?= $totalRows ?> data • Halaman <?= $pageDisplayCurrent ?> / <?= $pageDisplayTotal ?>
              </div>
            </div>
          </div>

        </div><!-- /.card -->
      </div><!-- /.col-12 -->
    </div><!-- /.row -->
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
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-semibold" for="add_id_siswa">Nama Siswa</label>
              <select id="add_id_siswa" name="id_siswa" class="form-select" required>
                <option value="" disabled selected>-- Pilih dari daftar siswa --</option>
                <?php foreach ($siswa_list as $s):
                  $opt = $s['nama_siswa'];
                  $opt .= $s['no_induk_siswa'] ? " (NIS: {$s['no_induk_siswa']})" : "";
                  if (!empty($s['nama_kelas'])) $opt .= " - {$s['nama_kelas']}";
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
            <button type="button"
              class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"
              data-bs-dismiss="modal">
              <i class="bi bi-x-lg"></i> Batal
            </button>
            <button type="submit"
              id="btnSubmitTambahAbsensi"
              class="btn btn-brand d-inline-flex align-items-center gap-2">
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
                    <strong>A: nomer</strong>,
                    <strong>B: nama siswa</strong>,
                    <strong>C: NIS</strong>,
                    <strong>D: wali kelas</strong>,
                    <strong>E: sakit</strong>,
                    <strong>F: izin</strong>,
                    <strong>G: alpha</strong>.
                    Data siswa akan disambungkan berdasarkan NIS.
                  </span>
                </div>
              </div>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
              <span class="text-muted" style="font-size:13px;">
                Klik tombol di samping untuk mengunduh template Excel.
              </span>
              <a
                href="../../assets/templates/template_data_absensi.xlsx"
                class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2"
                download>
                <i class="fa-solid fa-file-excel"></i>
                <span>Download Template</span>
              </a>
            </div>

            <hr class="my-2">

            <div class="mb-2">
              <label for="excelFileAbsensi" class="form-label fw-semibold mb-1">
                Upload File Excel
              </label>
              <div class="position-relative d-flex align-items-center">
                <input
                  type="file"
                  class="form-control"
                  id="excelFileAbsensi"
                  name="excel_file"
                  accept=".xlsx,.xls"
                  style="padding-right:35px;"
                  required
                  onchange="toggleClearButtonAbsensiImport()">

                <button
                  type="button"
                  id="clearFileBtnAbsensiImport"
                  onclick="clearFileAbsensiImport()"
                  title="Hapus file"
                  style="
                    position:absolute;
                    right:10px;
                    background:none;
                    border:none;
                    color:#6c757d;
                    font-size:20px;
                    line-height:1;
                    display:none;
                    cursor:pointer;
                  ">
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
            <button type="button"
              class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"
              data-bs-dismiss="modal">
              <i class="fa fa-times"></i> Batal
            </button>
            <button type="submit"
              id="btnSubmitImportAbsensi"
              class="btn btn-warning d-inline-flex align-items-center gap-2">
              <i class="fas fa-upload"></i> Upload &amp; Proses
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- MODAL KONFIRMASI HAPUS (single & bulk) -->
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
          <button type="button"
            class="btn btn-outline-secondary"
            data-bs-dismiss="modal">
            Batal
          </button>
          <button type="button"
            class="btn btn-danger d-inline-flex align-items-center gap-2"
            id="confirmDeleteBtn">
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

      const checkAll = document.getElementById('checkAll');
      const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
      const perPageSelect = document.getElementById('perPage');
      const paginationWrap = document.getElementById('paginationWrap');
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

      let pendingDeleteHandler = null;
      let editMode = false;

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
          if (pendingDeleteHandler) {
            pendingDeleteHandler();
          }
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
        boxes.forEach(box => {
          box.addEventListener('change', updateBulkUI);
        });
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

      function buildPagination(totalRows, page, perPage) {
        currentTotalRows = totalRows;
        currentPage = page;
        currentPerPage = perPage;

        const totalPages = Math.max(1, Math.ceil(totalRows / perPage));
        if (page > totalPages) page = totalPages;

        let from, to, shown;
        if (totalRows === 0) {
          from = 0;
          to = 0;
          shown = 0;
        } else {
          from = (page - 1) * perPage + 1;
          to = Math.min(page * perPage, totalRows);
          shown = to - from + 1;
        }

        const pageDisplayCurrent = totalRows === 0 ? 0 : page;
        const pageDisplayTotal = totalRows === 0 ? 0 : totalPages;
        pageInfo.innerHTML =
          `Menampilkan <strong>${shown}</strong> dari <strong>${totalRows}</strong> data • 
           Halaman <strong>${pageDisplayCurrent}</strong> / <strong>${pageDisplayTotal}</strong>`;

        let html = '<ul class="pagination mb-0">';

        const isFirst = (page <= 1);
        const isLast = (page >= totalPages);

        html += `<li class="page-item${isFirst ? ' disabled' : ''}">
                   <button class="page-link page-btn" type="button" data-page="1">&laquo; First</button>
                 </li>`;

        html += `<li class="page-item${isFirst ? ' disabled' : ''}">
                   <button class="page-link page-btn" type="button" data-page="${page - 1}">&lsaquo; Prev</button>
                 </li>`;

        html += `<li class="page-item active">
                   <button class="page-link" type="button" data-page="${page}">${page}</button>
                 </li>`;

        html += `<li class="page-item${isLast ? ' disabled' : ''}">
                   <button class="page-link page-btn" type="button" data-page="${page + 1}">Next &rsaquo;</button>
                 </li>`;

        html += `<li class="page-item${isLast ? ' disabled' : ''}">
                   <button class="page-link page-btn" type="button" data-page="${totalPages}">Last &raquo;</button>
                 </li>`;

        html += '</ul>';

        paginationWrap.innerHTML = html;

        paginationWrap.querySelectorAll('.page-btn').forEach(btn => {
          btn.addEventListener('click', () => {
            const target = parseInt(btn.getAttribute('data-page') || '1', 10);
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

      // Toggle mode edit
      toggleEditMode.addEventListener('change', () => {
        editMode = toggleEditMode.checked;
        applyEditModeToTable();
        bulkSaveBtn.disabled = !editMode;
      });

      // Simpan perubahan (submit form bulk edit)
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
            applyEditModeToTable(); // supaya mode edit tetap aktif setelah pencarian
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

      // Validasi modal Tambah & Import
      const formTambah = document.getElementById('formTambahAbsensi');
      const btnSubmitTambah = document.getElementById('btnSubmitTambahAbsensi');
      if (formTambah && btnSubmitTambah) {
        btnSubmitTambah.addEventListener('click', (e) => {
          if (!formTambah.checkValidity()) {
            e.preventDefault();
            formTambah.reportValidity();
          }
        });
      }

      const formImport = document.getElementById('formImportAbsensi');
      const btnSubmitImport = document.getElementById('btnSubmitImportAbsensi');
      if (formImport && btnSubmitImport) {
        btnSubmitImport.addEventListener('click', (e) => {
          if (!formImport.checkValidity()) {
            e.preventDefault();
            formImport.reportValidity();
          }
        });
      }

      // Inisialisasi awal
      attachCheckboxEvents();
      attachSingleDeleteEvents();
      buildPagination(currentTotalRows, currentPage, currentPerPage);
      applyEditModeToTable(); // default: off

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