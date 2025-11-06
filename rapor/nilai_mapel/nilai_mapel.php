<?php
include '../../includes/header.php';
?>

<body>
    <?php include '../../includes/navbar.php'; ?>

    <main class="content">
        <div class="cards row" style="margin-top: -50px;">
            <div class="col-12">
                <div class="card shadow-sm" style="border-radius: 15px;">
                    <div class="mt-0 d-flex align-items-center flex-wrap mb-0 p-3 top-bar">
                        <!-- Judul di kiri -->
                        <h5 class="mb-1 fw-semibold fs-4" style=" text-align: center">Nilai Mata Pelajaran - Bahasa Inggris</h5>

                    </div>

                    <!-- Filter Kelas & Search -->
<div class="ms-3 me-3 bg-white d-flex justify-content-between align-items-center flex-wrap p-3 gap-3 rounded shadow-sm">
  
  <!-- Pilih Kelas -->
  <div class="d-flex align-items-center gap-2">
    <label for="selectKelas" class="fw-semibold">Kelas:</label>
    <select id="selectKelas" class="form-select form-select-sm" style="width: 180px;">
      <option value="">-- Pilih Kelas --</option>
      <option value="1A">1A</option>
      <option value="1B">1B</option>
      <option value="2A">2A</option>
      <option value="2B">2B</option>
      <option value="3A">3A</option>
      <option value="3B">3B</option>
    </select>
  </div>

  <!-- Search -->
  <div class="d-flex align-items-center gap-2">
    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Cari nama siswa..." style="width: 220px;">
    <button id="searchBtn" class="btn btn-outline-secondary btn-sm p-2 rounded-3 d-flex align-items-center justify-content-center">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
        <path d="M11 6a5 5 0 1 0-2.9 4.7l3.85 3.85a1 1 0 0 0 1.414-1.414l-3.85-3.85A4.978 4.978 0 0 0 11 6zM6 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
      </svg>
    </button>
  </div>

</div>


                    <!-- Tabel nilai Mapel ambil data mapel -->
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <table class="table table-bordered text-center align-middle">
                                    <thead style="background-color:#1d52a2" class="text-white">
                                        <tr>
                                            <th rowspan="3">NO.</th>
                                            <th rowspan="3">NAMA</th>
                                            <th colspan="16">FORMATIF</th>
                                            <th colspan="4">SUMATIF</th>
                                            <th rowspan="3">SUMATIF<br>TENGAH<br>SEMESTER</th>
                                            <th rowspan="3">AKSI</th>
                                        </tr>
                                        <tr>
                                            <th colspan="4">LINGKUP MATERI 1</th>
                                            <th colspan="4">LINGKUP MATERI 2</th>
                                            <th colspan="4">LINGKUP MATERI 3</th>
                                            <th colspan="4">LINGKUP MATERI 4</th>
                                            <th colspan="4">LINGKUP MATERI</th>
                                        </tr>
                                        <tr>
                                            <th>TP1</th>
                                            <th>TP2</th>
                                            <th>TP3</th>
                                            <th>TP4</th>
                                            <th>TP1</th>
                                            <th>TP2</th>
                                            <th>TP3</th>
                                            <th>TP4</th>
                                            <th>TP1</th>
                                            <th>TP2</th>
                                            <th>TP3</th>
                                            <th>TP4</th>
                                            <th>TP1</th>
                                            <th>TP2</th>
                                            <th>TP3</th>
                                            <th>TP4</th>
                                            <th>LM1</th>
                                            <th>LM2</th>
                                            <th>LM3</th>
                                            <th>LM4</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Airlangga Gustav Arvizu L.</td>
                                            <td>100</td>
                                            <td>100</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>100</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>93</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>32</td>
                                            <td>
                                                <a href="#" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </a>
                                                <a href="hapus_mapel.php?id=1"
                                                    class="btn btn-danger btn-sm me-1 d-inline-flex align-items-center justify-content-center gap-1 px-2 py-1"
                                                    style="font-size: 15px;"
                                                    onclick="return confirm('Yakin ingin menghapus data ini?');">
                                                    <i class="bi bi-trash" style="font-size: 15px;"></i>
                                                    <span>Del</span>
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

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

            .h5 {
                justify-content: center;
            }

            .action-buttons a,
            .action-buttons button {
                width: auto;
            }
        }
    </style>

    <?php include '../../includes/footer.php'; ?>
