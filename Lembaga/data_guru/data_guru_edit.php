<?php
// --- Proses dulu, jangan output ---
require_once '../../koneksi.php';

$ALLOWED_JABATAN = ['Kepala Sekolah', 'Guru'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  header('Location: data_guru.php?err=' . urlencode('ID tidak valid.'));
  exit;
}

// Ambil data awal untuk form
$sqlFind = "SELECT id_guru, nama_guru, jabatan_guru FROM guru WHERE id_guru = ?";
$stmtFind = $koneksi->prepare($sqlFind);
$stmtFind->bind_param('i', $id);
$stmtFind->execute();
$res = $stmtFind->get_result();
if ($res->num_rows === 0) {
  header('Location: data_guru.php?err=' . urlencode('Data tidak ditemukan.'));
  exit;
}
$data = $res->fetch_assoc();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama_guru    = isset($_POST['nama_guru']) ? trim($_POST['nama_guru']) : '';
  $jabatan_guru = isset($_POST['jabatan_guru']) ? trim($_POST['jabatan_guru']) : '';

  if ($nama_guru === '') {
    $errors[] = 'Nama Guru wajib diisi.';
  }
  if (!in_array($jabatan_guru, $ALLOWED_JABATAN, true)) {
    $errors[] = 'Jabatan tidak valid. Pilih "Kepala Sekolah" atau "Guru".';
  }

  if (empty($errors)) {
    $sqlUpd = "UPDATE guru SET nama_guru = ?, jabatan_guru = ? WHERE id_guru = ?";
    $stmtUpd = $koneksi->prepare($sqlUpd);
    $stmtUpd->bind_param('ssi', $nama_guru, $jabatan_guru, $id);
    if ($stmtUpd->execute()) {
      header('Location: data_guru.php?msg=' . urlencode('Data guru berhasil diperbarui.'));
      exit;
    } else {
      $errors[] = 'Gagal memperbarui data: ' . $koneksi->error;
    }
  }

  // bila gagal, timpa $data untuk sticky form
  $data['nama_guru']    = $nama_guru;
  $data['jabatan_guru'] = $jabatan_guru;
}

// --- Baru output/HTML setelah tidak ada header() lagi ---
include '../../includes/header.php';
?>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <div class="dk-page">
    <div class="dk-main">
      <div class="dk-content-box">
        <div class="container py-4">
          <h4 class="fw-bold mb-4">Edit Data Guru</h4>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                  <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form method="post" novalidate>
            <div class="mb-3">
              <label class="form-label fw-semibold">Nama Guru</label>
              <input type="text" name="nama_guru" class="form-control" placeholder="Nama Guru" required
                value="<?= htmlspecialchars($data['nama_guru']); ?>">
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Jabatan</label>
              <select name="jabatan_guru" class="form-select" required>
                <option value="Kepala Sekolah" <?= ($data['jabatan_guru'] === 'Kepala Sekolah') ? 'selected' : '' ?>>Kepala Sekolah</option>
                <option value="Guru" <?= ($data['jabatan_guru'] === 'Guru') ? 'selected' : '' ?>>Guru</option>
              </select>
            </div>

            <div class="d-flex flex-wrap gap-2 justify-content-between">
              <button type="submit" class="btn btn-success">
                <i class="fa fa-save"></i> Simpan
              </button>
              <a href="data_guru.php" class="btn btn-danger">
                <i class="fas fa-times"></i> Batal
              </a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>

  <?php include '../../includes/footer.php'; ?>