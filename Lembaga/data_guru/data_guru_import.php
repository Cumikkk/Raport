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
           <a href="" class="btn btn-warning rounded-3">
            <i class="fas fa-upload"></i> Upload
          </a>
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

          
        </div>

      </div>
    </div>
  </div>
</div>

<?php
include '../../includes/footer.php';
?>
