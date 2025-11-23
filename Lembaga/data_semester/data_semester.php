<?php
// ====== CEK PARAMETER ALERT (SETELAH SIMPAN) ======
$alertMsg = '';
$alertClass = '';

if (isset($_GET['msg'])) {
  switch ($_GET['msg']) {
    case 'saved':
      $alertMsg   = 'Pengaturan semester berhasil disimpan.';
      $alertClass = 'alert-success';
      break;

    case 'error':
      $alertMsg   = 'Terjadi kesalahan saat menyimpan pengaturan semester.';
      $alertClass = 'alert-danger';
      break;
  }
}

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

          <!-- ALERT NOTIFIKASI (muncul setelah klik simpan) -->
          <?php if ($alertMsg !== ''): ?>
            <div id="alertArea">
              <div class="alert <?= $alertClass ?> alert-dismissible fade show mb-3" role="alert">
                <?= htmlspecialchars($alertMsg, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            </div>
          <?php endif; ?>

          <!-- Form Simpan -->
          <form action="semester_proses.php" method="POST">
            <div class="mb-3">
              <label class="form-label fw-semibold">Tahun Ajaran Aktif</label>
              <select class="form-select" id="tahunAjaran" name="tahun_ajaran" required>
                <option value="" selected disabled>--Pilih--</option>
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
                <option value="" selected disabled>--Pilih--</option>
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

  <!-- AUTO HIDE ALERT 5 DETIK (tidak mengubah tampilan) -->
  <script>
    window.addEventListener('DOMContentLoaded', function () {
      const alerts = document.querySelectorAll('#alertArea .alert');
      if (!alerts.length) return;

      setTimeout(() => {
        alerts.forEach(a => {
          a.style.transition = 'opacity 0.5s ease';
          a.style.opacity = '0';
          setTimeout(() => {
            if (a.parentNode) a.parentNode.removeChild(a);
          }, 600);
        });
      }, 5000); // 5 detik
    });
  </script>

  <?php
  include '../../includes/footer.php';
  ?>
