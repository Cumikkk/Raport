<?php
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../koneksi.php';

if (!isset($_GET['id'])) {
  echo "<script>alert('ID siswa tidak ditemukan!'); window.location='data_siswa.php';</script>";
  exit;
}

$id = $_GET['id'];

// Ambil data siswa
$siswa = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id_siswa = '$id'");
$data = mysqli_fetch_assoc($siswa);

// Ambil data kelas
$kelas = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_siswa']);
  $nisn     = mysqli_real_escape_string($koneksi, $_POST['no_induk_siswa']);
  $absen    = mysqli_real_escape_string($koneksi, $_POST['no_absen_siswa']);
  $id_kelas = mysqli_real_escape_string($koneksi, $_POST['id_kelas']);
  $komentar = mysqli_real_escape_string($koneksi, $_POST['komentar_siswa']);

  $query = "
    UPDATE siswa SET
      nama_siswa = '$nama',
      no_induk_siswa = '$nisn',
      no_absen_siswa = '$absen',
      id_kelas = '$id_kelas',
      komentar_siswa = '$komentar'
    WHERE id_siswa = '$id'
  ";

  if (mysqli_query($koneksi, $query)) {
    echo "<script>alert('Data berhasil diperbarui!'); window.location='data_siswa.php';</script>";
    exit;
  } else {
    echo "<script>alert('Gagal memperbarui data!'); history.back();</script>";
    exit;
  }
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
            <label class="form-label fw-semibold">Komentar</label>
            <textarea name="komentar_siswa" class="form-control" rows="3"><?= $data['komentar_siswa']; ?></textarea>
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
