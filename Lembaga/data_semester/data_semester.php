<?php
// pages/semester/data_semester.php

require_once '../../koneksi.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

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

// ====== AMBIL DATA SEMESTER TERAKHIR (UNTUK AUTO-SELECT) ======
$savedTahunAjaran   = '';
$savedSemesterAktif = ''; // '1' atau '2'

try {
  $sql = "SELECT nama_semester, tahun_ajaran FROM semester ORDER BY id_semester DESC LIMIT 1";
  $stmt = mysqli_prepare($koneksi, $sql);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  if ($row = mysqli_fetch_assoc($res)) {
    $savedSemesterAktif = trim((string)($row['nama_semester'] ?? ''));
    $savedTahunAjaran   = trim((string)($row['tahun_ajaran'] ?? ''));
  }
  mysqli_stmt_close($stmt);
} catch (Throwable $e) {
  // biarkan kosong jika gagal
}

include '../../includes/header.php';
?>

<body>

  <?php include '../../includes/navbar.php'; ?>

  <style>
    /* ===== Alert style (tetap) ===== */
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

    /* ✅ Letak tetap seperti semula, tapi tidak bikin konten "loncat" saat alert hilang */
    #alertAreaTop {
      position: relative;
      height: 0;
      /* default: tidak makan ruang */
    }

    /* kalau ada alert: kasih ruang secukupnya, biar posisi card tidak kebawah jauh */
    #alertAreaTop.has-alert {
      height: 56px;
      /* cukup untuk 1 bar alert normal */
    }

    /* alert di-absolute supaya height container tetap stabil (tidak dorong/naik-turun) */
    #alertAreaTop .dk-alert {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      margin-bottom: 0;
    }
  </style>

  <div class="dk-page" style="margin-top: 50px;">
    <div class="dk-main">

      <!-- ✅ ALERT DI LUAR BOX/CARD (letak seperti sebelumnya) -->
      <div class="container py-2">
        <div id="alertAreaTop" class="<?= ($alertMsg !== '' && $alertType !== '') ? 'has-alert' : ''; ?>">
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

                // ✅ nilai tersimpan dari DB (jika ada)
                const SAVED_TAHUN = <?= json_encode($savedTahunAjaran, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

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

                // opsi default (tetap seperti sebelumnya)
                const option1 = new Option(tahunAjaranSekarang, tahunAjaranSekarang);
                const option2 = new Option(tahunAjaranBerikutnya, tahunAjaranBerikutnya);
                select.add(option1);
                select.add(option2);

                // ✅ kalau sudah pernah simpan => auto selected, tidak perlu pilih ulang
                if (SAVED_TAHUN && typeof SAVED_TAHUN === 'string') {
                  let found = false;
                  for (const opt of select.options) {
                    if (opt.value === SAVED_TAHUN) {
                      found = true;
                      break;
                    }
                  }
                  if (!found) {
                    const optSaved = new Option(SAVED_TAHUN, SAVED_TAHUN);
                    select.add(optSaved);
                  }
                  select.value = SAVED_TAHUN;
                }
              </script>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Semester Aktif</label>
              <select class="form-select" name="semester_aktif" required>
                <option value="" disabled <?= ($savedSemesterAktif === '' ? 'selected' : ''); ?>>--Pilih--</option>
                <option value="1" <?= ($savedSemesterAktif === '1' ? 'selected' : ''); ?>>Ganjil</option>
                <option value="2" <?= ($savedSemesterAktif === '2' ? 'selected' : ''); ?>>Genap</option>
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
          if (nav && nav[0] && nav[0].type) return nav[0].type;
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

      function cleanStatusUrlParams() {
        try {
          const url = new URL(window.location.href);
          if (!url.searchParams.has('msg')) return;

          url.searchParams.delete('msg');
          const newUrl = url.pathname + (url.searchParams.toString() ? ('?' + url.searchParams.toString()) : '') + url.hash;
          window.history.replaceState({}, document.title, newUrl);
        } catch (e) {}
      }

      window.dkShowTopAlert = function(type, message, persist = true) {
        const area = document.getElementById('alertAreaTop');
        if (!area) return;

        // kunci ruang supaya tidak loncat saat alert tampil/hilang
        area.classList.add('has-alert');

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

      if (navType !== 'reload') {
        clearLastAlert();
      }

      const phpAlerts = document.querySelectorAll('#alertAreaTop .dk-alert');
      if (phpAlerts.length > 0) {
        const area = document.getElementById('alertAreaTop');
        if (area) area.classList.add('has-alert');

        phpAlerts.forEach(wireAlert);

        const first = phpAlerts[0];
        const t = first.getAttribute('data-dk-type') || (first.classList.contains('dk-alert-success') ? 'success' : 'danger');
        const m = first.getAttribute('data-dk-msg') || '';
        if (m.trim() !== '') saveLastAlert(t, m);

        cleanStatusUrlParams();
        return;
      }

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