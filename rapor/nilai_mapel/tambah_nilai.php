<?php
// ===== BACKEND =====
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
require_once __DIR__ . '/../../koneksi.php';
mysqli_set_charset($koneksi, 'utf8mb4');

// TERIMA id mapel & id_semester dari URL
$id_mapel = $_GET['id'] ?? $_GET['id_mapel'] ?? '';
$id_mapel = is_numeric($id_mapel) ? (int)$id_mapel : 0;

$id_semester_qs = (isset($_GET['id_semester']) && is_numeric($_GET['id_semester']))
  ? (int)$_GET['id_semester']
  : null;

// Validasi mapel harus ada
$nama_mapel = 'Tidak Diketahui';
if ($id_mapel > 0) {
  $q = $koneksi->prepare("SELECT nama_mata_pelajaran FROM mata_pelajaran WHERE id_mata_pelajaran = ? LIMIT 1");
  $q->bind_param('i', $id_mapel);
  $q->execute();
  $r = $q->get_result()->fetch_assoc();
  $q->close();
  if ($r) {
    $nama_mapel = $r['nama_mata_pelajaran'];
  } else {
    echo "<script>alert('Mapel tidak ditemukan.');location.href='nilai_mapel.php';</script>";
    exit;
  }
} else {
  echo "<script>alert('Parameter id mapel tidak valid.');location.href='nilai_mapel.php';</script>";
  exit;
}

// Ambil daftar siswa
$siswa = [];
$res = $koneksi->query("SELECT id_siswa, nama_siswa FROM siswa ORDER BY nama_siswa ASC");
while ($row = $res->fetch_assoc()) { $siswa[] = $row; }
$res->close();

