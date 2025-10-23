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
            <h5 class="mb-1 fw-semibold fs-4" style=" text-align: center">Data Siswa</h5>

            <!-- Tombol di kanan -->
            <div class="ms-auto d-flex gap-2 action-buttons">
              <a href="tambah_siswa.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1 p-2 pe-3 fw-semibold" style="border-radius: 5px;">
                <i class="fa-solid fa-plus fa-lg"></i>
                Tambah
              </a>

              <a href="data_siswa_import.php" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
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

          <!-- Tabel Data Siswa -->
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped align-middle">
                <thead style="background-color:#1d52a2" class="text-center text-white">
                  <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>Absen</th>
                    <th>Nama</th>
                    <th>NIS</th>
                    <th>Wali Kelas</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="text-center"><input type="checkbox" class="row-check"></td>
                    <td>1</td>
                    <td>Fahrul</td>
                    <td>123456</td>
                    <td>Bu Sutrisna</td>
                    <td class="text-center">
                      <a href="edit_siswa.php?id=1" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1" style="font-size: 15px;">
                        <i class="bi bi-pencil-square"></i><span>Edit</span>
                      </a>
                      <a href="hapus_mapel.php?id=1" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1"
                        onclick="return confirm('Yakin ingin menghapus data ini?');" style="font-size: 15px;">
                        <i class="bi bi-trash"></i><span>Del</span>
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center"><input type="checkbox" class="row-check"></td>
                    <td>2</td>
                    <td>Ahmad</td>
                    <td>654321</td>
                    <td>Pak Budi</td>
                    <td class="text-center">
                      <a href="edit_siswa.php?id=2" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1" style="font-size: 15px;">
                        <i class="bi bi-pencil-square"></i><span>Edit</span>
                      </a>
                      <a href="hapus_mapel.php?id=2" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1"
                        onclick="return confirm('Yakin ingin menghapus data ini?');" style="font-size: 15px;">
                        <i class="bi bi-trash"></i><span>Del</span>
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Tombol hapus terpilih -->
            <div class="mt-2">
              <button id="deleteSelected" class="btn btn-danger btn-sm">
                <i class="bi bi-trash"></i> Hapus Terpilih
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <style>
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
        justify-content: center;
      }

      .action-buttons a,
      .action-buttons button {
        width: auto;
      }
    }
  </style>

  <script>
    // Fungsi pilih semua
    const selectAll = document.getElementById('selectAll');
    const rowChecks = document.querySelectorAll('.row-check');

    selectAll.addEventListener('change', function() {
      rowChecks.forEach(cb => cb.checked = this.checked);
    });

    rowChecks.forEach(cb => {
      cb.addEventListener('change', () => {
        const allChecked = [...rowChecks].every(c => c.checked);
        selectAll.checked = allChecked;
      });
    });

    // Fungsi hapus terpilih
    document.getElementById('deleteSelected').addEventListener('click', function() {
      const selected = document.querySelectorAll('.row-check:checked');
      if (selected.length === 0) {
        alert('Tidak ada data yang dipilih!');
        return;
      }

      if (confirm(`Yakin ingin menghapus ${selected.length} data terpilih?`)) {
        selected.forEach(cb => {
          cb.closest('tr').remove();
        });
      }
    });
  </script>

  <?php include '../../includes/footer.php'; ?>

