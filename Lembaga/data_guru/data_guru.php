<?php
// ====== PROSES DATA & MODE AJAX (JALAN DULUAN, TANPA OUTPUT HTML) ======
require_once '../../koneksi.php';

// Start session untuk CSRF token hapus
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'] ?? '';

// Param
$q       = isset($_GET['q']) ? trim($_GET['q']) : '';
$perPage = isset($_GET['per']) ? (int)$_GET['per'] : 10;
$perPage = ($perPage >= 1 && $perPage <= 100) ? $perPage : 10;
$page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page    = max(1, $page);

// Hitung total
if ($q !== '') {
  $sqlCount = "SELECT COUNT(*) AS total FROM guru WHERE nama_guru LIKE CONCAT('%', ?, '%')";
  $stmtC = $koneksi->prepare($sqlCount);
  $stmtC->bind_param('s', $q);
} else {
  $sqlCount = "SELECT COUNT(*) AS total FROM guru";
  $stmtC = $koneksi->prepare($sqlCount);
}
$stmtC->execute();
$total = (int)$stmtC->get_result()->fetch_assoc()['total'];
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

// Ambil data halaman ini
if ($q !== '') {
  $sql = "SELECT id_guru, nama_guru, jabatan_guru
          FROM guru
          WHERE nama_guru LIKE CONCAT('%', ?, '%')
          ORDER BY nama_guru ASC
          LIMIT ? OFFSET ?";
  $stmt = $koneksi->prepare($sql);
  $stmt->bind_param('sii', $q, $perPage, $offset);
} else {
  $sql = "SELECT id_guru, nama_guru, jabatan_guru
          FROM guru
          ORDER BY nama_guru ASC
          LIMIT ? OFFSET ?";
  $stmt = $koneksi->prepare($sql);
  $stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($r = $result->fetch_assoc()) {
  $rows[] = [
    'id_guru' => (int)$r['id_guru'],
    'nama_guru' => $r['nama_guru'],
    'jabatan_guru' => $r['jabatan_guru'],
  ];
}

// Jika mode AJAX, balikan JSON dan stop (tidak include header/navbar)
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    'ok' => true,
    'csrf' => $csrf, // untuk tombol hapus (POST)
    'data' => $rows,
    'page' => $page,
    'per' => $perPage,
    'total' => $total,
    'totalPages' => $totalPages,
    // bawa kembali q biar front-end gampang
    'q' => $q,
  ]);
  exit;
}

// ====== DARI SINI BARU OUTPUT HTML (NON-AJAX) ======
include '../../includes/header.php';

