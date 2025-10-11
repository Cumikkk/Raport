<?php
include '../../includes/header.php';
?>

<body>
<?php
include '../../includes/navbar.php';
?>

<!-- Wrapper utama biar card-nya di tengah dan tidak tertutup navbar -->
<div class="d-flex justify-content-center align-items-start" style="min-height: 100vh; padding-top: 100px; background-color: #f8f9fa;">
  <div class="col-md-6">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">Import</h5>

        <!-- Step 1: Pilih file -->
        <div class="mb-3">
          <label for="excelFile" class="form-label">Pilih File Excel (.xlsx)</label>
          <input type="file" class="form-control" id="excelFile" accept=".xlsx, .xls">
        </div>

        <!-- Step 2: Upload / proses file -->
        <div class="mb-3 d-flex justify-content-end">
          <button id="uploadBtn" class="btn btn-warning rounded-3">Upload</button>
        </div>

        <!-- Step 3: Pilih Sheet -->
        <div class="mb-3" id="sheetContainer" style="display:none;">
          <label for="sheetSelect" class="form-label">Pilih Sheet</label>
          <select id="sheetSelect" class="form-select"></select>
        </div>

        <!-- Step 4: Pilih Kolom -->
        <div class="mb-3" id="columnsContainer" style="display:none;" hidden>
          <label class="form-label">Check Kolom yang akan diimport</label>
          <div class="d-flex flex-wrap gap-2"></div>
        </div>

        <!-- Step 5: Preview Data -->
        <div class="table-responsive" id="previewContainer" style="display:none;">
          <table class="table table-bordered mt-3">
            <thead class="table-primary">
              <tr></tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

        <!-- Step 6: Tombol Import & Batal -->
        <div class="d-flex justify-content-between align-items-center mt-3" id="importContainer" style="display:none;">
          <a href="data_guru.php" class="btn btn-danger rounded-3">
            <i class="fas fa-times"></i> Batal
          </a>

          <button id="importBtn" class="btn btn-primary rounded-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                 class="bi bi-cloud-arrow-up-fill" viewBox="0 0 16 16">
              <path
                d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 
                   0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 
                   9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2m2.354 
                   5.146a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 
                   7.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0z" />
            </svg>
            Import
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<?php
include '../../includes/footer.php';
?>
