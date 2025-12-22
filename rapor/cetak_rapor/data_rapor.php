<?php
include '../../koneksi.php';

// ====== PROSES DATA (MODE AJAX TANPA OUTPUT HTML) ======
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'] ?? '';

// ===== Parameter =====
$q       = isset($_GET['q']) ? trim($_GET['q']) : '';
$perPage = isset($_GET['per']) ? (int)$_GET['per'] : 10;
$perPage = ($perPage >= 1 && $perPage <= 100) ? $perPage : 10;
$page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page    = max(1, $page);

// ===== Hitung total =====
if ($q !== '') {
  $sqlCount = "SELECT COUNT(*) AS total
               FROM siswa s
               LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
               WHERE (s.nama_siswa LIKE CONCAT('%', ?, '%')
                      OR s.no_absen_siswa LIKE CONCAT('%', ?, '%')
                      OR s.no_induk_siswa LIKE CONCAT('%', ?, '%'))";
  $stmtC = $koneksi->prepare($sqlCount);
  if ($stmtC === false) {
    die('Prepare failed: ' . $koneksi->error);
  }
  $stmtC->bind_param('sss', $q, $q, $q);
} else {
  $sqlCount = "SELECT COUNT(*) AS total
               FROM siswa s
               LEFT JOIN kelas k ON s.id_kelas = k.id_kelas";
  $stmtC = $koneksi->prepare($sqlCount);
  if ($stmtC === false) {
    die('Prepare failed: ' . $koneksi->error);
  }
}
$stmtC->execute();
$totalRow = $stmtC->get_result()->fetch_assoc();
$total = (int)($totalRow['total'] ?? 0);
$totalPages = max(1, ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$stmtC->close();

// ===== Ambil data halaman ini =====
if ($q !== '') {
  $sql = "SELECT
            s.id_siswa,
            s.no_absen_siswa,
            s.nama_siswa,
            s.no_induk_siswa,
            k.nama_kelas
          FROM siswa s
          LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
          WHERE (s.nama_siswa LIKE CONCAT('%', ?, '%')
                 OR s.no_absen_siswa LIKE CONCAT('%', ?, '%')
                 OR s.no_induk_siswa LIKE CONCAT('%', ?, '%'))
          ORDER BY s.no_absen_siswa ASC
          LIMIT ? OFFSET ?";
  $stmt = $koneksi->prepare($sql);
  if ($stmt === false) {
    die('Prepare failed: ' . $koneksi->error);
  }
  $stmt->bind_param('sssii', $q, $q, $q, $perPage, $offset);
} else {
  $sql = "SELECT
            s.id_siswa,
            s.no_absen_siswa,
            s.nama_siswa,
            s.no_induk_siswa,
            k.nama_kelas
          FROM siswa s
          LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
          ORDER BY s.no_absen_siswa ASC
          LIMIT ? OFFSET ?";
  $stmt = $koneksi->prepare($sql);
  if ($stmt === false) {
    die('Prepare failed: ' . $koneksi->error);
  }
  $stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($r = $result->fetch_assoc()) {
  $rows[] = $r;
}
$stmt->close();

// ===== Mode AJAX (dipakai oleh pagination & search) =====
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    'ok'         => true,
    'csrf'       => $csrf,
    'data'       => $rows,
    'page'       => $page,
    'per'        => $perPage,
    'total'      => $total,
    'totalPages' => $totalPages,
    'q'          => $q,
  ]);
  exit;
}

