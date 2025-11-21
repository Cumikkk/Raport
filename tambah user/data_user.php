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
";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
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

  /* =======================
     BUTTON & HOVER
     ======================= */

  /* Tombol biru brand (Tambah User, Simpan, dll) */
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

  /* Tombol Edit (kuning) */
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

  /* Tombol Hapus (merah) */
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

  /* Tombol outline abu (Batal, dsb) */
  .btn-outline-secondary {
    border-color: #6c757d !important;
    color: #6c757d !important;
    background: transparent !important;
  }

  .btn-outline-secondary:hover {
    background: #e9ecef !important;
    color: #333 !important;
  }

  /* Tombol toggle password */
  .toggle-password {
    padding: 4px 8px !important;
  }

  .toggle-password:hover {
    background: #dfe3e6 !important;
    color: #333 !important;
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
  }
</style>

<main class="content">
  <div class="row g-3">
    <div class="col-12">
      <div class="card shadow-sm">

        <div class="top-bar p-3 p-md-4 d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between">
          <div class="d-flex flex-column gap-2">
            <h5 class="page-title mb-1 fw-bold fs-4">Data User</h5>

            <div class="search-wrap">
              <div class="searchbox" role="search" aria-label="Pencarian user">
                <i class="bi bi-search icon"></i>
                <input type="text" id="searchInput" placeholder="Ketik untuk mencari" autofocus>
              </div>
            </div>
          </div>

          <div class="d-flex gap-2 flex-wrap mt-3 mt-md-0">
            <a href="tambah_data_user.php"
              class="btn btn-brand btn-sm d-inline-flex align-items-center gap-2 px-3">
              <i class="bi bi-person-plus"></i> Tambah User
            </a>
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
                  $no = 1;
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

          <div class="mt-3 d-flex justify-content-start">
            <button type="button"
              id="bulkDeleteBtn"
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

<script>
  (function() {
    const input = document.getElementById('searchInput');
    const tbody = document.getElementById('userTbody');

    const checkAll = document.getElementById('checkAll');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const csrfToken = '<?= htmlspecialchars($csrf); ?>';

    let typingTimer;
    const debounceMs = 250;
    let currentController = null;

    function getRowCheckboxes() {
      return Array.from(document.querySelectorAll('.row-check'));
    }

    function updateBulkUI() {
      const boxes = getRowCheckboxes();
      const total = boxes.length;
      const checked = boxes.filter(b => b.checked).length;

      bulkDeleteBtn.disabled = checked === 0;

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

    // Toggle password (lihat / sembunyi)
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

    function doSearch(query) {
      setLoading();
      if (currentController) currentController.abort();
      currentController = new AbortController();

      const params = new URLSearchParams({
        q: query || ''
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
      typingTimer = setTimeout(() => doSearch(input.value), debounceMs);
    });

    // Inisialisasi awal
    attachCheckboxEvents();
    attachPasswordToggleEvents();
  })();
</script>

<?php include '../includes/footer.php'; ?>