<?php
include '../../includes/header.php';
include '../../koneksi.php';
?>

<body>
  <?php include '../../includes/navbar.php'; ?>

  <main class="content">
    <div class="cards row" style="margin-top: -50px;">
      <div class="col-12">
        <div class="card shadow-sm" style="border-radius: 15px;">
          <div class="mt-0 d-flex align-items-center flex-wrap mb-0 p-3 top-bar">
            <!-- Judul di kiri -->
            <h5 class="mb-1 fw-semibold fs-4" style=" text-align: center">Data Ekstrakurikuler</h5>

            <!-- Tombol di kanan -->
            <div class="ms-auto d-flex gap-2 action-buttons">
              <a href="data_ekstra_tambah.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1 p-2 pe-3 fw-semibold" style="border-radius: 5px;" data-bs-toggle="modal" data-bs-target="#tambahEkstraModal">
                <i class="fa-solid fa-plus fa-lg"></i>
                Tambah
              </a>

              <a href="data_ekstra_import.php" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
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
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                <path
                  d="M11 6a5 5 0 1 0-2.9 4.7l3.85 3.85a1 1 0 0 0 1.414-1.414l-3.85-3.85A4.978 4.978 0 0 0 11 6zM6 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
              </svg>
            </button>

            <button id="sortBtn" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 rounded-3">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                fill="currentColor" class="bi bi-sort-alpha-down" viewBox="0 0 16 16">
                <path fill-rule="evenodd"
                  d="M10.082 5.629 9.664 7H8.598l1.789-5.332h1.234L13.402 7h-1.12l-.419-1.371zm1.57-.785L11 2.687h-.047l-.652 2.157z" />
                <path
                  d="M12.96 14H9.028v-.691l2.579-3.72v-.054H9.098v-.867h3.785v.691l-2.567 3.72v.054h2.645zM4.5 2.5a.5.5 0 0 0-1 0v9.793l-1.146-1.147a.5.5 0 0 0-.708.708l2 1.999.007.007a.497.497 0 0 0 .7-.006l2-2a.5.5 0 0 0-.707-.708L4.5 12.293z" />
              </svg>
              Sort
            </button>
          </div>
          <!-- Modal Tambah Ekstrakurikuler -->
          <div class="modal fade" id="tambahEkstraModal" tabindex="-1" aria-labelledby="tambahEkstraLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content shadow-lg border-0">

                <!-- HEADER -->
                <div class="modal-header bg-primary text-white">
                  <h5 class="modal-title fw-bold" id="tambahEkstraLabel">
                    <i class="fa fa-book"></i> Tambah Ekstrakurikuler
                  </h5>
                </div>

                <!-- FORM -->
                <form action="ekstra_tambah_proses.php" method="POST">
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label fw-semibold">Nama Ekstrakurikuler</label>
                      <input type="text" name="nama_ekstra" class="form-control" placeholder="Nama Ekstrakurikuler" required>
                    </div>
                  </div>

                  <div class="modal-footer">
                    <div class="d-flex w-100 gap-2">
                      <button type="submit" class="btn btn-success w-50">
                        <i class="fa fa-save"></i>
                        Simpan</button>
                      <button type="button" class="btn btn-danger w-50" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i>
                        Batal</button>
                    </div>
                  </div>
                </form>

              </div>
            </div>
          </div>


          <!-- Tabel Data Mapel -->
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped align-middle">
                <thead style="background-color:#1d52a2" class="text-center text-white">
                  <tr>
                    <th>No</th>
                    <th>Nama Ekstrakurikuler</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $no = 0;
                  $query = "SELECT * FROM ekstrakurikuler ORDER BY id_ekstrakurikuler ASC";
                  $result = mysqli_query($koneksi, $query);
                  if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                      $no++;
                      echo "
                        <tr>
                          <td>{$no}</td>
                          <td>{$row['nama_ekstrakurikuler']}</td>
                          <td class='text-center'>
                            <button type='button' class='btn btn-warning btn-sm'
                              onclick='editEkstra({$row['id_ekstrakurikuler']}, \"{$row['nama_ekstrakurikuler']}\")'>
                              <i class='bi bi-pencil-square'></i> Edit
                            </button>

                            <a href='hapus_ekstra.php?id={$row['id_ekstrakurikuler']}' class='btn btn-danger btn-sm'
                              onclick=\"return confirm('Yakin ingin menghapus data ini?');\">
                              <i class='bi bi-trash'></i> Del
                            </a>
                          </td>
                        </tr>
                        ";
                    }
                  } else {
                    echo "<tr><td colspan='3' class='text-center'>Belum ada data</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Modal Edit Ekstrakurikuler -->
          <div class="modal fade" id="editEkstraModal" tabindex="-1" aria-labelledby="editEkstraLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-warning text-dark">
                  <h5 class="modal-title fw-bold" id="editEkstraLabel">
                    <i class="fa fa-edit"></i> Edit Ekstrakurikuler
                  </h5>
                </div>

                <form action="ekstra_edit_proses.php" method="POST">
                  <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                      <label class="form-label fw-semibold">Nama Ekstrakurikuler</label>
                      <input type="text" name="nama_ekstra" id="edit_nama_ekstra" class="form-control" required>
                    </div>
                  </div>

                  <div class="modal-footer">
                    <div class="d-flex w-100 gap-2">
                      <button type="submit" class="btn btn-success w-50">
                        <i class="fa fa-save"></i>
                        Update</button>
                      <button type="button" class="btn btn-danger w-50" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i>
                        Batal</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>



        </div>
      </div>
    </div>
  </main>
  <script>
    function editEkstra(id, nama) {
      // Isi field modal
      document.getElementById('edit_id').value = id;
      document.getElementById('edit_nama_ekstra').value = nama;

      // Buka modal edit
      const modal = new bootstrap.Modal(document.getElementById('editEkstraModal'));
      modal.show();
    }
  </script>


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
        justify-content: center;
      }

      .action-buttons a,
      .action-buttons button {
        width: auto;
      }
    }
  </style>

  <?php include '../../includes/footer.php'; ?>