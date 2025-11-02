<?php
// ====== BACKEND SETUP ======
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);

// Path koneksi (file ini di /rapor/absensi/)
require_once __DIR__ . '/../../koneksi.php';

/* --------- BULK DELETE --------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'bulk_delete') {
  $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? array_map('intval', $_POST['ids']) : [];
  if (!empty($ids)) {
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $koneksi->prepare("DELETE FROM absensi WHERE id_absensi IN ($marks)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $stmt->close();
  }
  header('Location: data_absensi.php');
  exit;
}

/* --------- DELETE SATU BARIS --------- */
if (isset($_GET['del'])) {
  $del = (int)$_GET['del'];
  if ($del > 0) {
    $stmt = $koneksi->prepare("DELETE FROM absensi WHERE id_absensi=?");
    $stmt->bind_param('i', $del);
    $stmt->execute();
    $stmt->close();
  }
  header('Location: data_absensi.php');
  exit;
}

/* --------- AMBIL DATA TABEL --------- */
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$params = [];
$types  = '';

$sql = "
  SELECT
    a.id_absensi,
    s.nama_siswa,
    s.no_induk_siswa AS nis,
    COALESCE(g.nama_guru, '-') AS wali_kelas,
    a.sakit, a.izin, a.alpha
  FROM absensi a
  JOIN siswa s ON s.id_siswa = a.id_siswa
  LEFT JOIN kelas k ON k.id_kelas = s.id_kelas
  LEFT JOIN guru  g ON g.id_guru  = k.id_guru
";

if ($search !== '') {
  $sql .= " WHERE (s.nama_siswa LIKE ? OR s.no_induk_siswa LIKE ? OR k.nama_kelas LIKE ? OR g.nama_guru LIKE ?) ";
  $like = "%{$search}%";
  $params = [$like, $like, $like, $like];
  $types  = 'ssss';
}

$sql .= " ORDER BY s.nama_siswa ASC ";

if ($params) {
  $stmt = $koneksi->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $result = $stmt->get_result();
  $stmt->close();
} else {
  $result = $koneksi->query($sql);
}
?>

