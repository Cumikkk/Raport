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
        <h4 class="fw-bold mb-4">Tambah Data Mapel</h4>

        <!-- Form Simpan -->
        <form action="mapel_tambah_proses.php" method="POST">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Mata Pelajaran</label>
            <input type="text" name="nama_mapel" class="form-control" placeholder="Nama Mapel" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Jenis Mata Pelajaran</label>
            <select name="jenis_mapel" class="form-select" required>
              <option value="" selected disabled>Pilih</option>
              <option value="Wajib">Wajib</option>
              <option value="Pilihan">Pilihan</option>
              <option value="Peminatan">Peminatan</option>
              <option value="Lokal">Lokal</option>
            </select>
          </div>

          <div class="d-flex flex-wrap gap-2 justify-content-between">
            <button type="submit" class="btn btn-success">
              <i class="fa fa-save"></i> Simpan
            </button>
            <a href="data_mapel.php" class="btn btn-danger">
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
