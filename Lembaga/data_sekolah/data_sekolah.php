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
    }

    body {
      font-family: "Poppins", sans-serif;
      background: var(--bg);
    }

    /* wrapper utama supaya konten selalu di tengah area konten */
    .sekolah-wrapper {
      max-width: 1100px;
      margin: 0 auto;
      padding: 24px 20px;
    }

    .page-title {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 18px;
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
      padding: 22px;
    }

    .grid {
      display: grid;
      gap: 18px;
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

    /* Ganti .row -> .form-row supaya tidak bentrok dengan Bootstrap */
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
      gap: 16px;
    }

    .alert {
      padding: 12px 14px;
      border-radius: 12px;
      margin-bottom: 16px;
      font-size: 14px;
      /* transition untuk efek menghilang halus */
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
      /* untuk posisi tombol X */
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

    /* kelas untuk menyembunyikan alert (dipakai JS) */
    .alert-hide {
      opacity: 0;
      transform: translateY(-4px);
      max-height: 0;
      margin: 0;
      padding-top: 0;
      padding-bottom: 0;
    }

    /* Tombol X di alert */
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

    /* Sticky actions on mobile */
    .actions {
      position: sticky;
      bottom: 0;
      background: var(--card);
      padding: 12px;
      border-radius: 12px;
      box-shadow: 0 -6px 18px rgba(0, 0, 0, .06);
    }

    /* Small helper for required */
    .req {
      color: #e11d48;
    }

    /* Error styling */
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
  </style>

  <div class="dk-page">
    <div class="dk-main">
      <div class="dk-content-box">
        <div class="container-fluid py-3">
          <div class="sekolah-wrapper">

            <div class="page-title">
            </div>

            <?php if (isset($_GET['status'])): ?>
              <?php if ($_GET['status'] === 'success'): ?>
                <div class="alert alert-success">
                  <span class="close-btn">&times;</span>
                  ✅ Data sekolah berhasil disimpan.
                </div>
              <?php else: ?>
                <div class="alert alert-danger">
                  <span class="close-btn">&times;</span>
                  ❌ Gagal menyimpan data sekolah
                  <?= isset($_GET['msg']) ? ': ' . htmlspecialchars($_GET['msg']) : '' ?>.
                </div>
              <?php endif; ?>
            <?php endif; ?>

            <div class="grid">
              <!-- Kiri: Form utama -->
              <div class="card">
                <h2 class="section-title">Data Sekolah</h2>
                <form action="save_data_sekolah.php" method="POST" enctype="multipart/form-data" class="stack" id="formSekolah">
                  <?php if (!empty($data['id_sekolah'])): ?>
                    <input type="hidden" name="id_sekolah" value="<?= (int)$data['id_sekolah'] ?>">
                  <?php endif; ?>
                  <input type="hidden" name="old_logo" value="<?= htmlspecialchars($data['logo_sekolah'] ?? '') ?>">

                  <div>
                    <label class="form-label">Nama Sekolah <span class="req">*</span></label>
                    <input
                      type="text"
                      name="nama_sekolah"
                      class="form-control"
                      required
                      autocomplete="organization"
                      value="<?= htmlspecialchars($data['nama_sekolah'] ?? '') ?>">
                  </div>

                  <div class="form-row cols-2">
                    <div>
                      <label class="form-label">NSM <span class="req">*</span></label>
                      <input
                        type="text"
                        name="nsm_sekolah"
                        class="form-control"
                        inputmode="numeric"
                        required
                        value="<?= htmlspecialchars($data['nsm_sekolah'] ?? '') ?>">
                    </div>
                    <div>
                      <label class="form-label">NPSN <span class="req">*</span></label>
                      <input
                        type="text"
                        name="npsn_sekolah"
                        class="form-control"
                        inputmode="numeric"
                        required
                        value="<?= htmlspecialchars($data['npsn_sekolah'] ?? '') ?>">
                    </div>
                  </div>

                  <div>
                    <label class="form-label">Alamat <span class="req">*</span></label>
                    <textarea
                      name="alamat_sekolah"
                      class="form-control"
                      rows="2"
                      autocomplete="street-address"
                      required><?= htmlspecialchars($data['alamat_sekolah'] ?? '') ?></textarea>
                  </div>

                  <div class="form-row cols-2">
                    <div>
                      <label class="form-label">Telepon <span class="req">*</span></label>
                      <input
                        type="text"
                        name="no_telepon_sekolah"
                        class="form-control"
                        inputmode="tel"
                        placeholder="08xxxxxxxxxx"
                        required
                        value="<?= htmlspecialchars($data['no_telepon_sekolah'] ?? '') ?>">
                    </div>
                    <div>
                      <label class="form-label">Kecamatan <span class="req">*</span></label>
                      <input
                        type="text"
                        name="kecamatan_sekolah"
                        class="form-control"
                        required
                        value="<?= htmlspecialchars($data['kecamatan_sekolah'] ?? '') ?>">
                    </div>
                  </div>

                  <div class="form-row cols-2">
                    <div>
                      <label class="form-label">Kabupaten/Kota <span class="req">*</span></label>
                      <input
                        type="text"
                        name="kabupaten_atau_kota_sekolah"
                        class="form-control"
                        required
                        value="<?= htmlspecialchars($data['kabupaten_atau_kota_sekolah'] ?? '') ?>">
                    </div>
                    <div>
                      <label class="form-label">Provinsi <span class="req">*</span></label>
                      <input
                        type="text"
                        name="provinsi_sekolah"
                        class="form-control"
                        required
                        value="<?= htmlspecialchars($data['provinsi_sekolah'] ?? '') ?>">
                    </div>
                  </div>

                  <div>
                    <label class="form-label">Logo Sekolah (opsional)</label>
                    <input
                      type="file"
                      name="logo_sekolah"
                      id="logoInput"
                      class="form-control"
                      accept=".jpg,.jpeg,.png,.webp">
                    <div class="hint">Format: JPG/PNG/WebP · Maks 10MB.</div>
                  </div>

                  <div class="actions">
                    <label class="form-label" style="display:flex;align-items:center;gap:8px">
                      <input
                        class="form-check-input"
                        type="checkbox"
                        value="1"
                        id="confirm"
                        required>
                      <span>Saya yakin akan menyimpan perubahan</span>
                    </label>
                    <button type="submit" class="btn btn-success" style="margin-top:10px">
                      Simpan
                    </button>
                  </div>
                </form>
              </div>

              <!-- Kanan: Preview Logo & Info -->
              <div class="card">
                <h2 class="section-title">Logo</h2>
                <div class="logo-wrap">
                  <img
                    id="logoPreview"
                    src="uploads/<?= htmlspecialchars(($data['logo_sekolah'] ?? '') ?: 'default.png') ?>"
                    class="logo-preview"
                    alt="Logo Sekolah">
                  <div class="hint">Gambar pratinjau akan berubah saat Anda memilih file.</div>
                </div>

                <hr style="border:none;border-top:1px solid #eee;margin:18px 0">
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

  <script>
    // --- VALIDASI WAJIB ISI SEMUA INPUT + ERROR MERAH & SCROLL KE PERTAMA YANG KOSONG ---
    document.addEventListener("DOMContentLoaded", () => {
      const form = document.getElementById("formSekolah");
      const inputs = form.querySelectorAll(".form-control:not([type='file'])"); // semua input kecuali file
      const checkbox = document.getElementById("confirm");
      const btnSubmit = form.querySelector("button[type='submit']");

      // Tambahkan elemen error-text di bawah setiap input/textarea
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
          if (err && err.classList.contains("error-text")) {
            err.style.display = "block";
          }
        } else {
          inp.classList.remove("error");
          if (err && err.classList.contains("error-text")) {
            err.style.display = "none";
          }
        }
      }

      function checkAllFilled() {
        let all = true;
        inputs.forEach(inp => {
          if (inp.value.trim() === "") {
            all = false;
          }
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

      // Awal: set state (kasih merah kalau ada yang kosong)
      checkbox.disabled = true;
      btnSubmit.disabled = true;
      btnSubmit.style.opacity = "0.6";
      btnSubmit.style.cursor = "not-allowed";
      updateState();

      // Event: setiap ketik input
      inputs.forEach(inp => {
        inp.addEventListener("input", () => {
          setFieldState(inp);
          updateState();
        });
        inp.addEventListener("blur", () => {
          setFieldState(inp);
        });
      });

      // Event: checkbox diubah
      checkbox.addEventListener("change", updateState);

      // Safety: kalau tetap submit & ada yang kosong -> scroll ke field pertama yang kosong
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

  <script>
    // Preview file logo instan
    const input = document.getElementById('logoInput');
    const img = document.getElementById('logoPreview');
    if (input) {
      input.addEventListener('change', (e) => {
        const f = e.target.files && e.target.files[0];
        if (!f) return;
        const url = URL.createObjectURL(f);
        img.src = url;
        img.onload = () => URL.revokeObjectURL(url);
      });
    }
  </script>

  <script>
    // --- AUTO DISMISS ALERT + TOMBOL X ---
    document.addEventListener('DOMContentLoaded', () => {
      const alerts = document.querySelectorAll('.alert');
      if (!alerts.length) return;

      alerts.forEach(alert => {
        // Auto hilang setelah 4 detik
        const timer = setTimeout(() => {
          alert.classList.add('alert-hide');
        }, 4000);

        // Tombol X untuk menutup
        const close = alert.querySelector('.close-btn');
        if (close) {
          close.addEventListener('click', (e) => {
            e.preventDefault();
            alert.classList.add('alert-hide');
            clearTimeout(timer);
          });
        }
      });
    });
  </script>

  <?php include '../../includes/footer.php'; ?>
</body>

</html>