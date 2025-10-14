<?php
include '../../includes/header.php';
?>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <main class="content">
    <div class="cards row" style="margin-top: -50px;">
      <div class="col-12">
        <div class="card shadow-sm" style="border-radius: 15px;">
          <div class="mt-0 d-flex align-items-center flex-wrap mb-0 p-3 top-bar">
            <!-- Judul di kiri -->
            <h5 class="mb-1 fw-semibold fs-4" style=" text-align: center">Data Guru</h5>

            <!-- Tombol di kanan -->
            <div class="ms-auto d-flex gap-2 action-buttons">
              <a href="data_guru_tambah.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1 p-2 pe-3 fw-semibold" style="border-radius: 5px;">
                <i class="fa-solid fa-plus fa-lg"></i>
                Tambah
              </a>

              <a href="data_guru_import.php" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-arrow-down fa-lg"></i>
                <span>Import</span>
              </a>

              <button id="exportBtn" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                 <i class="fa-solid fa-file-arrow-up fa-lg"></i>
                Export
              </button>
            </div>
          </div>

          <!-- Search & Sort -->
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

          <!-- Tabel Data Mapel -->
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped align-middle">
                <thead style="background-color:#1d52a2" class="text-center text-white">
                  <tr>
                    <th>No</th>
                    <th>Nama Guru</th>
                    <th>Jabatan</th>
                    <th>No Telepon</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>1</td>
                    <td>Bahasa Inggris</td>
                    <td>Honorer</td>
                    <td>0896262728</td>
                    <td class="text-center">
                      <a href="data_guru_edit.php?id=1"
                        class="btn btn-warning btn-sm me-1 d-inline-flex align-items-center justify-content-center gap-1 px-2 py-1 me-1"
                        style="font-size: 15px;">
                        <i class="bi bi-pencil-square" style="font-size: 15px;"></i>
                        <span>Edit</span>
                      </a>
                      <a href="hapus_mapel.php?id=1"
                        class="btn btn-danger btn-sm me-1 d-inline-flex align-items-center justify-content-center gap-1 px-2 py-1"
                        style="font-size: 15px;"
                        onclick="return confirm('Yakin ingin menghapus data ini?');">
                        <i class="bi bi-trash" style="font-size: 15px;"></i>
                        <span>Del</span>
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <style>
    /* Tambahan CSS Responsif */
    @media (max-width: 768px) {
      .top-bar {
        flex-direction: column !important;
        align-items: flex-center !important;
      }

      .action-buttons {
        margin-top: 10px;
        width: 100%;
        justify-content: center !important;
        flex-wrap: wrap;
      }

      .h5 {
        justify-content:center;
      }

      .action-buttons a,
      .action-buttons button {
        width: auto;
      }
    }
  </style>

  <?php include '../../includes/footer.php'; ?>
</body>
