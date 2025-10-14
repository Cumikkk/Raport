<?php
include '../../includes/header.php';
?>

<body>
<?php
include '../../includes/navbar.php';
?>

<!-- Wrapper utama -->
<div class="dk-page">
  <div class="dk-main">
    <div class="dk-content-box">
      <div class="container-fluid py-4">

        <div class="d-flex justify-content-center align-items-start" style="min-height: 100vh;">
          <div class="card shadow-sm border-0 rounded-4 w-100" style="max-width: 600px; margin-left: 20px;">
            <div class="card-body">
              <h5 class="fw-bold mb-4 text-center text-sm-start">Data Semester</h5>

              <form class="w-100">
                <!-- Tahun Ajaran Aktif -->
                <div class="mb-3">
                  <label for="tahunAjaran" class="form-label fw-semibold">Tahun Ajaran Aktif</label>
                  <select id="tahunAjaran" class="form-select">
                    <option selected>2025/2026</option>
                    <option>2024/2025</option>
                    <option>2023/2024</option>
                  </select>
                </div>

                <!-- Sistem Penilaian -->
                <div class="mb-3">
                  <label for="sistemPenilaian" class="form-label fw-semibold">Sistem Penilaian</label>
                  <select id="sistemPenilaian" class="form-select">
                    <option selected>Sistem Paket</option>
                    <option>Sistem SKS</option>
                  </select>
                </div>

                <!-- Semester Aktif -->
                <div class="mb-3">
                  <label for="semesterAktif" class="form-label fw-semibold">Semester Aktif</label>
                  <select id="semesterAktif" class="form-select">
                    <option selected>Ganjil - Paket</option>
                    <option>Genap - Paket</option>
                     <option>Genap</option>
                     <option>Ganjil</option>
                  </select>
                </div>

                <!-- Tombol Simpan -->
                <div class="d-flex justify-content-end mt-4">
                  <button type="submit" class="btn btn-success px-4 py-2 fw-semibold">
                    Simpan
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<style>
body {
  background-color: #f8f9fa;
}

/* RESPONSIVE FIXES */
@media (max-width: 992px) {
  .card {
    margin-left: 0 !important;
    width: 100% !important;
  }
  .card-body {
    padding: 20px 16px;
  }
  select.form-select,
  input.form-control {
    font-size: 14px;
  }
  button.btn {
    width: 100%;
  }
  h5 {
    text-align: center;
  }
}
</style>
