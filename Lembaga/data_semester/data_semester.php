<?php
// pages/semester/data_semester.php

// ====== CEK PARAMETER ALERT (SETELAH SIMPAN) ======
$alertMsg  = '';
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
    /* ===== Alert style ===== */
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

      <!-- ✅ ALERT DI LUAR BOX/CARD -->
      <div class="container py-3">
        <div id="alertAreaTop">
          <?php if ($alertMsg !== '' && $alertType !== ''): ?>
            <?php
            $storeType = ($alertType === 'success') ? 'success' : 'danger';
            $storeMsg  = $alertMsg;
            ?>
            <?php if ($alertType === 'success'): ?>
              <div class="dk-alert dk-alert-success"
                data-auto-hide="4000"
                data-dk-type="<?= htmlspecialchars($storeType, ENT_QUOTES, 'UTF-8'); ?>"
                data-dk-msg="<?= htmlspecialchars($storeMsg, ENT_QUOTES, 'UTF-8'); ?>">
                <span class="close-btn">&times;</span>
                <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>
                <?= htmlspecialchars($alertMsg, ENT_QUOTES, 'UTF-8'); ?>
              </div>
            <?php else: ?>
              <div class="dk-alert dk-alert-danger"
                data-auto-hide="4000"
                data-dk-type="<?= htmlspecialchars($storeType, ENT_QUOTES, 'UTF-8'); ?>"
                data-dk-msg="<?= htmlspecialchars($storeMsg, ENT_QUOTES, 'UTF-8'); ?>">
                <span class="close-btn">&times;</span>
                <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
                <?= htmlspecialchars($alertMsg, ENT_QUOTES, 'UTF-8'); ?>
              </div>
            <?php endif; ?>
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

  <!-- ✅ ALERT: hanya bisa diulang saat REFRESH, bukan saat pindah halaman -->
  <script>
    (function() {
      const ALERT_DURATION = 4000;
      const DK_ALERT_STORE_KEY = 'dk_top_alert_semester_v2_last';

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

      // hapus param msg dari URL tanpa reload
      function cleanStatusUrlParams() {
        try {
          const url = new URL(window.location.href);
          if (!url.searchParams.has('msg')) return;

          url.searchParams.delete('msg');
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
      // - Jika bukan reload (navigate/back_forward) => hapus storage, supaya pindah halaman tidak bisa mengulang alert
      if (navType !== 'reload') {
        clearLastAlert();
      }

      // 1) kalau ada alert dari PHP: simpan sebagai last alert, bersihkan URL param, selesai
      const phpAlerts = document.querySelectorAll('#alertAreaTop .dk-alert');
      if (phpAlerts.length > 0) {
        phpAlerts.forEach(wireAlert);

        const first = phpAlerts[0];
        const t = first.getAttribute('data-dk-type') || (first.classList.contains('dk-alert-success') ? 'success' : 'danger');
        const m = first.getAttribute('data-dk-msg') || '';
        if (m.trim() !== '') saveLastAlert(t, m);

        cleanStatusUrlParams();
        return;
      }

      // 2) tidak ada alert PHP => restore HANYA saat reload
      if (navType === 'reload') {
        try {
          const raw = sessionStorage.getItem(DK_ALERT_STORE_KEY);
          if (raw) {
            const obj = JSON.parse(raw);
            if (obj && obj.type && obj.message && obj.path === String(location.pathname || '')) {
              window.dkShowTopAlert(obj.type, obj.message, false);
            }
          }
        } catch (e) {}
      }
    })();
  </script>

  <?php include '../../includes/footer.php'; ?>
</body>