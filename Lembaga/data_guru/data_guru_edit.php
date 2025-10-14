<?php
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="dk-page">
  <div class="dk-main">
    <div class="dk-content-box">
      <div class="container py-4">
        <h4 class="fw-bold mb-4">Tambah Guru</h4>

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
          <a href="data_guru.php" class="btn btn-danger">
            <i class="fas fa-times" style="justify-content-end"></i> Batal
          </a>
        </form>
      </div>
    </div>
  </div>
</div>
