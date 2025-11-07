<?php
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../koneksi.php';

/* --- Subquery: ambil 1 catatan terbaru per siswa dari cetak_rapor ---
 * Asumsi ada kolom AUTO_INCREMENT: id_cetak_rapor
 * Jika tidak ada, GANTI blok $joinCrLatest (lihat komentar alternatif di bawah).
 */
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

/* --- Alternatif kalau kamu pakai kolom waktu (mis. updated_at) ---
$joinCrLatest = "
  LEFT JOIN (
    SELECT crx.id_siswa, crx.catatan_wali_kelas
    FROM cetak_rapor crx
    INNER JOIN (
      SELECT id_siswa, MAX(updated_at) AS max_ts
      FROM cetak_rapor
      GROUP BY id_siswa
    ) last ON last.id_siswa = crx.id_siswa AND last.max_ts = crx.updated_at
  ) cr ON s.id_siswa = cr.id_siswa
";
*/

// EXPORT CSV
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    $sqlExport = "
      SELECT 
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
    ";
    $res = mysqli_query($koneksi, $sqlExport);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=siswa_export_' . date('Ymd_His') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id_siswa','no_absen_siswa','nama_siswa','no_induk_siswa','kelas','catatan_wali_kelas']);

    while ($row = mysqli_fetch_assoc($res)) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}
?>

