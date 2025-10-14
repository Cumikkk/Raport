<?php
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="dk-page">
  <div class="dk-main d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="dk-content-box shadow-sm rounded" style="width: 100%; max-width: 550px; background: #fff;">
      <div class="container py-4">
        <h4 class="fw-bold mb-5 text-center">Edit Mata Pelajaran</h4>

        <form>

          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Mata Pelajaran</label>
            <input type="text" class="form-control" placeholder="Nama Mata Pelajaran" required>
          </div>

          <div class="d-flex justify-content-between mt-4">
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
