<?php
include '../../includes/header.php';
?>

<body>

<?php
include '../../includes/navbar.php';
?>

  

<main class="content">
  <div class="cards row">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Data Guru</h5>
        </div>
        <div class="mt-3 bg-white d-flex justify-content-between align-items-center flex-wrap">
          <div class="d-flex align-items-center gap-2">
            <input type="text" class="form-control form-control-sm" placeholder="Cari nama guru..." style="width: 200px;">
            <button class="btn btn-secondary btn-sm">
              <i class="bi bi-search"></i> Cari
            </button>
          </div>

          <a href="data_guru_tambah.php" class="btn btn-primary btn-sm" style="margin-right:20px">
            <i class="bi bi-plus-lg"></i> Tambah Guru
          </a>
        </div>

        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
              <thead style="background-color:  #1d52a2">
                <tr class="text-center" style="color:white">
                  <th>No</th>
                  <th>Nama Guru</th>
                  <th>Mata Pelajaran</th>
                  <th>No. Telepon</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>1</td>
                  <td>Ahmad Fauzi</td>
                  <td>Matematika</td>
                  <td>0812-3456-7890</td>
                  <td class="text-center">
                    <a href="edit_guru.php?id=1" class="btn btn-warning btn-sm">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="hapus_guru.php?id=1" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?');">
                      <i class="bi bi-trash"></i>
                    </a>
                  </td>
                </tr>
                <!-- Tambahkan data lain dari database di sini -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
<?php include '../../includes/footer.php'; ?>