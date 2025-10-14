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
        <h4 class="fw-bold mb-4">Edit Ekstrakurikuler</h4>

        <form>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Ekstrakurikuler</label>
            <input type="text" class="form-control" placeholder="Nama Ekstrakurikuler" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Jabatan</label>
            <select class="form-select" required>
              <option value="" selected disabled>Pilih Jabatan</option>
              <option value="Kepala Sekolah">Kepala Sekolah</option>
              <option value="Wakil Kepala Sekolah">Wakil Kepala Sekolah</option>
              <option value="Guru Mata Pelajaran">Guru Mata Pelajaran</option>
              <option value="Guru BK">Guru BK</option>
              <option value="Staff TU">Staff TU</option>
              <option value="Operator Sekolah">Operator Sekolah</option>
            </select>
          </div>

          <div class="d-flex flex-wrap gap-2 justify-content-between">
            <button type="submit" class="btn btn-success">
              <i class="fa fa-save"></i> Simpan
            </button>
            <a href="data_ekstra.php" class="btn btn-danger">
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
