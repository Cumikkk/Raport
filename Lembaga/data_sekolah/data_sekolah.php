<?php
// pages/sekolah/data_sekolah.php
include '../../includes/header.php';
require_once '../../koneksi.php'; // gunakan $koneksi (mysqli)

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$koneksi->set_charset('utf8mb4');

// Ambil 1 baris data (jika belum ada, $data = null)
$res  = $koneksi->query("SELECT * FROM sekolah ORDER BY id_sekolah ASC LIMIT 1");
$data = $res->fetch_assoc() ?: [];
?>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <!-- CropperJS (CDN) -->
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css"
    referrerpolicy="no-referrer" />

  <style>
    :root {
      --bg: #f6f7fb;
      --card: #fff;
      --primary: #004080;
      --muted: #6b7280;
      --ring: #3b82f6;
      --success: #16a34a;
      --warn: #f59e0b;
      --danger: #dc2626;
      --radius: 14px;
      --gap: 20px;
    }

    body {
      font-family: "Poppins", sans-serif;
      background: var(--bg);
    }

    .dk-content-box {
      background: transparent !important;
      box-shadow: none !important;
      border: 0 !important;
      padding: 0 !important;
    }

    .sekolah-wrapper {
      max-width: 1100px;
      margin: 0 auto;
      padding: var(--gap);
    }

    .page-title {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: var(--gap);
    }

    .page-title h1 {
      font-size: 22px;
      color: black;
      font-weight: 700;
      margin: 0;
    }

    .subtitle {
      color: var(--muted);
      font-size: 14px;
    }

    .card {
      background: var(--card);
      border-radius: var(--radius);
      box-shadow: 0 6px 18px rgba(0, 0, 0, .06);
      padding: var(--gap);
    }

    .grid {
      display: grid;
      gap: var(--gap);
    }

    @media (min-width: 900px) {
      .grid {
        grid-template-columns: 1.6fr .9fr;
      }
    }

    .section-title {
      font-weight: 600;
      margin: 0 0 10px 0;
      color: #111827;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr;
      gap: 14px;
    }

    @media (min-width: 700px) {
      .form-row.cols-2 {
        grid-template-columns: 1fr 1fr;
      }
    }

    .form-label {
      font-weight: 600;
      font-size: 14px;
      margin-bottom: 6px;
      display: block;
    }

    .form-control {
      width: 100%;
      padding: 12px 14px;
      border: 1px solid #d1d5db;
      border-radius: 10px;
      font-size: 15px;
      transition: box-shadow .2s, border-color .2s, background-color .2s;
      background: #fff;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--ring);
      box-shadow: 0 0 0 4px rgba(59, 130, 246, .12);
    }

    textarea.form-control {
      resize: vertical;
    }

    .hint {
      font-size: 12px;
      color: var(--muted);
      margin-top: 6px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 12px 16px;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      transition: transform .15s, opacity .15s;
    }

    .btn:active {
      transform: translateY(1px);
    }

    .btn-success {
      background: var(--success);
      color: #fff;
    }

    .btn-warning {
      background: var(--warn);
      color: #fff;
    }

    .stack {
      display: flex;
      flex-direction: column;
      gap: var(--gap);
    }

    .alert {
      padding: 12px 14px;
      border-radius: 12px;
      margin-bottom: var(--gap);
      font-size: 14px;
      transition:
        opacity 0.4s ease,
        transform 0.4s ease,
        max-height 0.4s ease,
        margin 0.4s ease,
        padding-top 0.4s ease,
        padding-bottom 0.4s ease;
      max-height: 200px;
      overflow: hidden;
      position: relative;
      will-change: opacity, transform, max-height;
    }

    /* ✅ Animasi MUNCUL */
    .alert-enter {
      opacity: 0;
      transform: translateY(-10px);
      max-height: 0;
      margin-bottom: 0;
      padding-top: 0;
      padding-bottom: 0;
    }

    .alert-enter.alert-enter-active {
      opacity: 1;
      transform: translateY(0);
      max-height: 200px;
      margin-bottom: var(--gap);
      padding-top: 12px;
      padding-bottom: 12px;
    }

    .alert-success {
      background: #e8f8ee;
      border: 1px solid #c8efd9;
      color: #166534;
    }

    .alert-danger {
      background: #fdecec;
      border: 1px solid #f5c2c2;
      color: #991b1b;
    }

    /* ✅ FIX: selector dibuat 2 class + important biar ngalahin enter-active */
    .alert.alert-hide {
      opacity: 0 !important;
      transform: translateY(-4px) !important;
      max-height: 0 !important;
      margin: 0 !important;
      padding-top: 0 !important;
      padding-bottom: 0 !important;
    }

    .alert .close-btn {
      position: absolute;
      top: 14px;
      right: 14px;
      font-weight: 700;
      cursor: pointer;
      opacity: 0.6;
      font-size: 18px;
      line-height: 1;
    }

    .alert .close-btn:hover {
      opacity: 1;
    }

    .logo-wrap {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 12px;
    }

    .logo-preview {
      width: 160px;
      height: 160px;
      border-radius: 12px;
      border: 1px solid #e5e7eb;
      object-fit: cover;
      background: #fafafa;
    }

    .actions {
      position: sticky;
      bottom: 0;
      z-index: 5;
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(8px);
      padding: var(--gap);
      margin-top: var(--gap);
      border-radius: var(--radius);
      border: 1px solid rgba(0, 0, 0, 0.08);
      box-shadow:
        0 -10px 24px rgba(0, 0, 0, 0.12),
        0 2px 10px rgba(0, 0, 0, 0.06);
    }

    .actions::before {
      content: "";
      position: absolute;
      left: 10px;
      right: 10px;
      top: -1px;
      height: 1px;
      background: rgba(0, 0, 0, 0.10);
      border-radius: 999px;
    }

    .req {
      color: #e11d48;
    }

    .form-control.error {
      border-color: #dc2626;
      background-color: #fef2f2;
    }

    .error-text {
      display: none;
      margin-top: 4px;
      font-size: 12px;
      color: #b91c1c;
    }

    .crop-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .55);
      display: none;
      align-items: center;
      justify-content: center;
      padding: 18px;
      z-index: 9999;
    }

    .crop-overlay.show {
      display: flex;
    }

    .crop-box {
      width: min(980px, 100%);
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
      overflow: hidden;
    }

    .crop-head {
      padding: 14px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid #eee;
    }

    .crop-head h3 {
      margin: 0;
      font-size: 16px;
      font-weight: 700;
      color: #111827;
    }

    .crop-close {
      border: none;
      background: transparent;
      font-size: 22px;
      line-height: 1;
      cursor: pointer;
      opacity: .7;
    }

    .crop-close:hover {
      opacity: 1;
    }

    .crop-body {
      padding: 14px 16px;
      display: grid;
      gap: 12px;
    }

    .crop-canvas {
      width: 100%;
      max-height: 65vh;
      overflow: hidden;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      background: #fafafa;
    }

    #cropImage {
      max-width: 100%;
      display: block;
    }

    .crop-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: space-between;
      align-items: center;
      padding-top: 4px;
    }

    .crop-actions .left,
    .crop-actions .right {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      align-items: center;
    }

    .btn-lite {
      padding: 10px 14px;
      border-radius: 10px;
      border: 1px solid #d1d5db;
      background: #fff;
      font-weight: 600;
      cursor: pointer;
      transition: none !important;
    }

    .btn-lite:hover {
      background: #e0f2fe;
      border-color: #38bdf8;
      color: #0f172a;
    }

    .btn-danger-lite {
      background: #dc2626;
      color: #fff;
      border: none;
      transition: none !important;
    }

    .btn-success-lite {
      background: #16a34a;
      color: #fff;
      border: none;
      transition: none !important;
    }

    .btn-danger-lite:hover {
      background: #b91c1c;
      color: #fff;
    }

    .btn-success-lite:hover {
      background: #15803d;
      color: #fff;
    }

    #btnCancelCrop,
    #btnApplyCrop {
      transition: none !important;
    }

    #btnCancelCrop:hover {
      background: #b91c1c !important;
      color: #fff !important;
      opacity: 1 !important;
      transform: none !important;
      box-shadow: none !important;
      filter: none !important;
    }

    #btnApplyCrop:hover {
      background: #15803d !important;
      color: #fff !important;
      opacity: 1 !important;
      transform: none !important;
      box-shadow: none !important;
      filter: none !important;
    }

    #btnCancelCrop:focus,
    #btnCancelCrop:focus-visible,
    #btnApplyCrop:focus,
    #btnApplyCrop:focus-visible {
      outline: none !important;
      box-shadow: none !important;
    }
  </style>

  <div class="dk-page">
    <div class="dk-main">
      <div class="dk-content-box">
        <div class="container-fluid py-3">
          <div class="sekolah-wrapper">

            <div class="page-title"></div>

            <?php if (isset($_GET['status'])): ?>
              <?php if ($_GET['status'] === 'success'): ?>
                <div class="alert alert-success alert-enter" data-auto-scroll="1">
                  <span class="close-btn">&times;</span>
                  ✅ Data sekolah berhasil disimpan.
                </div>
              <?php else: ?>
                <div class="alert alert-danger alert-enter" data-auto-scroll="1">
                  <span class="close-btn">&times;</span>
                  ❌ Gagal menyimpan data sekolah
                  <?= isset($_GET['msg']) ? ': ' . htmlspecialchars($_GET['msg']) : '' ?>.
                </div>
              <?php endif; ?>
            <?php endif; ?>

            <!-- ✅ ALERT UI UNTUK VALIDASI JS -->
            <div id="jsAlertContainer"></div>

            <div class="grid">
              <div class="card">
                <h2 class="section-title">Data Sekolah</h2>
                <form action="save_data_sekolah.php" method="POST" enctype="multipart/form-data" class="stack" id="formSekolah">
                  <?php if (!empty($data['id_sekolah'])): ?>
                    <input type="hidden" name="id_sekolah" value="<?= (int)$data['id_sekolah'] ?>">
                  <?php endif; ?>
                  <input type="hidden" name="old_logo" value="<?= htmlspecialchars($data['logo_sekolah'] ?? '') ?>">

                  <div>
                    <label class="form-label">Nama Sekolah <span class="req">*</span></label>
                    <input type="text" name="nama_sekolah" class="form-control" required autocomplete="organization"
                      value="<?= htmlspecialchars($data['nama_sekolah'] ?? '') ?>">
                  </div>

                  <div class="form-row cols-2">
                    <div>
                      <label class="form-label">NSM <span class="req">*</span></label>
                      <input type="text" name="nsm_sekolah" class="form-control" inputmode="numeric" required
                        value="<?= htmlspecialchars($data['nsm_sekolah'] ?? '') ?>">
                    </div>
                    <div>
                      <label class="form-label">NPSN <span class="req">*</span></label>
                      <input type="text" name="npsn_sekolah" class="form-control" inputmode="numeric" required
                        value="<?= htmlspecialchars($data['npsn_sekolah'] ?? '') ?>">
                    </div>
                  </div>

                  <div>
                    <label class="form-label">Alamat <span class="req">*</span></label>
                    <textarea name="alamat_sekolah" class="form-control" rows="2" autocomplete="street-address"
                      required><?= htmlspecialchars($data['alamat_sekolah'] ?? '') ?></textarea>
                  </div>

                  <div class="form-row cols-2">
                    <div>
                      <label class="form-label">Telepon <span class="req">*</span></label>
                      <input type="text" name="no_telepon_sekolah" class="form-control" inputmode="tel" placeholder="08xxxxxxxxxx" required
                        value="<?= htmlspecialchars($data['no_telepon_sekolah'] ?? '') ?>">
                    </div>
                    <div>
                      <label class="form-label">Kecamatan <span class="req">*</span></label>
                      <input type="text" name="kecamatan_sekolah" class="form-control" required
                        value="<?= htmlspecialchars($data['kecamatan_sekolah'] ?? '') ?>">
                    </div>
                  </div>

                  <div class="form-row cols-2">
                    <div>
                      <label class="form-label">Kabupaten/Kota <span class="req">*</span></label>
                      <input type="text" name="kabupaten_atau_kota_sekolah" class="form-control" required
                        value="<?= htmlspecialchars($data['kabupaten_atau_kota_sekolah'] ?? '') ?>">
                    </div>
                    <div>
                      <label class="form-label">Provinsi <span class="req">*</span></label>
                      <input type="text" name="provinsi_sekolah" class="form-control" required
                        value="<?= htmlspecialchars($data['provinsi_sekolah'] ?? '') ?>">
                    </div>
                  </div>

                  <div>
                    <label class="form-label">Logo Sekolah (opsional)</label>
                    <input type="file" name="logo_sekolah" id="logoInput" class="form-control"
                      accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <div class="hint">Format: JPG/PNG/WebP · Maks 10MB. (akan muncul crop sebelum upload)</div>
                  </div>

                  <div class="actions">
                    <label class="form-label" style="display:flex;align-items:center;gap:8px">
                      <input class="form-check-input" type="checkbox" value="1" id="confirm" required>
                      <span>Saya yakin akan menyimpan perubahan</span>
                    </label>
                    <button type="submit" class="btn btn-success" style="margin-top:10px">Simpan</button>
                  </div>
                </form>
              </div>

              <div class="card">
                <h2 class="section-title">Logo</h2>
                <div class="logo-wrap">
                  <img id="logoPreview"
                    src="uploads/<?= htmlspecialchars(($data['logo_sekolah'] ?? '') ?: 'default.png') ?>"
                    class="logo-preview"
                    alt="Logo Sekolah">
                  <div class="hint">Gambar pratinjau akan berubah saat Anda memilih file (hasil crop).</div>
                </div>

                <hr style="border:none;border-top:1px solid #eee;margin:10px 0 12px">

                <h2 class="section-title">Ringkasan</h2>
                <div style="font-size:14px;color:#374151;display:grid;gap:8px">
                  <div><strong>Nama:</strong> <?= htmlspecialchars($data['nama_sekolah'] ?? '-') ?></div>
                  <div><strong>NSM:</strong> <?= htmlspecialchars($data['nsm_sekolah'] ?? '-') ?></div>
                  <div><strong>NPSN:</strong> <?= htmlspecialchars($data['npsn_sekolah'] ?? '-') ?></div>
                  <div><strong>Telepon:</strong> <?= htmlspecialchars($data['no_telepon_sekolah'] ?? '-') ?></div>
                  <div><strong>Alamat:</strong> <?= htmlspecialchars($data['alamat_sekolah'] ?? '-') ?></div>
                  <div><strong>Kecamatan:</strong> <?= htmlspecialchars($data['kecamatan_sekolah'] ?? '-') ?></div>
                  <div><strong>Kab/Kota:</strong> <?= htmlspecialchars($data['kabupaten_atau_kota_sekolah'] ?? '-') ?></div>
                  <div><strong>Provinsi:</strong> <?= htmlspecialchars($data['provinsi_sekolah'] ?? '-') ?></div>
                </div>
              </div>
            </div><!-- /.grid -->
          </div><!-- /.sekolah-wrapper -->
        </div><!-- /.container-fluid -->
      </div><!-- /.dk-content-box -->
    </div><!-- /.dk-main -->
  </div><!-- /.dk-page -->

  <!-- =============================
       CROP OVERLAY (HIDDEN BY DEFAULT)
       ============================= -->
  <div class="crop-overlay" id="cropOverlay" aria-hidden="true">
    <div class="crop-box">
      <div class="crop-head">
        <h3>Crop Logo</h3>
        <button class="crop-close" id="cropCloseBtn" type="button" aria-label="Close">&times;</button>
      </div>

      <div class="crop-body">
        <div class="hint" style="margin-top:0;">
          Atur crop lalu klik <strong>Gunakan</strong>. Jika batal, file tidak akan terupload.
        </div>

        <div class="crop-canvas">
          <img id="cropImage" alt="Crop Area">
        </div>

        <div class="crop-actions">
          <div class="left">
            <button type="button" class="btn-lite" id="btnZoomIn">Zoom +</button>
            <button type="button" class="btn-lite" id="btnZoomOut">Zoom -</button>
            <button type="button" class="btn-lite" id="btnRotate">Putar</button>
            <button type="button" class="btn-lite" id="btnReset">Reset</button>
          </div>
          <div class="right">
            <button type="button" class="btn-lite btn-danger-lite" id="btnCancelCrop">Batal</button>
            <button type="button" class="btn-lite btn-success-lite" id="btnApplyCrop">Gunakan</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function animateInAlert(el, doScroll = true) {
      if (!el) return;

      if (doScroll) {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      }

      if (!el.classList.contains('alert-enter')) el.classList.add('alert-enter');

      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          el.classList.add('alert-enter-active');
        });
      });
    }

    // ✅ FIX: saat hide, buang class enter-active biar ga “ngunci” max-height/padding
    function hideAlert(el) {
      if (!el) return;
      el.classList.remove('alert-enter-active');
      el.classList.add('alert-hide');
    }

    function showUiAlert(type, message) {
      const container = document.getElementById('jsAlertContainer');
      if (!container) return;

      const el = document.createElement('div');
      el.className = `alert alert-${type} alert-enter`;
      el.innerHTML = `<span class="close-btn">&times;</span>${message}`;

      container.prepend(el);

      animateInAlert(el, true);

      const timer = setTimeout(() => {
        hideAlert(el);
      }, 4000);

      const close = el.querySelector('.close-btn');
      if (close) {
        close.addEventListener('click', (e) => {
          e.preventDefault();
          hideAlert(el);
          clearTimeout(timer);
        });
      }
    }
  </script>

  <script>
    // --- VALIDASI WAJIB ISI SEMUA INPUT + ERROR MERAH & SCROLL KE PERTAMA YANG KOSONG ---
    document.addEventListener("DOMContentLoaded", () => {
      const form = document.getElementById("formSekolah");
      const inputs = form.querySelectorAll(".form-control:not([type='file'])");
      const checkbox = document.getElementById("confirm");
      const btnSubmit = form.querySelector("button[type='submit']");

      inputs.forEach(inp => {
        const err = document.createElement("div");
        err.className = "error-text";
        err.textContent = "Wajib diisi.";
        inp.insertAdjacentElement("afterend", err);
      });

      function setFieldState(inp) {
        const err = inp.nextElementSibling;
        const empty = inp.value.trim() === "";
        if (empty) {
          inp.classList.add("error");
          if (err && err.classList.contains("error-text")) err.style.display = "block";
        } else {
          inp.classList.remove("error");
          if (err && err.classList.contains("error-text")) err.style.display = "none";
        }
      }

      function checkAllFilled() {
        let all = true;
        inputs.forEach(inp => {
          if (inp.value.trim() === "") all = false;
        });
        return all;
      }

      function updateState() {
        inputs.forEach(setFieldState);

        if (checkAllFilled()) {
          checkbox.disabled = false;
        } else {
          checkbox.checked = false;
          checkbox.disabled = true;
          btnSubmit.disabled = true;
          btnSubmit.style.opacity = "0.6";
          btnSubmit.style.cursor = "not-allowed";
        }

        if (!checkbox.disabled && checkbox.checked) {
          btnSubmit.disabled = false;
          btnSubmit.style.opacity = "1";
          btnSubmit.style.cursor = "pointer";
        }
      }

      checkbox.disabled = true;
      btnSubmit.disabled = true;
      btnSubmit.style.opacity = "0.6";
      btnSubmit.style.cursor = "not-allowed";
      updateState();

      inputs.forEach(inp => {
        inp.addEventListener("input", () => {
          setFieldState(inp);
          updateState();
        });
        inp.addEventListener("blur", () => setFieldState(inp));
      });

      checkbox.addEventListener("change", updateState);

      form.addEventListener("submit", (e) => {
        if (!checkAllFilled()) {
          e.preventDefault();
          let firstInvalid = null;
          inputs.forEach(inp => {
            if (inp.value.trim() === "") {
              setFieldState(inp);
              if (!firstInvalid) firstInvalid = inp;
            }
          });
          if (firstInvalid) {
            firstInvalid.focus();
            firstInvalid.scrollIntoView({
              behavior: "smooth",
              block: "center"
            });
          }
        }
      });
    });
  </script>

  <!-- CropperJS (CDN) -->
  <script
    src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"
    referrerpolicy="no-referrer"></script>

  <script>
    // ============================
    // FITUR CROP SEBELUM UPLOAD LOGO
    // ============================
    document.addEventListener('DOMContentLoaded', () => {
      const input = document.getElementById('logoInput');
      const imgPreview = document.getElementById('logoPreview');

      const overlay = document.getElementById('cropOverlay');
      const cropImg = document.getElementById('cropImage');

      const btnClose = document.getElementById('cropCloseBtn');
      const btnCancel = document.getElementById('btnCancelCrop');
      const btnApply = document.getElementById('btnApplyCrop');

      const btnZoomIn = document.getElementById('btnZoomIn');
      const btnZoomOut = document.getElementById('btnZoomOut');
      const btnRotate = document.getElementById('btnRotate');
      const btnReset = document.getElementById('btnReset');

      if (!input || !imgPreview || !overlay || !cropImg) return;

      let cropper = null;
      let objectUrl = null;
      let lastSelectedFile = null;

      function openOverlay() {
        overlay.classList.add('show');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
      }

      function closeOverlay() {
        overlay.classList.remove('show');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
      }

      function cleanupUrl() {
        if (objectUrl) {
          URL.revokeObjectURL(objectUrl);
          objectUrl = null;
        }
      }

      function destroyCropper() {
        if (cropper) {
          cropper.destroy();
          cropper = null;
        }
      }

      function resetFileInput() {
        input.value = '';
        lastSelectedFile = null;
      }

      input.addEventListener('change', (e) => {
        const f = e.target.files && e.target.files[0];
        if (!f) return;

        const okTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!okTypes.includes(f.type)) {
          showUiAlert('danger', '❌ Format gambar harus JPG / PNG / WebP.');
          resetFileInput();
          return;
        }

        const max = 10 * 1024 * 1024;
        if (f.size > max) {
          showUiAlert('danger', '❌ Ukuran logo melebihi 10MB.');
          resetFileInput();
          return;
        }

        lastSelectedFile = f;

        cleanupUrl();
        objectUrl = URL.createObjectURL(f);
        cropImg.src = objectUrl;

        openOverlay();

        cropImg.onload = () => {
          destroyCropper();
          cropper = new Cropper(cropImg, {
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            background: false,
            responsive: true,
            aspectRatio: 1
          });
        };
      });

      btnZoomIn && btnZoomIn.addEventListener('click', () => cropper && cropper.zoom(0.1));
      btnZoomOut && btnZoomOut.addEventListener('click', () => cropper && cropper.zoom(-0.1));
      btnRotate && btnRotate.addEventListener('click', () => cropper && cropper.rotate(90));
      btnReset && btnReset.addEventListener('click', () => cropper && cropper.reset());

      function cancelCrop() {
        destroyCropper();
        cleanupUrl();
        closeOverlay();
        resetFileInput();
      }

      btnCancel && btnCancel.addEventListener('click', cancelCrop);
      btnClose && btnClose.addEventListener('click', cancelCrop);

      btnApply && btnApply.addEventListener('click', () => {
        if (!cropper || !lastSelectedFile) return;

        const mime = lastSelectedFile.type || 'image/png';

        const canvas = cropper.getCroppedCanvas({
          width: 600,
          height: 600,
          imageSmoothingEnabled: true,
          imageSmoothingQuality: 'high'
        });

        if (!canvas) return;

        canvas.toBlob((blob) => {
          if (!blob) return;

          const ext = (mime === 'image/jpeg') ? 'jpg' : (mime === 'image/webp') ? 'webp' : 'png';
          const newFile = new File([blob], 'logo-crop.' + ext, {
            type: mime
          });

          const dt = new DataTransfer();
          dt.items.add(newFile);
          input.files = dt.files;

          const previewUrl = URL.createObjectURL(newFile);
          imgPreview.src = previewUrl;
          imgPreview.onload = () => URL.revokeObjectURL(previewUrl);

          showUiAlert('success', '✅ Logo berhasil diproses. Jangan lupa klik Simpan.');

          destroyCropper();
          cleanupUrl();
          closeOverlay();

          lastSelectedFile = null;
        }, mime, 0.92);
      });
    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // PHP alert saat page load
      const firstAlert = document.querySelector('.alert[data-auto-scroll="1"]') || document.querySelector('.alert');
      if (firstAlert) {
        animateInAlert(firstAlert, true);
      }

      const alerts = document.querySelectorAll('.alert');
      if (!alerts.length) return;

      alerts.forEach(alert => {
        const timer = setTimeout(() => {
          hideAlert(alert);
        }, 4000);

        const close = alert.querySelector('.close-btn');
        if (close) {
          close.addEventListener('click', (e) => {
            e.preventDefault();
            hideAlert(alert);
            clearTimeout(timer);
          });
        }
      });
    });
  </script>

  <?php include '../../includes/footer.php'; ?>
</body>

</html>