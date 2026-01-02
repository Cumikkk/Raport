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

      <!-- ✅ ALERT DI LUAR CARD / DK-CONTENT-BOX -->
      <div class="container py-3">
        <div id="alertAreaTop">
          <?php if (isset($_GET['msg']) && $_GET['msg'] !== ''): ?>
            <?php
            $storeType = 'success';
            $storeMsg  = (string)$_GET['msg'];
            ?>
            <div class="dk-alert dk-alert-success"
              data-auto-hide="4000"
              data-dk-type="<?= htmlspecialchars($storeType, ENT_QUOTES, 'UTF-8'); ?>"
              data-dk-msg="<?= htmlspecialchars($storeMsg, ENT_QUOTES, 'UTF-8'); ?>">
              <span class="close-btn">&times;</span>
              <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>
              <?= htmlspecialchars($_GET['msg'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>

          <?php if (isset($_GET['err']) && $_GET['err'] !== ''): ?>
            <?php
            $storeType = 'danger';
            $storeMsg  = (string)$_GET['err'];
            ?>
            <div class="dk-alert dk-alert-danger"
              data-auto-hide="4000"
              data-dk-type="<?= htmlspecialchars($storeType, ENT_QUOTES, 'UTF-8'); ?>"
              data-dk-msg="<?= htmlspecialchars($storeMsg, ENT_QUOTES, 'UTF-8'); ?>">
              <span class="close-btn">&times;</span>
              <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
              <?= htmlspecialchars($_GET['err'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="dk-content-box">
        <div class="container py-4">

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
      const DK_ALERT_STORE_KEY = 'dk_top_alert_cetakrapor_v1_last';

      function escapeHtml(str) {
        return String(str ?? '')
          .replaceAll('&', '&amp;')
          .replaceAll('<', '&lt;')
          .replaceAll('>', '&gt;')
          .replaceAll('"', '&quot;')
          .replaceAll("'", "&#039;");
      }

      function getNavType() {
        try {
          const nav = performance.getEntriesByType('navigation');
          if (nav && nav[0] && nav[0].type) return nav[0].type; // 'navigate' | 'reload' | 'back_forward'
        } catch (e) {}
        return 'navigate';
      }

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

      function saveLastAlert(type, message) {
        try {
          sessionStorage.setItem(DK_ALERT_STORE_KEY, JSON.stringify({
            type: String(type || ''),
            message: String(message || ''),
            path: String(location.pathname || ''),
            savedAt: Date.now()
          }));
        } catch (e) {}
      }

      function clearLastAlert() {
        try {
          sessionStorage.removeItem(DK_ALERT_STORE_KEY);
        } catch (e) {}
      }

      function clearAllTopAlerts(area) {
        if (!area) return;
        area.querySelectorAll('.dk-alert').forEach((el) => el.remove());
      }

      function wireAlert(el) {
        if (!el) return;
        animateAlertIn(el);

        const ms = parseInt(el.getAttribute('data-auto-hide') || String(ALERT_DURATION), 10);
        const timer = setTimeout(() => animateAlertOut(el), ms);
        el.dataset.timerId = String(timer);

        const close = el.querySelector('.close-btn');
        if (close && !close.dataset.bound) {
          close.dataset.bound = '1';
          close.addEventListener('click', (e) => {
            e.preventDefault();
            const t = el.dataset.timerId ? parseInt(el.dataset.timerId, 10) : 0;
            if (t) clearTimeout(t);

            // klik X => jangan ulang saat refresh
            clearLastAlert();
            animateAlertOut(el);
          });
        }
      }

      // hapus param msg/err dari URL tanpa reload
      function cleanStatusUrlParams() {
        try {
          const url = new URL(window.location.href);
          const has = url.searchParams.has('msg') || url.searchParams.has('err');
          if (!has) return;

          url.searchParams.delete('msg');
          url.searchParams.delete('err');

          const newUrl = url.pathname + (url.searchParams.toString() ? ('?' + url.searchParams.toString()) : '') + url.hash;
          window.history.replaceState({}, document.title, newUrl);
        } catch (e) {}
      }

      // show 1 alert saja + simpan sebagai "terakhir"
      window.dkShowTopAlert = function(type, message, persist = true) {
        const area = document.getElementById('alertAreaTop');
        if (!area) return;

        clearAllTopAlerts(area);

        const cls = (type === 'success') ? 'dk-alert-success' : 'dk-alert-danger';
        const icon = (type === 'success') ?
          '<i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>' :
          '<i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>';

        const div = document.createElement('div');
        div.className = `dk-alert ${cls}`;
        div.setAttribute('data-auto-hide', String(ALERT_DURATION));
        div.innerHTML = `<span class="close-btn">&times;</span>${icon}${escapeHtml(message)}`;

        area.prepend(div);
        wireAlert(div);

        if (persist) saveLastAlert(type, message);
      };

      const navType = getNavType();

      // RULE UTAMA:
      // - pindah halaman / back-forward => jangan bisa restore
      // - repeat hanya saat reload
      if (navType !== 'reload') {
        clearLastAlert();
      }

      // aktifkan animasi untuk alert PHP (jika ada)
      const phpAlerts = document.querySelectorAll('#alertAreaTop .dk-alert');
      if (phpAlerts.length > 0) {
        phpAlerts.forEach(wireAlert);

        // scroll ke atas kalau ada alert
        try {
          window.scrollTo({
            top: 0,
            behavior: 'smooth'
          });
        } catch (e) {
          window.scrollTo(0, 0);
        }

        // simpan untuk repeat (cuma saat reload)
        const first = phpAlerts[0];
        const t = first.getAttribute('data-dk-type') || (first.classList.contains('dk-alert-success') ? 'success' : 'danger');
        const m = first.getAttribute('data-dk-msg') || '';
        if (m.trim() !== '') saveLastAlert(t, m);

        // bersihkan URL param tapi tetap bisa repeat via reload
        cleanStatusUrlParams();
        return;
      }

      // restore hanya saat reload
      if (navType === 'reload') {
        try {
          const raw = sessionStorage.getItem(DK_ALERT_STORE_KEY);
          if (raw) {
            const obj = JSON.parse(raw);
            if (obj && obj.type && obj.message && obj.path === String(location.pathname || '')) {
              window.dkShowTopAlert(obj.type, obj.message, false);

              try {
                window.scrollTo({
                  top: 0,
                  behavior: 'smooth'
                });
              } catch (e) {
                window.scrollTo(0, 0);
              }
            }
          }
        } catch (e) {}
      }
    })();
  </script>

  <?php include '../../includes/footer.php'; ?>
</body>