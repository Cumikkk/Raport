<?php
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../koneksi.php';

if (!isset($_GET['id'])) {
  echo "<script>alert('ID siswa tidak ditemukan!'); window.location='data_siswa.php';</script>";
  exit;
}

$id = (int)$_GET['id'];

// Ambil data siswa + catatan wali
$query = mysqli_query($koneksi, "
  SELECT s.*, cr.catatan_wali_kelas
  FROM siswa s
  LEFT JOIN cetak_rapor cr ON s.id_siswa = cr.id_siswa
  WHERE s.id_siswa = '$id'
");
$data = mysqli_fetch_assoc($query);

// Ambil data kelas
$kelas = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_siswa']);
  $nisn     = mysqli_real_escape_string($koneksi, $_POST['no_induk_siswa']);
  $absen    = mysqli_real_escape_string($koneksi, $_POST['no_absen_siswa']);
  $id_kelas = mysqli_real_escape_string($koneksi, $_POST['id_kelas']);
  $catatan  = mysqli_real_escape_string($koneksi, $_POST['catatan_wali_kelas']);

  mysqli_begin_transaction($koneksi);

  try {
    mysqli_query($koneksi, "
      UPDATE siswa SET 
        nama_siswa='$nama',
        no_induk_siswa='$nisn',
        no_absen_siswa='$absen',
        id_kelas='$id_kelas'
      WHERE id_siswa='$id'
    ");

    mysqli_query($koneksi, "
      INSERT INTO cetak_rapor (id_siswa, catatan_wali_kelas)
      VALUES ('$id', '$catatan')
      ON DUPLICATE KEY UPDATE catatan_wali_kelas='$catatan'
    ");

    mysqli_commit($koneksi);
    echo "<script>alert('Data berhasil diperbarui!'); window.location='data_siswa.php';</script>";
  } catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo "<script>alert('Gagal memperbarui data!'); history.back();</script>";
  }
  exit;
}
?>

<div class="dk-page" style="margin-top: 50px;">
  <div class="dk-main">
    <div class="dk-content-box">
      <div class="container py-4">
        <h4 class="fw-bold mb-4">Edit Data Siswa</h4>
        <form method="POST">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Siswa</label>
            <input type="text" name="nama_siswa" class="form-control" value="<?= $data['nama_siswa']; ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">NISN</label>
            <input type="text" name="no_induk_siswa" class="form-control" value="<?= $data['no_induk_siswa']; ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Absen</label>
            <input type="text" name="no_absen_siswa" class="form-control" value="<?= $data['no_absen_siswa']; ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Kelas</label>
            <select name="id_kelas" class="form-control" required>
              <option value="">-- Pilih Kelas --</option>
              <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>
                <option value="<?= $k['id_kelas']; ?>" <?= ($data['id_kelas'] == $k['id_kelas']) ? 'selected' : ''; ?>>
                  <?= $k['nama_kelas']; ?>
                </option>
              <?php } ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Catatan Wali Kelas</label>
            <textarea name="catatan_wali_kelas" class="form-control" rows="3"><?= htmlspecialchars($data['catatan_wali_kelas'] ?? ''); ?></textarea>
          </div>

          <div class="d-flex flex-wrap gap-2 justify-content-between">
            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Update</button>
            <a href="data_siswa.php" class="btn btn-danger"><i class="fas fa-times"></i> Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
