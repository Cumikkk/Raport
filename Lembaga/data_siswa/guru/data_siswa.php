<?php
include '../../../koneksi.php';

// ====== PROSES DATA (MODE AJAX TANPA OUTPUT HTML) ======
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'] ?? '';

// Pastikan hanya guru yang boleh akses
$id_guru = isset($_SESSION['id_guru']) ? (int)$_SESSION['id_guru'] : 0;
if ($id_guru <= 0) {
  // Jika tidak ada id_guru, kembalikan kosong agar aman
  $rows = [];
  $total = 0;
  $totalPages = 1;
  $page = 1;
} else {
  // Cari kelas yang diampu guru ini
  $kelasIds = [];
  $stmtK = $koneksi->prepare("SELECT id_kelas FROM kelas WHERE id_guru = ?");
  if ($stmtK) {
    $stmtK->bind_param('i', $id_guru);
    $stmtK->execute();
    $resK = $stmtK->get_result();
    while ($rowK = $resK->fetch_assoc()) {
      $kelasIds[] = (int)$rowK['id_kelas'];
    }
    $stmtK->close();
  }

  if (empty($kelasIds)) {
    $rows = [];
    $total = 0;
    $totalPages = 1;
    $page = 1;
  } else {
    // ===== Parameter =====
    $q       = isset($_GET['q']) ? trim($_GET['q']) : '';
    $perPage = isset($_GET['per']) ? (int)$_GET['per'] : 10;
    $perPage = ($perPage >= 1 && $perPage <= 100) ? $perPage : 10;
    $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page    = max(1, $page);

    $inKelas  = implode(',', array_fill(0, count($kelasIds), '?'));
    $typesK   = str_repeat('i', count($kelasIds));

    // ===== Hitung total =====
    if ($q !== '') {
      $sqlCount = "SELECT COUNT(*) AS total
                   FROM siswa s
                   LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
                   WHERE s.id_kelas IN ($inKelas)
                     AND (s.nama_siswa LIKE CONCAT('%', ?, '%')
                          OR s.no_absen_siswa LIKE CONCAT('%', ?, '%')
                          OR s.no_induk_siswa LIKE CONCAT('%', ?, '%'))";
      $stmtC = $koneksi->prepare($sqlCount);
      if ($stmtC === false) {
        die('Prepare failed: ' . $koneksi->error);
      }
      $params = array_merge($kelasIds, [$q, $q, $q]);
      $types  = $typesK . 'sss';
      $stmtC->bind_param($types, ...$params);
    } else {
      $sqlCount = "SELECT COUNT(*) AS total
                   FROM siswa s
                   LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
                   WHERE s.id_kelas IN ($inKelas)";
      $stmtC = $koneksi->prepare($sqlCount);
      if ($stmtC === false) {
        die('Prepare failed: ' . $koneksi->error);
      }
      $params = $kelasIds;
      $types  = $typesK;
      $stmtC->bind_param($types, ...$params);
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
              LEFT JOIN cetak_rapor cr ON cr.id_siswa = s.id_siswa
              WHERE s.id_kelas IN ($inKelas)
                AND (
                  s.nama_siswa LIKE CONCAT('%', ?, '%')
                  OR s.no_absen_siswa LIKE CONCAT('%', ?, '%')
                  OR s.no_induk_siswa LIKE CONCAT('%', ?, '%')
                )
              ORDER BY s.no_absen_siswa ASC
              LIMIT ? OFFSET ?";

      $stmt = $koneksi->prepare($sql);
      if ($stmt === false) {
        die('Prepare failed: ' . $koneksi->error);
      }

      $params = array_merge($kelasIds, [$q, $q, $q, $perPage, $offset]);
      $types  = $typesK . 'sssii';
      $stmt->bind_param($types, ...$params);
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
              LEFT JOIN cetak_rapor cr ON cr.id_siswa = s.id_siswa
              WHERE s.id_kelas IN ($inKelas)
              ORDER BY s.no_absen_siswa ASC
              LIMIT ? OFFSET ?";

      $stmt = $koneksi->prepare($sql);
      if ($stmt === false) {
        die('Prepare failed: ' . $koneksi->error);
      }

      $params = array_merge($kelasIds, [$perPage, $offset]);
      $types  = $typesK . 'ii';
      $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($r = $result->fetch_assoc()) {
      $rows[] = $r;
    }
    $stmt->close();
  }
}

// ===== Mode AJAX (dipakai oleh pagination & search) =====
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    'ok'         => true,
    'csrf'       => $csrf,
    'data'       => $rows,
    'page'       => $page ?? 1,
    'per'        => $perPage ?? 10,
    'total'      => $total ?? 0,
    'totalPages' => $totalPages ?? 1,
    'q'          => $q ?? '',
  ]);
  exit;
}

