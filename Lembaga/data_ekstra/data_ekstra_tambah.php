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
          <h4 class="fw-bold mb-4">Tambah Ekstrakurikuler</h4>

          <form action="ekstra_tambah_proses.php" method="POST">
            <div class="mb-3">
              <label class="form-label fw-semibold">Nama Ekstrakurikuler</label>
              <input type="text" name="nama_ekstra" class="form-control" placeholder="Nama Ekstrakurikuler" required>
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