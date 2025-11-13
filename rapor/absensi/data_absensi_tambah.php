<?php
// ==== BACKEND: dropdown id_siswa + input angka sakit/izin/alpha ====
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
require_once __DIR__ . '/../../koneksi.php';

// --- Self-healing kolom snapshot di absensi (aman di-run berkali-kali) ---
$koneksi->query("
  ALTER TABLE absensi
    ADD COLUMN IF NOT EXISTS nama_siswa_text VARCHAR(100) NOT NULL DEFAULT '-' AFTER id_absensi,
    ADD COLUMN IF NOT EXISTS nis_text        VARCHAR(50)  NOT NULL DEFAULT '-' AFTER nama_siswa_text,
    ADD COLUMN IF NOT EXISTS wali_kelas_text VARCHAR(100) NOT NULL DEFAULT '-' AFTER nis_text
");

// Ambil list siswa untuk dropdown
$siswa_list = [];
$q = $koneksi->query("
  SELECT s.id_siswa, s.nama_siswa, s.no_induk_siswa, s.no_absen_siswa,
         k.nama_kelas, g.nama_guru
  FROM siswa s
  LEFT JOIN kelas k ON k.id_kelas = s.id_kelas
  LEFT JOIN guru  g ON g.id_guru  = k.id_guru
  ORDER BY s.nama_siswa ASC
");
while ($r = $q->fetch_assoc()) {
  $siswa_list[] = $r;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Ambil input & validasi
  $id_siswa = isset($_POST['id_siswa']) ? (int)$_POST['id_siswa'] : 0;
  $sakit    = isset($_POST['sakit']) ? (int)$_POST['sakit'] : 0;
  $izin     = isset($_POST['izin']) ? (int)$_POST['izin'] : 0;
  $alpha    = isset($_POST['alpha']) ? (int)$_POST['alpha'] : 0;

  if ($id_siswa <= 0) {
    header('Location: data_absensi_tambah.php?err=siswa_required');
    exit;
  }
  // Pastikan tidak negatif
  if ($sakit < 0 || $izin < 0 || $alpha < 0) {
    header('Location: data_absensi_tambah.php?err=angka_negatif');
    exit;
  }

  // Ambil snapshot dari relasi siswa → kelas → guru
  $stmt = $koneksi->prepare("
    SELECT s.nama_siswa, s.no_induk_siswa, g.nama_guru
    FROM siswa s
    LEFT JOIN kelas k ON k.id_kelas = s.id_kelas
    LEFT JOIN guru  g ON g.id_guru  = k.id_guru
    WHERE s.id_siswa = ?
    LIMIT 1
  ");
  $stmt->bind_param('i', $id_siswa);
  $stmt->execute();
  $snap = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$snap) {
    header('Location: data_absensi_tambah.php?err=siswa_tidak_ditemukan');
    exit;
  }

  $nama_snap = $snap['nama_siswa']     !== null && $snap['nama_siswa']     !== '' ? $snap['nama_siswa']     : '-';
  $nis_snap  = $snap['no_induk_siswa'] !== null && $snap['no_induk_siswa'] !== '' ? $snap['no_induk_siswa'] : '-';
  $wali_snap = $snap['nama_guru']      !== null && $snap['nama_guru']      !== '' ? $snap['nama_guru']      : '-';

  // Ambil id_semester (pakai yang pertama/aktif)
  $id_semester = null;
  $res = $koneksi->query("SELECT id_semester FROM semester ORDER BY id_semester ASC LIMIT 1");
  if ($res && $res->num_rows) {
    $id_semester = (int)$res->fetch_assoc()['id_semester'];
  }

  // Insert ke absensi
  if ($id_semester !== null) {
    $stmt = $koneksi->prepare("
      INSERT INTO absensi (id_semester, id_siswa, nama_siswa_text, nis_text, wali_kelas_text, sakit, izin, alpha)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('iisssiii', $id_semester, $id_siswa, $nama_snap, $nis_snap, $wali_snap, $sakit, $izin, $alpha);
    $stmt->execute();
    $stmt->close();
  } else {
    // Tidak ada semester → isi 0 sementara (jika FK aktif, nonaktifkan sementara)
    $koneksi->query("SET FOREIGN_KEY_CHECKS=0");
    $dummy = 0;
    $stmt = $koneksi->prepare("
      INSERT INTO absensi (id_semester, id_siswa, nama_siswa_text, nis_text, wali_kelas_text, sakit, izin, alpha)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('iisssiii', $dummy, $id_siswa, $nama_snap, $nis_snap, $wali_snap, $sakit, $izin, $alpha);
    $stmt->execute();
    $stmt->close();
    $koneksi->query("SET FOREIGN_KEY_CHECKS=1");
  }

  header('Location: data_absensi.php?msg=add_success');
  exit;
}
?>

<?php include '../../includes/header.php'; ?>
<body>
<?php include '../../includes/navbar.php'; ?>

<div class="dk-page" style="margin-top: 50px;">
  <div class="dk-main">
    <div class="dk-content-box">
      <div class="container py-4">
        <h4 class="fw-bold mb-4">Tambah Data Absensi</h4>

        <?php if (isset($_GET['err'])): ?>
          <?php if ($_GET['err'] === 'siswa_required'): ?>
            <div class="alert alert-danger">Silakan pilih <b>Nama Siswa</b> dari dropdown.</div>
          <?php elseif ($_GET['err'] === 'angka_negatif'): ?>
            <div class="alert alert-danger">Input <b>Sakit/Izin/Alpha</b> tidak boleh bernilai negatif.</div>
          <?php elseif ($_GET['err'] === 'siswa_tidak_ditemukan'): ?>
            <div class="alert alert-danger">Data siswa tidak ditemukan.</div>
          <?php endif; ?>
        <?php endif; ?>

        <!-- FORM: Nama Siswa dari dropdown + input angka sakit/izin/alpha -->
        <form method="post" action="">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Siswa</label>
            <select name="id_siswa" class="form-select" required>
              <option value="">-- Pilih dari daftar siswa --</option>
              <?php foreach ($siswa_list as $s): 
                $opt = $s['nama_siswa'];
                $opt .= $s['no_induk_siswa'] ? " (NIS: {$s['no_induk_siswa']})" : "";
                if (!empty($s['nama_kelas'])) $opt .= " - {$s['nama_kelas']}";
                if (!empty($s['no_absen_siswa'])) $opt .= " [Absen: {$s['no_absen_siswa']}]";
              ?>
                <option value="<?= (int)$s['id_siswa'] ?>"><?= htmlspecialchars($opt, ENT_QUOTES) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="row g-3">
            <div class="col-sm-4">
              <label class="form-label fw-semibold">Sakit</label>
              <input type="number" name="sakit" class="form-control" min="0" step="1" value="0" required>
            </div>
            <div class="col-sm-4">
              <label class="form-label fw-semibold">Izin</label>
              <input type="number" name="izin" class="form-control" min="0" step="1" value="0" required>
            </div>
            <div class="col-sm-4">
              <label class="form-label fw-semibold">Alpha</label>
              <input type="number" name="alpha" class="form-control" min="0" step="1" value="0" required>
            </div>
          </div>

          <div class="d-flex flex-wrap gap-2 justify-content-between mt-4">
            <button type="submit" class="btn btn-success">
              <i class="fa fa-save"></i> Simpan
            </button>
            <a href="data_absensi.php" class="btn btn-danger">
              <i class="fas fa-times"></i> Batal
            </a>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
