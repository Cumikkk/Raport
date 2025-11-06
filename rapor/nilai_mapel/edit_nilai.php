<?php
// ===== BACKEND: Edit Nilai (tanpa ubah desain tampilan) =====
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
require_once __DIR__ . '/../../koneksi.php';
mysqli_set_charset($koneksi, 'utf8mb4');

// helper escape aman & tanpa warning null
function esc($v) {
  return $v === null ? '' : htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Ambil parameter kunci
$id_mapel    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_semester = (isset($_GET['id_semester']) && is_numeric($_GET['id_semester'])) ? (int)$_GET['id_semester'] : 0;
$id_siswa    = (isset($_GET['id_siswa']) && is_numeric($_GET['id_siswa'])) ? (int)$_GET['id_siswa'] : 0;

if ($id_mapel <= 0 || $id_semester <= 0 || $id_siswa <= 0) {
  echo "<script>alert('Parameter tidak lengkap.');location.href='mapel.php';</script>";
  exit;
}

// Nama Mapel
$nama_mapel = 'Tidak Diketahui';
$stmt = $koneksi->prepare("SELECT nama_mata_pelajaran FROM mata_pelajaran WHERE id_mata_pelajaran=? LIMIT 1");
$stmt->bind_param('i', $id_mapel);
$stmt->execute();
$mm = $stmt->get_result()->fetch_assoc();
if ($mm) $nama_mapel = $mm['nama_mata_pelajaran'];
$stmt->close();

// Nama Siswa
$nama_siswa = 'Tidak Diketahui';
$stmt = $koneksi->prepare("SELECT nama_siswa FROM siswa WHERE id_siswa=? LIMIT 1");
$stmt->bind_param('i', $id_siswa);
$stmt->execute();
$ss = $stmt->get_result()->fetch_assoc();
if ($ss) $nama_siswa = $ss['nama_siswa'];
$stmt->close();

// Ambil record nilai yang akan diedit
$sqlGet = "
  SELECT *
  FROM nilai_mata_pelajaran
  WHERE id_semester=? AND id_mata_pelajaran=? AND id_siswa=? LIMIT 1
";
$stmt = $koneksi->prepare($sqlGet);
$stmt->bind_param('iii', $id_semester, $id_mapel, $id_siswa);
$stmt->execute();
$nilai = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$nilai) {
  echo "<script>alert('Data nilai belum ada untuk siswa ini. Silakan tambahkan terlebih dahulu.');location.href='tambah_nilai.php?id="
       . urlencode($id_mapel) . "&id_semester=" . urlencode($id_semester) . "';</script>";
  exit;
}

// Helper: ambil angka 0-100 atau NULL
function numOrNull($key) {
  if (!isset($_POST[$key]) || $_POST[$key] === '') return null;
  return (int)$_POST[$key];
}

