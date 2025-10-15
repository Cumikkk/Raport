<?php
include '../../includes/header.php';
?>

<body>

<?php
include '../../includes/navbar.php';
?>

<div class="dk-page" style="margin-top: 50px;">
  <div class="dk-main">
    <div class="dk-content-box">
      <div class="container py-4">
        <h4 class="fw-bold mb-4">Tambah Data Absensi</h4>

        <form>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Siswa</label>
            <input type="text" class="form-control" placeholder="Nama Siswa" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">No Absen</label>
            <input type="text" class="form-control" placeholder="No Absen" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Keterangan</label>
            <select class="form-select" required>
              <option value="" selected disabled>-- Pilih --</option>
              <option value="Kepala Sekolah">Sakit</option>
              <option value="Wakil Kepala Sekolah">Izin</option>
              <option value="Guru Mata Pelajaran">Alpha</option>
            </select>

          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Wali Kelas</label>
            <input type="text" class="form-control" placeholder="Wali Kelas" required>
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

<?php
include '../../includes/footer.php';
?>
