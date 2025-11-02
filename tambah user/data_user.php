<?php
include '../includes/header.php';
include '../includes/navbar.php';
require_once '../koneksi.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$like   = "%{$search}%";

$sql = "
  SELECT u.id_user, u.username, u.role_user, u.id_guru, g.nama_guru
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
    /* judul & teks utama (sangat gelap) */
    --text: #111827;
    /* teks tabel */
    --muted: #475569;
    /* hint/sekunder */
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

  .helper {
    color: var(--muted);
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

  .btn-brand {
    background: var(--brand);
    border-color: var(--brand);
    color: #fff;
    font-weight: 700;
  }

  .btn-brand:hover {
    background: var(--brand-600);
    border-color: var(--brand-600);
  }

  /* subtle focus */
  .searchbox:focus-within {
    box-shadow: 0 0 0 3px rgba(10, 77, 179, .15);
  }
</style>

<main class="content">
  <div class="row g-3">
    <div class="col-12">
      <div class="card shadow-sm">

        <div class="top-bar p-3 p-md-4 d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between">
          <div class="d-flex flex-column gap-2">
            <h5 class="page-title mb-1 fw-bold fs-4">Data User</h5>

            <!-- Hapus tombol 'Cari' — live as-you-type -->
            <div class="search-wrap">
              <div class="searchbox" role="search" aria-label="Pencarian user">
                <i class="bi bi-search icon"></i>
                <input type="text" id="searchInput" placeholder="Ketik untuk mencari" autofocus>
              </div>
            </div>
          </div>

          <div class="d-flex gap-2 flex-wrap mt-3 mt-md-0">
            <a href="tambah_data_user.php" class="btn btn-brand btn-sm d-inline-flex align-items-center gap-2 px-3">
              <i class="bi bi-plus-lg"></i> Tambah User
            </a>
          </div>
        </div>

        <div class="card-body pt-0">
          <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle mb-0">
              <thead class="text-center">
                <tr>
                  <th style="width:70px;">No</th>
                  <th>Nama Guru</th>
                  <th>Username</th>
                  <th>Role</th>
                  <th style="width:200px;">Aksi</th>
                </tr>
              </thead>
              <tbody id="userTbody" class="text-center">
                <?php if (mysqli_num_rows($result) === 0): ?>
                  <tr>
                    <td colspan="5">Belum ada data</td>
                  </tr>
                  <?php else: $no = 1;
                  while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                      <td data-label="No"><?= $no++; ?></td>
                      <td data-label="Nama (Guru)"><?= htmlspecialchars($row['nama_guru'] ?? '-') ?></td>
                      <td data-label="Username"><?= htmlspecialchars($row['username']) ?></td>
                      <td data-label="Role"><span class="badge role-badge"><?= htmlspecialchars($row['role_user']) ?></span></td>
                      <td data-label="Aksi">
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                          <a href="edit_user.php?id=<?= (int)$row['id_user'] ?>" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1">
                            <i class="bi bi-pencil-square"></i> Edit
                          </a>
                          <a href="hapus_user.php?id=<?= (int)$row['id_user'] ?>" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1"
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
        </div>

      </div>
    </div>
  </div>
</main>

<script>
  (function() {
    const input = document.getElementById('searchInput');
    const tbody = document.getElementById('userTbody');
    const clear = document.getElementById('clearBtn');

    let typingTimer;
    const debounceMs = 250;
    let currentController = null;

    function setLoading() {
      tbody.innerHTML = `<tr><td colspan="5">Sedang mencari…</td></tr>`;
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
        })
        .catch(e => {
          if (e.name === 'AbortError') return;
          tbody.innerHTML = `<tr><td colspan="5">Gagal memuat data.</td></tr>`;
          console.error(e);
        });
    }

    input.addEventListener('input', () => {
      clearTimeout(typingTimer);
      typingTimer = setTimeout(() => doSearch(input.value), debounceMs);
    });

    clear.addEventListener('click', () => {
      input.value = '';
      input.focus();
      doSearch('');
    });
  })();
</script>

<?php include '../includes/footer.php'; ?>