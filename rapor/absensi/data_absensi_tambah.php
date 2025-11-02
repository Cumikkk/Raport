<?php
// ==== BACKEND: de-duplicate siswa & simpan absensi (tampilan tetap) ====
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
require_once __DIR__ . '/../../koneksi.php';

function norm_name($s) {
  // normalisasi sederhana: trim + collapse spasi + lower
  $s = trim($s);
  $s = preg_replace('/\s+/', ' ', $s);
  return mb_strtolower($s, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama_raw  = $_POST['nama_siswa'] ?? '';
  $no_absen  = trim($_POST['no_absen'] ?? '');
  $ket       = trim($_POST['keterangan'] ?? '');

  $nama_siswa = trim($nama_raw);
  if ($nama_siswa === '') {
    header('Location: data_absensi_tambah.php?err=nama_required');
    exit;
  }

  // ====== Cari siswa existing (hindari duplikasi nama) ======
  $id_siswa = null;

  // Layer 1: match exact (case-insensitive) nama + no_absen
  if ($no_absen !== '') {
    $stmt = $koneksi->prepare("
      SELECT id_siswa
      FROM siswa
      WHERE LOWER(TRIM(REPLACE(nama_siswa, '  ', ' '))) = ?
        AND no_absen_siswa = ?
      LIMIT 1
    ");
    $nama_key = norm_name($nama_siswa);
    $stmt->bind_param('ss', $nama_key, $no_absen);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) $id_siswa = (int)$row['id_siswa'];
  }

  // Layer 2: match no_absen saja
  if (!$id_siswa && $no_absen !== '') {
    $stmt = $koneksi->prepare("SELECT id_siswa FROM siswa WHERE no_absen_siswa = ? LIMIT 1");
    $stmt->bind_param('s', $no_absen);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) $id_siswa = (int)$row['id_siswa'];
  }

  // Layer 3: match nama saja (case-insensitive)
  if (!$id_siswa) {
    $stmt = $koneksi->prepare("
      SELECT id_siswa
      FROM siswa
      WHERE LOWER(TRIM(REPLACE(nama_siswa, '  ', ' '))) = ?
      LIMIT 1
    ");
    $nama_key = norm_name($nama_siswa);
    $stmt->bind_param('s', $nama_key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) $id_siswa = (int)$row['id_siswa'];
  }

  // Kalau tetap belum ketemu → buat siswa baru (minimal)
  if (!$id_siswa) {
    $id_kelas = null;  // boleh null
    $nis      = null;  // boleh null (no_induk_siswa)
    $stmt = $koneksi->prepare("
      INSERT INTO siswa (id_kelas, no_induk_siswa, no_absen_siswa, nama_siswa)
      VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('isss', $id_kelas, $nis, $no_absen, $nama_siswa);
    $stmt->execute();
    $id_siswa = $stmt->insert_id;
    $stmt->close();
  } else {
    // Opsional: sinkronkan no_absen jika user mengisi dan berbeda
    if ($no_absen !== '') {
      $stmt = $koneksi->prepare("UPDATE siswa SET no_absen_siswa = ? WHERE id_siswa = ?");
      $stmt->bind_param('si', $no_absen, $id_siswa);
      $stmt->execute();
      $stmt->close();
    }
  }

  // Mapping keterangan → sakit/izin/alpha
  $sakit = $ket === 'sakit' ? 1 : 0;
  $izin  = $ket === 'izin'  ? 1 : 0;
  $alpha = $ket === 'alpha' ? 1 : 0;

  // Ambil id_semester aktif/pertama jika ada
  $id_semester = null;
  $res = $koneksi->query("SELECT id_semester FROM semester ORDER BY id_semester ASC LIMIT 1");
  if ($res && $res->num_rows) {
    $id_semester = (int)$res->fetch_assoc()['id_semester'];
  }

  if ($id_semester !== null) {
    $stmt = $koneksi->prepare("
      INSERT INTO absensi (id_semester, id_siswa, sakit, izin, alpha)
      VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('iiiii', $id_semester, $id_siswa, $sakit, $izin, $alpha);
    $stmt->execute();
    $stmt->close();
  } else {
    // Tidak ada semester → pakai 0 (FK off sementara)
    $koneksi->query("SET FOREIGN_KEY_CHECKS=0");
    $dummy = 0;
    $stmt = $koneksi->prepare("
      INSERT INTO absensi (id_semester, id_siswa, sakit, izin, alpha)
      VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('iiiii', $dummy, $id_siswa, $sakit, $izin, $alpha);
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

        <?php if (isset($_GET['err']) && $_GET['err']==='nama_required'): ?>
          <div class="alert alert-danger">Nama siswa wajib diisi.</div>
        <?php endif; ?>

        <!-- TAMPILAN TETAP (tidak diubah) -->
        <form method="post" action="">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Siswa</label>
            <input type="text" name="nama_siswa" class="form-control" placeholder="Nama Siswa" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">No Absen</label>
            <input type="text" name="no_absen" class="form-control" placeholder="No Absen">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Keterangan</label>
            <select name="keterangan" class="form-select" required>
              <option value="" selected disabled>-- Pilih --</option>
              <option value="sakit">Sakit</option>
              <option value="izin">Izin</option>
              <option value="alpha">Alpha</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Wali Kelas</label>
            <input type="text" name="wali_kelas" class="form-control" placeholder="Wali Kelas">
          </div>

          <div class="d-flex flex-wrap gap-2 justify-content-between">
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
