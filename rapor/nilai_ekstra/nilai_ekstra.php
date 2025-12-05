<?php
include '../../koneksi.php';
include '../../includes/header.php';

/* =========================
 *  PAGINATION CONFIG
 * ========================= */
$allowedPer = [10, 20, 50, 100];
$perPage    = isset($_GET['per']) ? (int)$_GET['per'] : 10;
if (!in_array($perPage, $allowedPer, true)) {
    $perPage = 10;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

/* Hitung total data nilai_ekstrakurikuler */
$resCount = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM nilai_ekstrakurikuler");
$rowCount = mysqli_fetch_assoc($resCount);
$total    = (int)($rowCount['total'] ?? 0);

$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

/* =========================
 *  DATA UNTUK FILTER EKSTRA
 * ========================= */
$queryEkstraFilter = mysqli_query(
    $koneksi,
    "SELECT DISTINCT nama_ekstrakurikuler 
     FROM ekstrakurikuler 
     ORDER BY nama_ekstrakurikuler ASC"
);
?>

<body>
<?php include '../../includes/navbar.php'; ?>

<main class="content">
  <div class="cards row" style="margin-top: -50px;">
    <div class="col-12">
      <div class="card shadow-sm" style="border-radius: 15px;">

        <!-- ALERT (muncul setelah tambah / hapus) -->
        <?php if (!empty($_GET['status']) && !empty($_GET['msg'])): ?>
          <?php
          $status = $_GET['status'];
          $msg    = $_GET['msg'];
          $class  = $status === 'success' ? 'alert-success' : 'alert-danger';
          ?>
          <div id="pageAlert"
               class="alert <?= $class ?> alert-dismissible fade show mx-3 mt-3 mb-0 auto-dismiss-alert"
               role="alert"
               data-auto-dismiss="true">
            <?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <!-- TOP BAR: JUDUL + TOMBOL -->
        <div class="mt-0 d-flex align-items-center flex-wrap mb-0 p-3 top-bar">
          <h5 class="mb-1 fw-semibold fs-4" style="text-align:center;">Nilai Ekstrakulikuler</h5>

          <div class="ms-auto d-flex gap-2 action-buttons">
            <!-- Tambah -->
            <button class="btn btn-primary btn-md px-3 py-2 d-flex align-items-center gap-2"
                    type="button"
                    data-bs-toggle="modal" data-bs-target="#modalTambahEkstra">
              <i class="fa fa-plus"></i> Tambah
            </button>

            <!-- Import -->
            <button type="button"
                    class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2"
                    data-bs-toggle="modal" data-bs-target="#modalImportEkstra">
              <i class="fa-solid fa-file-arrow-down fa-lg"></i>
              <span>Import</span>
            </button>

            <!-- Export -->
            <button id="exportBtn" type="button"
                    class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
              <i class="fa-solid fa-file-arrow-up fa-lg"></i>
              Export
            </button>
          </div>
        </div>

        <!-- BAR FILTER: Ekstrakurikuler (kiri) + Tampil & Mode Edit (kanan) -->
        <div class="ms-3 me-3 bg-white d-flex justify-content-between align-items-center flex-wrap p-3 gap-3 rounded shadow-sm ekstra-filter-bar">
          <!-- KIRI: Ekstrakurikuler -->
          <div class="d-flex align-items-center gap-2">
            <label for="selectEkstra" class="fw-semibold mb-0">Ekstrakurikuler :</label>
            <select id="selectEkstra" class="form-select form-select-sm" style="width: 180px;">
              <option value="">All</option>
              <?php while ($ek = mysqli_fetch_assoc($queryEkstraFilter)): ?>
                <option value="<?= htmlspecialchars(strtolower($ek['nama_ekstrakurikuler'])) ?>">
                  <?= htmlspecialchars($ek['nama_ekstrakurikuler']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- KANAN: Tampil + Mode Edit -->
          <div class="d-flex align-items-center gap-3 ekstra-filter-right">
            <div class="d-flex align-items-center gap-2">
              <label class="fw-semibold mb-0" for="perPageSelect">Tampil</label>
              <select id="perPageSelect" class="form-select form-select-sm" style="width: 90px;">
                <?php foreach ($allowedPer as $opt): ?>
                  <option value="<?= $opt ?>" <?= $perPage === $opt ? 'selected' : '' ?>>
                    <?= $opt ?>/hal
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" id="toggleEdit">
              <label class="form-check-label fw-semibold" for="toggleEdit">Mode Edit</label>
            </div>
          </div>
        </div>

        <div class="card-body">

          <!-- SEARCH -->
          <div class="mb-3 d-flex justify-content-start search-row">
            <input type="text" id="searchInput" class="form-control form-control-sm"
                   placeholder="Cari siswa atau kelas..." style="max-width:260px; width:100%;">
          </div>

          <?php
          /* =========================
           *  QUERY NILAI + LIMIT
           * ========================= */
          $sqlNilai = "
            SELECT 
              n.id_nilai_ekstrakurikuler,
              s.nama_siswa,
              k.nama_kelas,
              e.nama_ekstrakurikuler,
              n.nilai_ekstrakurikuler
            FROM nilai_ekstrakurikuler AS n
            LEFT JOIN siswa AS s ON n.id_siswa = s.id_siswa
            LEFT JOIN kelas AS k ON s.id_kelas = k.id_kelas
            LEFT JOIN ekstrakurikuler AS e ON n.id_ekstrakurikuler = e.id_ekstrakurikuler
            ORDER BY k.nama_kelas, s.nama_siswa
            LIMIT {$perPage} OFFSET {$offset}
          ";
          $result   = mysqli_query($koneksi, $sqlNilai);
          $pageRows = mysqli_num_rows($result);

          // daftar ekstra utk select di kolom
          $ekstra = mysqli_query($koneksi, "SELECT id_ekstrakurikuler, nama_ekstrakurikuler FROM ekstrakurikuler");
          $ekstra_list = [];
          while ($e = mysqli_fetch_assoc($ekstra)) {
            $ekstra_list[] = $e;
          }
          ?>

          <!-- FORM SIMPAN NILAI -->
          <form id="formNilaiEkstra" method="POST" action="simpan_nilai_ekstra.php">
            <div class="table-responsive">
              <table class="table table-bordered table-striped align-middle">
                <thead style="background-color:#1d52a2" class="text-center text-white">
                  <tr>
                    <th style="width:40px;">
                      <input type="checkbox" id="selectAll">
                    </th>
                    <th>No</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Ekstrakurikuler</th>
                    <th>Nilai</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody id="tbodyNilai" class="text-center">
                  <?php if ($pageRows > 0): ?>
                    <?php $no = $offset + 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                      <tr>
                        <!-- checkbox untuk hapus multiple -->
                        <td class="text-center">
                          <input type="checkbox"
                                 class="row-check"
                                 value="<?= $row['id_nilai_ekstrakurikuler'] ?>">
                        </td>

                        <td>
                          <input type="hidden" name="id[]" value="<?= $row['id_nilai_ekstrakurikuler'] ?>">
                          <?= $no++ ?>
                        </td>
                        <td>
                          <input type="text" class="form-control form-control-sm"
                                 value="<?= htmlspecialchars($row['nama_siswa']) ?>" disabled>
                        </td>
                        <td>
                          <input type="text" class="form-control form-control-sm"
                                 value="<?= htmlspecialchars($row['nama_kelas']) ?>" style="width:100px;" disabled>
                        </td>
                        <td>
                          <select name="nama_ekstra[]" class="form-select form-select-sm" disabled>
                            <?php foreach ($ekstra_list as $e): ?>
                              <option value="<?= $e['id_ekstrakurikuler'] ?>"
                                <?= ($e['nama_ekstrakurikuler'] == $row['nama_ekstrakurikuler']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($e['nama_ekstrakurikuler']) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </td>
                        <td>
                          <select name="nilai[]" class="form-select form-select-sm" disabled>
                            <?php
                            $opsiNilai = ['A', 'B', 'C', 'D'];
                            foreach ($opsiNilai as $n):
                            ?>
                              <option value="<?= $n ?>" <?= ($row['nilai_ekstrakurikuler'] == $n) ? 'selected' : '' ?>>
                                <?= $n ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </td>
                        <td class="text-center">
                          <a href="hapus_nilai_ekstra.php?id=<?= $row['id_nilai_ekstrakurikuler'] ?>"
                             class="btn btn-danger btn-sm"
                             onclick="return confirm('Yakin ingin menghapus data ini?');">
                            <i class="bi bi-trash"></i> Hapus
                          </a>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="7" class="text-center text-muted">Tidak ada data</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <?php if ($pageRows > 0): ?>
              <div class="d-flex justify-content-between flex-wrap mt-3 gap-2">
                <button type="button" id="deleteSelected" class="btn btn-danger btn-sm" disabled>
                  <i class="bi bi-trash"></i> Hapus Terpilih
                </button>

                <button type="submit" class="btn btn-success">
                  <i class="bi bi-save"></i> Simpan
                </button>
              </div>
            <?php endif; ?>
          </form>

          <!-- FORM TERSEMBUNYI UNTUK HAPUS MULTIPLE (KIRIM KE FILE PROSES) -->
          <form id="formDeleteMultiple" action="nilai_ekstra_hapus_multiple.php" method="POST" style="display:none;">
            <!-- field ini dibaca di file proses -->
            <input type="hidden" name="hapus_multiple" value="1">
          </form>

          <!-- PAGINATION -->
          <?php if ($total > 0): ?>
            <?php
            $self = htmlspecialchars($_SERVER['PHP_SELF']);
            $base = $self . '?per=' . $perPage . '&page=';
            $start = max(1, $page - 2);
            $end   = min($totalPages, $page + 2);
            ?>
            <nav aria-label="Page navigation">
              <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                  <a class="page-link" href="<?= $base . '1' ?>">« First</a>
                </li>
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                  <a class="page-link" href="<?= $base . max(1, $page - 1) ?>">‹ Prev</a>
                </li>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                  <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $base . $i ?>"><?= $i ?></a>
                  </li>
                <?php endfor; ?>

                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                  <a class="page-link" href="<?= $base . min($totalPages, $page + 1) ?>">Next ›</a>
                </li>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                  <a class="page-link" href="<?= $base . $totalPages ?>">Last »</a>
                </li>
              </ul>
              <p class="text-center text-muted mt-2 mb-0">
                Menampilkan <strong><?= $pageRows ?></strong> dari <strong><?= $total ?></strong> data •
                Halaman <strong><?= $page ?></strong> / <strong><?= $totalPages ?></strong>
              </p>
            </nav>
          <?php endif; ?>

          <!-- ====== SCRIPT FILTER + SEARCH + HAPUS MULTIPLE ====== -->
          <script>
          document.addEventListener('DOMContentLoaded', function () {
            const selectEkstra = document.getElementById('selectEkstra');
            const searchInput  = document.getElementById('searchInput');
            const tbody        = document.getElementById('tbodyNilai');
            const rows         = Array.from(tbody.querySelectorAll('tr'));

            const selectAll       = document.getElementById('selectAll');
            const deleteBtn       = document.getElementById('deleteSelected');
            const formDeleteMulti = document.getElementById('formDeleteMultiple');

            function getInputValue(td) {
              const input  = td.querySelector('input');
              const select = td.querySelector('select');
              if (input)  return (input.value || '').toLowerCase();
              if (select) {
                const opt = select.options[select.selectedIndex];
                return opt ? opt.text.toLowerCase() : '';
              }
              return (td.innerText || '').toLowerCase();
            }

            function clearHighlight(row) {
              row.classList.remove('row-highlight');
            }

            function setHighlight(row) {
              row.classList.add('row-highlight');
            }

            function filterTable() {
              const selectedEkstra = (selectEkstra.value || '').toLowerCase();
              const searchText     = (searchInput.value || '').toLowerCase().trim();
              let visibleCount     = 0;

              rows.forEach(row => {
                // kolom: 0=checkbox, 1=No, 2=Nama, 3=Kelas, 4=Ekstra
                const nama   = getInputValue(row.cells[2]);
                const kelas  = getInputValue(row.cells[3]);
                const ekstra = getInputValue(row.cells[4]);

                const matchEkstra  = !selectedEkstra || ekstra.includes(selectedEkstra);
                const matchSearch  = !searchText || nama.includes(searchText) || kelas.includes(searchText);

                if (matchEkstra && matchSearch) {
                  row.style.display = '';
                  visibleCount++;
                  if (searchText) setHighlight(row); else clearHighlight(row);
                } else {
                  row.style.display = 'none';
                  clearHighlight(row);
                }
              });

              let emptyRow = document.getElementById('noDataRow');
              if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.id = 'noDataRow';
                emptyRow.innerHTML = `<td colspan="7" class="text-center text-muted">Data Tidak di Temukan</td>`;
                tbody.appendChild(emptyRow);
              }
              emptyRow.style.display = visibleCount === 0 ? '' : 'none';
            }

            // Hapus multiple
            function updateDeleteState() {
              const checks    = tbody.querySelectorAll('.row-check');
              const anyCheck  = Array.from(checks).some(cb => cb.checked);
              deleteBtn.disabled = !anyCheck;

              if (checks.length === 0) {
                if (selectAll) selectAll.checked = false;
              } else if (selectAll) {
                selectAll.checked = Array.from(checks).every(cb => cb.checked);
              }
            }

            function initCheckboxHandlers() {
              const checks = tbody.querySelectorAll('.row-check');
              checks.forEach(cb => {
                cb.addEventListener('change', updateDeleteState);
              });

              if (selectAll) {
                selectAll.addEventListener('change', () => {
                  const checked = selectAll.checked;
                  checks.forEach(cb => cb.checked = checked);
                  updateDeleteState();
                });
              }

              if (deleteBtn && formDeleteMulti) {
                deleteBtn.addEventListener('click', function () {
                  const selected = tbody.querySelectorAll('.row-check:checked');
                  if (selected.length === 0) {
                    alert('Tidak ada data yang dipilih.');
                    return;
                  }
                  if (!confirm('Yakin ingin menghapus data terpilih?')) {
                    return;
                  }

                  // Hapus semua input id_nilai[] lama, tapi biarkan input hapus_multiple
                  formDeleteMulti
                    .querySelectorAll("input[name='id_nilai[]']")
                    .forEach(el => el.remove());

                  selected.forEach(cb => {
                    const hidden = document.createElement('input');
                    hidden.type  = 'hidden';
                    hidden.name  = 'id_nilai[]';
                    hidden.value = cb.value;
                    formDeleteMulti.appendChild(hidden);
                  });

                  formDeleteMulti.submit();
                });
              }

              updateDeleteState();
            }

            selectEkstra.addEventListener('change', filterTable);
            searchInput.addEventListener('input', filterTable);

            // Per-page -> reload
            const perSel = document.getElementById('perPageSelect');
            if (perSel) {
              perSel.addEventListener('change', function () {
                const per = this.value;
                window.location.href = '?per=' + encodeURIComponent(per) + '&page=1';
              });
            }

            filterTable();
            initCheckboxHandlers();
          });
          </script>

          <!-- MODE EDIT -->
          <script>
          const toggleEdit = document.getElementById("toggleEdit");

          if (localStorage.getItem("editModeEkstra") === "on") {
            toggleEdit.checked = true;
          }

          function setFormState() {
            const isEdit = toggleEdit.checked;
            localStorage.setItem("editModeEkstra", isEdit ? "on" : "off");

            document.querySelectorAll("select[name='nama_ekstra[]'], select[name='nilai[]']")
              .forEach(select => {
                select.disabled = !isEdit;
              });
          }

          toggleEdit.addEventListener("change", setFormState);
          setFormState();
          </script>

          <!-- AUTO DISMISS ALERT 5 DETIK -->
          <script>
          document.addEventListener('DOMContentLoaded', function () {
            const alertEl = document.querySelector('.auto-dismiss-alert');
            if (alertEl) {
              setTimeout(function () {
                if (!alertEl.classList.contains('show')) return;
                alertEl.classList.remove('show'); // bootstrap fade out
                setTimeout(function () {
                  if (alertEl && alertEl.parentNode) {
                    alertEl.parentNode.removeChild(alertEl);
                  }
                }, 500); // waktu animasi fade
              }, 5000); // 5 detik
            }
          });
          </script>

        </div><!-- card-body -->
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

  .ekstra-filter-bar {
    flex-direction: column !important;
    align-items: stretch !important;
  }

  .ekstra-filter-bar > div {
    width: 100%;
  }

  .ekstra-filter-right {
    justify-content: space-between;
  }

  .search-row {
    justify-content: center;
  }

  #searchInput {
    max-width: 100%;
  }
}