</body><?php 
// ===== BACKEND (fix: hide jejak + aman dari htmlspecialchars(NULL)) =====
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
require_once __DIR__ . '/../../koneksi.php';
mysqli_set_charset($koneksi, 'utf8mb4');

// Helper aman untuk cetak nilai (hindari warning PHP 8.1+)
function safe($v) {
  if ($v === null) return '';
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Ambil id mapel dari query string
$id_mapel = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_mapel <= 0) {
  echo "<script>alert('Parameter id mapel tidak valid.');location.href='mapel.php';</script>";
  exit;
}

// Ambil daftar semester
$semester_list = [];
$qSem = $koneksi->query("SELECT id_semester, COALESCE(nama_semester, CONCAT('Semester ', id_semester)) AS nama_semester FROM semester ORDER BY id_semester ASC");
while ($row = $qSem->fetch_assoc()) $semester_list[] = $row;
$qSem->close();

// Tentukan semester aktif (default: terakhir)
$id_semester = isset($_GET['id_semester']) && is_numeric($_GET['id_semester'])
  ? (int)$_GET['id_semester']
  : ($semester_list ? (int)end($semester_list)['id_semester'] : 1);

// Ambil nama mapel
$mapel_nama = 'Tidak Diketahui';
$qMap = $koneksi->prepare("SELECT nama_mata_pelajaran FROM mata_pelajaran WHERE id_mata_pelajaran = ? LIMIT 1");
$qMap->bind_param('i', $id_mapel);
$qMap->execute();
$resMap = $qMap->get_result()->fetch_assoc();
if ($resMap) $mapel_nama = $resMap['nama_mata_pelajaran'];
$qMap->close();

// ===== INTI PERBAIKAN JEJAK =====
// 1) Pakai INNER JOIN agar hanya siswa yang punya baris nilai ditampilkan.
// 2) Tambah GROUP BY untuk berjaga-jaga jika dulu sempat ada duplikat baris nilai.
$rows = [];
$sql = "
  SELECT 
    s.id_siswa,
    s.nama_siswa,
    s.no_absen_siswa,
    n.tp1_lm1, n.tp2_lm1, n.tp3_lm1, n.tp4_lm1, n.sumatif_lm1,
    n.tp1_lm2, n.tp2_lm2, n.tp3_lm2, n.tp4_lm2, n.sumatif_lm2,
    n.tp1_lm3, n.tp2_lm3, n.tp3_lm3, n.tp4_lm3, n.sumatif_lm3,
    n.tp1_lm4, n.tp2_lm4, n.tp3_lm4, n.tp4_lm4, n.sumatif_lm4,
    n.sumatif_tengah_semester
  FROM nilai_mata_pelajaran n
  INNER JOIN siswa s 
    ON s.id_siswa = n.id_siswa
  WHERE n.id_mata_pelajaran = ?
    AND n.id_semester = ?
  GROUP BY 
    s.id_siswa, s.nama_siswa, s.no_absen_siswa,
    n.tp1_lm1, n.tp2_lm1, n.tp3_lm1, n.tp4_lm1, n.sumatif_lm1,
    n.tp1_lm2, n.tp2_lm2, n.tp3_lm2, n.tp4_lm2, n.sumatif_lm2,
    n.tp1_lm3, n.tp2_lm3, n.tp3_lm3, n.tp4_lm3, n.sumatif_lm3,
    n.tp1_lm4, n.tp2_lm4, n.tp3_lm4, n.tp4_lm4, n.sumatif_lm4,
    n.sumatif_tengah_semester
  ORDER BY (s.no_absen_siswa + 0), s.nama_siswa
