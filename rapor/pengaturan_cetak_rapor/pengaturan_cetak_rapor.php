<?php
// pages/cetak_rapor/pengaturan_cetak_rapor.php
require_once '../../koneksi.php';
include '../../includes/header.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$query = mysqli_query($koneksi, "SELECT * FROM pengaturan_cetak_rapor LIMIT 1");
$data  = mysqli_fetch_assoc($query);
?>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <style>
    .dk-alert {
      padding: 12px 14px;
      border-radius: 12px;
      margin-bottom: 20px;
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

    .dk-alert-warning {
      background: #fff7ed;
      border: 1px solid #fed7aa;
      color: #9a3412;
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

      <!-- ✅ ALERT DI LUAR CARD / DK-CONTENT-BOX -->
      <div class="container py-3">
        <div id="alertAreaTop">
          <?php if (isset($_GET['msg']) && $_GET['msg'] !== ''): ?>
            <div class="dk-alert dk-alert-success" data-auto-hide="4000">
              <span class="close-btn">&times;</span>
              ✅ <?= htmlspecialchars($_GET['msg'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>

          <?php if (isset($_GET['err']) && $_GET['err'] !== ''): ?>
            <div class="dk-alert dk-alert-danger" data-auto-hide="4000">
              <span class="close-btn">&times;</span>
              ❌ <?= htmlspecialchars($_GET['err'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="dk-content-box">
        <div class="container py-4">

          <!-- ✅ Judul pojok kiri -->
          <h4 class="fw-bold mb-4 text-center">Pengaturan Cetak Rapor</h4>

          <form action="proses_simpan_pengaturan_cetak_rapor.php" method="POST" autocomplete="off">
            <div class="mb-3">
              <label class="form-label fw-semibold">Tempat Cetak</label>
              <input type="text" name="tempat_cetak" class="form-control"
                placeholder="Isi dengan nama kabupaten atau kecamatan"
                value="<?= isset($data['tempat_cetak']) ? htmlspecialchars($data['tempat_cetak'], ENT_QUOTES, 'UTF-8') : '' ?>">
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Tanggal Cetak</label>
              <input type="date" name="tanggal_cetak" class="form-control"
                value="<?= isset($data['tanggal_cetak']) ? htmlspecialchars($data['tanggal_cetak'], ENT_QUOTES, 'UTF-8') : '' ?>">
            </div>

            <?php if (!empty($data['id_pengaturan_cetak_rapor'])): ?>
              <input type="hidden" name="id_pengaturan_cetak_rapor" value="<?= (int)$data['id_pengaturan_cetak_rapor'] ?>">
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

  <script>
    (function() {
      const ALERT_DURATION = 4000;

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

      const hasAlert = document.querySelector('#alertAreaTop .dk-alert');
      if (hasAlert) {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      }
    })();
  </script>

  <?php include '../../includes/footer.php'; ?>
</body>