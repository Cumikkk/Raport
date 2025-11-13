<?php
include '../../includes/header.php';
?>

<?php
// ===== BACKEND: ambil data tanpa mengubah tampilan =====
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
require_once __DIR__ . '/../../koneksi.php';
mysqli_set_charset($koneksi, 'utf8mb4');

$mapel = [];
try {
  $cek = $koneksi->query("SHOW TABLES LIKE 'mata_pelajaran'");
  if ($cek && $cek->num_rows > 0) {
    // Pakai kolom sesuai skema: id_mata_pelajaran, nama_mata_pelajaran, kelompok_mata_pelajaran
    $stmt = $koneksi->prepare("
      SELECT id_mata_pelajaran, nama_mata_pelajaran, kelompok_mata_pelajaran
      FROM mata_pelajaran
      ORDER BY nama_mata_pelajaran ASC
    ");
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
      $mapel[] = $row;
    }
    $stmt->close();
  }
} catch (Throwable $e) {
  // biarkan kosong jika error, tampilan tetap sama
}
?>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <main class="content">
    <div class="cards row" style="margin-top: -50px;">
      <div class="col-12">
        <div class="card shadow-sm" style="border-radius: 15px;">
          <div class="mt-0 d-flex align-items-center flex-wrap mb-0 p-3 top-bar">
            <!-- Judul di kiri -->
            <h5 class="mb-1 fw-semibold fs-4" style=" text-align: center">Nilai Mata Pelajaran</h5>

            <!-- Tombol di kanan -->
            <div class="ms-auto d-flex gap-2 action-buttons">
              <a href="nilai_mapel_tambah.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1 p-2 pe-3 fw-semibold" style="border-radius: 5px;">
                <i class="fa-solid fa-plus fa-lg"></i>
                Tambah
              </a>

              <a href="nilai_mapel_import.php" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-arrow-down fa-lg"></i>
                <span>Import</span>
              </a>

              <button id="exportBtn" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                 <i class="fa-solid fa-file-arrow-up fa-lg"></i>
                Export
              </button>
            </div>
          </div>

          <!-- Search & Sort -->
          <div class="ms-3 me-3 bg-white d-flex justify-content-center align-items-center flex-wrap p-2 gap-2">
            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search" style="width: 200px;">
            <button id="searchBtn" class="btn btn-outline-secondary btn-sm p-2 rounded-3 d-flex align-items-center justify-content-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                <path d="M11 6a5 5 0 1 0-2.9 4.7l3.85 3.85a1 1 0 0 0 1.414-1.414l-3.85-3.85A4.978 4.978 0 0 0 11 6zM6 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
              </svg>
            </button>

            <button id="sortBtn" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 rounded-3">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sort-alpha-down" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M10.082 5.629 9.664 7H8.598l1.789-5.332h1.234L13.402 7h-1.12l-.419-1.371zm1.57-.785L11 2.687h-.047l-.652 2.157z" />
                <path d="M12.96 14H9.028v-.691l2.579-3.72v-.054H9.098v-.867h3.785v.691l-2.567 3.72v.054h2.645zM4.5 2.5a.5.5 0 0 0-1 0v9.793l-1.146-1.147a.5.5 0 0 0-.708.708l2 1.999.007.007a.497.497 0 0 0 .7-.006l2-2a.5.5 0 0 0-.707-.708L4.5 12.293z" />
              </svg>
              Sort
            </button>
          </div>

          <!-- Tabel nilai Mapel ambil data mapel -->
          <div class="card-body">
            <div class="table-responsive">
              <table id="mapelTable" class="table table-bordered table-striped align-middle">
                <thead style="background-color:#1d52a2" class="text-center text-white">
                  <tr>
                    <th>No</th>
                    <th>Mata Pelajaran</th>
                    <th>Jenis</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody id="mapelBody">
                  <?php if (count($mapel) === 0): ?>
                    <tr>
                      <td colspan="4" class="text-center text-muted">Belum ada data mata pelajaran.</td>
                    </tr>
                  <?php else: $no=1; foreach ($mapel as $m): ?>
                    <tr>
                      <td><?= $no++; ?></td>
                      <td><?= htmlspecialchars($m['nama_mata_pelajaran']); ?></td>
                      <td><?= htmlspecialchars($m['kelompok_mata_pelajaran']); ?></td>
                      <td class="text-center">
                        <a href="nilai_mapel.php?id=<?= urlencode($m['id_mata_pelajaran']); ?>"
                          class="btn btn-warning btn-sm me-1 d-inline-flex align-items-center justify-content-center gap-1 px-2 py-1 me-1"
                          style="font-size: 15px;">
                          <i class="bi bi-pencil-square" style="font-size: 15px;"></i>
                          <span>Details</span>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <style>
    /* Tambahan CSS Responsif */
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
      .h5 { justify-content:center; }
      .action-buttons a,
      .action-buttons button { width: auto; }
    }
  </style>

  <script>
    // ====== LIVE SEARCH (tanpa Enter, + renumber) ======
    (function () {
      const input = document.getElementById('searchInput');
      const btn   = document.getElementById('searchBtn');
      const body  = document.getElementById('mapelBody');

      const debounce = (fn, delay = 120) => {
        let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
      };

      function filter() {
        const q = (input.value || '').trim().toLowerCase();
        const rows = Array.from(body.querySelectorAll('tr'));
        let visible = 0;

        rows.forEach(tr => {
          const tds = tr.querySelectorAll('td');
          if (tds.length < 4) return; // skip placeholder
          const nama  = (tds[1].textContent || '').toLowerCase();
          const jenis = (tds[2].textContent || '').toLowerCase();
          const match = !q || nama.includes(q) || jenis.includes(q);
          tr.style.display = match ? '' : 'none';
          if (match) {
            visible++;
            tds[0].textContent = visible; // renumber kolom "No"
          }
        });
      }

      if (input) input.addEventListener('input', debounce(filter, 120));
      if (btn) btn.addEventListener('click', (e) => { e.preventDefault(); filter(); input.focus(); });

      // Normalisasi nomor awal
      filter();
    })();

    // ====== SORT A-Z / Z-A (klik bergantian) ======
    (function(){
      const btn = document.getElementById('sortBtn');
      const body = document.getElementById('mapelBody');
      let asc = false; // pertama klik -> A-Z

      function sortRows() {
        const rows = Array.from(body.querySelectorAll('tr')).filter(tr => tr.querySelectorAll('td').length > 1);
        rows.sort((a, b) => {
          const A = a.children[1].textContent.trim().toLowerCase();
          const B = b.children[1].textContent.trim().toLowerCase();
          return asc ? A.localeCompare(B) : B.localeCompare(A);
        });
        rows.forEach((tr, i) => {
          tr.children[0].textContent = i + 1;
          body.appendChild(tr);
        });
        asc = !asc;
      }
      if (btn) btn.addEventListener('click', sortRows);
    })();

    // ====== EXPORT CSV (tampilan sama, hanya aksi tombol) ======
    (function(){
      const btn = document.getElementById('exportBtn');
      if (!btn) return;
      btn.addEventListener('click', () => {
        const table = document.getElementById('mapelTable');
        if (!table) return;
        let csv = [];
        for (const row of table.querySelectorAll('tr')) {
          if (row.style.display === 'none') continue;
          const cells = Array.from(row.querySelectorAll('th,td')).map(td => {
            let text = td.innerText.replace(/\r?\n|\r/g, ' ').replace(/"/g, '""').trim();
            return `"${text}"`;
          });
          csv.push(cells.join(','));
        }
        const blob = new Blob([csv.join('\n')], {type: 'text/csv;charset=utf-8;'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'mapel.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
      });
    })();
  </script>

  <?php include '../../includes/footer.php'; ?>
</body>
