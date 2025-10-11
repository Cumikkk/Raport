<?php
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="dk-page">
  <div class="dk-main">
    <div class="dk-content-box">
      <div class="container py-4">
        <h4 class="fw-bold mb-4">Edit Data Kelas</h4>

        <form>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Kelas</label>
            <input type="text" class="form-control" value="XII IPA 1" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Wali Kelas</label>
            <input type="text" class="form-control" value="Irsyal Velani, S.Si." required>
          </div>

          <button type="submit" class="btn btn-success">
            <i class="fa fa-save"></i> Update
          </button>
          <a href="index.php" class="btn btn-secondary">Kembali</a>
        </form>
      </div>
    </div>
  </div>
</div>
