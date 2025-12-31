<?php
// pages/kelas/data_kelas.php
require_once '../../koneksi.php';
include '../../includes/header.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

$search  = isset($_GET['q']) ? trim($_GET['q']) : '';
$tingkat = isset($_GET['tingkat']) ? trim($_GET['tingkat']) : '';
$allowedTingkat = ['', 'X', 'XI', 'XII'];
if (!in_array($tingkat, $allowedTingkat, true)) $tingkat = '';

$like = "%{$search}%";
$tingkatParam = $tingkat ?: '';

// data awal (tanpa pagination)
$sql = "
  SELECT
    k.id_kelas, k.id_guru, k.nama_kelas, k.tingkat_kelas,
    g.nama_guru AS wali_kelas,
    (SELECT COUNT(*) FROM siswa s WHERE s.id_kelas = k.id_kelas) AS jumlah_siswa
  FROM kelas k
  LEFT JOIN guru g ON g.id_guru = k.id_guru
  WHERE (k.nama_kelas LIKE ? OR COALESCE(g.nama_guru,'') LIKE ?)
    AND (? = '' OR k.tingkat_kelas = ?)
  ORDER BY FIELD(k.tingkat_kelas,'X','XI','XII'), k.nama_kelas
";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'ssss', $like, $like, $tingkatParam, $tingkatParam);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// list guru (wali wajib)
$guruList = [];
$resGuru = mysqli_query($koneksi, "SELECT id_guru, nama_guru FROM guru ORDER BY nama_guru ASC");
while ($g = mysqli_fetch_assoc($resGuru)) $guruList[] = $g;

