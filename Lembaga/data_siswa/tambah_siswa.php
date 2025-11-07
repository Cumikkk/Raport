<?php
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../koneksi.php';

// Ambil daftar kelas untuk dropdown
$kelasQuery = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");

if ($_SERVER['REQUEST_METHOD'] == "POST") {

  $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_siswa']);
  $nisn     = mysqli_real_escape_string($koneksi, $_POST['no_induk_siswa']);
  $absen    = mysqli_real_escape_string($koneksi, $_POST['no_absen_siswa']);
  $id_kelas = mysqli_real_escape_string($koneksi, $_POST['id_kelas']);
  $catatan  = mysqli_real_escape_string($koneksi, $_POST['catatan_wali_kelas']);

  mysqli_begin_transaction($koneksi);

  try {
    mysqli_query($koneksi, "
      INSERT INTO siswa (nama_siswa, no_induk_siswa, no_absen_siswa, id_kelas)
      VALUES ('$nama', '$nisn', '$absen', '$id_kelas')
    ");
    $id_siswa = mysqli_insert_id($koneksi);

    mysqli_query($koneksi, "
      INSERT INTO cetak_rapor (id_siswa, catatan_wali_kelas)
      VALUES ('$id_siswa', '$catatan')
    ");

    mysqli_commit($koneksi);
    echo "<script>alert('Data berhasil ditambahkan');window.location='data_siswa.php';</script>";
  } catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo "<script>alert('Gagal menambahkan data!');history.back();</script>";
  }
}
?>

<div class="dk-page" style="margin-top: 50px;">
  <div class="dk-main">
    <div class="dk-content-box">
      <div class="container py-4">
        <h4 class="fw-bold mb-4">Tambah Data Siswa</h4>

        <form method="POST">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Siswa</label>
            <input type="text" name="nama_siswa" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">NISN</label>
            <input type="text" name="no_induk_siswa" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Absen</label>
            <input type="text" name="no_absen_siswa" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Kelas</label>
            <select name="id_kelas" class="form-control" required>
              <option value="" selected disabled>-- Pilih Kelas --</option>
              <?php while ($k = mysqli_fetch_assoc($kelasQuery)): ?>
                <option value="<?= $k['id_kelas']; ?>"><?= $k['nama_kelas']; ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Catatan Wali Kelas</label>
            <textarea name="catatan_wali_kelas" class="form-control" rows="3"></textarea>
          </div>
          <div class="d-flex flex-wrap gap-2 justify-content-between">
            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
            <a href="data_siswa.php" class="btn btn-danger"><i class="fas fa-times"></i> Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
