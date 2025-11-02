<?php
ob_start();
require_once '../../koneksi.php';

// ===== UPDATE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_kelas   = (int)($_POST['id_kelas'] ?? 0);
  $nama_kelas = trim($_POST['nama_kelas'] ?? '');
  $tingkat    = trim($_POST['tingkat_kelas'] ?? '');
  $id_guru    = ($_POST['id_guru'] ?? '') === '' ? null : (int)$_POST['id_guru'];

  $allowed = ['X', 'XI', 'XII'];
  if ($id_kelas <= 0 || $nama_kelas === '' || !in_array($tingkat, $allowed, true)) {
    header('Location: edit_data.php?id=' . $id_kelas . '&err=valid');
    exit;
  }

  if ($id_guru === null) {
    $stmt = mysqli_prepare($koneksi, "UPDATE kelas SET id_guru = NULL, tingkat_kelas = ?, nama_kelas = ? WHERE id_kelas = ?");
    mysqli_stmt_bind_param($stmt, 'ssi', $tingkat, $nama_kelas, $id_kelas);
  } else {
    $stmt = mysqli_prepare($koneksi, "UPDATE kelas SET id_guru = ?, tingkat_kelas = ?, nama_kelas = ? WHERE id_kelas = ?");
    mysqli_stmt_bind_param($stmt, 'issi', $id_guru, $tingkat, $nama_kelas, $id_kelas);
  }
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  header('Location: datakelas.php?msg=updated');
  exit;
}

// ===== LOAD FORM DATA =====
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  header('Location: datakelas.php');
  exit;
}

$stmt = mysqli_prepare($koneksi, "SELECT id_kelas, id_guru, tingkat_kelas, nama_kelas FROM kelas WHERE id_kelas = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res  = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$data) {
  header('Location: datakelas.php');
  exit;
}

$guru = mysqli_query($koneksi, "SELECT id_guru, nama_guru FROM guru ORDER BY nama_guru ASC");

include '../../includes/header.php';
include '../../includes/navbar.php';
$err = $_GET['err'] ?? '';
?>

<body>

  <div class="dk-page" style="margin-top: 50px;">
    <div class="dk-main">
      <div class="dk-content-box">
        <div class="container py-4">
          <h4 class="fw-bold mb-3">Edit Data Kelas</h4>

          <?php if ($err === 'valid'): ?>
            <div class="alert alert-danger">Isi Nama Kelas dan pilih Tingkat dengan benar.</div>
          <?php endif; ?>

          <form method="POST">
            <input type="hidden" name="id_kelas" value="<?= (int)$data['id_kelas'] ?>">

            <div class="mb-3">
              <label class="form-label fw-semibold">Nama Kelas</label>
              <input type="text" name="nama_kelas" class="form-control" placeholder="mis. XII IPA 1"
                value="<?= htmlspecialchars($data['nama_kelas']) ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Tingkat</label>
              <select name="tingkat_kelas" class="form-select" required>
                <option value="X" <?= $data['tingkat_kelas'] === 'X'  ? 'selected' : ''; ?>>X</option>
                <option value="XI" <?= $data['tingkat_kelas'] === 'XI' ? 'selected' : ''; ?>>XI</option>
                <option value="XII" <?= $data['tingkat_kelas'] === 'XII' ? 'selected' : ''; ?>>XII</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Wali Kelas (opsional)</label>
              <select name="id_guru" class="form-select">
                <option value="">— Tidak Ada —</option>
                <?php while ($g = mysqli_fetch_assoc($guru)): ?>
                  <option value="<?= (int)$g['id_guru'] ?>" <?= ($data['id_guru'] == $g['id_guru']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($g['nama_guru']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="d-flex flex-wrap gap-2 justify-content-between">
              <button type="submit" class="btn btn-success">
                <i class="fa fa-save"></i> Simpan
              </button>
              <a href="datakelas.php" class="btn btn-danger">
                <i class="fas fa-times"></i> Batal
              </a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>

  <?php include '../../includes/footer.php'; ?>