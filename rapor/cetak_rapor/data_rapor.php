<?php
include '../../includes/header.php';
?>

<body>
<?php include '../../includes/navbar.php'; ?>

<main class="content">
  <div class="cards row" style="margin-top: -50px;">
    <div class="col-12">
      <div class="card shadow-sm" style="border-radius: 15px;">

        <!-- ===== BAR ATAS ===== -->
        <div class="mt-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between p-3 top-bar">

          <!-- Kiri: Judul dan Dropdown -->
          <div class="d-flex flex-column align-items-md-start align-items-center text-md-start text-center mb-2 mb-md-0">
            <h5 class="mb-2 fw-semibold fs-4">Cetak Rapor Siswa</h5>

            <!-- ===== FILTER CONTAINER ===== -->
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
            <a href="data_absensi_tambah.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1 px-3 fw-semibold" style="border-radius: 5px;">
              <i class="fa-solid fa-print fa-lg"></i> Print Semua Rapor
            </a>

            <a href="data_absensi_import.php" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
              <i class="fa-solid fa-file-arrow-down fa-lg"></i> <span>Import</span>
            </a>
          </div>
        </div>

        <!-- ===== SEARCH ===== -->
        <div class="ms-3 me-3 bg-white d-flex justify-content-start align-items-center flex-wrap p-2 gap-2">
          <div class="position-relative" style="max-width:200px; width:80%;">
            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search...">
            <span class="search-icon"><i class="bi bi-search"></i></span>
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
                <?php
                include '../../koneksi.php';
                $q = mysqli_query($koneksi, "
                  SELECT s.id_siswa, s.no_absen_siswa, s.nama_siswa, s.no_induk_siswa, s.komentar_siswa, k.nama_kelas
                  FROM siswa s
                  LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
                  ORDER BY s.no_absen_siswa ASC
                ");
                while ($d = mysqli_fetch_assoc($q)) : ?>
                  <tr data-kelas="<?= htmlspecialchars($d['nama_kelas']); ?>">
                    <td><?= htmlspecialchars($d['no_absen_siswa']); ?></td>
                    <td><?= htmlspecialchars($d['nama_siswa']); ?></td>
                    <td><?= htmlspecialchars($d['no_induk_siswa']); ?></td>
                    <td><?= htmlspecialchars($d['nama_kelas'] ?? '-'); ?></td>
                    <td class="text-start"><?= nl2br(htmlspecialchars($d['komentar_siswa'])); ?></td>
                    <td>
                      <a href="preview_rapor.php?id=<?= $d['id_siswa']; ?>" class="btn btn-info btn-sm">
                        <i class="fa-solid fa-eye fa-lg"></i> Preview
                      </a>
                      <a href="print_rapor.php?id=<?= $d['id_siswa']; ?>" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-print fa-lg"></i> Print
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</main>

<script>
// Filter & Search
const searchInput = document.getElementById('searchInput');
const filterTingkat = document.getElementById('tingkat');
const filterKelas = document.getElementById('kelas');

[searchInput, filterTingkat, filterKelas].forEach(el => {
  el.addEventListener('input', filterTable);
});

function filterTable() {
  const s = searchInput.value.toLowerCase();
  const t = filterTingkat.value.toLowerCase();
  const k = filterKelas.value.toLowerCase();
  document.querySelectorAll('#tbodyData tr').forEach(row => {
    const rText = row.innerText.toLowerCase();
    const rKelas = row.dataset.kelas.toLowerCase();
    const matchesFilter = (!t || rText.includes(t)) && (!k || rKelas.includes(k));
    const matchesSearch = !s || rText.includes(s);

    if(matchesFilter && matchesSearch){
      row.style.display = '';
      row.style.backgroundColor = s ? '#d4edda' : '';
    } else {
      row.style.display = 'none';
      row.style.backgroundColor = '';
    }
  });
}
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

.filter-container { display:flex; flex-direction:column; gap:10px; }
.filter-group label { min-width:70px; }

@media (min-width:768px){ .filter-container{ align-items:flex-start; } }
@media (max-width:768px){
  .top-bar{ flex-direction:column !important; align-items:center !important; text-align:center; }
  .filter-container{ width:100%; align-items:center !important; }
  .filter-group{ justify-content:center !important; width:100%; }
  .dk-select{ width:100% !important; }
  .action-buttons{ justify-content:center !important; margin-top:10px; }
}
</style>

<?php include '../../includes/footer.php'; ?>
