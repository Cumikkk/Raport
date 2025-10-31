<?php
include '../../includes/header.php';
?>

<body>
<?php
include '../../includes/navbar.php';
?>

<div class="dk-page" style="margin-top: 50px;">
  <div class="dk-main">
    <div class="dk-content-box">
      <div class="container py-4">
        <h4 class="fw-bold mb-4">Import Data Siswa</h4>

        <!-- ===== DROPDOWN FILTER ===== -->
        <div class="d-flex flex-column align-items-start mb-4" style="gap: 15px;">
          <!-- Dropdown Tingkat -->
          <div class="filter-group d-flex align-items-center" style="gap: 12px;">
            <label for="tingkat" class="form-label fw-semibold mb-0" style="min-width: 70px;">Tingkat</label>
            <select id="tingkat" class="form-select dk-select" style="width: 160px;">
              <option selected disabled>--Pilih--</option>
              <option>X</option>
              <option>XI</option>
              <option>XII</option>
            </select>
          </div>

          <!-- Dropdown Kelas -->
          <div class="filter-group d-flex align-items-center" style="gap: 12px;">
            <label for="kelas" class="form-label fw-semibold mb-0" style="min-width: 70px;">Kelas</label>
            <select id="kelas" class="form-select dk-select" style="width: 160px;">
              <option selected disabled>--Pilih--</option>
              <option>IPA 1</option>
              <option>IPA 2</option>
              <option>IPS 1</option>
              <option>IPS 2</option>
            </select>
          </div>
        </div>

        <!-- ===== INPUT FILE ===== -->
        <div class="mb-3">
          <label for="excelFile" class="form-label">Pilih File Excel (.xlsx)</label>
          
          <div class="position-relative d-flex align-items-center">
            <input 
              type="file" 
              class="form-control" 
              id="excelFile" 
              accept=".xlsx, .xls" 
              onchange="toggleClearButton()" 
              onclick="checkBeforeChoose(event)" 
              style="padding-right: 35px;"
            >
            <button 
              type="button" 
              id="clearFileBtn" 
              onclick="clearFile()" 
              title="Hapus file"
              style="
                position: absolute;
                right: 10px;
                background: none;
                border: none;
                color: #6c757d;
                font-size: 20px;
                line-height: 1;
                display: none;
                cursor: pointer;
              ">
              &times;
            </button>
          </div>
          <small id="warningText" style="color: #dc3545; display: none;">*Silakan pilih tingkat dan kelas terlebih dahulu.</small>
        </div>

        <!-- ===== BUTTONS ===== -->
        <div class="d-flex justify-content-between mt-4">
          <a href="data_siswa.php" class="btn btn-danger">
            <i class="fa fa-times"></i> Batal
          </a>
          <a href="" class="btn btn-warning">
            <i class="fas fa-upload"></i> Upload
          </a>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
  const tingkat = document.getElementById("tingkat");
  const kelas = document.getElementById("kelas");
  const fileInput = document.getElementById("excelFile");
  const clearBtn = document.getElementById("clearFileBtn");
  const warningText = document.getElementById("warningText");

  // Fungsi cek sebelum memilih file
  function checkBeforeChoose(e) {
    const valid = tingkat.value !== "--Pilih--" && kelas.value !== "--Pilih--";

    if (!valid) {
      e.preventDefault(); // Batalkan dialog file
      warningText.style.display = "block";
      warningText.style.opacity = 1;
      warningText.style.transition = "opacity 0.3s ease";
    } else {
      warningText.style.display = "none";
    }
  }

  // Fungsi hilangkan pesan otomatis jika user sudah pilih tingkat & kelas
  function checkSelection() {
    const valid = tingkat.value !== "--Pilih--" && kelas.value !== "--Pilih--";
    if (valid) {
      warningText.style.display = "none";
    }
  }

  // Event listener untuk kedua dropdown
  tingkat.addEventListener("change", checkSelection);
  kelas.addEventListener("change", checkSelection);

  // Fungsi tombol clear file
  function toggleClearButton() {
    clearBtn.style.display = fileInput.files.length > 0 ? "block" : "none";
  }

  function clearFile() {
    fileInput.value = "";
    clearBtn.style.display = "none";
  }
</script>

<?php
include '../../includes/footer.php';
?>
