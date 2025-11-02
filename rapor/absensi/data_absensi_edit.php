<?php
// ===== Logic backend (tanpa ubah tampilan) =====
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
require_once __DIR__ . '/../../koneksi.php';

// id absensi yang diedit
$id_absensi = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_absensi <= 0) { header('Location: data_absensi.php'); exit; }

// Ambil data absensi + siswa terkait (untuk prefilling dan id_siswa)
$stmt = $koneksi->prepare("
  SELECT
    a.id_absensi,
    a.id_siswa,
    a.sakit, a.izin, a.alpha,
    s.no_absen_siswa,
    s.nama_siswa,
    COALESCE(g.nama_guru, '') AS wali_kelas
  FROM absensi a
  LEFT JOIN siswa s ON s.id_siswa = a.id_siswa
  LEFT JOIN kelas k ON k.id_kelas = s.id_kelas
  LEFT JOIN guru  g ON g.id_guru  = k.id_guru
  WHERE a.id_absensi = ?
  LIMIT 1
");
$stmt->bind_param('i', $id_absensi);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) { header('Location: data_absensi.php'); exit; }

// Tentukan keterangan aktif saat ini
$currentKet = '';
if ((int)$data['sakit'] > 0)      $currentKet = 'sakit';
elseif ((int)$data['izin'] > 0)   $currentKet = 'izin';
elseif ((int)$data['alpha'] > 0)  $currentKet = 'alpha';

// Proses update saat submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama_siswa = trim($_POST['nama_siswa'] ?? '');
  $no_absen   = trim($_POST['no_absen'] ?? '');
  $ket        = trim($_POST['keterangan'] ?? '');

  if ($nama_siswa === '' || $no_absen === '') {
    // wajib diisi, balik saja
    header('Location: data_absensi_edit.php?id='.$id_absensi);
    exit;
  }

  // 1) Update data siswa yang terkait (mengizinkan edit nama & absen langsung)
  $stmtS = $koneksi->prepare("
    UPDATE siswa
    SET nama_siswa = ?, no_absen_siswa = ?
    WHERE id_siswa = ?
  ");
  $stmtS->bind_param('ssi', $nama_siswa, $no_absen, $data['id_siswa']);
  $stmtS->execute();
  $stmtS->close();

  // 2) Mapping keterangan ke kolom sakit/izin/alpha
  $sakit = $ket === 'sakit' ? 1 : 0;
  $izin  = $ket === 'izin'  ? 1 : 0;
  $alpha = $ket === 'alpha' ? 1 : 0;

  // 3) Update absensi (id_siswa tetap sama; id_semester tidak disentuh)
  $stmtA = $koneksi->prepare("
    UPDATE absensi
    SET sakit = ?, izin = ?, alpha = ?
    WHERE id_absensi = ?
  ");
  $stmtA->bind_param('iiii', $sakit, $izin, $alpha, $id_absensi);
  $stmtA->execute();
  $stmtA->close();

  header('Location: data_absensi.php?msg=edit_success');
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
        <h4 class="fw-bold mb-4">Edit Data Absensi</h4>

        <!-- TAMPILAN TETAP, hanya ditambah name/value agar tersimpan -->
        <form method="post" action="">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Siswa</label>
            <input type="text" name="nama_siswa" class="form-control"
                   placeholder="Nama Guru"
                   value="<?= htmlspecialchars($data['nama_siswa'] ?? '', ENT_QUOTES) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">No Absen</label>
            <input type="text" name="no_absen" class="form-control"
                   placeholder="No Absen"
                   value="<?= htmlspecialchars($data['no_absen_siswa'] ?? '', ENT_QUOTES) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Keterangan</label>
            <select name="keterangan" class="form-select" required>
              <option value="" disabled <?= $currentKet===''?'selected':''; ?>>Pilih</option>
              <option value="sakit" <?= $currentKet==='sakit'?'selected':''; ?>>Sakit</option>
              <option value="izin"  <?= $currentKet==='izin'?'selected':''; ?>>Izin</option>
              <option value="alpha" <?= $currentKet==='alpha'?'selected':''; ?>>Alpha</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Wali Kelas</label>
            <input type="text" class="form-control" placeholder="Wali Kelas"
                   value="<?= htmlspecialchars($data['wali_kelas'] ?? '', ENT_QUOTES) ?>" readonly>
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