// Ambil daftar semester dan tentukan default (terakhir)
$semester_list = [];
$cekSem = $koneksi->query("
  SELECT id_semester, COALESCE(nama_semester, CONCAT('Semester ', id_semester)) AS nama_semester
  FROM semester ORDER BY id_semester ASC
");
while ($row = $cekSem->fetch_assoc()) { $semester_list[] = $row; }
$id_semester_default = $semester_list ? (int)end($semester_list)['id_semester'] : 1;

// Jika URL membawa id_semester yang valid, pakai itu
if ($id_semester_qs !== null) {
  foreach ($semester_list as $sem) {
    if ((int)$sem['id_semester'] === $id_semester_qs) {
      $id_semester_default = $id_semester_qs;
      break;
    }
  }
}

// Helper: ambil integer atau NULL
function postIntOrNull($key) {
  if (!isset($_POST[$key]) || $_POST[$key] === '') return null;
  return (int)$_POST[$key];
}

// Saat submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_siswa    = isset($_POST['id_siswa']) ? (int)$_POST['id_siswa'] : 0;
  $id_semester = isset($_POST['id_semester']) ? (int)$_POST['id_semester'] : $id_semester_default;

  // Nilai LM1..LM4 (TP1..TP4) + Sumatif LM1..LM4 + STS
  $tp1_lm1 = postIntOrNull('tp1_lm1'); $tp2_lm1 = postIntOrNull('tp2_lm1'); $tp3_lm1 = postIntOrNull('tp3_lm1'); $tp4_lm1 = postIntOrNull('tp4_lm1');
  $tp1_lm2 = postIntOrNull('tp1_lm2'); $tp2_lm2 = postIntOrNull('tp2_lm2'); $tp3_lm2 = postIntOrNull('tp3_lm2'); $tp4_lm2 = postIntOrNull('tp4_lm2');
  $tp1_lm3 = postIntOrNull('tp1_lm3'); $tp2_lm3 = postIntOrNull('tp2_lm3'); $tp3_lm3 = postIntOrNull('tp3_lm3'); $tp4_lm3 = postIntOrNull('tp4_lm3');
  $tp1_lm4 = postIntOrNull('tp1_lm4'); $tp2_lm4 = postIntOrNull('tp2_lm4'); $tp3_lm4 = postIntOrNull('tp3_lm4'); $tp4_lm4 = postIntOrNull('tp4_lm4');

  $sumatif_lm1 = postIntOrNull('sumatif_lm1');
  $sumatif_lm2 = postIntOrNull('sumatif_lm2');
  $sumatif_lm3 = postIntOrNull('sumatif_lm3');
  $sumatif_lm4 = postIntOrNull('sumatif_lm4');

  $sumatif_tengah_semester = postIntOrNull('sumatif_tengah_semester');

  if ($id_siswa <= 0) {
    echo "<script>alert('Siswa harus dipilih!');history.back();</script>";
    exit;
  }

  try {
    // Pastikan semester valid (hindari FK gagal)
    $cekS = $koneksi->prepare("SELECT 1 FROM semester WHERE id_semester=? LIMIT 1");
    $cekS->bind_param('i', $id_semester);
    $cekS->execute();
    $adaSem = $cekS->get_result()->fetch_row();
    $cekS->close();
    if (!$adaSem) {
      echo "<script>alert('Semester tidak ditemukan.');history.back();</script>";
      exit;
    }

    // Cegah duplikat (semester + mapel + siswa)
    $cek = $koneksi->prepare("
      SELECT 1 FROM nilai_mata_pelajaran 
      WHERE id_semester=? AND id_mata_pelajaran=? AND id_siswa=? LIMIT 1
    ");
    $cek->bind_param('iii', $id_semester, $id_mapel, $id_siswa);
    $cek->execute();
    $ada = $cek->get_result()->fetch_row();
    $cek->close();

    if ($ada) {
      echo "<script>alert('Data nilai untuk siswa & semester ini sudah ada!');history.back();</script>";
      exit;
    }

    // INSERT lengkap (24 kolom total)
    $stmt = $koneksi->prepare("
     INSERT INTO nilai_mata_pelajaran(
      id_semester, id_siswa, id_mata_pelajaran,
      tp1_lm1, tp2_lm1, tp3_lm1, tp4_lm1, sumatif_lm1,
      tp1_lm2, tp2_lm2, tp3_lm2, tp4_lm2, sumatif_lm2,
      tp1_lm3, tp2_lm3, tp3_lm3, tp4_lm3, sumatif_lm3,
      tp1_lm4, tp2_lm4, tp3_lm4, tp4_lm4, sumatif_lm4,
      sumatif_tengah_semester)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    if (!$stmt) {
     throw new Exception("Prepare failed: " . $koneksi->error);
    }

    $stmt->bind_param(
    // 24 integer placeholders
    'iiiiiiiiiiiiiiiiiiiiiiii',
    $id_semester, $id_siswa, $id_mapel,
    $tp1_lm1, $tp2_lm1, $tp3_lm1, $tp4_lm1, $sumatif_lm1,
    $tp1_lm2, $tp2_lm2, $tp3_lm2, $tp4_lm2, $sumatif_lm2,
    $tp1_lm3, $tp2_lm3, $tp3_lm3, $tp4_lm3, $sumatif_lm3,
    $tp1_lm4, $tp2_lm4, $tp3_lm4, $tp4_lm4, $sumatif_lm4,
    $sumatif_tengah_semester
    );

$stmt->execute();
$stmt->close();

    // Redirect kembali ke nilai_mapel dengan semester aktif
    header("Location: nilai_mapel.php?id=" . urlencode($id_mapel) . "&id_semester=" . urlencode($id_semester));
    exit;

  } catch (Throwable $e) {
    echo "<script>alert('Gagal menyimpan data: " . addslashes($e->getMessage()) . "');history.back();</script>";
    exit;
  }
}
?>

<?php include '../../includes/header.php'; ?>
<body>
<?php include '../../includes/navbar.php'; ?>

<main class="content">
  <div class="cards row" style="margin-top:-50px;">
    <div class="col-12">
      <div class="card shadow-sm" style="border-radius:15px;">
        <div class="mt-0 d-flex align-items-center flex-wrap mb-0 p-3 top-bar">
          <h5 class="mb-1 fw-semibold fs-4" style="text-align:center">
            Tambah Nilai Mata Pelajaran - <?= htmlspecialchars($nama_mapel); ?>
          </h5>
        </div>

        <div class="card-body">
          <form method="post" action="">
            <input type="hidden" name="id_mapel" value="<?= htmlspecialchars($id_mapel); ?>">

            <div class="mb-3">
              <label class="form-label fw-semibold">Pilih Siswa</label>
              <select name="id_siswa" class="form-select" required>
                <option value="">-- Pilih Siswa --</option>
                <?php foreach ($siswa as $s): ?>
                  <option value="<?= $s['id_siswa']; ?>"><?= htmlspecialchars($s['nama_siswa']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Pilih Semester</label>
              <select name="id_semester" class="form-select" required>
                <?php foreach ($semester_list as $sem): ?>
                  <option value="<?= $sem['id_semester']; ?>" <?= ($sem['id_semester'] == $id_semester_default) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($sem['nama_semester']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- ===== FORM NILAI (sesuai yang sudah kamu tulis) ===== -->
            <h6 class="fw-bold mt-4 mb-2 text-primary">Nilai Lingkup Materi 1</h6>
            <div class="row">
              <div class="col-md-3 mb-3"><label class="form-label">TP1</label><input type="number" name="tp1_lm1" class="form-control" min="0" max="100"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP2</label><input type="number" name="tp2_lm1" class="form-control" min="0" max="100"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP3</label><input type="number" name="tp3_lm1" class="form-control" min="0" max="100"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP4</label><input type="number" name="tp4_lm1" class="form-control" min="0" max="100"></div>
            </div>

            <h6 class="fw-bold mt-4 mb-2 text-primary">Nilai Lingkup Materi 2</h6>
            <div class="row">
              <div class="col-md-3 mb-3"><label class="form-label">TP1</label><input type="number" name="tp1_lm2" class="form-control" min="0" max="100"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP2</label><input type="number" name="tp2_lm2" class="form-control" min="0" max="100"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP3</label><input type="number" name="tp3_lm2" class="form-control" min="0" max="100"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP4</label><input type="number" name="tp4_lm2" class="form-control" min="0" max="100"></div>
            </div>

            <h6 class="fw-bold mt-4 mb-2 text-primary">Nilai Lingkup Materi 3</h6>
            <div class="row">
              <div class="col-md-3 mb-3"><label class="form-label">TP1</label><input type="number" name="tp1_lm3" class="form-control" min="0" max="100"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP2</label><input type="number" name="tp2_lm3" class="form-control" min="0" max="100"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP3</label><input type="number" name="tp3_lm3" class="form-control" min="0" max="100"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP4</label><input type="number" name="tp4_lm3" class="form-control" min="0" max="100"></div>
            </div>

            <h6 class="fw-bold mt-4 mb-2 text-primary">Nilai Lingkup Materi 4</h6>
            <div class="row">
              <div class="col-md-3 mb-3"><label class="form-label">TP1</label><input type="number" name="tp1_lm4" class="form-control" min="0" max="100"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP2</label><input type="number" name="tp2_lm4" class="form-control" min="0" max="100"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP3</label><input type="number" name="tp3_lm4" class="form-control" min="0" max="100"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP4</label><input type="number" name="tp4_lm4" class="form-control" min="0" max="100"></div>
            </div>

            <h6 class="fw-bold mt-4 mb-2 text-primary">Sumatif Lingkup Materi</h6>
            <div class="row">
              <div class="col-md-3 mb-3"><label class="form-label">LM1</label><input type="number" name="sumatif_lm1" class="form-control" min="0" max="100"></div>
              <div class="col-md-3 mb-3"><label class="form-label">LM2</label><input type="number" name="sumatif_lm2" class="form-control" min="0" max="100"></div>
              <div class="col-md-3 mb-3"><label class="form-label">LM3</label><input type="number" name="sumatif_lm3" class="form-control" min="0" max="100"></div>
              <div class="col-md-3 mb-3"><label class="form-label">LM4</label><input type="number" name="sumatif_lm4" class="form-control" min="0" max="100"></div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Sumatif Tengah Semester (STS)</label>
              <input type="number" name="sumatif_tengah_semester" class="form-control" min="0" max="100">
            </div>

            <div class="d-flex justify-content-between mt-4">
              <button type="submit" class="btn btn-success px-4">
                <i class="fa fa-save"></i> Simpan
              </button>
              <a href="nilai_mapel.php?id=<?= htmlspecialchars($id_mapel) ?>&id_semester=<?= htmlspecialchars($id_semester_default) ?>"
                 class="btn btn-danger px-4">
                <i class="fas fa-times"></i> Batal
              </a>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</main>

<?php include '../../includes/footer.php'; ?>
</body>
