<?php
include '../includes/header.php';
include '../includes/navbar.php';
require_once '../koneksi.php';

// Session + CSRF untuk bulk delete
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$like   = "%{$search}%";

// Pagination server-side (untuk tampilan awal sebelum AJAX)
$allowedPer = [10, 20, 50, 100, 1000000]; // ✅ tambah "Semua"
$perPage    = isset($_GET['per']) ? (int)$_GET['per'] : 10;
if (!in_array($perPage, $allowedPer, true)) {
  $perPage = 10;
}
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Hitung total data
$countSql = "
  SELECT COUNT(*) AS total
  FROM user u
  LEFT JOIN guru g ON g.id_guru = u.id_guru
  WHERE u.username LIKE ?
     OR COALESCE(g.nama_guru,'') LIKE ?
";
$stmtCount = mysqli_prepare($koneksi, $countSql);
mysqli_stmt_bind_param($stmtCount, 'ss', $like, $like);
mysqli_stmt_execute($stmtCount);
$resCount  = mysqli_stmt_get_result($stmtCount);
$rowCount  = mysqli_fetch_assoc($resCount);
$totalRows = (int)($rowCount['total'] ?? 0);

$totalPages = max(1, (int)ceil($totalRows / $perPage));
if ($page > $totalPages) {
  $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

// Ambil data user untuk halaman awal
$sql = "
  SELECT 
    u.id_user, 
    u.username, 
    u.password_user,
    u.role_user, 
    u.id_guru, 
    g.nama_guru
  FROM user u
  LEFT JOIN guru g ON g.id_guru = u.id_guru
  WHERE u.username LIKE ?
     OR COALESCE(g.nama_guru,'') LIKE ?
  ORDER BY u.id_user DESC
  LIMIT ? OFFSET ?
";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'ssii', $like, $like, $perPage, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Ambil daftar guru untuk modal tambah & edit
$guruList = [];
$guruRes = mysqli_query($koneksi, "SELECT id_guru, nama_guru FROM guru ORDER BY nama_guru ASC");
while ($g = mysqli_fetch_assoc($guruRes)) {
  $guruList[] = $g;
}

// Hitung info awal
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
    /* ✅ ruang bawah agar dropdown perPage kebuka ke bawah */
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

  .role-badge {
    background: #ecf2ff;
    color: #0a2a88;
    border: 1px solid #d6e2ff;
    font-weight: 700;
  }

  .highlight-row {
    background-color: #d4edda !important;
  }

  .password-text {
    font-family: monospace;
  }

  /* TRANSISI TABEL + OVERLAY LOADING */
  #userTbody {
    transition: opacity 0.25s ease, transform 0.25s ease;
  }

  #userTbody.tbody-loading {
    opacity: 0.4;
    transform: scale(0.995);
  }

  #userTbody.tbody-loaded {
    opacity: 1;
    transform: scale(1);
  }

  #userTableWrap {
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

  /* Tombol & hover */
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

  .toggle-password {
    padding: 4px 8px !important;
  }

  .toggle-password:hover {
    background: #dfe3e6 !important;
    color: #333 !important;
  }

  /* Styling dropdown per halaman */
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

  /* ===========================
     ✅ ALERT ANIMATION
     =========================== */
  .alert {
    padding: 12px 14px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-size: 14px;
    max-height: 220px;
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

  .alert-anim {
    opacity: 0;
    transform: translateY(-8px);
    transition:
      opacity 0.35s ease,
      transform 0.35s ease,
      max-height 0.35s ease,
      margin 0.35s ease,
      padding-top 0.35s ease,
      padding-bottom 0.35s ease;
    will-change: opacity, transform;
  }

  .alert-show {
    opacity: 1;
    transform: translateY(0);
  }

  .alert-hide {
    opacity: 0;
    transform: translateY(-6px);
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
    user-select: none;
  }

  .alert .close-btn:hover {
    opacity: 1;
  }

  /* ✅ PAGINATION GROUP */
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

      <!-- CONTAINER ALERT GLOBAL -->
      <div id="globalAlertContainer">
        <?php if (isset($_GET['status'])): ?>
          <?php if ($_GET['status'] === 'success'): ?>
            <div class="alert alert-success alert-anim">
              <span class="close-btn">&times;</span>
              ✅ <?= htmlspecialchars($_GET['msg'] ?? 'Operasi berhasil.', ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php else: ?>
            <div class="alert alert-danger alert-anim">
              <span class="close-btn">&times;</span>
              ❌ <?= htmlspecialchars($_GET['msg'] ?? 'Terjadi kesalahan.', ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <div class="card shadow-sm">
        <div class="top-bar p-3 p-md-4">
          <div class="d-flex flex-column gap-3 w-100">
            <div>
              <h5 class="page-title mb-0 fw-bold fs-4">Data User</h5>
            </div>

            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2">
              <div class="d-flex search-perpage-row align-items-md-center gap-2 flex-grow-1">
                <div class="search-wrap flex-grow-1">
                  <div class="searchbox" role="search" aria-label="Pencarian user">
                    <i class="bi bi-search icon"></i>
                    <input type="text" id="searchInput" placeholder="Ketik untuk mencari" autofocus>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-md-end">
                <button type="button"
                  class="btn btn-brand btn-sm d-inline-flex align-items-center gap-2 px-3"
                  data-bs-toggle="modal"
                  data-bs-target="#modalTambahUser">
                  <i class="bi bi-person-plus"></i> Tambah User
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="card-body pt-0">
          <div class="table-responsive" id="userTableWrap">
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
                  <th>Username</th>
                  <th>Password</th>
                  <th>Role</th>
                  <th style="width:200px;">Aksi</th>
                </tr>
              </thead>
              <tbody id="userTbody" class="text-center tbody-loaded">
                <?php if (mysqli_num_rows($result) === 0): ?>
                  <tr>
                    <td colspan="7">Belum ada data</td>
                  </tr>
                  <?php else:
                  $no = $offset + 1;
                  $rowClass = ($search !== '') ? 'highlight-row' : '';
                  while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="<?= $rowClass; ?>">
                      <td class="text-center" data-label="Pilih">
                        <input type="checkbox" class="row-check" value="<?= (int)$row['id_user'] ?>">
                      </td>
                      <td data-label="No"><?= $no++; ?></td>
                      <td data-label="Nama (Guru)"><?= htmlspecialchars($row['nama_guru'] ?? '-') ?></td>
                      <td data-label="Username"><?= htmlspecialchars($row['username']) ?></td>
                      <td data-label="Password">
                        <?php
                        $pwd = $row['password_user'] ?? '';
                        if ($pwd === '') echo '-';
                        else { ?>
                          <div class="d-inline-flex align-items-center gap-1 password-cell">
                            <span class="password-text" data-visible="0">••••••</span>
                            <button type="button"
                              class="btn btn-sm btn-outline-secondary toggle-password"
                              data-password="<?= htmlspecialchars($pwd, ENT_QUOTES, 'UTF-8') ?>"
                              title="Lihat / sembunyikan password">
                              <i class="bi bi-eye"></i>
                            </button>
                          </div>
                        <?php } ?>
                      </td>
                      <td data-label="Role">
                        <span class="badge role-badge"><?= htmlspecialchars($row['role_user']) ?></span>
                      </td>
                      <td data-label="Aksi">
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                          <button type="button"
                            class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-edit-user"
                            data-id="<?= (int)$row['id_user'] ?>"
                            data-role="<?= htmlspecialchars($row['role_user'], ENT_QUOTES, 'UTF-8') ?>"
                            data-id-guru="<?= (int)($row['id_guru'] ?? 0) ?>"
                            data-username="<?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?>">
                            <i class="bi bi-pencil-square"></i> Edit
                          </button>

                          <button type="button"
                            class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-delete-single"
                            data-href="hapus_data_user.php?id=<?= (int)$row['id_user'] ?>"
                            data-label="<?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?>">
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
                    <?php if ($opt === 1000000): ?>
                      <option value="1000000" <?= $perPage === 1000000 ? 'selected' : '' ?>>Semua</option>
                    <?php else: ?>
                      <option value="<?= $opt ?>" <?= $perPage === $opt ? 'selected' : '' ?>>
                        <?= $opt ?>/hal
                      </option>
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

  <!-- MODAL TAMBAH USER -->
  <div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Data User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <form action="proses_tambah_data_user.php" method="POST" autocomplete="off">
          <div class="modal-body">
            <div id="addUserAlert" class="alert alert-danger alert-anim d-none mb-3"></div>

            <div class="mb-3">
              <label for="add_role" class="form-label fw-semibold">Role</label>
              <select id="add_role" name="role" class="form-select" required>
                <option value="" disabled selected>-- Pilih Role --</option>
                <option value="Admin">Admin</option>
                <option value="Guru">Guru</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="add_id_guru" class="form-label fw-semibold">Pilih Guru</label>
              <select id="add_id_guru" name="id_guru" class="form-select" required>
                <option value="" disabled selected>-- Pilih Guru --</option>
                <?php foreach ($guruList as $g): ?>
                  <option value="<?= (int)$g['id_guru'] ?>"><?= htmlspecialchars($g['nama_guru']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="add_username" class="form-label fw-semibold">Username</label>
              <input type="text" id="add_username" name="username" maxlength="50" class="form-control" required>
            </div>

            <div class="mb-3">
              <label for="add_password_user" class="form-label fw-semibold">Password</label>
              <input type="password" id="add_password_user" name="password_user" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" data-bs-dismiss="modal">
              <i class="bi bi-x-lg"></i> Batal
            </button>
            <button type="submit" class="btn btn-brand d-inline-flex align-items-center gap-2">
              <i class="bi bi-check2-circle"></i> Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- MODAL EDIT USER -->
  <div class="modal fade" id="modalEditUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Data User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <form action="proses_edit_data_user.php" method="POST" autocomplete="off">
          <input type="hidden" name="id_user" id="edit_id_user">
          <div class="modal-body">
            <div id="editUserAlert" class="alert alert-danger alert-anim d-none mb-3"></div>

            <div class="mb-3">
              <label for="edit_role" class="form-label fw-semibold">Role</label>
              <select id="edit_role" name="role" class="form-select" required>
                <option value="" disabled>-- Pilih Role --</option>
                <option value="Admin">Admin</option>
                <option value="Guru">Guru</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="edit_id_guru" class="form-label fw-semibold">Pilih Guru</label>
              <select id="edit_id_guru" name="id_guru" class="form-select" required>
                <option value="" disabled>-- Pilih Guru --</option>
                <?php foreach ($guruList as $g): ?>
                  <option value="<?= (int)$g['id_guru'] ?>"><?= htmlspecialchars($g['nama_guru']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="edit_username" class="form-label fw-semibold">Username</label>
              <input type="text" id="edit_username" name="username" maxlength="50" class="form-control" required>
            </div>

            <div class="mb-3">
              <label for="edit_password_user" class="form-label fw-semibold">Password (opsional)</label>
              <input type="password" id="edit_password_user" name="password_user" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
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

</main>

<script>
  (function() {
    const input = document.getElementById('searchInput');
    const tbody = document.getElementById('userTbody');
    const tableWrap = document.getElementById('userTableWrap');
    const loadingOverlay = document.getElementById('tableLoadingOverlay');

    const checkAll = document.getElementById('checkAll');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

    const perPageSelect = document.getElementById('perPage');
    const paginationUl = document.getElementById('paginationWrap');
    const pageInfo = document.getElementById('pageInfo');

    const csrfToken = '<?= htmlspecialchars($csrf, ENT_QUOTES, "UTF-8"); ?>';

    const confirmModalEl = document.getElementById('confirmDeleteModal');
    const confirmBodyEl = document.getElementById('confirmDeleteBody');
    const confirmBtn = document.getElementById('confirmDeleteBtn');

    const addUserAlert = document.getElementById('addUserAlert');
    const editUserAlert = document.getElementById('editUserAlert');
    const globalAlertContainer = document.getElementById('globalAlertContainer');

    const modalAddEl = document.getElementById('modalTambahUser');
    const modalEditEl = document.getElementById('modalEditUser');

    let typingTimer;
    const debounceMs = 250;
    let currentController = null;

    let currentQuery = '<?= htmlspecialchars($search, ENT_QUOTES, "UTF-8"); ?>';
    let currentPage = <?= (int)$page ?>;
    let currentPerPage = <?= (int)$perPage ?>;
    let currentTotalRows = <?= (int)$totalRows ?>;

    let pendingDeleteHandler = null;

    // ✅ AUTO SCROLL KE ATAS SAAT ALERT MUNCUL
    function scrollToTopSmooth() {
      try {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      } catch (e) {
        window.scrollTo(0, 0);
      }
      // fallback tambahan (beberapa browser)
      document.documentElement.scrollTop = 0;
      document.body.scrollTop = 0;
    }

    // ✅ PAKSA AUTO SCROLL KE ATAS jika ada alert (global / modal)
    function forceScrollTopOnAlertRender(el) {
      if (!el) return;

      // Delay kecil supaya DOM sudah ke-render sebelum scroll
      setTimeout(() => {
        scrollToTopSmooth();

        // Kalau alert-nya di dalam modal, scroll body modal juga ke atas
        const modal = el.closest('.modal');
        if (modal) {
          const modalBody = modal.querySelector('.modal-body');
          if (modalBody) {
            try {
              modalBody.scrollTo({
                top: 0,
                behavior: 'smooth'
              });
            } catch (e) {
              modalBody.scrollTop = 0;
            }
          }
          const modalDialog = modal.querySelector('.modal-dialog');
          if (modalDialog) {
            try {
              modalDialog.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
              });
            } catch (e) {}
          }
        }
      }, 10);
    }

    /* ===========================
       ✅ ALERT HELPERS (AMAN)
       =========================== */
    function animateIn(el) {
      if (!el) return;
      el.classList.add('alert-anim');
      requestAnimationFrame(() => el.classList.add('alert-show'));
      forceScrollTopOnAlertRender(el); // ✅ otomatis scroll saat alert muncul
    }

    function animateOut(el, after) {
      if (!el) return;
      if (!el.isConnected) return;
      el.classList.remove('alert-show');
      el.classList.add('alert-hide');
      setTimeout(() => {
        if (typeof after === 'function') after();
      }, 350);
    }

    function stopAlertTimer(box) {
      if (!box) return;
      const t = box.dataset.timerId ? parseInt(box.dataset.timerId, 10) : 0;
      if (t) {
        clearTimeout(t);
        delete box.dataset.timerId;
      }
    }

    function clearModalAlert(mode) {
      const box = (mode === 'add') ? addUserAlert : editUserAlert;
      if (!box) return;
      stopAlertTimer(box);
      box.innerHTML = '';
      box.classList.add('d-none');
      box.classList.remove('alert-hide', 'alert-show');
    }

    function showModalAlert(mode, message) {
      const box = (mode === 'add') ? addUserAlert : editUserAlert;
      if (!box) return;

      // ✅ bersihkan timer sebelumnya
      stopAlertTimer(box);

      box.classList.remove('d-none', 'alert-hide', 'alert-show');
      box.innerHTML = `<span class="close-btn">&times;</span> ${message}`;
      animateIn(box);

      const closeBtn = box.querySelector('.close-btn');

      const hide = () => {
        if (!box.isConnected || box.classList.contains('d-none')) return;
        animateOut(box, () => {
          box.classList.add('d-none');
          box.innerHTML = '';
          box.classList.remove('alert-hide', 'alert-show');
          stopAlertTimer(box);
        });
      };

      const timer = setTimeout(hide, 4000);
      box.dataset.timerId = String(timer);

      if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
          e.preventDefault();
          stopAlertTimer(box);
          hide();
        }, {
          once: true
        });
      }
    }

    function showTopAlert(type, message) {
      if (!globalAlertContainer) return;
      const isSuccess = (type === 'success');

      globalAlertContainer.innerHTML =
        `<div class="alert ${isSuccess ? 'alert-success' : 'alert-danger'} alert-anim">
        <span class="close-btn">&times;</span>
        ${isSuccess ? '✅' : '❌'} ${message}
      </div>`;

      const alertEl = globalAlertContainer.querySelector('.alert');
      if (!alertEl) return;

      animateIn(alertEl);

      const closeBtn = alertEl.querySelector('.close-btn');
      const timer = setTimeout(() => animateOut(alertEl), 4000);

      if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
          e.preventDefault();
          clearTimeout(timer);
          animateOut(alertEl);
        });
      }
    }

    // ✅ MutationObserver: kalau ada alert global baru (dari operasi apa pun), otomatis scroll ke atas
    (function observeGlobalAlert() {
      if (!globalAlertContainer || typeof MutationObserver === 'undefined') return;
      const obs = new MutationObserver(() => {
        const al = globalAlertContainer.querySelector('.alert');
        if (al) forceScrollTopOnAlertRender(al);
      });
      obs.observe(globalAlertContainer, {
        childList: true,
        subtree: true
      });
    })();

    // ✅ saat modal ditutup: matikan timer & bersihkan alert (fix error menghilang)
    if (modalAddEl && typeof bootstrap !== 'undefined') {
      modalAddEl.addEventListener('hidden.bs.modal', () => clearModalAlert('add'));
    }
    if (modalEditEl && typeof bootstrap !== 'undefined') {
      modalEditEl.addEventListener('hidden.bs.modal', () => clearModalAlert('edit'));
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

    function attachPasswordToggleEvents() {
      const buttons = tbody.querySelectorAll('.toggle-password');
      buttons.forEach(btn => {
        btn.addEventListener('click', () => {
          const cell = btn.closest('.password-cell');
          if (!cell) return;

          const span = cell.querySelector('.password-text');
          const icon = btn.querySelector('i');
          const visible = span.getAttribute('data-visible') === '1';
          const realPassword = btn.getAttribute('data-password') || '';

          if (visible) {
            span.textContent = '••••••';
            span.setAttribute('data-visible', '0');
            if (icon) {
              icon.classList.remove('bi-eye-slash');
              icon.classList.add('bi-eye');
            }
          } else {
            span.textContent = realPassword;
            span.setAttribute('data-visible', '1');
            if (icon) {
              icon.classList.remove('bi-eye');
              icon.classList.add('bi-eye-slash');
            }
          }
        });
      });
    }

    function attachEditModalEvents() {
      const editButtons = document.querySelectorAll('.btn-edit-user');
      const modalEl = document.getElementById('modalEditUser');
      if (!modalEl) return;

      const inputIdUser = document.getElementById('edit_id_user');
      const inputRole = document.getElementById('edit_role');
      const inputGuru = document.getElementById('edit_id_guru');
      const inputUser = document.getElementById('edit_username');
      const inputPass = document.getElementById('edit_password_user');

      editButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();

          const id = btn.getAttribute('data-id') || '';
          const role = btn.getAttribute('data-role') || '';
          const idGuru = btn.getAttribute('data-id-guru') || '';
          const username = btn.getAttribute('data-username') || '';

          if (inputIdUser) inputIdUser.value = id;
          if (inputRole) inputRole.value = role;
          if (inputGuru) inputGuru.value = (idGuru && idGuru !== '0') ? idGuru : '';
          if (inputUser) inputUser.value = username;
          if (inputPass) inputPass.value = '';

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

          const href = btn.getAttribute('data-href');
          const label = btn.getAttribute('data-label') || 'user ini';

          showDeleteConfirm(`Yakin ingin menghapus user "${label}"?`, () => {
            window.location.href = href;
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
          doSearch(currentQuery, target, currentPerPage, true);
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
      form.action = 'hapus_data_user.php';

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

      showDeleteConfirm(`Yakin ingin menghapus ${count} user terpilih?`, () => {
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

      fetch('ajax_user_list.php?' + params.toString(), {
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
          attachPasswordToggleEvents();
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

    function handleFormSubmit(form, mode) {
      clearModalAlert(mode);

      const submitBtn = form.querySelector('button[type="submit"]');
      const origHtml = submitBtn ? submitBtn.innerHTML : '';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
      }

      fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(r => {
          if (!r.ok) throw new Error('HTTP ' + r.status);
          return r.json();
        })
        .then(data => {
          if (!data || typeof data.success === 'undefined') throw new Error('Respon tidak valid');

          if (!data.success) {
            showModalAlert(mode, data.message || 'Terjadi kesalahan.');
            return;
          }

          if (typeof bootstrap !== 'undefined') {
            const modalEl = mode === 'add' ? modalAddEl : modalEditEl;
            if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).hide();
          }

          showTopAlert('success', data.message || 'Berhasil menyimpan data.');
          doSearch(currentQuery, currentPage, currentPerPage, true);

          if (mode === 'add') form.reset();
        })
        .catch(err => {
          console.error(err);
          showModalAlert(mode, 'Gagal mengirim data ke server.');
        })
        .finally(() => {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origHtml;
          }
        });
    }

    input.addEventListener('input', () => {
      clearTimeout(typingTimer);
      typingTimer = setTimeout(() => doSearch(input.value, 1, currentPerPage, false), debounceMs);
    });

    if (perPageSelect) {
      perPageSelect.addEventListener('change', () => {
        const val = parseInt(perPageSelect.value || '10', 10);
        if (isNaN(val) || val <= 0) return;
        currentPerPage = val;
        doSearch(currentQuery, 1, currentPerPage, true);
      });
    }

    // init
    attachCheckboxEvents();
    attachPasswordToggleEvents();
    attachEditModalEvents();
    attachSingleDeleteEvents();
    buildPagination(currentTotalRows, currentPage, currentPerPage);

    const formAdd = document.querySelector('#modalTambahUser form');
    if (formAdd) formAdd.addEventListener('submit', (e) => {
      e.preventDefault();
      handleFormSubmit(formAdd, 'add');
    });

    const formEdit = document.querySelector('#modalEditUser form');
    if (formEdit) formEdit.addEventListener('submit', (e) => {
      e.preventDefault();
      handleFormSubmit(formEdit, 'edit');
    });

    // animasikan alert global yang sudah ada dari PHP
    document.addEventListener('DOMContentLoaded', () => {
      const existing = globalAlertContainer ? globalAlertContainer.querySelectorAll('.alert.alert-anim') : [];

      if (existing.length > 0) {
        // ✅ kalau ada alert dari redirect hapus/tambah/edit dll, auto scroll ke atas
        scrollToTopSmooth();
      }

      existing.forEach(alertEl => {
        animateIn(alertEl);
        const closeBtn = alertEl.querySelector('.close-btn');
        const timer = setTimeout(() => animateOut(alertEl), 4000);
        if (closeBtn) {
          closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            clearTimeout(timer);
            animateOut(alertEl);
          });
        }
      });
    });

  })();
</script>

<?php include '../includes/footer.php'; ?>