// Submit update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Ambil semua field nilai
  $tp1_lm1 = numOrNull('tp1_lm1');  $tp2_lm1 = numOrNull('tp2_lm1');  $tp3_lm1 = numOrNull('tp3_lm1');  $tp4_lm1 = numOrNull('tp4_lm1');  $sumatif_lm1 = numOrNull('sumatif_lm1');
  $tp1_lm2 = numOrNull('tp1_lm2');  $tp2_lm2 = numOrNull('tp2_lm2');  $tp3_lm2 = numOrNull('tp3_lm2');  $tp4_lm2 = numOrNull('tp4_lm2');  $sumatif_lm2 = numOrNull('sumatif_lm2');
  $tp1_lm3 = numOrNull('tp1_lm3');  $tp2_lm3 = numOrNull('tp2_lm3');  $tp3_lm3 = numOrNull('tp3_lm3');  $tp4_lm3 = numOrNull('tp4_lm3');  $sumatif_lm3 = numOrNull('sumatif_lm3');
  $tp1_lm4 = numOrNull('tp1_lm4');  $tp2_lm4 = numOrNull('tp2_lm4');  $tp3_lm4 = numOrNull('tp3_lm4');  $tp4_lm4 = numOrNull('tp4_lm4');  $sumatif_lm4 = numOrNull('sumatif_lm4');
  $sumatif_tengah_semester = numOrNull('sumatif_tengah_semester');

  try {
    $sqlUpd = "
      UPDATE nilai_mata_pelajaran SET
        tp1_lm1=?, tp2_lm1=?, tp3_lm1=?, tp4_lm1=?, sumatif_lm1=?,
        tp1_lm2=?, tp2_lm2=?, tp3_lm2=?, tp4_lm2=?, sumatif_lm2=?,
        tp1_lm3=?, tp2_lm3=?, tp3_lm3=?, tp4_lm3=?, sumatif_lm3=?,
        tp1_lm4=?, tp2_lm4=?, tp3_lm4=?, tp4_lm4=?, sumatif_lm4=?,
        sumatif_tengah_semester=?
      WHERE id_semester=? AND id_mata_pelajaran=? AND id_siswa=? LIMIT 1
    ";
    $stmt = $koneksi->prepare($sqlUpd);

    // 24 argumen total (20 TP+Sumatif LM, 1 STS, 3 kunci)
    $types = str_repeat('i', 24);
    $stmt->bind_param(
      $types,
      $tp1_lm1, $tp2_lm1, $tp3_lm1, $tp4_lm1, $sumatif_lm1,
      $tp1_lm2, $tp2_lm2, $tp3_lm2, $tp4_lm2, $sumatif_lm2,
      $tp1_lm3, $tp2_lm3, $tp3_lm3, $tp4_lm3, $sumatif_lm3,
      $tp1_lm4, $tp2_lm4, $tp3_lm4, $tp4_lm4, $sumatif_lm4,
      $sumatif_tengah_semester,
      $id_semester, $id_mapel, $id_siswa
    );
    $stmt->execute();
    $stmt->close();

    header("Location: nilai_mapel.php?id=" . urlencode($id_mapel) . "&id_semester=" . urlencode($id_semester));
    exit;
  } catch (Throwable $e) {
    echo "<script>alert('Gagal menyimpan: " . addslashes($e->getMessage()) . "');history.back();</script>";
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
          <h5 class="mb-1 fw-semibold fs-4 text-center">
            Edit Nilai Mata Pelajaran - <?= esc($nama_mapel); ?>
          </h5>
          <div class="ms-auto small text-muted">
            Siswa: <strong><?= esc($nama_siswa); ?></strong> &middot;
            Semester: <strong><?= (int)$id_semester; ?></strong>
          </div>
        </div>

        <div class="card-body">
          <form method="post" action="">
            <!-- info kunci (read-only) -->
            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="form-label fw-semibold">Siswa</label>
                <input class="form-control" value="<?= esc($nama_siswa); ?>" disabled>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Mapel</label>
                <input class="form-control" value="<?= esc($nama_mapel); ?>" disabled>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Semester</label>
                <input class="form-control" value="<?= (int)$id_semester; ?>" disabled>
              </div>
            </div>

            <h6 class="fw-bold mt-1 mb-2 text-primary">Lingkup Materi 1</h6>
            <div class="row">
              <div class="col-md-3 mb-3"><label class="form-label">TP1</label>
                <input type="number" name="tp1_lm1" class="form-control" min="0" max="100" value="<?= esc($nilai['tp1_lm1'] ?? null); ?>"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP2</label>
                <input type="number" name="tp2_lm1" class="form-control" min="0" max="100" value="<?= esc($nilai['tp2_lm1'] ?? null); ?>"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP3</label>
                <input type="number" name="tp3_lm1" class="form-control" min="0" max="100" value="<?= esc($nilai['tp3_lm1'] ?? null); ?>"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP4</label>
                <input type="number" name="tp4_lm1" class="form-control" min="0" max="100" value="<?= esc($nilai['tp4_lm1'] ?? null); ?>"></div>
            </div>

            <h6 class="fw-bold mt-4 mb-2 text-primary">Lingkup Materi 2</h6>
            <div class="row">
              <div class="col-md-3 mb-3"><label class="form-label">TP1</label>
                <input type="number" name="tp1_lm2" class="form-control" min="0" max="100" value="<?= esc($nilai['tp1_lm2'] ?? null); ?>"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP2</label>
                <input type="number" name="tp2_lm2" class="form-control" min="0" max="100" value="<?= esc($nilai['tp2_lm2'] ?? null); ?>"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP3</label>
                <input type="number" name="tp3_lm2" class="form-control" min="0" max="100" value="<?= esc($nilai['tp3_lm2'] ?? null); ?>"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP4</label>
                <input type="number" name="tp4_lm2" class="form-control" min="0" max="100" value="<?= esc($nilai['tp4_lm2'] ?? null); ?>"></div>
            </div>

            <h6 class="fw-bold mt-4 mb-2 text-primary">Lingkup Materi 3</h6>
            <div class="row">
              <div class="col-md-3 mb-3"><label class="form-label">TP1</label>
                <input type="number" name="tp1_lm3" class="form-control" min="0" max="100" value="<?= esc($nilai['tp1_lm3'] ?? null); ?>"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP2</label>
                <input type="number" name="tp2_lm3" class="form-control" min="0" max="100" value="<?= esc($nilai['tp2_lm3'] ?? null); ?>"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP3</label>
                <input type="number" name="tp3_lm3" class="form-control" min="0" max="100" value="<?= esc($nilai['tp3_lm3'] ?? null); ?>"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP4</label>
                <input type="number" name="tp4_lm3" class="form-control" min="0" max="100" value="<?= esc($nilai['tp4_lm3'] ?? null); ?>"></div>
            </div>

            <h6 class="fw-bold mt-4 mb-2 text-primary">Lingkup Materi 4</h6>
            <div class="row">
              <div class="col-md-3 mb-3"><label class="form-label">TP1</label>
                <input type="number" name="tp1_lm4" class="form-control" min="0" max="100" value="<?= esc($nilai['tp1_lm4'] ?? null); ?>"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP2</label>
                <input type="number" name="tp2_lm4" class="form-control" min="0" max="100" value="<?= esc($nilai['tp2_lm4'] ?? null); ?>"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP3</label>
                <input type="number" name="tp3_lm4" class="form-control" min="0" max="100" value="<?= esc($nilai['tp3_lm4'] ?? null); ?>"></div>
              <div class="col-md-3 mb-3"><label class="form-label">TP4</label>
                <input type="number" name="tp4_lm4" class="form-control" min="0" max="100" value="<?= esc($nilai['tp4_lm4'] ?? null); ?>"></div>
            </div>

            <h6 class="fw-bold mt-4 mb-2 text-primary">Sumatif Lingkup Materi</h6>
            <div class="row">
              <div class="col-md-3 mb-3"><label class="form-label">LM1</label>
                <input type="number" name="sumatif_lm1" class="form-control" min="0" max="100" value="<?= esc($nilai['sumatif_lm1'] ?? null); ?>"></div>
              <div class="col-md-3 mb-3"><label class="form-label">LM2</label>
                <input type="number" name="sumatif_lm2" class="form-control" min="0" max="100" value="<?= esc($nilai['sumatif_lm2'] ?? null); ?>"></div>
              <div class="col-md-3 mb-3"><label class="form-label">LM3</label>
                <input type="number" name="sumatif_lm3" class="form-control" min="0" max="100" value="<?= esc($nilai['sumatif_lm3'] ?? null); ?>"></div>
              <div class="col-md-3 mb-3"><label class="form-label">LM4</label>
                <input type="number" name="sumatif_lm4" class="form-control" min="0" max="100" value="<?= esc($nilai['sumatif_lm4'] ?? null); ?>"></div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Sumatif Tengah Semester (STS)</label>
              <input type="number" name="sumatif_tengah_semester" class="form-control" min="0" max="100" value="<?= esc($nilai['sumatif_tengah_semester'] ?? null); ?>">
            </div>

            <div class="d-flex justify-content-between mt-4">
              <button type="submit" class="btn btn-success px-4">
                <i class="fa fa-save"></i> Simpan Perubahan
              </button>
              <a href="nilai_mapel.php?id=<?= htmlspecialchars($id_mapel) ?>&id_semester=<?= htmlspecialchars($id_semester) ?>" class="btn btn-danger px-4">
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
