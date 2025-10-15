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
        <h4 class="fw-bold mb-4" style="text-align:center;">Pengaturan Cetak Rapor</h4>

        <form>
          <div class="mb-3">
            <label class="form-label fw-semibold">Tempat Cetak</label>
            <input type="text" class="form-control" placeholder="Isi dengan nama kabupaten atau kecamatan" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Tanggal Cetak</label>
            <input type="date" class="form-control" placeholder="Tanggal Cetak Rapor" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Watermark</label>
            <input type="text" class="form-control" placeholder="kosongkan jika tidak ingin menggunakan watermark" required>
          </div>


          

          <div class="d-flex flex-wrap gap-2 justify-content-between">
            <button type="submit" class="btn btn-success">
              <i class="fa fa-save"></i> Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
include '../../includes/footer.php';
?>
