<?php
include '../../includes/header.php';
include '../../koneksi.php';
$query = mysqli_query($koneksi, "SELECT * FROM pengaturan_cetak_rapor LIMIT 1");
$data = mysqli_fetch_assoc($query);
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

          <form action="pengaturan_cetak-simpan.php" method="POST">
            <div class="mb-3">
              <label class="form-label fw-semibold">Tempat Cetak</label>
              <input type="text" name="tempat_cetak" class="form-control"
                placeholder="Isi dengan nama kabupaten atau kecamatan"
                value="<?= isset($data['tempat_cetak']) ? htmlspecialchars($data['tempat_cetak']) : '' ?>">
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Tanggal Cetak</label>
              <input type="date" name="tanggal_cetak" class="form-control"
                value="<?= isset($data['tanggal_cetak']) ? $data['tanggal_cetak'] : '' ?>">
            </div>

            <!-- jika ada id berarti mode edit -->
            <?php if (isset($data['id'])): ?>
              <input type="hidden" name="id" value="<?= $data['id'] ?>">
            <?php endif; ?>

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