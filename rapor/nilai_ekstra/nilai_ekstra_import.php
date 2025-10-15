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
        <h4 class="fw-bold mb-4">Import Data Nilai Ekstrakulikuler</h4>

        <div class="mb-3">
          <label for="excelFile" class="form-label">Pilih File Excel (.xlsx)</label>
          
          <div class="position-relative" style="display: flex; align-items: center;">
            <!-- Input file -->
            <input 
              type="file" 
              class="form-control" 
              id="excelFile" 
              accept=".xlsx, .xls" 
              onchange="toggleClearButton()" 
              style="padding-right: 35px;"
            >

            <!-- Tombol X tanpa border, sejajar -->
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
        </div>

        <div class="d-flex justify-content-between mt-4">
          <a href="nilai_ekstra.php" class="btn btn-danger">
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
  const fileInput = document.getElementById("excelFile");
  const clearBtn = document.getElementById("clearFileBtn");

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
