<?php
include '../../includes/header.php';
?>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <main class="content">
    <div class="cards row" style="margin-top: -50px;">
      <div class="col-12">
        <div class="card shadow-sm" style="border-radius: 15px;">

          <!-- ===== BAR ATAS ===== -->
          <div class="mt-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between p-3 top-bar">

            <!-- Kiri: Judul dan Dropdown -->
            <div class="d-flex flex-column align-items-md-start align-items-center text-md-start text-center mb-2 mb-md-0">
              <h5 class="mb-2 fw-semibold fs-4">Cetak Rapor Siswa</h5>

              <!-- ===== FILTER CONTAINER ===== -->
              <div class="filter-container d-flex flex-column align-items-center align-items-md-start gap-2">

                <!-- Dropdown Tingkat -->
                <div class="filter-group d-flex align-items-center gap-2">
                  <label for="tingkat" class="form-label fw-semibold mb-0">Tingkat</label>
                  <select id="tingkat" class="form-select dk-select" style="width: 140px;">
                    <option selected disabled>--Pilih--</option>
                    <option>X</option>
                    <option>XI</option>
                    <option>XII</option>
                  </select>
                </div>

                <!-- Dropdown Kelas (sekarang di bawah Tingkat) -->
                <div class="filter-group d-flex align-items-center gap-2">
                  <label for="kelas" class="form-label fw-semibold mb-0">Kelas</label>
                  <select id="kelas" class="form-select dk-select" style="width: 160px;">
                    <option selected disabled>--Pilih--</option>
                    <option>IPA 1</option>
                    <option>IPA 2</option>
                    <option>IPS 1</option>
                    <option>IPS 2</option>
                  </select>
                </div>

              </div>
            </div>

            <!-- Kanan: Tombol -->
            <div class="d-flex gap-2 flex-wrap justify-content-md-end justify-content-center mt-3 mt-md-0 action-buttons">
              <a href="data_absensi_tambah.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1 px-3 fw-semibold" style="border-radius: 5px;">
                <i class="fa-solid fa-print fa-lg"></i> Print Raport
              </a>

              <a href="data_absensi_import.php" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-arrow-down fa-lg"></i> <span>Import</span>
              </a>
            </div>
          </div>

          <!-- ===== SEARCH & SORT ===== -->
          <div class="ms-3 me-3 bg-white d-flex justify-content-center align-items-center flex-wrap p-2 gap-2">
            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search" style="width: 200px;">
            <button id="searchBtn" class="btn btn-outline-secondary btn-sm p-2 rounded-3 d-flex align-items-center justify-content-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                <path d="M11 6a5 5 0 1 0-2.9 4.7l3.85 3.85a1 1 0 0 0 1.414-1.414l-3.85-3.85A4.978 4.978 0 0 0 11 6zM6 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
              </svg>
            </button>

            <button id="sortBtn" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 rounded-3">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sort-alpha-down" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M10.082 5.629 9.664 7H8.598l1.789-5.332h1.234L13.402 7h-1.12l-.419-1.371zm1.57-.785L11 2.687h-.047l-.652 2.157z" />
                <path d="M12.96 14H9.028v-.691l2.579-3.72v-.054H9.098v-.867h3.785v.691l-2.567 3.72v.054h2.645zM4.5 2.5a.5.5 0 0 0-1 0v9.793l-1.146-1.147a.5.5 0 0 0-.708.708l2 1.999.007.007a.497.497 0 0 0 .7-.006l2-2a.5.5 0 0 0-.707-.708L4.5 12.293z" />
              </svg>
              Sort
            </button>
          </div>

          <!-- ===== TABEL SISWA ===== -->
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped align-middle">
                <thead style="background-color:#1d52a2" class="text-center text-white">
                  <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>Absen</th>
                    <th>NISN</th>
                    <th>Nama</th>
                    <th>L/P</th>
                    <th>Komentar</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody class="text-center">
                  <tr>
                    <td><input type="checkbox" class="row-check"></td>
                    <td>1</td>
                    <td>1234556</td>
                    <td>Fahrul</td>
                    <td>L</td>
                    <td>Perlu diperbaiki lagi</td>
                    <td>
                      <a href="data_absensi_import.php?id=1" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1">
                        <i class="bi bi-pencil-square"></i>Edit
                      </a>
                      <a href="hapus_mapel.php?id=1" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1"
                        onclick="return confirm('Yakin ingin menghapus data ini?');">
                        <i class="bi bi-trash"></i>Del
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <td><input type="checkbox" class="row-check"></td>
                    <td>2</td>
                    <td>654321</td>
                    <td>Ahmad</td>
                    <td>L</td>
                    <td>Perlu diperbaiki lagi</td>
                    <td>
                      <a href="edit_siswa.php?id=2" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1">
                        <i class="bi bi-pencil-square"></i>Edit
                      </a>
                      <a href="hapus_mapel.php?id=2" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1"
                        onclick="return confirm('Yakin ingin menghapus data ini?');">
                        <i class="bi bi-trash"></i>Del
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Tombol Hapus Terpilih -->
            <div class="mt-2">
              <button id="deleteSelected" class="btn btn-danger btn-sm" disabled>
                <i class="bi bi-trash"></i> Hapus Terpilih
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- ===== SCRIPT CHECKBOX ===== -->
  <script>
    const selectAll = document.getElementById('selectAll');
    const rowChecks = document.querySelectorAll('.row-check');
    const deleteBtn = document.getElementById('deleteSelected');

    selectAll.addEventListener('change', function () {
      rowChecks.forEach(chk => chk.checked = this.checked);
      toggleDeleteButton();
    });

    rowChecks.forEach(chk => {
      chk.addEventListener('change', () => {
        selectAll.checked = [...rowChecks].every(c => c.checked);
        toggleDeleteButton();
      });
    });

    function toggleDeleteButton() {
      const adaYangDipilih = [...rowChecks].some(c => c.checked);
      deleteBtn.disabled = !adaYangDipilih;
    }
  </script>

  <!-- ===== STYLE RESPONSIVE ===== -->
  <style>
    .filter-container {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .filter-group label {
      min-width: 70px;
    }

    @media (min-width: 768px) {
      .filter-container {
        align-items: flex-start;
      }
    }

    @media (max-width: 768px) {
      .top-bar {
        flex-direction: column !important;
        align-items: center !important;
        text-align: center;
      }

      .filter-container {
        width: 100%;
        align-items: center !important;
      }

      .filter-group {
        justify-content: center !important;
        width: 100%;
      }

      .dk-select {
        width: 100% !important;
      }

      .action-buttons {
        justify-content: center !important;
        margin-top: 10px;
      }
    }
  </style>

  <?php include '../../includes/footer.php'; ?>
