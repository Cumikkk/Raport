<?php
// pages/semester/data_semester.php

// ====== CEK PARAMETER ALERT (SETELAH SIMPAN) ======
$alertMsg = '';
$alertType = ''; // success | danger

if (isset($_GET['msg'])) {
  switch ($_GET['msg']) {
    case 'saved':
      $alertMsg  = 'Pengaturan semester berhasil disimpan.';
      $alertType = 'success';
      break;

    case 'error':
      $alertMsg  = 'Terjadi kesalahan saat menyimpan pengaturan semester.';
      $alertType = 'danger';
      break;
  }
}

include '../../includes/header.php';
?>

<body>

  <?php include '../../includes/navbar.php'; ?>

  <style>
    /* ===== Alert style (meniru pola di data guru) ===== */
    .dk-alert {
      padding: 12px 14px;
      border-radius: 12px;
      margin-bottom: 14px;
      font-size: 14px;
      max-height: 220px;
      overflow: hidden;
      position: relative;
      opacity: 0;
      transform: translateY(-10px);
      transition: opacity .35s ease, transform .35s ease,
        max-height .35s ease, margin .35s ease, padding .35s ease;
    }

    .dk-alert.dk-show {
      opacity: 1;
      transform: translateY(0);
    }

    .dk-alert.dk-hide {
      opacity: 0;
      transform: translateY(-6px);
      max-height: 0;
      margin: 0;
      padding-top: 0;
      padding-bottom: 0;
    }

    .dk-alert-success {
      background: #e8f8ee;
      border: 1px solid #c8efd9;
      color: #166534;
    }

    .dk-alert-danger {
      background: #fdecec;
      border: 1px solid #f5c2c2;
      color: #991b1b;
    }

    .dk-alert .close-btn {
      position: absolute;
      top: 14px;
      right: 14px;
      font-weight: 800;
      cursor: pointer;
      opacity: .6;
      font-size: 18px;
      line-height: 1;
      user-select: none;
    }

    .dk-alert .close-btn:hover {
      opacity: 1;
    }

    #alertAreaTop {
      position: relative;
    }
  </style>

  <div class="dk-page" style="margin-top: 50px;">
    <div class="dk-main">

      <!-- ✅ ALERT DI LUAR BOX/CARD (SEPERTI DATA GURU) -->
      <div class="container py-3">
        <div id="alertAreaTop">
          <?php if ($alertMsg !== ''): ?>
            <div class="dk-alert <?= $alertType === 'success' ? 'dk-alert-success' : 'dk-alert-danger' ?>" data-auto-hide="5000">
              <span class="close-btn">&times;</span>
              <?= $alertType === 'success' ? '✅' : '❌' ?>
              <?= htmlspecialchars($alertMsg, ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="dk-content-box">
        <div class="container py-4">
          <h4 class="fw-bold mb-4 jus text-center">Data Semester</h4>

          <!-- Form Simpan -->
          <form action="proses_simpan_data_semester.php" method="POST">
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

  <!-- ✅ AUTO HIDE + CLOSE (DK ALERT) -->
  <script>
    (function() {
      const ALERT_DURATION = 5000;

      function animateAlertIn(el) {
        if (!el) return;
        requestAnimationFrame(() => el.classList.add('dk-show'));
      }

      function animateAlertOut(el) {
        if (!el) return;
        el.classList.add('dk-hide');
        setTimeout(() => {
          if (el && el.parentNode) el.parentNode.removeChild(el);
        }, 450);
      }

      function wireAlert(el) {
        if (!el) return;
        animateAlertIn(el);

        const ms = parseInt(el.getAttribute('data-auto-hide') || String(ALERT_DURATION), 10);
        const timer = setTimeout(() => animateAlertOut(el), ms);

        const close = el.querySelector('.close-btn');
        if (close) {
          close.addEventListener('click', (e) => {
            e.preventDefault();
            clearTimeout(timer);
            animateAlertOut(el);
          });
        }
      }

      document.querySelectorAll('#alertAreaTop .dk-alert').forEach(wireAlert);
    })();
  </script>

  <?php include '../../includes/footer.php'; ?>
</body>