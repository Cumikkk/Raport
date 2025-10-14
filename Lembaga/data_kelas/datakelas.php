<?php
include '../../includes/header.php';
?>

<body>
<?php
include '../../includes/navbar.php';
?>

<div class="dk-page">
  <div class="dk-main">
    <div class="dk-content-box">
      <div class="container-fluid py-3">

        <!-- HEADER + CARI + TOMBOL -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
          <!-- Judul -->
          <h4 class="fw-bold mb-2 mb-sm-0">Data Kelas</h4>

          <!-- Kolom Pencarian + Tombol -->
          <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 search-group w-100 w-sm-auto">

            <!-- Form Cari -->
            <form class="d-flex flex-nowrap flex-grow-1 flex-sm-grow-0" role="search">
              <input class="form-control me-2" type="search"
                     placeholder="Cari kelas..." aria-label="Search" style="max-width:180px; flex-grow:1;">
              <button class="btn btn-outline-secondary btn-md px-" type="submit">
                <i class="fa fa-search"></i>
              </button>
            </form>

            <!-- Tombol Tambah & Import -->
            <div class="d-flex flex-wrap gap-2 mt-2 mt-sm-0 button-group">
              <a href="tambah_data.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1 p-2 pe-3 fw-semibold" style="border-radius: 5px;">
                <i class="fa-solid fa-plus fa-lg" style="justify-conten-center"></i>
                Tambah
              </a>

              <a href="import.php" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-arrow-down fa-lg"></i>
                <span>Import</span>
              </a>

              <button id="exportBtn" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                 <i class="fa-solid fa-file-arrow-up fa-lg"></i>
                Export
              </button>
            </div>
          </div>
        </div>

        <!-- FILTER TINGKAT -->
        <div class="row mb-3">
          <div class="col-md-3">
            <label for="tingkat" class="form-label fw-semibold">Tingkat</label>
            <select id="tingkat" class="form-select dk-select">
              <option>--Pilih--</option>
              <option>X</option>
              <option>XI</option>
              <option>XII</option>
            </select>
          </div>
        </div>

        <!-- TABEL DATA -->
        <div class="table-responsive">
          <table id="dataKelas" class="table dk-table table-bordered table-striped align-middle w-100">
            <thead class="table-light">
              <tr style="color:white">
                <th></th>
                <th>No</th>
                <th>Nama Kelas</th>
                <th>Jumlah Siswa</th>
                <th>Wali Kelas</th>
                <th>Tingkat</th>
                <th>Jurusan</th>
                <th>Jenis</th>
                <th>Kurikulum</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><input type="checkbox" class="row-checkbox"></td>
                <td>1</td>
                <td>XII IPA 1</td>
                <td>23</td>
                <td>Irsyal Velani, S.Si.</td>
                <td>XII</td>
                <td>UMUM</td>
                <td>Paket</td>
                <td>Kurmer</td>
                <td>
                  <a class="btn btn-warning btn-sm me-1" href="edit_data.php">Edit</a>
                  <button class="btn btn-danger btn-sm">Del</button>
                </td>
              </tr>
              <tr>
                <td><input type="checkbox" class="row-checkbox"></td>
                <td>2</td>
                <td>XII IPA 2</td>
                <td>0</td>
                <td>M. Masyfu’ Auliya’Ihaq, S.Pd</td>
                <td>XII</td>
                <td>UMUM</td>
                <td>Paket</td>
                <td>Kurmer</td>
                <td>
                  <button class="btn btn-warning btn-sm me-1">Edit</button>
                  <button class="btn btn-danger btn-sm">Del</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- AREA PILIH SEMUA + HAPUS -->
        <div id="selectArea"
             class="d-flex flex-wrap justify-content-start align-items-center gap-2 mt-3 mb-3"
             style="display: none; margin-top: 10px; margin-bottom: 15px;">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="selectAll" style="box-shadow:none">
            <label class="form-check-label fw-semibold" for="selectAll">Pilih Semua</label>
          </div>
          <button id="deleteSelected" class="btn btn-danger btn-sm">
            <i class="fa fa-trash"></i> Hapus Terpilih
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Tambahan CSS agar tombol sejajar di bawah search ketika responsive -->
<style>
@media (max-width: 576px) {
  .search-group {
    flex-direction: column;
    align-items: stretch !important;
  }
  .search-group form {
    width: 100%;
  }
  .button-group {
    width: 100%;
    justify-content: start !important;
  }
  .button-group a {
    flex: 1 1 auto;
  }
}
</style>

<script>
$(document).ready(function() {
  const table = $('#dataKelas').DataTable({
    responsive: true,
    dom: '<"top"l>rt<"bottom"ip><"clear">',
    language: {
      lengthMenu: "Tampilkan kelas _MENU_ ",
      zeroRecords: "Data tidak ditemukan",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
      infoEmpty: "Tidak ada data",
      paginate: {
        first: "Pertama",
        last: "Terakhir",
        next: "Next",
        previous: "Prev"
      }
    }
  });

  $('#dataKelas_length').after($('#selectArea'));
  $('#selectArea').show();

  // Pilih Semua
  $('#selectAll').on('change', function() {
    const checked = this.checked;
    table.$('.row-checkbox').prop('checked', checked);
  });

  $('#dataKelas').on('change', '.row-checkbox', function() {
    const total = table.$('.row-checkbox').length;
    const checkedCount = table.$('.row-checkbox:checked').length;
    $('#selectAll').prop('checked', total === checkedCount);
  });

  $('#deleteSelected').on('click', function() {
    const selected = table.$('.row-checkbox:checked').length;
    if (selected === 0) {
      alert('Tidak ada data yang dipilih.');
    } else {
      if (confirm(`Yakin ingin menghapus ${selected} data terpilih?`)) {
        alert(`${selected} data telah dihapus (simulasi).`);
      }
    }
  });
});
</script>
