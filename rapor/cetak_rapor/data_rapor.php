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

/* Join catatan terbaru per siswa dari cetak_rapor */
$joinCrLatest = "
  LEFT JOIN (
    SELECT crx.id_siswa, crx.catatan_wali_kelas
    FROM cetak_rapor crx
    INNER JOIN (
      SELECT id_siswa, MAX(id_cetak_rapor) AS max_id
      FROM cetak_rapor
      GROUP BY id_siswa
    ) last ON last.id_siswa = crx.id_siswa AND last.max_id = crx.id_cetak_rapor
  ) cr ON s.id_siswa = cr.id_siswa
";

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
               $joinCrLatest
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
               LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
               $joinCrLatest";
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
            k.nama_kelas,
            cr.catatan_wali_kelas
          FROM siswa s
          LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
          $joinCrLatest
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
            k.nama_kelas,
            cr.catatan_wali_kelas
          FROM siswa s
          LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
          $joinCrLatest
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
        <div class="mt-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between p-3 top-bar">

          <!-- Kiri: Judul + Search + Filter -->
          <div class="d-flex flex-column align-items-md-start align-items-center text-md-start text-center mb-2 mb-md-0">
            <h5 class="mb-2 fw-semibold fs-4">Cetak Rapor Siswa</h5>

            <!-- ===== SEARCH + PER PAGE (DI BAWAH JUDUL) ===== -->
            <form id="searchForm"
                  class="d-flex flex-wrap align-items-center gap-2 mb-2 w-100 justify-content-start justify-content-md-start">
              <div class="position-relative" style="width:240px; max-width:100%;">
                <input
                  type="text"
                  id="searchInput"
                  class="form-control form-control-sm"
                  placeholder="Search..."
                  value="<?= htmlspecialchars($q) ?>"
                >
                <span class="search-icon"><i class="bi bi-search"></i></span>
              </div>

              <select id="perSelect" class="form-select form-select-sm" style="width:120px;">
                <?php foreach ([10, 20, 50, 100] as $opt): ?>
                  <option value="<?= $opt ?>" <?= $perPage === $opt ? 'selected' : '' ?>><?= $opt ?>/hal</option>
                <?php endforeach; ?>
              </select>
            </form>

            <!-- ===== FILTER TINGKAT & KELAS (DI BAWAH SEARCH) ===== -->
            <div class="filter-container d-flex flex-column align-items-md-start align-items-center gap-2">

              <!-- Dropdown Tingkat -->
              <div class="filter-group d-flex align-items-center gap-2">
                <label for="tingkat" class="form-label fw-semibold mb-0">Tingkat</label>
                <select id="tingkat" class="form-select dk-select" style="width: 140px;">
                  <option value="">-- Semua --</option>
                  <option>X</option>
                  <option>XI</option>
                  <option>XII</option>
                </select>
              </div>

              <!-- Dropdown Kelas -->
              <div class="filter-group d-flex align-items-center gap-2">
                <label for="kelas" class="form-label fw-semibold mb-0">Kelas</label>
                <select id="kelas" class="form-select dk-select" style="width: 160px;">
                  <option value="">-- Semua --</option>
                  <option>IPA 1</option>
                  <option>IPA 2</option>
                  <option>IPS 1</option>
                  <option>IPS 2</option>
                </select>
              </div>

            </div>
          </div>

          <!-- Kanan: Tombol Print & Import -->
          <div class="d-flex gap-2 flex-wrap justify-content-md-end justify-content-center mt-3 mt-md-0 action-buttons">
            <a href="data_absensi_tambah.php"
               class="btn btn-primary btn-sm d-flex align-items-center gap-1 px-3 fw-semibold"
               style="border-radius: 5px;">
              <i class="fa-solid fa-print fa-lg"></i> Print Semua Rapor
            </a>

            <a href="data_absensi_import.php"
               class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
              <i class="fa-solid fa-file-arrow-down fa-lg"></i> <span>Import</span>
            </a>
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
                  <th>Komentar</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody id="tbodyData" class="text-center">
                <?php if (empty($rows)): ?>
                  <tr>
                    <td colspan="6" class="text-center text-muted py-4">Tidak ada data.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($rows as $d): ?>
                    <tr data-kelas="<?= htmlspecialchars($d['nama_kelas'] ?? '') ?>">
                      <td><?= htmlspecialchars($d['no_absen_siswa']); ?></td>
                      <td><?= htmlspecialchars($d['nama_siswa']); ?></td>
                      <td><?= htmlspecialchars($d['no_induk_siswa']); ?></td>
                      <td><?= htmlspecialchars($d['nama_kelas'] ?? '-'); ?></td>
                      <td class="text-start"><?= nl2br(htmlspecialchars($d['catatan_wali_kelas'] ?? '')); ?></td>
                      <td>
                        <a href="preview_rapor.php?id=<?= (int)$d['id_siswa']; ?>" class="btn btn-info btn-sm">
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

          <!-- PAGINATION -->
          <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center" id="pagination"></ul>
            <p class="text-center text-muted mt-2 mb-0" id="pageInfo"></p>
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
  // jaga newline -> <br> (untuk kolom komentar)
  return safe.replace(/\r\n|\r|\n/g, '<br>');
}

