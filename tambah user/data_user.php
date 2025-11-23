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
$allowedPer = [10, 20, 50, 100];
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
    max-width: 520px;
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

  /* Styling dropdown per halaman supaya mirip searchbox */
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

  /* ALERT – sama seperti di halaman Data Sekolah */
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

  /* Mobile “card table” */
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

      <!-- ALERT DI LUAR CARD, DI ATAS DATA USER -->
      <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'success'): ?>
          <div class="alert alert-success">
            <span class="close-btn">&times;</span>
            ✅ <?= htmlspecialchars($_GET['msg'] ?? 'Operasi berhasil.', ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php else: ?>
          <div class="alert alert-danger">
            <span class="close-btn">&times;</span>
            ❌ <?= htmlspecialchars($_GET['msg'] ?? 'Terjadi kesalahan.', ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <div class="card shadow-sm">

        <!-- TOP BAR -->
        <div class="top-bar p-3 p-md-4">
          <div class="d-flex flex-column gap-3 w-100">
            <div>
              <h5 class="page-title mb-0 fw-bold fs-4">Data User</h5>
            </div>

            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2">
              <!-- Search + per page -->
              <div class="d-flex search-perpage-row align-items-md-center gap-2 flex-grow-1">
                <div class="search-wrap flex-grow-1">
                  <div class="searchbox" role="search" aria-label="Pencarian user">
                    <i class="bi bi-search icon"></i>
                    <input type="text" id="searchInput" placeholder="Ketik untuk mencari" autofocus>
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

              <!-- Tombol Tambah User -> modal -->
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
          <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle mb-0">
              <thead class="text-center">
                <tr>
                  <th style="width:50px;" class="text-center">
                    <input type="checkbox" id="checkAll" title="Pilih Semua">
                  </th>
                  <th style="width:70px;">No</th>
                  <th>Nama Lengkap</th>
                  <th>Username</th>
                  <th>Password</th>
                  <th>Role</th>
                  <th style="width:200px;">Aksi</th>
                </tr>
              </thead>
              <tbody id="userTbody" class="text-center">
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
                        if ($pwd === '') {
                          echo '-';
                        } else {
                        ?>
                          <div class="d-inline-flex align-items-center gap-1 password-cell">
                            <span class="password-text" data-visible="0">••••••</span>
                            <button
                              type="button"
                              class="btn btn-sm btn-outline-secondary toggle-password"
                              data-password="<?= htmlspecialchars($pwd, ENT_QUOTES, 'UTF-8') ?>"
                              title="Lihat / sembunyikan password">
                              <i class="bi bi-eye"></i>
                            </button>
                          </div>
                        <?php
                        }
                        ?>
                      </td>
                      <td data-label="Role">
                        <span class="badge role-badge"><?= htmlspecialchars($row['role_user']) ?></span>
                      </td>
                      <td data-label="Aksi">
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                          <!-- Edit pakai modal -->
                          <button type="button"
                            class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-edit-user"
                            data-id="<?= (int)$row['id_user'] ?>"
                            data-role="<?= htmlspecialchars($row['role_user'], ENT_QUOTES, 'UTF-8') ?>"
                            data-id-guru="<?= (int)($row['id_guru'] ?? 0) ?>"
                            data-username="<?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?>">
                            <i class="bi bi-pencil-square"></i> Edit
                          </button>

                          <!-- Hapus pakai modal konfirmasi -->
                          <button type="button"
                            class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-delete-single"
                            data-href="hapus_user.php?id=<?= (int)$row['id_user'] ?>"
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

          <!-- HAPUS TERPILIH -->
          <div class="mt-3 d-flex justify-content-start">
            <button type="button" id="bulkDeleteBtn"
              class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1"
              disabled>
              <i class="bi bi-trash3"></i> <span>Hapus Terpilih</span>
            </button>
          </div>

          <!-- Info & Pagination -->
          <div class="mt-3 d-flex flex-column align-items-center gap-1">
            <nav id="paginationWrap" class="d-flex justify-content-center"></nav>
            <div id="pageInfo" class="page-info-text text-muted text-center">
              Menampilkan <?= $shown ?> dari <?= $totalRows ?> data • Halaman <?= $pageDisplayCurrent ?> / <?= $pageDisplayTotal ?>
            </div>
          </div>
        </div>

      </div><!-- /.card -->
    </div><!-- /.col-12 -->
  </div><!-- /.row -->

  <!-- MODAL TAMBAH USER -->
  <div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <form action="proses_tambah_user.php" method="POST" autocomplete="off">
          <div class="modal-body">
            <div class="mb-3">
              <label for="add_role" class="form-label">Role</label>
              <select id="add_role" name="role" class="form-select" required>
                <option value="" disabled selected>-- Pilih Role --</option>
                <option value="Admin">Admin</option>
                <option value="Guru">Guru</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="add_id_guru" class="form-label">Pilih Guru</label>
              <select id="add_id_guru" name="id_guru" class="form-select" required>
                <option value="" disabled selected>-- Pilih Guru --</option>
                <?php foreach ($guruList as $g): ?>
                  <option value="<?= (int)$g['id_guru'] ?>">
                    <?= htmlspecialchars($g['nama_guru']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="add_username" class="form-label">Username</label>
              <input
                type="text"
                id="add_username"
                name="username"
                maxlength="50"
                class="form-control"
                required>
            </div>

            <div class="mb-3">
              <label for="add_password_user" class="form-label">Password</label>
              <input
                type="password"
                id="add_password_user"
                name="password_user"
                class="form-control"
                required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button"
              class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"
              data-bs-dismiss="modal">
              <i class="bi bi-x-lg"></i> Batal
            </button>
            <button type="submit"
              class="btn btn-brand d-inline-flex align-items-center gap-2">
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
          <h5 class="modal-title">Edit User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <form action="proses_edit_user.php" method="POST" autocomplete="off">
          <input type="hidden" name="id_user" id="edit_id_user">
          <div class="modal-body">
            <div class="mb-3">
              <label for="edit_role" class="form-label">Role</label>
              <select id="edit_role" name="role" class="form-select" required>
                <option value="" disabled>-- Pilih Role --</option>
                <option value="Admin">Admin</option>
                <option value="Guru">Guru</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="edit_id_guru" class="form-label">Pilih Guru</label>
              <select id="edit_id_guru" name="id_guru" class="form-select" required>
                <option value="" disabled>-- Pilih Guru --</option>
                <?php foreach ($guruList as $g): ?>
                  <option value="<?= (int)$g['id_guru'] ?>">
                    <?= htmlspecialchars($g['nama_guru']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="edit_username" class="form-label">Username</label>
              <input
                type="text"
                id="edit_username"
                name="username"
                maxlength="50"
                class="form-control"
                required>
            </div>

            <div class="mb-3">
              <label for="edit_password_user" class="form-label">Password (opsional)</label>
              <input
                type="password"
                id="edit_password_user"
                name="password_user"
                class="form-control"
                placeholder="Kosongkan jika tidak ingin mengubah password">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button"
              class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"
              data-bs-dismiss="modal">
              <i class="bi bi-x-lg"></i> Batal
            </button>
            <button type="submit"
              class="btn btn-brand d-inline-flex align-items-center gap-2">
              <i class="bi bi-save"></i> Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- MODAL KONFIRMASI HAPUS (untuk single & bulk) -->
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

</main>

<script>
  (function() {
    const input = document.getElementById('searchInput');
    const tbody = document.getElementById('userTbody');

    const checkAll = document.getElementById('checkAll');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const perPageSelect = document.getElementById('perPage');
    const paginationWrap = document.getElementById('paginationWrap');
    const pageInfo = document.getElementById('pageInfo');
    const csrfToken = '<?= htmlspecialchars($csrf); ?>';

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

    // handler yang akan dijalankan ketika user klik "Hapus" di modal
    let pendingDeleteHandler = null;

    function showDeleteConfirm(message, handler) {
      if (!confirmModalEl || !confirmBodyEl || !confirmBtn) {
        // fallback kalau modal tidak ada / bootstrap belum siap
        if (confirm(message)) {
          handler();
        }
        return;
      }

      confirmBodyEl.textContent = message;
      pendingDeleteHandler = handler;

      // reset handler dulu supaya tidak numpuk
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
        // fallback
        if (confirm(message)) {
          handler();
        }
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

    // Toggle password di tabel
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

    // EDIT: pakai modal Bootstrap saat tombol Edit diklik
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
            const editModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            editModal.show();
          }
        });
      });
    }

    // HAPUS SATU USER – pakai modal konfirmasi
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

    // Build pagination UI
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
          doSearch(currentQuery, target, currentPerPage);
        });
      });

      if (perPageSelect) {
        perPageSelect.value = String(perPage);
      }
    }

    checkAll.addEventListener('change', () => {
      const boxes = getRowCheckboxes();
      boxes.forEach(b => {
        b.checked = checkAll.checked;
      });
      updateBulkUI();
    });

    // HAPUS TERPILIH – lewat modal konfirmasi
    bulkDeleteBtn.addEventListener('click', () => {
      const boxes = getRowCheckboxes().filter(b => b.checked);
      if (boxes.length === 0) return;

      const count = boxes.length;

      const form = document.createElement('form');
      form.method = 'post';
      form.action = 'hapus_user.php';

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

    function setLoading() {
      tbody.innerHTML = `<tr><td colspan="7">Sedang mencari…</td></tr>`;
    }

    function doSearch(query, page, perPage) {
      setLoading();
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
            buildPagination(
              isNaN(total) ? 0 : total,
              isNaN(pg) ? 1 : pg,
              isNaN(pp) ? currentPerPage : pp
            );
          }

          attachCheckboxEvents();
          attachPasswordToggleEvents();
          attachEditModalEvents();
          attachSingleDeleteEvents();
        })
        .catch(e => {
          if (e.name === 'AbortError') return;
          tbody.innerHTML = `<tr><td colspan="7">Gagal memuat data.</td></tr>`;
          console.error(e);
        });
    }

    input.addEventListener('input', () => {
      clearTimeout(typingTimer);
      typingTimer = setTimeout(() => {
        doSearch(input.value, 1, currentPerPage);
      }, debounceMs);
    });

    if (perPageSelect) {
      perPageSelect.addEventListener('change', () => {
        const val = parseInt(perPageSelect.value || '10', 10);
        if (isNaN(val) || val <= 0) return;
        currentPerPage = val;
        doSearch(currentQuery, 1, currentPerPage);
      });
    }

    // Inisialisasi pertama kali
    attachCheckboxEvents();
    attachPasswordToggleEvents();
    attachEditModalEvents();
    attachSingleDeleteEvents();
    buildPagination(currentTotalRows, currentPage, currentPerPage);
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

<?php include '../includes/footer.php'; ?>