.row-highlight {
  background-color: #d4edda !important;
  transition: background-color 0.2s ease;
}
</style>

<?php include '../../includes/footer.php'; ?>

<!-- ========== MODAL TAMBAH ========== -->
<div class="modal fade" id="modalTambahEkstra" tabindex="-1" aria-labelledby="modalTambahEkstraLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius: 10px;">
      <div class="modal-header" style="background-color: #0d6efd; color: white; border-top-left-radius: 10px; border-top-right-radius: 10px;">
        <h5 class="modal-title fw-semibold" id="modalTambahEkstraLabel">
          <i class="fa fa-star"></i> Tambah Nilai Ekstrakurikuler
        </h5>
      </div>

      <div class="modal-body">
        <form action="nilai_ekstra_tambah_proses.php" method="POST">

          <!-- Nama Siswa -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Siswa</label>
            <select name="id_siswa" class="form-select" required>
              <option value="" selected disabled>-- Pilih Siswa --</option>
              <?php
              $querySiswa = mysqli_query($koneksi, "SELECT id_siswa, nama_siswa FROM siswa ORDER BY nama_siswa ASC");
              while ($siswa = mysqli_fetch_assoc($querySiswa)) {
                  echo "<option value='{$siswa['id_siswa']}'>{$siswa['nama_siswa']}</option>";
              }
              ?>
            </select>
          </div>

          <!-- Semester -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Semester</label>
            <select name="id_semester" class="form-select" required>
              <option value="" selected disabled>-- Pilih Semester --</option>
              <?php
              $query_semester = mysqli_query($koneksi, "SELECT * FROM semester ORDER BY id_semester ASC");
              while ($sem = mysqli_fetch_assoc($query_semester)) {
                  echo "<option value='{$sem['id_semester']}'>{$sem['nama_semester']} - {$sem['tahun_ajaran']}</option>";
              }
              ?>
            </select>
          </div>

          <!-- Ekstrakurikuler -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Ekstrakurikuler</label>
            <select name="id_ekstra" class="form-select" required>
              <option value="" selected disabled>-- Pilih Ekstrakurikuler --</option>
              <?php
              $queryEkstra = mysqli_query($koneksi, "SELECT id_ekstrakurikuler, nama_ekstrakurikuler FROM ekstrakurikuler ORDER BY nama_ekstrakurikuler ASC");
              while ($ekstra = mysqli_fetch_assoc($queryEkstra)) {
                  echo "<option value='{$ekstra['id_ekstrakurikuler']}'>{$ekstra['nama_ekstrakurikuler']}</option>";
              }
              ?>
            </select>
          </div>

          <!-- Nilai -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Nilai</label>
            <select name="nilai_ekstrakurikuler" class="form-select" required>
              <option value="" selected disabled>-- Pilih Nilai --</option>
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="C">C</option>
              <option value="D">D</option>
            </select>
          </div>

          <!-- Tombol -->
          <div class="modal-footer">
            <div class="d-flex w-100 gap-2">
              <button type="submit" class="btn btn-success w-50">
                <i class="fa fa-save"></i> Simpan
              </button>
              <button type="button" class="btn btn-danger w-50" data-bs-dismiss="modal">
                <i class="fa fa-times"></i> Batal
              </button>
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<!-- ========== MODAL IMPORT ========== -->
<div class="modal fade" id="modalImportEkstra" tabindex="-1" aria-labelledby="modalImportEkstraLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius: 10px;">
      <div class="modal-header" style="background-color: #198754; color: white; border-top-left-radius: 10px; border-top-right-radius: 10px;">
        <h5 class="modal-title fw-semibold" id="modalImportEkstraLabel">
          Import Data Nilai Ekstrakulikuler
        </h5>
      </div>

      <div class="modal-body">
        <form action="nilai_ekstra_import.php" method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="excelFileImport" class="form-label">Pilih File Excel (.xlsx)</label>

            <div class="position-relative" style="display:flex;align-items:center;">
              <input 
                type="file" 
                class="form-control" 
                id="excelFileImport" 
                name="excel_file"
                accept=".xlsx, .xls" 
                onchange="toggleClearButtonImport()" 
                style="padding-right:35px;">
              <button 
                type="button" 
                id="clearFileBtnImport" 
                onclick="clearFileImport()" 
                title="Hapus file"
                style="
                  position:absolute;
                  right:10px;
                  background:none;
                  border:none;
                  color:#6c757d;
                  font-size:20px;
                  line-height:1;
                  display:none;
                  cursor:pointer;
                ">
                &times;
              </button>
            </div>
          </div>

          <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
              <i class="fa fa-times"></i> Batal
            </button>
            <button type="submit" class="btn btn-warning">
              <i class="fas fa-upload"></i> Upload
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>

<script>
// Script khusus input file Import
const fileInputImport = document.getElementById("excelFileImport");
const clearBtnImport  = document.getElementById("clearFileBtnImport");

function toggleClearButtonImport() {
  if (!fileInputImport || !clearBtnImport) return;
  clearBtnImport.style.display = fileInputImport.files.length > 0 ? "block" : "none";
}

function clearFileImport() {
  if (!fileInputImport || !clearBtnImport) return;
  fileInputImport.value = "";
  clearBtnImport.style.display = "none";
}
</script>

</body>
