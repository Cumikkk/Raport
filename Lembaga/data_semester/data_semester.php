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
          <h4 class="fw-bold mb-4 jus">Data Semester</h4>

          <!-- Form Simpan -->
          <form action="semester_proses.php" method="POST">
            <div class="mb-3">
              <label class="form-label fw-semibold">Tahun Ajaran Aktif</label>
              <select class="form-select" id="tahunAjaran" name="tahun_ajaran" required>
                <option value="" selected disabled>Pilih</option>
              </select>

              <script>
                const select = document.getElementById('tahunAjaran');

                const today = new Date();
                const tahunSekarang = today.getFullYear();
                const bulanSekarang = today.getMonth() + 1;

                let tahunAwal, tahunAkhir;

                if (bulanSekarang >= 7) {
                  tahunAwal = tahunSekarang;
                  tahunAkhir = tahunSekarang + 1;
                } else {
                  tahunAwal = tahunSekarang - 1;
                  tahunAkhir = tahunSekarang;
                }

                const tahunAjaranSekarang = `${tahunAwal}/${tahunAkhir}`;
                const tahunAjaranBerikutnya = `${tahunAwal + 1}/${tahunAkhir + 1}`;

                const option1 = new Option(tahunAjaranSekarang, tahunAjaranSekarang);
                const option2 = new Option(tahunAjaranBerikutnya, tahunAjaranBerikutnya);

                select.add(option1);
                select.add(option2);
              </script>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Semester Aktif</label>
              <select class="form-select" name="semester_aktif" required>
                <option value="" selected disabled>Pilih</option>
                <option value="1">Ganjil</option>
                <option value="2">Genap</option>
              </select>
            </div>

            <button type="submit" class="btn btn-success">
              <i class="fa fa-save"></i> Simpan
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php
  include '../../includes/footer.php';
  ?>