(function() {
  const tbody         = document.getElementById('tbodyData');
  const pagUl         = document.getElementById('pagination');
  const pageInfo      = document.getElementById('pageInfo');
  const input         = document.getElementById('searchInput');
  const perSel        = document.getElementById('perSelect');
  const filterTingkat = document.getElementById('tingkat');
  const filterKelas   = document.getElementById('kelas');
  let currentPage     = <?= (int)$page ?>;
  let typingTimer;

  function renderRows(data) {
    if (!data || data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Data Tidak di Temukan</td></tr>';
      return;
    }

    let html = '';
    for (const r of data) {
      const noAbsen      = escapeHtml(r.no_absen_siswa || '');
      const nama         = escapeHtml(r.nama_siswa || '');
      const nisn         = escapeHtml(r.no_induk_siswa || '');
      const kelas        = escapeHtml(r.nama_kelas || '-');
      const kelasAttr    = escapeHtml(r.nama_kelas || '');
      const catatanSafe  = escapeHtml(r.catatan_wali_kelas || '').replace(/\r\n|\r|\n/g, '<br>');

      html += `
        <tr data-kelas="${kelasAttr}">
          <td>${noAbsen}</td>
          <td>${nama}</td>
          <td>${nisn}</td>
          <td>${kelas}</td>
          <td class="text-start">${catatanSafe}</td>
          <td>
            <a href="preview_rapor.php?id=${r.id_siswa}" class="btn btn-info btn-sm">
              <i class="fa-solid fa-eye fa-lg"></i> Preview
            </a>
            <a href="print_rapor.php?id=${r.id_siswa}" class="btn btn-primary btn-sm">
              <i class="fa-solid fa-print fa-lg"></i> Print
            </a>
          </td>
        </tr>`;
    }
    tbody.innerHTML = html;
  }

  function renderPagination(page, totalPages, total, showed, per) {
    const start = Math.max(1, page - 2);
    const end   = Math.min(totalPages, page + 2);
    let html    = '';

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
    pageInfo.innerHTML = `Menampilkan <strong>${showed}</strong> dari <strong>${total}</strong> data • Halaman <strong>${page}</strong> / <strong>${totalPages}</strong>`;

    [...pagUl.querySelectorAll('a[data-page]')].forEach(a => {
      a.addEventListener('click', e => {
        e.preventDefault();
        currentPage = Number(a.getAttribute('data-page'));
        doSearch();
      });
    });
  }

  async function doSearch() {
    const q    = input.value.trim();
    const per  = Number(perSel.value || 10);
    const page = currentPage;
    const params = new URLSearchParams({ ajax: '1', q, per, page });

    try {
      const res  = await fetch(`?${params.toString()}`);
      const json = await res.json();
      if (!json.ok) return;

      renderRows(json.data);
      renderPagination(json.page, json.totalPages, json.total, json.data.length, json.per);

      // setelah data ter-render, terapkan filter + highlight
      filterTable();
    } catch (e) {
      console.error(e);
    }
  }

  function filterTable() {
    const sRaw = (input.value || '');
    const s    = sRaw.toLowerCase();
    const t    = (filterTingkat && filterTingkat.value || '').toLowerCase();
    const k    = (filterKelas && filterKelas.value || '').toLowerCase();

    const rows = tbody.querySelectorAll('tr');
    rows.forEach(row => {
      const cells = row.querySelectorAll('td');
      if (cells.length < 5) return;

      const absenText = cells[0].textContent || '';
      const namaText  = cells[1].textContent || '';
      const nisnText  = cells[2].textContent || '';
      const kelasText = cells[3].textContent || '';
      const komText   = cells[4].textContent || '';

      const rowText = (absenText + ' ' + namaText + ' ' + nisnText + ' ' + kelasText + ' ' + komText).toLowerCase();
      const kelasAttr = (row.dataset.kelas || '').toLowerCase();

      const matchesFilter = (!t || rowText.includes(t)) && (!k || kelasAttr.includes(k));
      const matchesSearch = !s || rowText.includes(s);

      if (matchesFilter && matchesSearch) {
        row.style.display = '';

        // highlight di setiap sel (kecuali aksi)
        cells[0].innerHTML = highlightText(absenText, sRaw);
        cells[1].innerHTML = highlightText(namaText, sRaw);
        cells[2].innerHTML = highlightText(nisnText, sRaw);
        cells[3].innerHTML = highlightText(kelasText, sRaw);
        cells[4].innerHTML = highlightText(komText, sRaw);

        if (sRaw) {
          row.classList.add('row-highlight');
        } else {
          row.classList.remove('row-highlight');
        }
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
    typingTimer = setTimeout(doSearch, 250);
  });

  // Event: per-page
  perSel.addEventListener('change', () => {
    currentPage = 1;
    doSearch();
  });

  // Event: filter tingkat & kelas (client-side)
  if (filterTingkat) {
    filterTingkat.addEventListener('change', filterTable);
  }
  if (filterKelas) {
    filterKelas.addEventListener('change', filterTable);
  }

  // jalankan pertama kali
  doSearch();
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

/* Sama seperti di data_siswa.php */
.highlight {
  background-color: #d4edda;
  padding: 1px 2px;
  border-radius: 3px;
}
.row-highlight {
  background-color: #d4edda !important;
  transition: background-color 0.3s ease;
}

.filter-container { display:flex; flex-direction:column; gap:10px; }
.filter-group label { min-width:70px; }

@media (min-width:768px){
  .filter-container{ align-items:flex-start; }
}
@media (max-width:768px){
  .top-bar{ flex-direction:column !important; align-items:center !important; text-align:center; }
  .filter-container{ width:100%; align-items:center !important; }
  .filter-group{ justify-content:center !important; width:100%; }
  .dk-select{ width:100% !important; }
  .action-buttons{ justify-content:center !important; margin-top:10px; }

  #pagination { flex-wrap: wrap !important; justify-content: center !important; gap: 4px !important; margin-top: 10px; }
  #pagination .page-link { padding: 4px 8px; font-size: 13px; }
  #pageInfo { font-size: 13px; margin-top: 5px; white-space: normal; }
  nav[aria-label="Page navigation"] { overflow: visible !important; position: relative; z-index: 10; }
}
</style>
