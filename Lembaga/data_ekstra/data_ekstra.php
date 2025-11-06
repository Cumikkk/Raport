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

          <!-- Search  -->
          <div class="ms-3 me-3 bg-white d-flex justify-content-center align-items-center flex-wrap p-2 gap-2">
            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search" style="width: 200px;">
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
              <table class="table table-bordered table-striped align-middle" id ="dataTable">
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
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('searchInput');
      const table = document.getElementById('dataTable');
      const rows = table.querySelectorAll('tbody tr');

      searchInput.addEventListener('input', function() {
        const searchText = this.value.toLowerCase();
        let visibleCount = 0;

        rows.forEach(row => {
          const rowText = row.textContent.toLowerCase();
          if (rowText.includes(searchText)) {
            row.style.display = '';
            visibleCount++;
          } else {
            row.style.display = 'none';
          }
        });

        // Tambah pesan "tidak ada data"
        let noData = document.getElementById('noDataRow');
        if (!noData) {
          noData = document.createElement('tr');
          noData.id = 'noDataRow';
          noData.innerHTML = `<td colspan="${table.querySelectorAll('thead th').length}" class="text-center text-muted">Tidak ada data ditemukan</td>`;
          table.querySelector('tbody').appendChild(noData);
        }
        noData.style.display = visibleCount === 0 ? '' : 'none';
      });
    });

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