<main class="content">
  <div class="cards row" style="margin-top: -50px;">
    <div class="col-12">
      <div class="card shadow-sm" style="border-radius: 15px;">
        <div class="mt-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between p-3 top-bar">
          <div class="d-flex flex-column align-items-md-start align-items-center text-md-start text-center mb-2 mb-md-0">
            <h5 class="mb-2 fw-semibold fs-4">Data Siswa</h5>

            <div class="filter-container d-flex flex-column gap-2 mt-2">
              <div class="filter-group d-flex align-items-center gap-2">
                <label for="filterTingkat" class="form-label fw-semibold mb-0">Tingkat</label>
                <select id="filterTingkat" class="form-select dk-select" style="width: 120px;">
                  <option selected value="">-- Semua --</option>
                  <option>X</option>
                  <option>XI</option>
                  <option>XII</option>
                </select>
              </div>
              <div class="filter-group d-flex align-items-center gap-2">
                <label for="filterKelas" class="form-label fw-semibold mb-0">Kelas</label>
                <select id="filterKelas" class="form-select dk-select" style="width: 140px;">
                  <option selected value="">-- Semua --</option>
                  <?php
                  $kelasQuery = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                  while ($k = mysqli_fetch_assoc($kelasQuery)) {
                      echo '<option value="'.htmlspecialchars($k['id_kelas']).'">'.htmlspecialchars($k['nama_kelas']).'</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
          </div>

          <div class="d-flex gap-2 flex-wrap justify-content-md-end justify-content-center mt-3 mt-md-0 action-buttons">
            <a href="tambah_siswa.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1 px-3 fw-semibold">
              <i class="fa-solid fa-plus"></i> Tambah
            </a>
            <a href="data_siswa_import.php" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
              <i class="fa-solid fa-file-arrow-down"></i> Import
            </a>
            <button id="exportBtn" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
              <i class="fa-solid fa-file-arrow-up"></i> Export
            </button>
          </div>
        </div>

        <div class="card-body">
          <div class="search-container mt-3 position-relative">
            <input type="text" id="searchInput" class="form-control form-control-sm search-input" placeholder="Masukan kata kunci">
            <span class="search-icon"><i class="bi bi-search"></i></span>
          </div>

          <form id="formDeleteMultiple" action="hapus_siswa_multiple.php" method="POST">
          <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped align-middle">
              <thead style="background-color:#1d52a2" class="text-center text-white">
                <tr>
                  <th><input type="checkbox" id="selectAll"></th>
                  <th>Absen</th>
                  <th>Nama</th>
                  <th>NISN</th>
                  <th>Kelas</th>
                  <th>Komentar</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody id="tbodyData">
                <?php
                $sqlList = "
                  SELECT s.*, k.nama_kelas, cr.catatan_wali_kelas
                  FROM siswa s
                  LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
                  $joinCrLatest
                  ORDER BY s.no_absen_siswa ASC
                ";
                $query = mysqli_query($koneksi, $sqlList);

                if (mysqli_num_rows($query) > 0):
                  while ($data = mysqli_fetch_assoc($query)):
                ?>
                  <tr data-kelas="<?= $data['id_kelas']; ?>" data-tingkat="<?= htmlspecialchars($data['tingkat'] ?? ''); ?>">
                    <td class="text-center"><input type="checkbox" name="id_siswa[]" class="row-check" value="<?= $data['id_siswa']; ?>"></td>
                    <td class="text-center"><?= htmlspecialchars($data['no_absen_siswa']); ?></td>
                    <td><?= htmlspecialchars($data['nama_siswa']); ?></td>
                    <td class="text-center"><?= htmlspecialchars($data['no_induk_siswa']); ?></td>
                    <td class="text-center"><?= htmlspecialchars($data['nama_kelas'] ?? '-'); ?></td>
                    <td><?= nl2br(htmlspecialchars($data['catatan_wali_kelas'] ?? '')); ?></td>
                    <td class="text-center">
                      <a href="edit_siswa.php?id=<?= $data['id_siswa']; ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil-square"></i> Edit
                      </a>
                      <a href="hapus_siswa.php?id=<?= $data['id_siswa']; ?>" onclick="return confirm('Yakin ingin menghapus data?');" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i> Del
                      </a>
                    </td>
                  </tr>
                <?php
                  endwhile;
                else:
                  echo '<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data siswa yang tersedia.</td></tr>';
                endif;
                ?>
              </tbody>
            </table>
          </div>
          <button type="submit" id="deleteSelected" class="btn btn-danger btn-sm mt-2" disabled>
            <i class="bi bi-trash"></i> Hapus Terpilih
          </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include '../../includes/footer.php'; ?>

<!-- ===== Script: Select All, Hapus Terpilih, Filter, Search ===== -->
<script>
// ===== EXPORT =====
document.getElementById('exportBtn').onclick = () => {
  const url = new URL(window.location.href);
  url.searchParams.set('action','export');
  window.location.href = url.toString();
};

// ===== UTIL =====
function getRowChecks() {
  return Array.from(document.querySelectorAll('input.row-check'));
}
function anyChecked() {
  return document.querySelector('input.row-check:checked') !== null;
}
function toggleDeleteButton() {
  const btn = document.getElementById('deleteSelected');
  if (btn) btn.disabled = !anyChecked();
}

// ===== EVENT DELEGATION =====
document.addEventListener('change', (e) => {
  if (e.target && e.target.id === 'selectAll') {
    const checked = e.target.checked;
    getRowChecks().forEach(cb => { cb.checked = checked; });
    toggleDeleteButton();
    return;
  }
  if (e.target && e.target.matches('input.row-check')) {
    const all = getRowChecks();
    const header = document.getElementById('selectAll');
    if (header) header.checked = all.length > 0 && all.every(cb => cb.checked);
    toggleDeleteButton();
  }
});

// Protek submit tanpa pilihan
const formDelete = document.getElementById('formDeleteMultiple');
if (formDelete) {
  formDelete.addEventListener('submit', (e) => {
    if (!anyChecked()) {
      e.preventDefault();
      alert('Pilih setidaknya satu data siswa yang akan dihapus.');
    }
  });
}

// ===== FILTER =====
const filterTingkat = document.getElementById('filterTingkat');
const filterKelas   = document.getElementById('filterKelas');
function filterTable(){
  const t = (filterTingkat?.value || '').toLowerCase();
  const k = filterKelas?.value || '';
  document.querySelectorAll('#tbodyData tr').forEach(row => {
    const rTingkat = row.dataset.tingkat?.toLowerCase() || '';
    const rKelas   = row.dataset.kelas || '';
    row.style.display = ((!t || rTingkat === t) && (!k || rKelas === k)) ? '' : 'none';
  });
}
filterTingkat?.addEventListener('change', filterTable);
filterKelas?.addEventListener('change', filterTable);

// ===== SEARCH =====
const searchInput = document.getElementById('searchInput');
searchInput?.addEventListener('input', () => {
  const filter = (searchInput.value || '').toLowerCase();
  document.querySelectorAll('#tbodyData tr').forEach(row => {
    const text = row.innerText.toLowerCase();
    if (!filter) {
      row.style.display = '';
      row.classList.remove('highlight');
    } else if (text.includes(filter)) {
      row.style.display = '';
      row.classList.add('highlight');
    } else {
      row.style.display = 'none';
      row.classList.remove('highlight');
    }
  });
});

// INIT
document.addEventListener('DOMContentLoaded', () => {
  const header = document.getElementById('selectAll');
  if (header) {
    const all = getRowChecks();
    header.checked = all.length > 0 && all.every(cb => cb.checked);
  }
  toggleDeleteButton();
});
</script>

<style>
/* FILTER */
.filter-container { display:flex; flex-direction:column; gap:10px; }
.filter-group label { min-width:60px; }

/* SEARCH */
.search-container { width:180px; position:relative; margin-top:10px; }
.search-input { padding-right:30px; padding-top:4px; padding-bottom:4px; }
.search-icon { position:absolute; top:50%; right:8px; transform:translateY(-50%); color:#6c757d; pointer-events:none; font-size:0.9rem; }
.highlight { background-color: rgba(0, 128, 0, 0.2); }

/* RESPONSIVE */
@media (min-width:768px){
  .filter-container{ flex-direction:column; align-items:flex-start; } 
}
@media (max-width:768px){
  .top-bar{ flex-direction:column !important; align-items:center !important; text-align:center; }
  .filter-container{ width:100%; align-items:center !important; margin-top:10px; }
  .filter-group{ justify-content:center !important; width:100%; }
  .dk-select{ width:100% !important; }
  .action-buttons{ justify-content:center !important; margin-top:10px; }
  .table-responsive{ overflow-x:auto; }
}
</style>