// helper bikin query string mempertahankan q & per
function keep_params($params = [])
{
  $curr = $_GET;
  foreach ($params as $k => $v) {
    $curr[$k] = $v;
  }
  return http_build_query($curr);
}
?>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <main class="content">
    <div class="cards row" style="margin-top: -50px;">
      <div class="col-12">
        <div class="card shadow-sm" style="border-radius: 15px;">

          <div class="mt-0 d-flex align-items-center flex-wrap mb-0 p-3 top-bar">
            <h5 class="mb-1 fw-semibold fs-4" style="text-align:center">Data Guru</h5>

            <div class="ms-auto d-flex gap-2 action-buttons">
              <a href="data_guru_tambah.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1 p-2 pe-3 fw-semibold" style="border-radius:5px;">
                <i class="fa-solid fa-plus fa-lg"></i> Tambah
              </a>
              <a href="data_guru_import.php" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-arrow-down fa-lg"></i><span>Import</span>
              </a>
              <button id="exportBtn" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2" type="button">
                <i class="fa-solid fa-file-arrow-up fa-lg"></i> Export
              </button>
            </div>
          </div>

          <?php if (isset($_GET['msg']) && $_GET['msg'] !== ''): ?>
            <div class="alert alert-success mx-3 mt-3 mb-0" role="alert">
              <?= htmlspecialchars($_GET['msg']) ?>
            </div>
          <?php endif; ?>

          <?php if (isset($_GET['err']) && $_GET['err'] !== ''): ?>
            <div class="alert alert-danger mx-3 mt-3 mb-0" role="alert">
              <?= htmlspecialchars($_GET['err']) ?>
            </div>
          <?php endif; ?>

          <!-- Live Search & Per page (progressive enhancement) -->
          <form method="get" class="ms-3 me-3 bg-white d-flex justify-content-center align-items-center flex-wrap p-2 gap-2" id="searchForm">
            <input type="text" name="q" id="searchInput" class="form-control form-control-sm"
              placeholder="Ketik untuk mencari" style="width: 260px;" value="<?= htmlspecialchars($q) ?>">
            <select name="per" id="perSelect" class="form-select form-select-sm" style="width:120px;">
              <?php foreach ([10, 20, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= $perPage === $opt ? 'selected' : '' ?>><?= $opt ?>/hal</option>
              <?php endforeach; ?>
            </select>
            <noscript>
              <button class="btn btn-outline-secondary btn-sm p-2 rounded-3" type="submit">Search</button>
            </noscript>
            <a class="btn btn-outline-secondary btn-sm rounded-3" href="data_guru.php">Reset</a>
          </form>

          <div class="card-body">

            <div class="table-responsive">
              <table class="table table-bordered table-striped align-middle" id="guruTable">
                <thead style="background-color:#1d52a2" class="text-center text-white">
                  <tr>
                    <th style="width:40px;" class="text-center">
                      <input type="checkbox" id="checkAll" title="Pilih Semua">
                    </th>
                    <th style="width:60px;">No</th>
                    <th>Nama Guru</th>
                    <th>Jabatan</th>
                    <th style="width:180px;">Aksi</th>
                  </tr>
                </thead>
                <tbody id="guruTbody">
                  <?php
                  $no = $offset + 1;
                  foreach ($rows as $row):
                  ?>
                    <tr>
                      <td class="text-center">
                        <input type="checkbox" class="row-check" value="<?= (int)$row['id_guru']; ?>">
                      </td>
                      <td class="text-center"><?= $no++; ?></td>
                      <td><?= htmlspecialchars($row['nama_guru']); ?></td>
                      <td class="text-center"><?= htmlspecialchars($row['jabatan_guru']); ?></td>
                      <td class="text-center d-flex gap-1 justify-content-center">
                        <a href="data_guru_edit.php?id=<?= (int)$row['id_guru']; ?>"
                          class="btn btn-warning btn-sm d-inline-flex align-items-center justify-content-center gap-1 px-2 py-1">
                          <i class="bi bi-pencil-square"></i> <span>Edit</span>
                        </a>
                        <form method="post" action="data_guru_hapus.php" onsubmit="return confirm('Yakin ingin menghapus data ini?');" class="d-inline">
                          <input type="hidden" name="id" value="<?= (int)$row['id_guru']; ?>">
                          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf); ?>">
                          <button type="submit" class="btn btn-danger btn-sm d-inline-flex align-items-center justify-content-center gap-1 px-2 py-1">
                            <i class="bi bi-trash"></i> <span>Del</span>
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach;
                  if (count($rows) === 0): ?>
                    <tr>
                      <td colspan="5" class="text-center">Belum ada data.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <!-- HAPUS TERPILIH DI BAWAH TABEL -->
            <div class="mt-2 d-flex justify-content-start">
              <button type="button" id="bulkDeleteBtn" class="btn btn-danger btn-sm d-flex align-items-center gap-1" disabled>
                <i class="bi bi-trash"></i> <span>Hapus Terpilih</span>
              </button>
            </div>

            <!-- Pagination (akan dioverride oleh JS saat live search) -->
            <nav aria-label="Page navigation" class="mt-3">
              <ul class="pagination justify-content-center" id="pagination">
                <?php
                $prev = max(1, $page - 1);
                $next = min($totalPages, $page + 1);
                ?>
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                  <a class="page-link" href="?<?= keep_params(['page' => 1]) ?>">« First</a>
                </li>
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                  <a class="page-link" href="?<?= keep_params(['page' => $prev]) ?>">‹ Prev</a>
                </li>
                <?php
                $start = max(1, $page - 2);
                $end   = min($totalPages, $page + 2);
                for ($i = $start; $i <= $end; $i++):
                ?>
                  <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= keep_params(['page' => $i]) ?>"><?= $i ?></a>
                  </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                  <a class="page-link" href="?<?= keep_params(['page' => $next]) ?>">Next ›</a>
                </li>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                  <a class="page-link" href="?<?= keep_params(['page' => $totalPages]) ?>">Last »</a>
                </li>
              </ul>
              <p class="text-center text-muted mt-2 mb-0" id="pageInfo">
                Menampilkan <strong><?= count($rows) ?></strong> dari <strong><?= $total ?></strong> data • Halaman <strong><?= $page ?></strong> / <strong><?= $totalPages ?></strong>
              </p>
            </nav>

          </div>
        </div>
      </div>
    </div>
  </main>

  <style>
    @media (max-width: 768px) {
      .top-bar {
        flex-direction: column !important;
        align-items: flex-center !important;
      }

      .action-buttons {
        margin-top: 10px;
        width: 100%;
        justify-content: center !important;
        flex-wrap: wrap;
      }

      .h5 {
        justify-content: center;
      }

      .action-buttons a,
      .action-buttons button {
        width: auto;
      }
    }
  </style>

  <script>
    (function() {
      const input = document.getElementById('searchInput');
      const perSel = document.getElementById('perSelect');
      const tbody = document.getElementById('guruTbody');
      const pagUl = document.getElementById('pagination');
      const pageInfo = document.getElementById('pageInfo');

      const checkAll = document.getElementById('checkAll');
      const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
      const csrfToken = '<?= htmlspecialchars($csrf); ?>';

      let typingTimer = null;
      let currentPage = <?= (int)$page ?>;

      function escapeHtml(str) {
        const div = document.createElement('div');
        div.innerText = str ?? '';
        return div.innerHTML;
      }

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

        if (!confirm(`Yakin ingin menghapus ${boxes.length} data guru terpilih?`)) {
          return;
        }

        const form = document.createElement('form');
        form.method = 'post';
        form.action = 'data_guru_hapus.php';

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

      function renderRows(data, startNumber) {
        if (!Array.isArray(data) || data.length === 0) {
          tbody.innerHTML = '<tr><td colspan="5" class="text-center">Belum ada data.</td></tr>';
          updateBulkUI();
          return;
        }
        let html = '';
        let no = startNumber;
        for (const r of data) {
          html += `
            <tr>
              <td class="text-center">
                <input type="checkbox" class="row-check" value="${Number(r.id_guru)}">
              </td>
              <td class="text-center">${no++}</td>
              <td>${escapeHtml(r.nama_guru)}</td>
              <td class="text-center">${escapeHtml(r.jabatan_guru)}</td>
              <td class="text-center d-flex gap-1 justify-content-center">
                <a href="data_guru_edit.php?id=${Number(r.id_guru)}"
                   class="btn btn-warning btn-sm d-inline-flex align-items-center justify-content-center gap-1 px-2 py-1">
                  <i class="bi bi-pencil-square"></i> <span>Edit</span>
                </a>
                <form method="post" action="data_guru_hapus.php" onsubmit="return confirm('Yakin ingin menghapus data ini?');" class="d-inline">
                  <input type="hidden" name="id" value="${Number(r.id_guru)}">
                  <input type="hidden" name="csrf" value="${csrfToken}">
                  <button type="submit" class="btn btn-danger btn-sm d-inline-flex align-items-center justify-content-center gap-1 px-2 py-1">
                    <i class="bi bi-trash"></i> <span>Del</span>
                  </button>
                </form>
              </td>
            </tr>
          `;
        }
        tbody.innerHTML = html;
        attachCheckboxEvents();
      }

      function renderPagination(page, totalPages, total, showed, per) {
        // bangun pagination kecil (window ±2)
        const start = Math.max(1, page - 2);
        const end = Math.min(totalPages, page + 2);
        let html = '';

        const makeLi = (disabled, target, text, active = false) => {
          const cls = [
            'page-item',
            disabled ? 'disabled' : '',
            active ? 'active' : ''
          ].filter(Boolean).join(' ');
          const aAttr = disabled ? 'tabindex="-1" aria-disabled="true"' : `data-page="${target}"`;
          return `<li class="${cls}"><a class="page-link" href="#" ${aAttr}>${text}</a></li>`;
        };

        html += makeLi(page <= 1, 1, '« First');
        html += makeLi(page <= 1, Math.max(1, page - 1), '‹ Prev');

        for (let i = start; i <= end; i++) {
          html += makeLi(false, i, String(i), i === page);
        }

        html += makeLi(page >= totalPages, Math.min(totalPages, page + 1), 'Next ›');
        html += makeLi(page >= totalPages, totalPages, 'Last »');

        pagUl.innerHTML = html;

        pageInfo.innerHTML =
          `Menampilkan <strong>${showed}</strong> dari <strong>${total}</strong> data • Halaman <strong>${page}</strong> / <strong>${totalPages}</strong>`;

        // attach handler click
        [...pagUl.querySelectorAll('a.page-link[data-page]')].forEach(a => {
          a.addEventListener('click', (e) => {
            e.preventDefault();
            const target = Number(a.getAttribute('data-page') || '1');
            currentPage = target;
            doSearch(); // fetch halaman target
          });
        });
      }

      async function doSearch() {
        const q = input.value.trim();
        const per = Number(perSel.value || 10);
        const page = currentPage;

        const params = new URLSearchParams({
          ajax: '1',
          q: q,
          per: String(per),
          page: String(page),
        });
        const url = `?${params.toString()}`;

        try {
          const res = await fetch(url, {
            headers: {
              'X-Requested-With': 'fetch'
            }
          });
          const json = await res.json();
          if (!json || !json.ok) return;

          const startNumber = (json.page - 1) * json.per + 1;
          renderRows(json.data, startNumber);
          renderPagination(json.page, json.totalPages, json.total, json.data.length, json.per);
        } catch (e) {
          console.error(e);
        }
      }

      // Ketik untuk cari (debounce 250ms)
      input.addEventListener('input', () => {
        clearTimeout(typingTimer);
        currentPage = 1; // reset ke halaman 1 saat mengganti query
        typingTimer = setTimeout(doSearch, 250);
      });

      // Ganti per-page -> refetch
      perSel.addEventListener('change', () => {
        currentPage = 1;
        doSearch();
      });

      // Inisialisasi
      attachCheckboxEvents(); // untuk HTML awal (kalau fetch gagal)
      doSearch(); // sinkronkan dengan data AJAX
    })();
  </script>

  <?php include '../../includes/footer.php'; ?>