<?php include '../../includes/header.php'; ?>
<body>
  <?php include '../../includes/navbar.php'; ?>

  <main class="content">
    <div class="cards row" style="margin-top: -50px;">
      <div class="col-12">
        <div class="card shadow-sm" style="border-radius: 15px;">

          <!-- BAR ATAS -->
          <div class="mt-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between p-3 top-bar">
            <div class="d-flex flex-column align-items-md-start align-items-center text-md-start text-center mb-2 mb-md-0">
              <h5 class="mb-2 fw-semibold fs-4">Data Absensi Siswa</h5>
              <div class="filter-group d-flex align-items-center gap-2">
                <label for="tingkat" class="form-label fw-semibold mb-0">Tingkat</label>
                <select id="tingkat" class="form-select dk-select" style="width: 120px;">
                  <option>--Pilih--</option>
                  <option>X</option>
                  <option>XI</option>
                  <option>XII</option>
                </select>
              </div>
            </div>

            <div class="d-flex gap-2 flex-wrap justify-content-md-end justify-content-center mt-3 mt-md-0 action-buttons">
              <a href="data_absensi_tambah.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1 px-3 fw-semibold" style="border-radius: 5px;">
                <i class="fa-solid fa-plus fa-lg"></i> Tambah
              </a>
              <a href="data_absensi_import.php" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-arrow-down fa-lg"></i> <span>Import</span>
              </a>
              <button id="exportBtn" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-arrow-up fa-lg"></i> Export
              </button>
            </div>
          </div>

          <!-- Search & Sort -->
          <div class="ms-3 me-3 bg-white d-flex justify-content-center align-items-center flex-wrap p-2 gap-2">
            <form method="get" class="d-flex align-items-center gap-2">
              <input type="text" id="searchInput" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES) ?>" class="form-control form-control-sm" placeholder="Search" style="width: 200px;">
              <button class="btn btn-outline-secondary btn-sm p-2 rounded-3 d-flex align-items-center justify-content-center" id="searchBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                  <path d="M11 6a5 5 0 1 0-2.9 4.7l3.85 3.85a1 1 0 0 0 1.414-1.414l-3.85-3.85A4.978 4.978 0 0 0 11 6zM6 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
                </svg>
              </button>
              <button type="button" id="sortBtn" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 rounded-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sort-alpha-down" viewBox="0 0 16 16">
                  <path fill-rule="evenodd" d="M10.082 5.629 9.664 7H8.598l1.789-5.332h1.234L13.402 7h-1.12l-.419-1.371zm1.57-.785L11 2.687h-.047l-.652 2.157z" />
                  <path d="M12.96 14H9.028v-.691l2.579-3.72v-.054H9.098v-.867h3.785v.691l-2.567 3.72v.054h2.645zM4.5 2.5a.5.5 0 0 0-1 0v9.793l-1.146-1.147a.5.5 0 0 0-.708.708l2 1.999.007.007a.497.497 0 0 0 .7-.006l2-2a.5.5 0 0 0-.707-.708L4.5 12.293z" />
                </svg>
                Sort
              </button>
            </form>
          </div>

          <!-- Tabel -->
          <div class="card-body">
            <div class="table-responsive">
              <form method="post" id="bulkForm">
                <input type="hidden" name="aksi" value="bulk_delete">
                <table class="table table-bordered table-striped align-middle">
                  <thead style="background-color:#1d52a2" class="text-center text-white">
                    <tr>
                      <th rowspan="2"><input type="checkbox" id="selectAll"></th>
                      <th rowspan="2">Absen</th>
                      <th rowspan="2">Nama</th>
                      <th rowspan="2">NIS</th>
                      <th rowspan="2">Wali Kelas</th>
                      <th colspan="3">Keterangan</th>
                      <th rowspan="2">Aksi</th>
                    </tr>
                    <tr>
                      <th>Sakit</th>
                      <th>Izin</th>
                      <th>Alpha</th>
                    </tr>
                  </thead>
                  <tbody class="text-center">
                    <?php
                    $no = 1;
                    if ($result && $result->num_rows > 0):
                      while ($row = $result->fetch_assoc()):
                        $id   = (int)$row['id_absensi'];
                        $nama = htmlspecialchars($row['nama_siswa'] ?? '-', ENT_QUOTES);
                        $nis  = htmlspecialchars($row['nis'] ?? '-', ENT_QUOTES);
                        $wali = htmlspecialchars($row['wali_kelas'] ?? '-', ENT_QUOTES);
                        $skt  = (int)($row['sakit'] ?? 0);
                        $izn  = (int)($row['izin'] ?? 0);
                        $alp  = (int)($row['alpha'] ?? 0);
                    ?>
                      <tr>
                        <td><input type="checkbox" class="row-check" name="ids[]" value="<?= $id ?>"></td>
                        <td><?= $no++; ?></td>
                        <td><?= $nama ?></td>
                        <td><?= $nis ?></td>
                        <td><?= $wali ?></td>
                        <td><?= $skt ?></td>
                        <td><?= $izn ?></td>
                        <td><?= $alp ?></td>
                        <td>
                          <a href="data_absensi_edit.php?id=<?= $id ?>" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1">
                            <i class="bi bi-pencil-square"></i>Edit
                          </a>
                          <a href="data_absensi.php?del=<?= $id ?>" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1"
                             onclick="return confirm('Yakin ingin menghapus data ini?');">
                            <i class="bi bi-trash"></i>Del
                          </a>
                        </td>
                      </tr>
                    <?php
                      endwhile;
                    else:
                    ?>
                      <tr><td colspan="9" class="text-center text-muted">Tidak ada data.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </form>
            </div>

            <!-- Tombol Hapus Terpilih -->
            <div class="mt-2">
              <button id="deleteSelected" class="btn btn-danger btn-sm" disabled>
                <i class="bi bi-trash"></i> Hapus Terpilih
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- SCRIPT CHECKBOX / BULK -->
  <script>
    const selectAll = document.getElementById('selectAll');
    const deleteBtn = document.getElementById('deleteSelected');
    const bulkForm  = document.getElementById('bulkForm');

    function toggleDeleteButton() {
      const any = [...document.querySelectorAll('.row-check')].some(c => c.checked);
      deleteBtn.disabled = !any;
    }

    selectAll.addEventListener('change', function () {
      document.querySelectorAll('.row-check').forEach(chk => chk.checked = this.checked);
      toggleDeleteButton();
    });

    document.addEventListener('change', (e) => {
      if (e.target.classList.contains('row-check')) {
        const all = [...document.querySelectorAll('.row-check')];
        selectAll.checked = all.length > 0 && all.every(c => c.checked);
        toggleDeleteButton();
      }
    });

    deleteBtn.addEventListener('click', function () {
      if (this.disabled) return;
      if (confirm('Yakin ingin menghapus data terpilih?')) {
        bulkForm.submit();
      }
    });
  </script>

  <style>
    @media (max-width: 768px) {
      .top-bar { flex-direction: column !important; align-items: center !important; text-align: center; }
      .filter-group { justify-content: center !important; margin-top: 5px; }
      .action-buttons { justify-content: center !important; margin-top: 10px; }
    }
  </style>

  <?php include '../../includes/footer.php'; ?>
