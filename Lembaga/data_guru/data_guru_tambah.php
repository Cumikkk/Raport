<?php
// --- Proses dulu, jangan output apa pun dulu ---
require_once '../../koneksi.php';

$ALLOWED_JABATAN = ['Kepala Sekolah', 'Guru'];
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
    $sql = "INSERT INTO guru (nama_guru, jabatan_guru) VALUES (?, ?)";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('ss', $nama_guru, $jabatan_guru);
    if ($stmt->execute()) {
      // Redirect SEBELUM ada output
      header('Location: data_guru.php?msg=' . urlencode('Data guru berhasil ditambahkan.'));
      exit;
    } else {
      $errors[] = 'Gagal menyimpan data: ' . $koneksi->error;
    }
  }
}

// --- Setelah semua kemungkinan redirect, baru boleh output/HTML ---
include '../../includes/header.php';
?>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <div class="dk-page" style="margin-top: 50px;">
    <div class="dk-main">
      <div class="dk-content-box">
        <div class="container py-4">
          <h4 class="fw-bold mb-4">Tambah Guru</h4>

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
                value="<?= isset($nama_guru) ? htmlspecialchars($nama_guru) : '' ?>">
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Jabatan</label>
              <select name="jabatan_guru" class="form-select" required>
                <option value="" disabled <?= !isset($jabatan_guru) || $jabatan_guru === '' ? 'selected' : '' ?>>Pilih Jabatan</option>
                <option value="Kepala Sekolah" <?= (isset($jabatan_guru) && $jabatan_guru === 'Kepala Sekolah') ? 'selected' : '' ?>>Kepala Sekolah</option>
                <option value="Guru" <?= (isset($jabatan_guru) && $jabatan_guru === 'Guru') ? 'selected' : '' ?>>Guru</option>
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