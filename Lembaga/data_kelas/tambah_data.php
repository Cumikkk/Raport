<?php
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="dk-page">
  <div class="dk-main d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="dk-content-box shadow-sm rounded" style="width: 100%; max-width: 550px; background: #fff;">
      <div class="container py-4">
        <h4 class="fw-bold mb-4 text-center">Tambah Data Kelas</h4>

        <form>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Guru</label>
            <input type="text" class="form-control" placeholder="Nama Guru" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Jabatan</label>
            <input type="text" class="form-control" placeholder="Jabatan Guru" required>
          </div>

          <button type="submit" class="btn btn-success">
            <i class="fa fa-save"></i> Simpan
          </button>
          <a href="datakelas.php" class="btn btn-danger">
            <i class="fas fa-times" style="justify-content-end"></i> Batal
          </a>
        </form>
      </div>
    </div>
  </div>
</div>
