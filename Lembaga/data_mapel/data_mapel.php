<?php
include '../../includes/header.php';
?>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <main class="content">
    <div class="cards row" style="margin-top:-50px;">
      <div class="col-12">
        <div class="card shadow-sm" style="border-radius:15px;">
          <div class="card-header bg-white border-0 p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
              <!-- Header Title -->
              <h5 class="fw-semibold fs-4 mb-0">Data Mata Pelajaran</h5>

              <!-- Tombol group -->
              <div class="d-flex flex-wrap gap-2 tombol-aksi">
                <a href="data_mapel_tambah.php"
                  class="btn btn-primary btn-sm d-flex align-items-center gap-1 fw-semibold p-2 px-3"
                  style="border-radius:5px;">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-plus" viewBox="0 0 16 16">
                    <path
                      d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4" />
                  </svg>
                  Tambah
                </a>

                <button id="importBtn"
                  class="btn btn-warning btn-sm d-flex align-items-center rounded-2 p-2 gap-1 px-3 fw-semibold">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                    class="bi bi-cloud-arrow-up-fill" viewBox="0 0 16 16">
                    <path
                      d="M8 2a5.53 5.53 0 0 0-3.594 1.342C3.64 3.999 3.085 4.86 2.942 5.723 1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2m.354 8.854.001-3.708a.5.5 0 0 0-1 0v3.708L6.208 9.707a.5.5 0 0 0-.708.707l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 1 0-.708-.707z" />
                  </svg>
                  Import
                </button>

                <button id="exportBtn"
                  class="btn btn-success btn-sm d-flex align-items-center rounded-2 p-2 gap-1 px-3 fw-semibold">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                    class="bi bi-cloud-arrow-down-fill" viewBox="0 0 16 16">
                    <path
                      d="M8 2a5.53 5.53 0 0 0-3.594 1.342C3.64 3.999 3.085 4.86 2.942 5.723 1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2m2.354 6.854-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 1 1 .708-.708L7.5 9.293V5.5a.5.5 0 0 1 1 0v3.793l1.146-1.147a.5.5 0 0 1 .708.708" />
                  </svg>
                  Export
                </button>
              </div>
            </div>
          </div>

          <!-- Search & Sort -->
          <div class="ms-3 me-3 bg-white d-flex justify-content-center align-items-center flex-wrap p-2 gap-2">
            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search"
              style="width: 200px;">
            <button id="searchBtn"
              class="btn btn-outline-secondary btn-sm p-2 rounded-3 d-flex align-items-center justify-content-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                class="bi bi-search" viewBox="0 0 16 16">
                <path
                  d="M11 6a5 5 0 1 0-2.9 4.7l3.85 3.85a1 1 0 0 0 1.414-1.414l-3.85-3.85A4.978 4.978 0 0 0 11 6zM6 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
              </svg>
            </button>
            <button id="sortBtn"
              class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 rounded-3">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                class="bi bi-sort-alpha-down" viewBox="0 0 16 16">
                <path fill-rule="evenodd"
                  d="M10.082 5.629 9.664 7H8.598l1.789-5.332h1.234L13.402 7h-1.12l-.419-1.371zm1.57-.785L11 2.687h-.047l-.652 2.157z" />
                <path
                  d="M12.96 14H9.028v-.691l2.579-3.72v-.054H9.098v-.867h3.785v.691l-2.567 3.72v.054h2.645zM4.5 2.5a.5.5 0 0 0-1 0v9.793l-1.146-1.147a.5.5 0 0 0-.708.708l2 1.999.007.007a.497.497 0 0 0 .7-.006l2-2a.5.5 0 0 0-.707-.708L4.5 12.293z" />
              </svg>
              Sort
            </button>
          </div>

          <!-- Tabel Data -->
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped align-middle">
                <thead style="background-color:#1d52a2" class="text-center text-white">
                  <tr>
                    <th>No</th>
                    <th>Nama Mata Pelajaran</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>1</td>
                    <td>Matematika</td>
                    <td class="text-center">
                      <a href="data_mapel_edit.php?id=1" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil-square">Edit</i>
                      </a>
                      <a href="hapus_mapel.php?id=1" class="btn btn-danger btn-sm"
                        onclick="return confirm('Yakin ingin menghapus data ini?');">
                        <i class="bi bi-trash">Del</i>
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
    /* RESPONSIVE KHUSUS UNTUK TOMBOL */
    @media (max-width: 768px) {
      .tombol-aksi {
        width: 100%;
        justify-content: center !important;
        margin-top: 10px;
      }

      .card-header h5 {
        width: 100%;
        text-align: center;
      }
    }
  </style>

  <?php include '../../includes/footer.php'; ?>

