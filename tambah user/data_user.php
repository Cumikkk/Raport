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

  .page-info-text {
    font-size: 0.95rem;
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
      <div class="card shadow-sm">

        <!-- TOP BAR: judul, search + perPage + Tambah User satu baris -->
        <div class="top-bar p-3 p-md-4">
          <div class="d-flex flex-column gap-3 w-100">
            <div>
              <h5 class="page-title mb-0 fw-bold fs-4">Data User</h5>
            </div>

            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2">
              <!-- Search + per page (kiri) -->
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

              <!-- Tombol Tambah User (kanan), sejajar dengan search row -->
              <div class="d-flex justify-content-md-end">
                <a href="tambah_data_user.php"
                  class="btn btn-brand btn-sm d-inline-flex align-items-center gap-2 px-3">
                  <i class="bi bi-person-plus"></i> Tambah User
                </a>
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
                          <a href="edit_user.php?id=<?= (int)$row['id_user'] ?>"
                            class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1">
                            <i class="bi bi-pencil-square"></i> Edit
                          </a>
                          <a href="hapus_user.php?id=<?= (int)$row['id_user'] ?>"
                            class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1"
                            onclick="return confirm('Yakin ingin menghapus user ini?');">
                            <i class="bi bi-trash"></i> Hapus
                          </a>
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

          <!-- Info & Pagination (TENGAH, DI BAWAH HAPUS TERPILIH) -->
          <div class="mt-3 d-flex flex-column align-items-center gap-1">
            <nav id="paginationWrap" class="d-flex justify-content-center"></nav>
            <div id="pageInfo" class="page-info-text text-muted text-center">
              Menampilkan <?= $shown ?> dari <?= $totalRows ?> data • Halaman <?= $pageDisplayCurrent ?> / <?= $pageDisplayTotal ?>
            </div>
          </div>
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

    let typingTimer;
    const debounceMs = 250;
    let currentController = null;

    let currentQuery = '<?= htmlspecialchars($search, ENT_QUOTES, "UTF-8"); ?>';
    let currentPage = <?= (int)$page ?>;
    let currentPerPage = <?= (int)$perPage ?>;
    let currentTotalRows = <?= (int)$totalRows ?>;

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

    // Build pagination UI ala « First • ‹ Prev • 1 • Next › • Last »
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
      pageInfo.textContent = `Menampilkan ${shown} dari ${totalRows} data • Halaman ${pageDisplayCurrent} / ${pageDisplayTotal}`;

      let html = '<ul class="pagination mb-0">'; // normal size (tanpa pagination-sm)

      const isFirst = (page <= 1);
      const isLast = (page >= totalPages);

      // First
      html += `<li class="page-item${isFirst ? ' disabled' : ''}">
                 <button class="page-link page-btn" type="button" data-page="1">&laquo; First</button>
               </li>`;

      // Prev
      html += `<li class="page-item${isFirst ? ' disabled' : ''}">
                 <button class="page-link page-btn" type="button" data-page="${page - 1}">&lsaquo; Prev</button>
               </li>`;

      // Current page
      html += `<li class="page-item active">
                 <button class="page-link" type="button" data-page="${page}">${page}</button>
               </li>`;

      // Next
      html += `<li class="page-item${isLast ? ' disabled' : ''}">
                 <button class="page-link page-btn" type="button" data-page="${page + 1}">Next &rsaquo;</button>
               </li>`;

      // Last
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

    bulkDeleteBtn.addEventListener('click', () => {
      const boxes = getRowCheckboxes().filter(b => b.checked);
      if (boxes.length === 0) return;

      if (!confirm(`Yakin ingin menghapus ${boxes.length} user terpilih?`)) {
        return;
      }

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

      document.body.appendChild(form);
      form.submit();
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

    // Inisialisasi pertama kali (server-side data)
    attachCheckboxEvents();
    attachPasswordToggleEvents();
    buildPagination(currentTotalRows, currentPage, currentPerPage);
  })();
</script>

<?php include '../includes/footer.php'; ?>