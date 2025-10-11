<?php
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="dk-page">
  <div class="dk-main">
    <div class="dk-content-box">
      <div class="container py-4">
        <h4 class="fw-bold mb-4">Import Data Kelas</h4>

        <form enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Opsi</label>
            <select class="form-select" required>
              <option value="">-- Pilih Opsi --</option>
              <option value="X">X</option>
              <option value="XI">XI</option>
              <option value="XII">XII</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Upload File</label>
            <input type="file" class="form-control" required>
          </div>

          <button type="submit" class="btn btn-success">
            <i class="fa fa-save"></i> Simpan
          </button>
          <a href="datakelas.php" class="btn btn-danger">
            <i class="fas fa-times"></i> Batal
          </a>
        </form>
      </div>
    </div>
  </div>
</div>