$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';
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
    }

    .content {
      padding: clamp(12px, 2vw, 20px);
      padding-bottom: 160px;
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

    /* samakan dengan data_guru */

    /* samakan dengan data_guru (lebih pendek) */
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

    /* filter disamakan barisnya dengan data_guru (rapi + lebih pendek) */
    .search-perpage-row {
      gap: 12px;
    }

    .filter-wrap {
      width: 140px;
      min-width: 140px;
    }

    /* lebih pendek dari sebelumnya */
    .filter-select {
      border: 1px solid var(--ring);
      border-radius: 10px;
      padding: 6px 12px;
      font-size: 14px;
      color: var(--ink);
      background: #fff;
      text-align: center;
    }

    /* caret (v terbalik) seperti select data/hal di data guru */
    .filter-select {
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23475569' viewBox='0 0 16 16'%3E%3Cpath d='M1.5 5.5l6 6 6-6' stroke='%23475569' stroke-width='1.8' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 10px center;
      background-size: 12px;
      padding-right: 32px;
      /* ruang untuk icon */
    }

    .filter-select option {
      text-align: center;
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

    #kelasTbody {
      transition: opacity .25s ease, transform .25s ease;
    }

    #kelasTbody.tbody-loading {
      opacity: .4;
      transform: scale(.995);
    }

    #kelasTbody.tbody-loaded {
      opacity: 1;
      transform: scale(1);
    }

    #kelasTableWrap {
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

    .dk-alert {
      padding: 12px 14px;
      border-radius: 12px;
      margin-bottom: 20px;
      font-size: 14px;
      max-height: 220px;
      overflow: hidden;
      position: relative;
      opacity: 0;
      transform: translateY(-10px);
      transition: opacity .35s ease, transform .35s ease, max-height .35s ease, margin .35s ease, padding .35s ease;
    }

    .dk-alert.dk-show {
      opacity: 1;
      transform: translateY(0);
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
    }

    .dk-alert .close-btn:hover {
      opacity: 1;
    }

    #alertAreaTop {
      position: relative;
    }

    .modal-alert-area {
      margin-bottom: 12px;
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

      .filter-wrap {
        width: 100%;
        min-width: 0;
      }

      .search-perpage-row {
        flex-direction: column;
        align-items: stretch !important;
      }

      /* samakan pola mobile data_guru */
      .search-wrap {
        max-width: 100%;
      }
    }
  </style>

  <main class="content">
    <div class="row g-3">
      <div class="col-12">

        <div id="alertAreaTop">
          <?php if ($msg !== ''): ?>
            <div class="dk-alert dk-alert-success" data-auto-hide="4000">
              <span class="close-btn">&times;</span>
              ✅ <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>

          <?php if ($err !== ''): ?>
            <div class="dk-alert dk-alert-danger" data-auto-hide="4000">
              <span class="close-btn">&times;</span>
              ❌ <?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="card shadow-sm">
          <div class="top-bar p-3 p-md-4">
            <div class="d-flex flex-column gap-3 w-100">
              <div>
                <h5 class="page-title mb-0 fw-bold fs-4">Data Kelas</h5>
              </div>

              <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2">
                <div class="d-flex search-perpage-row align-items-md-center flex-grow-1">
                  <div class="search-wrap flex-grow-1">
                    <div class="searchbox" role="search" aria-label="Pencarian kelas">
                      <i class="bi bi-search icon"></i>
                      <input type="text" id="searchInput" placeholder="Ketik untuk mencari" autofocus>
                    </div>
                  </div>

                  <div class="filter-wrap">
                    <select id="tingkatFilter" class="form-select filter-select" aria-label="Filter Tingkat">
                      <option value="">Semua Tingkat </option>
                      <option value="X" <?= $tingkat === 'X'  ? 'selected' : '' ?>>Tingkat X</option>
                      <option value="XI" <?= $tingkat === 'XI' ? 'selected' : '' ?>>Tingkat XI</option>
                      <option value="XII" <?= $tingkat === 'XII' ? 'selected' : '' ?>>Tingkat XII</option>
                    </select>
                  </div>
                </div>

                <div class="d-flex justify-content-md-end flex-wrap gap-2">
                  <button type="button"
                    class="btn btn-brand btn-sm d-inline-flex align-items-center gap-2 px-3"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTambahKelas">
                    <i class="fa-solid fa-plus fa-lg"></i> Tambah Kelas
                  </button>

                  <button type="button"
                    class="btn btn-success btn-sm d-inline-flex align-items-center gap-2 px-3"
                    data-bs-toggle="modal"
                    data-bs-target="#modalImportKelas">
                    <i class="fa-solid fa-file-arrow-down fa-lg"></i> Import
                  </button>

                  <button id="exportBtn"
                    class="btn btn-success btn-sm d-inline-flex align-items-center gap-2 px-3"
                    type="button"
                    onclick="window.location='export.php'">
                    <i class="fa-solid fa-file-arrow-up fa-lg"></i> Export
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="card-body pt-0">
            <div class="table-responsive" id="kelasTableWrap">
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
                    <th>Nama Kelas</th>
                    <th style="width:140px;">Jumlah Siswa</th>
                    <th>Wali Kelas</th>
                    <th style="width:120px;">Tingkat</th>
                    <th style="width:220px;">Aksi</th>
                  </tr>
                </thead>

                <tbody id="kelasTbody" class="text-center tbody-loaded">
                  <?php if (!mysqli_num_rows($result)): ?>
                    <tr>
                      <td colspan="7">Belum ada data.</td>
                    </tr>
                    <?php else:
                    $no = 1;
                    $rowClass = ($search !== '' ? 'highlight-row' : '');
                    while ($row = mysqli_fetch_assoc($result)):
                    ?>
                      <tr class="<?= $rowClass ?>">
                        <td class="text-center" data-label="Pilih">
                          <input type="checkbox" class="row-check" value="<?= (int)$row['id_kelas'] ?>">
                        </td>
                        <td data-label="No"><?= $no++; ?></td>
                        <td data-label="Nama Kelas"><?= htmlspecialchars($row['nama_kelas']) ?></td>
                        <td data-label="Jumlah Siswa" class="text-center"><?= (int)$row['jumlah_siswa'] ?></td>
                        <td data-label="Wali Kelas"><?= htmlspecialchars($row['wali_kelas'] ?? '-') ?></td>
                        <td data-label="Tingkat" class="text-center"><?= htmlspecialchars($row['tingkat_kelas']) ?></td>
                        <td data-label="Aksi">
                          <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <button type="button"
                              class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-edit-kelas"
                              data-id="<?= (int)$row['id_kelas'] ?>"
                              data-nama="<?= htmlspecialchars($row['nama_kelas'], ENT_QUOTES, 'UTF-8') ?>"
                              data-tingkat="<?= htmlspecialchars($row['tingkat_kelas'], ENT_QUOTES, 'UTF-8') ?>"
                              data-idguru="<?= (int)($row['id_guru'] ?? 0) ?>">
                              <i class="bi bi-pencil-square"></i> Edit
                            </button>

                            <button type="button"
                              class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-delete-single"
                              data-id="<?= (int)$row['id_kelas'] ?>"
                              data-label="<?= htmlspecialchars($row['nama_kelas'], ENT_QUOTES, 'UTF-8') ?>">
                              <i class="bi bi-trash"></i> Hapus
                            </button>
                          </div>
                        </td>
                      </tr>
                  <?php endwhile;
                  endif;
                  mysqli_stmt_close($stmt); ?>
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

          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- MODAL TAMBAH -->
  <div class="modal fade" id="modalTambahKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Data Kelas</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>

        <form id="formTambahKelas" action="proses_tambah_data_kelas.php" method="POST" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
          <div class="modal-body">
            <div id="modalAlertTambah" class="modal-alert-area"></div>

            <div class="mb-3">
              <label class="form-label fw-semibold" for="add_nama_kelas">Nama Kelas</label>
              <input type="text" id="add_nama_kelas" name="nama_kelas" class="form-control" maxlength="100" required placeholder="Nama Kelas">
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold" for="add_tingkat_kelas">Tingkat</label>
              <select id="add_tingkat_kelas" name="tingkat_kelas" class="form-select" required>
                <option value="" disabled selected>Pilih Tingkat</option>
                <option value="X">Tingkat X</option>
                <option value="XI">Tingkat XI</option>
                <option value="XII">Tingkat XII</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold" for="add_id_guru">Wali Kelas</label>
              <select id="add_id_guru" name="id_guru" class="form-select" required>
                <option value="" disabled selected>Pilih Wali Kelas</option>
                <?php foreach ($guruList as $g): ?>
                  <option value="<?= (int)$g['id_guru'] ?>"><?= htmlspecialchars($g['nama_guru']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" data-bs-dismiss="modal">
              <i class="bi bi-x-lg"></i> Batal
            </button>
            <button type="submit" id="btnSubmitTambahKelas" class="btn btn-brand d-inline-flex align-items-center gap-2">
              <i class="bi bi-check2-circle"></i> Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- MODAL EDIT -->
  <div class="modal fade" id="modalEditKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Data Kelas</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>

        <form id="formEditKelas" action="proses_edit_data_kelas.php" method="POST" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="id_kelas" id="edit_id_kelas">
          <div class="modal-body">
            <div id="modalAlertEdit" class="modal-alert-area"></div>

            <div class="mb-3">
              <label class="form-label fw-semibold" for="edit_nama_kelas">Nama Kelas</label>
              <input type="text" id="edit_nama_kelas" name="nama_kelas" class="form-control" maxlength="100" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold" for="edit_tingkat_kelas">Tingkat</label>
              <select id="edit_tingkat_kelas" name="tingkat_kelas" class="form-select" required>
                <option value="X">Tingkat X</option>
                <option value="XI">Tingkat XI</option>
                <option value="XII">Tingkat XII</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold" for="edit_id_guru">Wali Kelas</label>
              <select id="edit_id_guru" name="id_guru" class="form-select" required>
                <option value="" disabled>Pilih Wali Kelas</option>
                <?php foreach ($guruList as $g): ?>
                  <option value="<?= (int)$g['id_guru'] ?>"><?= htmlspecialchars($g['nama_guru']) ?></option>
                <?php endforeach; ?>
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

  <!-- MODAL IMPORT -->
  <div class="modal fade" id="modalImportKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div>
            <h5 class="modal-title fw-semibold">Import Data Kelas</h5>
            <p class="mb-0 text-muted" style="font-size: 13px;">Gunakan template resmi agar susunan kolom sesuai dengan sistem.</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>

        <form id="formImportKelas" action="proses_import_data_kelas.php" method="POST" enctype="multipart/form-data" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
          <div class="modal-body pt-3">
            <div id="modalAlertImport" class="modal-alert-area"></div>

            <div class="mb-3 p-3 rounded-3" style="background:#f9fafb;border:1px solid #e5e7eb;">
              <div class="d-flex align-items-start gap-2">
                <div class="mt-1"><i class="fa-solid fa-circle-info" style="color:#0a4db3;"></i></div>
                <div style="font-size:13px;">
                  <strong>Langkah import data kelas:</strong>
                  <ol class="mb-1 ps-3" style="padding-left:18px;">
                    <li>Download template Excel terlebih dahulu.</li>
                    <li>Isi data kelas sesuai kolom yang tersedia.</li>
                    <li>Upload kembali file Excel tersebut di form ini.</li>
                  </ol>
                  <span class="text-muted">
                    Struktur kolom template:
                    <strong>A: No.</strong>, <strong>B: Nama Kelas</strong>, <strong>C: Tingkat</strong>, <strong>D: Wali Kelas</strong>.
                  </span>
                </div>
              </div>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
              <span class="text-muted" style="font-size:13px;">Klik tombol di samping untuk mengunduh template Excel.</span>
              <a href="../../assets/templates/template_data_kelas.xlsx" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2" download>
                <i class="fa-solid fa-file-excel"></i><span>Download Template</span>
              </a>
            </div>

            <hr class="my-2">

            <div class="mb-2">
              <label for="excelFileKelas" class="form-label fw-semibold mb-1">Upload File Excel</label>
              <div class="position-relative d-flex align-items-center">
                <input type="file" class="form-control" id="excelFileKelas" name="excel_file" accept=".xlsx,.xls"
                  style="padding-right:35px;" required onchange="toggleClearButtonKelasImport()">
                <button type="button" id="clearFileBtnKelasImport" onclick="clearFileKelasImport()" title="Hapus file"
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
            <button type="submit" id="btnSubmitImportKelas" class="btn btn-warning d-inline-flex align-items-center gap-2">
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
    function toggleClearButtonKelasImport() {
      const fileInput = document.getElementById('excelFileKelas');
      const clearBtn = document.getElementById('clearFileBtnKelasImport');
      if (!fileInput || !clearBtn) return;
      clearBtn.style.display = fileInput.files.length > 0 ? 'block' : 'none';
    }

    function clearFileKelasImport() {
      const fileInput = document.getElementById('excelFileKelas');
      const clearBtn = document.getElementById('clearFileBtnKelasImport');
      if (!fileInput || !clearBtn) return;
      fileInput.value = '';
      clearBtn.style.display = 'none';
    }
  </script>

  <script>
    (function() {
      const input = document.getElementById('searchInput');
      const tingkatFilter = document.getElementById('tingkatFilter');

      const tbody = document.getElementById('kelasTbody');
      const tableWrap = document.getElementById('kelasTableWrap');
      const loadingOverlay = document.getElementById('tableLoadingOverlay');

      const checkAll = document.getElementById('checkAll');
      const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

      const csrfToken = '<?= htmlspecialchars($csrf, ENT_QUOTES, "UTF-8"); ?>';

      const confirmModalEl = document.getElementById('confirmDeleteModal');
      const confirmBodyEl = document.getElementById('confirmDeleteBody');
      const confirmBtn = document.getElementById('confirmDeleteBtn');

      let typingTimer;
      const debounceMs = 250;
      let currentController = null;

      let currentQuery = '<?= htmlspecialchars($search, ENT_QUOTES, "UTF-8"); ?>';
      let currentTingkat = '<?= htmlspecialchars($tingkat, ENT_QUOTES, "UTF-8"); ?>';

      let pendingDeleteHandler = null;

      const ALERT_DURATION = 4000;

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
        const close = el.querySelector('.close-btn');
        if (close) {
          close.addEventListener('click', (e) => {
            e.preventDefault();
            clearTimeout(timer);
            animateAlertOut(el);
          });
        }
      }

      function escapeHtml(str) {
        return String(str ?? '')
          .replaceAll('&', '&amp;')
          .replaceAll('<', '&lt;')
          .replaceAll('>', '&gt;')
          .replaceAll('"', '&quot;')
          .replaceAll("'", "&#039;");
      }

      function showTopAlert(type, message) {
        const area = document.getElementById('alertAreaTop');
        if (!area) return;

        const cls = type === 'success' ? 'dk-alert-success' : type === 'warning' ? 'dk-alert-warning' : 'dk-alert-danger';
        const icon = type === 'success' ? '✅' : type === 'warning' ? '⚠️' : '❌';

        const div = document.createElement('div');
        div.className = `dk-alert ${cls}`;
        div.setAttribute('data-auto-hide', String(ALERT_DURATION));
        div.innerHTML = `<span class="close-btn">&times;</span> ${icon} ${escapeHtml(message)}`;

        area.prepend(div);
        wireAlert(div);
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      }

      function showModalAlert(containerId, type, message) {
        const box = document.getElementById(containerId);
        if (!box) return;

        box.innerHTML = '';
        const cls = type === 'success' ? 'dk-alert-success' : type === 'warning' ? 'dk-alert-warning' : 'dk-alert-danger';
        const icon = type === 'success' ? '✅' : type === 'warning' ? '⚠️' : '❌';

        const div = document.createElement('div');
        div.className = `dk-alert ${cls}`;
        div.setAttribute('data-auto-hide', String(ALERT_DURATION));
        div.innerHTML = `<span class="close-btn">&times;</span> ${icon} ${escapeHtml(message)}`;

        box.appendChild(div);
        wireAlert(div);
      }
      document.querySelectorAll('#alertAreaTop .dk-alert').forEach(wireAlert);

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

      function attachEditModalEvents() {
        const editButtons = document.querySelectorAll('.btn-edit-kelas');
        const modalEl = document.getElementById('modalEditKelas');
        if (!modalEl) return;

        const inputId = document.getElementById('edit_id_kelas');
        const inputNama = document.getElementById('edit_nama_kelas');
        const inputTingkat = document.getElementById('edit_tingkat_kelas');
        const inputGuru = document.getElementById('edit_id_guru');

        editButtons.forEach(btn => {
          btn.addEventListener('click', (e) => {
            e.preventDefault();
            const id = btn.getAttribute('data-id') || '';
            const nama = btn.getAttribute('data-nama') || '';
            const tingkat = btn.getAttribute('data-tingkat') || 'X';
            const idguru = btn.getAttribute('data-idguru') || '';

            if (inputId) inputId.value = id;
            if (inputNama) inputNama.value = nama;
            if (inputTingkat) inputTingkat.value = tingkat;
            if (inputGuru) inputGuru.value = idguru;

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
            const label = btn.getAttribute('data-label') || 'kelas ini';
            if (!id) return;

            showDeleteConfirm(`Yakin ingin menghapus kelas "${label}"?`, () => {
              const form = document.createElement('form');
              form.method = 'post';
              form.action = 'hapus_data_kelas.php';

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

      if (checkAll) {
        checkAll.addEventListener('change', () => {
          const boxes = getRowCheckboxes();
          boxes.forEach(b => b.checked = checkAll.checked);
          updateBulkUI();
        });
      }

      if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', () => {
          const boxes = getRowCheckboxes().filter(b => b.checked);
          if (boxes.length === 0) return;

          const count = boxes.length;
          const form = document.createElement('form');
          form.method = 'post';
          form.action = 'hapus_data_kelas.php';

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

          showDeleteConfirm(`Yakin ingin menghapus ${count} data kelas terpilih?`, () => {
            document.body.appendChild(form);
            form.submit();
          });
        });
      }

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

      function doSearch(query, tingkat, useScroll = false) {
        setLoading(useScroll);

        if (currentController) currentController.abort();
        currentController = new AbortController();

        currentQuery = query || '';
        currentTingkat = tingkat || '';

        const params = new URLSearchParams({
          q: currentQuery,
          tingkat: currentTingkat
        });

        fetch('ajax_kelas_list.php?' + params.toString(), {
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
            attachCheckboxEvents();
            attachEditModalEvents();
            attachSingleDeleteEvents();
            finishLoading();
          })
          .catch(e => {
            if (e.name === 'AbortError') return;
            tbody.innerHTML = `<tr><td colspan="7">Gagal memuat data.</td></tr>`;
            finishLoading();
            console.error(e);
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

      async function postFormAjax(form, btn, modalAlertId, onSuccess, onError) {
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
            const m = 'Respon server tidak valid.';
            onError ? onError({
              ok: false,
              type: 'danger',
              msg: m
            }) : showModalAlert(modalAlertId, 'danger', m);
            disableBtn(btn, false);
            return;
          }

          if (data.ok) {
            onSuccess && onSuccess(data);
          } else {
            onError ? onError(data) : showModalAlert(modalAlertId, data.type || 'danger', data.msg || 'Terjadi kesalahan.');
          }
        } catch (err) {
          const m = 'Gagal terhubung ke server.';
          onError ? onError({
            ok: false,
            type: 'danger',
            msg: m
          }) : showModalAlert(modalAlertId, 'danger', m);
          console.error(err);
        } finally {
          disableBtn(btn, false);
        }
      }

      // fokus pencarian saat halaman dibuka
      window.addEventListener('load', () => {
        if (input) {
          input.focus({
            preventScroll: true
          });
          const v = input.value;
          input.value = '';
          input.value = v;
        }
      });

      if (input) input.value = currentQuery;
      if (tingkatFilter) tingkatFilter.value = currentTingkat;

      if (input) {
        input.addEventListener('input', () => {
          clearTimeout(typingTimer);
          typingTimer = setTimeout(() => {
            doSearch(input.value, currentTingkat, false);
          }, debounceMs);
        });
      }

      if (tingkatFilter) {
        tingkatFilter.addEventListener('change', () => {
          currentTingkat = tingkatFilter.value || '';
          doSearch(currentQuery, currentTingkat, true);
        });
      }

      // tambah
      const formTambah = document.getElementById('formTambahKelas');
      const btnTambah = document.getElementById('btnSubmitTambahKelas');
      const modalTambahEl = document.getElementById('modalTambahKelas');

      if (formTambah) {
        formTambah.addEventListener('submit', (e) => {
          e.preventDefault();
          postFormAjax(
            formTambah,
            btnTambah,
            'modalAlertTambah',
            (data) => {
              if (typeof bootstrap !== 'undefined' && modalTambahEl) {
                bootstrap.Modal.getOrCreateInstance(modalTambahEl).hide();
              }
              formTambah.reset();
              doSearch(currentQuery, currentTingkat, true);
              showTopAlert(data.type || 'success', data.msg || 'Berhasil.');
            },
            (data) => {
              showModalAlert('modalAlertTambah', data.type || 'warning', data.msg || 'Terjadi kesalahan.');
            }
          );
        });
      }

      // edit
      const formEdit = document.getElementById('formEditKelas');
      const modalEditEl = document.getElementById('modalEditKelas');

      if (formEdit) {
        formEdit.addEventListener('submit', (e) => {
          e.preventDefault();
          const submitBtn = formEdit.querySelector('button[type="submit"]');
          postFormAjax(
            formEdit,
            submitBtn,
            'modalAlertEdit',
            (data) => {
              if (typeof bootstrap !== 'undefined' && modalEditEl) {
                bootstrap.Modal.getOrCreateInstance(modalEditEl).hide();
              }
              doSearch(currentQuery, currentTingkat, true);
              showTopAlert(data.type || 'success', data.msg || 'Berhasil.');
            },
            (data) => {
              showModalAlert('modalAlertEdit', data.type || 'warning', data.msg || 'Terjadi kesalahan.');
            }
          );
        });
      }

      // import -> alert di luar modal
      const formImport = document.getElementById('formImportKelas');
      const btnImport = document.getElementById('btnSubmitImportKelas');
      const modalImportEl = document.getElementById('modalImportKelas');

      if (formImport) {
        formImport.addEventListener('submit', (e) => {
          e.preventDefault();
          postFormAjax(
            formImport,
            btnImport,
            'modalAlertImport',
            (data) => {
              if (typeof bootstrap !== 'undefined' && modalImportEl) {
                bootstrap.Modal.getOrCreateInstance(modalImportEl).hide();
              }
              formImport.reset();
              clearFileKelasImport();
              doSearch(currentQuery, currentTingkat, true);
              showTopAlert(data.type || 'success', data.msg || 'Import selesai.');
            },
            (data) => {
              showTopAlert(data.type || 'warning', data.msg || 'Import bermasalah.');
            }
          );
        });
      }

      // init
      attachCheckboxEvents();
      attachEditModalEvents();
      attachSingleDeleteEvents();

      // bersihkan alert modal saat dibuka + fokus field
      const modalTambah = document.getElementById('modalTambahKelas');
      if (modalTambah) {
        modalTambah.addEventListener('shown.bs.modal', () => {
          const box = document.getElementById('modalAlertTambah');
          if (box) box.innerHTML = '';
          const field = document.getElementById('add_nama_kelas');
          if (field) field.focus();
        });
      }
      const modalEdit = document.getElementById('modalEditKelas');
      if (modalEdit) {
        modalEdit.addEventListener('shown.bs.modal', () => {
          const box = document.getElementById('modalAlertEdit');
          if (box) box.innerHTML = '';
        });
      }
      const modalImport = document.getElementById('modalImportKelas');
      if (modalImport) {
        modalImport.addEventListener('shown.bs.modal', () => {
          const box = document.getElementById('modalAlertImport');
          if (box) box.innerHTML = '';
        });
      }

    })();
  </script>

  <?php include '../../includes/footer.php'; ?>
</body>