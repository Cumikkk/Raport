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
          <h4 class="fw-bold mb-2 mb-sm-0">Data Kelas</h4>

          <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 search-group w-100 w-sm-auto">

            <!-- Form Cari -->
            <form class="d-flex flex-nowrap flex-grow-1 flex-sm-grow-0" role="search">
              <input class="form-control me-2" type="search"
                     placeholder="Cari kelas..." aria-label="Search" style="max-width:180px; flex-grow:1;">
              <button class="btn btn-outline-secondary btn-md" type="submit">
                <i class="fa fa-search"></i>
              </button>
            </form>

            <!-- Tombol Tambah & Import & Export -->
            <div class="d-flex flex-wrap gap-2 mt-2 mt-sm-0 button-group">
              <a href="tambah_data.php" class="btn btn-primary btn-md px-3 py-2 d-flex align-items-center gap-2">
                <i class="fa-solid fa-plus fa-lg"></i> Tambah
              </a>

              <a href="import.php" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-arrow-down fa-lg"></i> Import
              </a>

              <button id="exportBtn" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-arrow-up fa-lg"></i> Export
              </button>
            </div>
          </div>
        </div>

        <!-- FILTER TINGKAT & KELAS (VERTIKAL SELALU) -->
        <div class="mb-3" style="max-width: 300px;">
          <div class="mb-3">
            <label for="tingkat" class="form-label fw-semibold">Tingkat</label>
            <select id="tingkat" class="form-select dk-select">
              <option selected disabled>--Pilih--</option>
              <option>X</option>
              <option>XI</option>
              <option>XII</option>
            </select>
          </div>

          <div>
            <label for="kelas" class="form-label fw-semibold">Tampilkan Kelas</label>
            <select id="kelas" class="form-select dk-select">
              <option selected disabled>--Pilih--</option>
              <option>IPA 1</option>
              <option>IPA 2</option>
              <option>IPS 1</option>
              <option>IPS 2</option>
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
                <th>Komentar</th>
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
                <td>Perlu diperbaiki</td>
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
                <td>Perlu diperbaiki</td>
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
             class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 mb-3"
             style="margin-top: 10px; margin-bottom: 15px;">
          
          <div class="d-flex align-items-center gap-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="selectAll" style="box-shadow:none">
              <label class="form-check-label fw-semibold" for="selectAll">Pilih Semua</label>
            </div>

            <button id="deleteSelected" class="btn btn-danger btn-sm" disabled>
              <i class="fa fa-trash"></i> Hapus Terpilih
            </button>
          </div>

          <!-- Dropdown tampilkan jumlah kelas -->
        </div>

      </div>
    </div>
  </div>
</div>

<!-- CSS RESPONSIVE -->
<style>
@media (max-width: 576px) {
  h4.fw-bold {
    text-align: center;
    width: 100%;
    margin-bottom: 12px !important;
  }

  .search-group {
    flex-direction: column;
    align-items: center !important;
    width: 100%;
    gap: 12px;
  }

  .button-group {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    width: 100%;
  }

  /* Dropdown tampil vertikal penuh */
  .mb-3 {
    width: 100%;
  }
}
</style>

<!-- SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const checkboxes = document.querySelectorAll('.row-checkbox');
  const selectAll = document.getElementById('selectAll');
  const deleteBtn = document.getElementById('deleteSelected');

  function updateDeleteButton() {
    const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
    deleteBtn.disabled = checkedCount === 0;
  }

  selectAll.addEventListener('change', function() {
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateDeleteButton();
  });

  checkboxes.forEach(cb => cb.addEventListener('change', () => {
    const allChecked = [...checkboxes].every(c => c.checked);
    selectAll.checked = allChecked;
    updateDeleteButton();
  }));

  deleteBtn.addEventListener('click', () => {
    const selected = document.querySelectorAll('.row-checkbox:checked').length;
    if (selected > 0 && confirm(`Yakin ingin menghapus ${selected} data terpilih?`)) {
      alert(`${selected} data berhasil dihapus (simulasi).`);
    }
  });
});
</script>

<?php
include '../../includes/footer.php';
?>