include '../../../includes/header.php';
include '../../../includes/navbar.php';
?>

<main class="content">
  <div class="cards row" style="margin-top: -50px;">
    <div class="col-12">
      <div class="card shadow-sm" style="border-radius: 15px;">

        <!-- ===== BAR ATAS ===== -->
        <div class="mt-0 d-flex flex-column flex-md-row align-items-md-start justify-content-between p-3 top-bar gap-3">

          <!-- Kiri: Judul + Search -->
          <div class="d-flex flex-column w-100">
            <h5 class="mb-2 fw-semibold fs-4 text-md-start text-center">Daftar Siswa Binaan</h5>

            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2">

              <!-- Search -->
              <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 flex-grow-1">
                <div class="position-relative search-wrap" style="max-width:280px; width:100%;">
                  <input
                    type="text"
                    id="searchInput"
                    class="form-control form-control-sm"
                    placeholder="Ketik untuk mencari..."
                    value="<?= htmlspecialchars($q ?? ''); ?>">
                  <span class="search-icon"><i class="bi bi-search"></i></span>
                </div>
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
                      <td class="text-start align-top">
                        <?php if (!empty($d['catatan_wali_kelas'])): ?>
                          <?= nl2br(htmlspecialchars(mb_strimwidth($d['catatan_wali_kelas'], 0, 100, '...'))) ?>
                        <?php else: ?>
                          <span class="text-muted fst-italic">Belum ada komentar</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <a href="komentar2.php?id=<?= (int)$d['id_siswa']; ?>" class="btn btn-warning btn-sm" target="_blank">
                          <i class="fa-solid fa-comment-dots"></i> Komentar
                        </a>
                        <a href="../../../rapor/cetak_rapor/print_rapor.php?id=<?= (int)$d['id_siswa']; ?>" class="btn btn-primary btn-sm" target="_blank">
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
          <nav aria-label="Page navigation" class="mt-3">
            <div class="pager-area">
              <div class="pager-group">
                <ul class="pagination mb-0" id="paginationWrap"></ul>

                <div class="pager-sep" aria-hidden="true"></div>

                <select id="perPage" class="form-select form-select-sm per-select">
                  <?php foreach ([10, 20, 50, 100] as $opt): ?>
                    <option value="<?= $opt ?>" <?= (isset($perPage) && $perPage === $opt) ? 'selected' : '' ?>><?= $opt ?>/hal</option>
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

<?php include '../../../includes/footer.php'; ?>

<script>
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
    const perSel = document.getElementById('perPage');

    let currentPage = <?= isset($page) ? (int)$page : 1 ?>;
    let typingTimer;

    function renderRows(data) {
      if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Data Tidak di Temukan</td></tr>';
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
          <td class="text-start align-top komentar-cell">
            <?php if (!empty($d['catatan_wali_kelas'])): ?>
                <?= nl2br(htmlspecialchars(mb_strimwidth($d['catatan_wali_kelas'], 0, 100, '...'))) ?>
            <?php else: ?>
                <span class="text-muted fst-italic">Belum ada komentar</span>
            <?php endif; ?>
          </td>
          <td style="width:15%;">
            <a href="komentar2.php?id=${r.id_siswa}" class="btn btn-warning btn-sm" title="Komentar">
              <i class="fa-solid fa-comment-dots"></i>
            </a>
            <a href="../../../rapor/cetak_rapor/print_rapor.php?id=${r.id_siswa}" class="btn btn-primary btn-sm" target="_blank" title="Print">
              <i class="fa-solid fa-print fa-lg"></i>
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
      } catch (e) {
        console.error(e);
      }
    }

    // Event: search (server-side) + highlight sederhana
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

  .komentar-cell {
    max-width: 250px;
    /* batasi lebar */
    max-height: 80px;
    /* batasi tinggi */
    overflow-y: auto;
    /* scroll kalau panjang */
    white-space: normal;
    word-break: break-word;
    font-size: 0.9rem;
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