";
$q = $koneksi->prepare($sql);
$q->bind_param('ii', $id_mapel, $id_semester);
$q->execute();
$res = $q->get_result();
while ($r = $res->fetch_assoc()) $rows[] = $r;
$q->close();
?>

<?php include '../../includes/header.php'; ?>

<body>
<?php include '../../includes/navbar.php'; ?>

<main class="content">
  <div class="cards row" style="margin-top:-50px;">
    <div class="col-12">
      <div class="card shadow-sm" style="border-radius:15px;">
        <div class="mt-0 d-flex align-items-center flex-wrap mb-0 p-3 top-bar">
          <h5 class="mb-1 fw-semibold fs-4 text-center">
            Nilai Mata Pelajaran - <?= safe($mapel_nama); ?>
          </h5>
        </div>

        <!-- Bagian atas (tampilan asli dipertahankan) -->
        <div class="ms-3 me-3 bg-white d-flex justify-content-between align-items-center flex-wrap p-3 gap-3 rounded shadow-sm">
          <!-- dropdown semester tersembunyi (tidak ubah tampilan) -->
          <form method="get" style="display:none">
            <input type="hidden" name="id" value="<?= safe($id_mapel); ?>">
            <select name="id_semester" onchange="this.form.submit()">
              <?php foreach ($semester_list as $sem): ?>
                <option value="<?= $sem['id_semester']; ?>" <?= ($sem['id_semester'] == $id_semester) ? 'selected' : ''; ?>>
                  <?= safe($sem['nama_semester']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>

          <div class="d-flex align-items-center gap-2">
            <label for="selectKelas" class="fw-semibold">Kelas:</label>
            <select id="selectKelas" class="form-select form-select-sm" style="width: 180px;">
              <option value="">-- Pilih Kelas --</option>
              <option value="1A">1A</option>
              <option value="1B">1B</option>
              <option value="2A">2A</option>
              <option value="2B">2B</option>
              <option value="3A">3A</option>
              <option value="3B">3B</option>
            </select>
          </div>

          <div class="d-flex align-items-center gap-2">
            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Cari nama siswa..." style="width: 220px;">
            <button id="searchBtn" class="btn btn-outline-secondary btn-sm p-2 rounded-3 d-flex align-items-center justify-content-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                <path d="M11 6a5 5 0 1 0-2.9 4.7l3.85 3.85a1 1 0 0 0 1.414-1.414l-3.85-3.85A4.978 4.978 0 0 0 11 6zM6 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
              </svg>
            </button>
          </div>

          <div class="d-flex align-items-center gap-2">
            <!-- Tambah -->
            <a href="tambah_nilai.php?id=<?= urlencode($id_mapel); ?>&id_semester=<?= urlencode($id_semester); ?>"
              class="btn btn-primary btn-sm d-flex align-items-center gap-2">
              <i class="fa-solid fa-plus"></i><span>Tambah</span>
            </a>

            <!-- Import -->
            <a href="import_nilai.php?id=<?= urlencode($id_mapel); ?>&id_semester=<?= urlencode($id_semester); ?>"
              class="btn btn-success btn-sm d-flex align-items-center gap-2">
              <i class="fa-solid fa-download"></i><span>Import</span>
            </a>

            <!-- Export -->
            <a href="nilai_mapel_export2.php?id=<?= urlencode($id_mapel) ?>&id_semester=<?= urlencode($id_semester) ?>"
              class="btn btn-success btn-sm d-flex align-items-center gap-2">
              <i class="fa-solid fa-file-excel"></i><span>Export</span>
            </a>
            </a>
          </div>
        </div>

        <!-- Tabel nilai (tampilan sama) -->
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
              <thead style="background-color:#1d52a2" class="text-white">
                <tr>
                  <th rowspan="3">NO.</th>
                  <th rowspan="3">NAMA</th>
                  <th colspan="16">FORMATIF</th>
                  <th colspan="4">SUMATIF</th>
                  <th rowspan="3">SUMATIF<br>TENGAH<br>SEMESTER</th>
                  <th rowspan="3">AKSI</th>
                </tr>
                <tr>
                  <th colspan="4">LINGKUP MATERI 1</th>
                  <th colspan="4">LINGKUP MATERI 2</th>
                  <th colspan="4">LINGKUP MATERI 3</th>
                  <th colspan="4">LINGKUP MATERI 4</th>
                  <th colspan="4">LINGKUP MATERI</th>
                </tr>
                <tr>
                  <th>TP1</th><th>TP2</th><th>TP3</th><th>TP4</th>
                  <th>TP1</th><th>TP2</th><th>TP3</th><th>TP4</th>
                  <th>TP1</th><th>TP2</th><th>TP3</th><th>TP4</th>
                  <th>TP1</th><th>TP2</th><th>TP3</th><th>TP4</th>
                  <th>LM1</th><th>LM2</th><th>LM3</th><th>LM4</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($rows) === 0): ?>
                  <tr><td colspan="24" class="text-center text-muted">Belum ada data nilai untuk semester ini.</td></tr>
                <?php else: $no=1; foreach ($rows as $r): ?>
                  <tr>
                    <td><?= $no++; ?></td>
                    <td><?= safe($r['nama_siswa']); ?></td>
                    <td><?= safe($r['tp1_lm1']); ?></td>
                    <td><?= safe($r['tp2_lm1']); ?></td>
                    <td><?= safe($r['tp3_lm1']); ?></td>
                    <td><?= safe($r['tp4_lm1']); ?></td>
                    <td><?= safe($r['tp1_lm2']); ?></td>
                    <td><?= safe($r['tp2_lm2']); ?></td>
                    <td><?= safe($r['tp3_lm2']); ?></td>
                    <td><?= safe($r['tp4_lm2']); ?></td>
                    <td><?= safe($r['tp1_lm3']); ?></td>
                    <td><?= safe($r['tp2_lm3']); ?></td>
                    <td><?= safe($r['tp3_lm3']); ?></td>
                    <td><?= safe($r['tp4_lm3']); ?></td>
                    <td><?= safe($r['tp1_lm4']); ?></td>
                    <td><?= safe($r['tp2_lm4']); ?></td>
                    <td><?= safe($r['tp3_lm4']); ?></td>
                    <td><?= safe($r['tp4_lm4']); ?></td>
                    <td><?= safe($r['sumatif_lm1']); ?></td>
                    <td><?= safe($r['sumatif_lm2']); ?></td>
                    <td><?= safe($r['sumatif_lm3']); ?></td>
                    <td><?= safe($r['sumatif_lm4']); ?></td>
                    <td><?= safe($r['sumatif_tengah_semester']); ?></td>
                    <td>
                      <a class="btn btn-warning btn-sm px-2 py-1" href="edit_nilai.php?id=<?= urlencode($id_mapel) ?>&id_semester=<?= urlencode($id_semester) ?>&id_siswa=<?= urlencode($r['id_siswa']) ?>">
                        <i class="bi bi-pencil-square"></i> Edit
                      </a>
                      <a href="hapus_nilai.php?id_siswa=<?= urlencode($r['id_siswa']); ?>&id_mapel=<?= urlencode($id_mapel); ?>&id_semester=<?= urlencode($id_semester); ?>"
                         class="btn btn-danger btn-sm d-inline-flex align-items-center justify-content-center gap-1 px-2 py-1"
                         onclick="return confirm('Yakin ingin menghapus nilai siswa ini?');">
                        <i class="bi bi-trash"></i><span>Del</span>
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

<?php include '../../includes/footer.php'; ?>
</body>