include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<main class="content">
  <div class="cards row" style="margin-top: -50px;">
    <div class="col-12">
      <div class="card shadow-sm" style="border-radius: 15px;">

        <!-- ===== BAR ATAS ===== -->
        <div class="mt-0 d-flex flex-column flex-md-row align-items-md-start justify-content-between p-3 top-bar gap-3">

          <!-- Kiri: Judul + Search + Filter -->
          <div class="d-flex flex-column w-100">
            <h5 class="mb-2 fw-semibold fs-4 text-md-start text-center">Cetak Rapor Siswa</h5>

            <!-- ✅ SEARCH + FILTER (tingkat & kelas) sejajar -->
            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2">

              <!-- kiri: search + filter -->
              <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 flex-grow-1">

                <!-- Search -->
                <div class="position-relative search-wrap" style="max-width:280px; width:100%;">
                  <input
                    type="text"
                    id="searchInput"
                    class="form-control form-control-sm"
                    placeholder="Ketik untuk mencari..."
                    value="<?= htmlspecialchars($q) ?>">
                  <span class="search-icon"><i class="bi bi-search"></i></span>
                </div>

                <!-- Filter Tingkat -->
                <div class="filter-inline d-flex align-items-center gap-2">
                  <label for="tingkat" class="form-label fw-semibold mb-0">Tingkat</label>
                  <select id="tingkat" class="form-select form-select-sm dk-select" style="width: 140px;">
                    <option value="">-- Semua --</option>
                    <option>X</option>
                    <option>XI</option>
                    <option>XII</option>
                  </select>
                </div>

                <!-- Filter Kelas -->
                <div class="filter-inline d-flex align-items-center gap-2">
                  <label for="kelas" class="form-label fw-semibold mb-0">Kelas</label>
                  <select id="kelas" class="form-select form-select-sm dk-select" style="width: 160px;">
                    <option value="">-- Semua --</option>
                    <option>IPA 1</option>
                    <option>IPA 2</option>
                    <option>IPS 1</option>
                    <option>IPS 2</option>
                  </select>
                </div>

              </div>

              <!-- kanan: tombol -->
              <div class="d-flex gap-2 flex-wrap justify-content-md-end justify-content-center action-buttons">
                <a href="data_absensi_tambah.php"
                  class="btn btn-primary btn-sm d-flex align-items-center gap-1 px-3 fw-semibold"
                  style="border-radius: 5px;">
                  <i class="fa-solid fa-print fa-lg"></i> Print Semua Rapor
                </a>

                <a href="data_absensi_import.php"
                  class="btn btn-success btn-sm px-3 d-flex align-items-center gap-2">
                  <i class="fa-solid fa-file-arrow-down fa-lg"></i> <span>Import</span>
                </a>
              </div>

            </div>
          </div>

        </div>

        <!-- ===== TABEL SISWA ===== -->
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
              <thead style="background-color:#1d52a2" class="text-center text-white">
                <tr>
                  <th>Absen</th>
                  <th>Nama</th>
                  <th>NISN</th>
                  <th>Kelas</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody id="tbodyData" class="text-center">
                <?php if (empty($rows)): ?>
                  <tr>
                    <td colspan="5" class="text-center text-muted py-4">Tidak ada data.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($rows as $d): ?>
                    <tr data-kelas="<?= htmlspecialchars($d['nama_kelas'] ?? '') ?>">
                      <td><?= htmlspecialchars($d['no_absen_siswa']); ?></td>
                      <td><?= htmlspecialchars($d['nama_siswa']); ?></td>
                      <td><?= htmlspecialchars($d['no_induk_siswa']); ?></td>
                      <td><?= htmlspecialchars($d['nama_kelas'] ?? '-'); ?></td>
                      <td>
                        <a href="preview.php?id=<?= (int)$d['id_siswa']; ?>" class="btn btn-info btn-sm">
                          <i class="fa-solid fa-eye fa-lg"></i> Preview
                        </a>
                        <a href="print_rapor.php?id=<?= (int)$d['id_siswa']; ?>" class="btn btn-primary btn-sm">
                          <i class="fa-solid fa-print fa-lg"></i> Print
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- ✅ PAGINATION (Referensi 2): Pagination + perPage dalam 1 grup -->
          <nav aria-label="Page navigation" class="mt-3">
            <div class="pager-area">
              <div class="pager-group">
                <ul class="pagination mb-0" id="paginationWrap"></ul>

                <div class="pager-sep" aria-hidden="true"></div>

                <select id="perPage" class="form-select form-select-sm per-select">
                  <?php foreach ([10, 20, 50, 100] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $perPage === $opt ? 'selected' : '' ?>><?= $opt ?>/hal</option>
                  <?php endforeach; ?>
                </select>
              </div>

              <p class="text-center text-muted mb-0" id="pageInfo"></p>
            </div>
          </nav>

        </div>

      </div>
    </div>
  </div>
</main>

<?php include '../../includes/footer.php'; ?>

<script>
  // ===== Utility mirip data_siswa.php =====
  function escapeHtml(str) {
    const div = document.createElement('div');
    div.innerText = str ?? '';
    return div.innerHTML;
  }

  function highlightText(text, keyword) {
    let safe = escapeHtml(text);
    if (keyword) {
      const escaped = keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const pattern = new RegExp('(' + escaped + ')', 'gi');
      safe = safe.replace(pattern, '<span class="highlight">$1</span>');
    }
    return safe.replace(/\r\n|\r|\n/g, '<br>');
  }

  (function() {
    const tbody = document.getElementById('tbodyData');
    const pagUl = document.getElementById('paginationWrap');
    const pageInfo = document.getElementById('pageInfo');

    const input = document.getElementById('searchInput');

    // ✅ per page sekarang ada di bawah (pagination group)
    const perSel = document.getElementById('perPage');

    const filterTingkat = document.getElementById('tingkat');
    const filterKelas = document.getElementById('kelas');

    let currentPage = <?= (int)$page ?>;
    let typingTimer;

    function renderRows(data) {
      if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Data Tidak di Temukan</td></tr>';
        return;
      }

      let html = '';
      for (const r of data) {
        const noAbsen = escapeHtml(r.no_absen_siswa || '');
        const nama = escapeHtml(r.nama_siswa || '');
        const nisn = escapeHtml(r.no_induk_siswa || '');
        const kelas = escapeHtml(r.nama_kelas || '-');
        const kelasAttr = escapeHtml(r.nama_kelas || '');

        html += `
        <tr data-kelas="${kelasAttr}">
          <td>${noAbsen}</td>
          <td>${nama}</td>
          <td>${nisn}</td>
          <td>${kelas}</td>
          <td>
            <a href="preview.php?id=${r.id_siswa}" class="btn btn-info btn-sm" target="_blank">
              <i class="fa-solid fa-eye fa-lg"></i> Preview
            </a>
            <a href="print_rapor.php?id=${r.id_siswa}" class="btn btn-primary btn-sm" target="_blank">
              <i class="fa-solid fa-print fa-lg"></i> Print
            </a>
          </td>
        </tr>`;
      }
      tbody.innerHTML = html;
    }

    function renderPagination(page, totalPages, total, showed) {
      const start = Math.max(1, page - 2);
      const end = Math.min(totalPages, page + 2);
      let html = '';

      const makeLi = (disabled, target, text, active = false) => {
        const cls = ['page-item', disabled ? 'disabled' : '', active ? 'active' : '']
          .filter(Boolean)
          .join(' ');
        const aAttr = disabled ? 'tabindex="-1"' : `data-page="${target}"`;
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

      const pageDisplayCurrent = total === 0 ? 0 : page;
      const pageDisplayTotal = total === 0 ? 0 : totalPages;
      pageInfo.innerHTML =
        `Menampilkan <strong>${showed}</strong> dari <strong>${total}</strong> data • Halaman <strong>${pageDisplayCurrent}</strong> / <strong>${pageDisplayTotal}</strong>`;

      [...pagUl.querySelectorAll('a[data-page]')].forEach(a => {
        a.addEventListener('click', e => {
          e.preventDefault();
          const target = Number(a.getAttribute('data-page'));
          if (!target || target === currentPage) return;
          currentPage = target;
          doSearch(true);
        });
      });
    }

    async function doSearch(fromPaginationOrPerpage = false) {
      const q = input.value.trim();
      const per = Number(perSel.value || 10);
      const page = currentPage;

      const params = new URLSearchParams({
        ajax: '1',
        q,
        per,
        page
      });

      try {
        const res = await fetch(`?${params.toString()}`);
        const json = await res.json();
        if (!json.ok) return;

        renderRows(json.data);
        renderPagination(json.page, json.totalPages, json.total, json.data.length);

        // filter client-side (tingkat & kelas) + highlight
        filterTable();
      } catch (e) {
        console.error(e);
      }
    }

    function filterTable() {
      const sRaw = (input.value || '');
      const s = sRaw.toLowerCase();
      const t = (filterTingkat && filterTingkat.value || '').toLowerCase();
      const k = (filterKelas && filterKelas.value || '').toLowerCase();

      const rows = tbody.querySelectorAll('tr');
      rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length < 4) return;

        const absenText = cells[0].textContent || '';
        const namaText = cells[1].textContent || '';
        const nisnText = cells[2].textContent || '';
        const kelasText = cells[3].textContent || '';

        const rowText = (absenText + ' ' + namaText + ' ' + nisnText + ' ' + kelasText).toLowerCase();
        const kelasAttr = (row.dataset.kelas || '').toLowerCase();

        const matchesFilter = (!t || rowText.includes(t)) && (!k || kelasAttr.includes(k));
        const matchesSearch = !s || rowText.includes(s);

        if (matchesFilter && matchesSearch) {
          row.style.display = '';

          // highlight (kecuali aksi)
          cells[0].innerHTML = highlightText(absenText, sRaw);
          cells[1].innerHTML = highlightText(namaText, sRaw);
          cells[2].innerHTML = highlightText(nisnText, sRaw);
          cells[3].innerHTML = highlightText(kelasText, sRaw);

          if (sRaw) row.classList.add('row-highlight');
          else row.classList.remove('row-highlight');
        } else {
          row.style.display = 'none';
          row.classList.remove('row-highlight');
        }
      });
    }

    // Event: search (server-side) + highlight
    input.addEventListener('input', () => {
      clearTimeout(typingTimer);
      currentPage = 1;
      typingTimer = setTimeout(() => doSearch(false), 250);
    });

    // Event: per-page (bawah)
    perSel.addEventListener('change', () => {
      currentPage = 1;
      doSearch(true);
    });

    // Event: filter tingkat & kelas (client-side)
    if (filterTingkat) filterTingkat.addEventListener('change', filterTable);
    if (filterKelas) filterKelas.addEventListener('change', filterTable);

    // init
    doSearch(false);
  })();
</script>

<style>
  .search-icon {
    position: absolute;
    top: 50%;
    right: 8px;
    transform: translateY(-50%);
    color: #6c757d;
    pointer-events: none;
    font-size: 0.9rem;
  }

  .highlight {
    background-color: #d4edda;
    padding: 1px 2px;
    border-radius: 3px;
  }

  .row-highlight {
    background-color: #d4edda !important;
    transition: background-color 0.3s ease;
  }

  /* ✅ Pagination Group (Referensi 2) */
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

  @media (max-width: 768px) {
    .top-bar {
      flex-direction: column !important;
      align-items: center !important;
      text-align: center;
    }

    .action-buttons {
      justify-content: center !important;
      margin-top: 8px;
    }

    .filter-inline {
      justify-content: center;
    }

    .dk-select {
      width: 100% !important;
    }

    .pager-sep {
      display: none;
    }

    .pager-group {
      width: 100%;
    }

    #paginationWrap {
      flex-wrap: wrap !important;
      justify-content: center !important;
      gap: 4px !important;
    }

    #paginationWrap .page-link {
      padding: 4px 8px;
      font-size: 13px;
    }

    #pageInfo {
      font-size: 13px;
      white-space: normal;
    }
  }
</style>
