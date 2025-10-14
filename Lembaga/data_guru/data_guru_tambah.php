<?php
include '../../includes/header.php';
?>

<body>

  <?php
  include '../../includes/navbar.php';
  ?>

  <body>


    <!-- MAIN CONTENT -->
    <main class="content ">
      <section class="cards" style="margin-top:-50px;">
        <div class="row g-3">
          <div class="d-flex justify-content align-items-center">
            <!-- Tombol Back / Icon -->
            <a href="data_guru.php" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-arrow-left-circle" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z" />
              </svg>

            </a>
            <span class="ms-2 fw-semibold">Back</span>
          </div>
          <!-- Card kiri -->
          <div class="col-md-6">
            <div class="card shadow-sm h-100">
              <div class="card-body">
                <h5 class="card-title mb-4 fw-semibold">Tambah Data Guru</h5>
                <form>
                  <!-- Nama Guru -->
                  <div class="mb-3">
                    <label for="namaGuru" class="form-label">Nama Guru</label>
                    <input type="text" class="form-control" id="namaGuru" placeholder="Masukkan nama guru">
                  </div>

                  <!-- Jabatan -->
                  <div class="mb-3">
                    <label for="jabatan" class="form-label">Jabatan</label>
                    <select class="form-select" id="jabatan">
                      <option selected>Pilih Jabatan Guru</option>
                      <option value="1">Kepala Sekolah</option>
                      <option value="2">Guru Reguler</option>
                    </select>
                  </div>

                  <!-- No. Telp -->
                  <div class="mb-3">
                    <label for="noTelp" class="form-label">No Telpon</label>
                    <input type="Number" class="form-control" id="noTlp" placeholder="Masukkan No Telp">
                  </div>

                  <!-- Tombol Simpan -->
                  <div class="text-end">
                    <button type="submit" class="btn btn-primary rounded-3">Simpan</button>
                  </div>
                </form>
              </div>

            </div>
          </div>

          <!-- Card kanan -->
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
                  <label class="form-label">Pilih Kolom yang ingin diimport</label>
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

                <!-- Step 6: Tombol Import -->
                <div class="d-flex justify-content-end mt-3" id="importContainer" style="display:none;">
                  <button id="importBtn" class="btn btn-primary rounded-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cloud-arrow-up-fill" viewBox="0 0 16 16">
                      <path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2m2.354 5.146a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0z" />
                    </svg>
                    Import
                  </button>
                </div>

              </div>
            </div>

          </div>
        </div>
      </section>
    </main>

    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script>
      const uploadBtn = document.getElementById('uploadBtn');
      const fileInput = document.getElementById('excelFile');
      const sheetContainer = document.getElementById('sheetContainer');
      const sheetSelect = document.getElementById('sheetSelect');
      const columnsContainer = document.getElementById('columnsContainer').querySelector('div');
      const previewContainer = document.getElementById('previewContainer');
      const previewTable = previewContainer.querySelector('table');
      const importContainer = document.getElementById('importContainer');

      let workbook, selectedSheetData;

      // Step 2: Upload / baca file Excel
      uploadBtn.addEventListener('click', () => {
        const file = fileInput.files[0];
        if (!file) return alert('Pilih file Excel dulu');

        const reader = new FileReader();
        reader.onload = e => {
          const data = new Uint8Array(e.target.result);
          workbook = XLSX.read(data, {
            type: 'array'
          });

          const defaultOption = document.createElement('option');
          defaultOption.value = '';
          defaultOption.textContent = 'Pilih Salah satu';
          defaultOption.selected = true;
          defaultOption.disabled = true;
          sheetSelect.appendChild(defaultOption);

          // tampilkan dropdown sheet
          workbook.SheetNames.forEach(name => {
            const option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            sheetSelect.appendChild(option);
          });
          sheetContainer.style.display = 'block';
        };
        reader.readAsArrayBuffer(file);
      });

      // Step 3 & 4: Pilih sheet -> tampilkan kolom (auto deteksi header + gabung 3 baris header merge)
      sheetSelect.addEventListener('change', () => {
        const sheetName = sheetSelect.value;
        const worksheet = workbook.Sheets[sheetName];
        const jsonData = XLSX.utils.sheet_to_json(worksheet, {
          header: 1
        });
        selectedSheetData = jsonData;

        // keywords untuk flexible match
        const headerKeywords = ["NO", "MATA"];
        let headerRowIndex = -1;

        // cari baris header utama
        for (let i = 0; i < jsonData.length; i++) {
          const row = jsonData[i].map(c => (c || '').toString().trim().toUpperCase());
          const matchCount = headerKeywords.filter(k => row.some(cell => cell.includes(k))).length;
          if (matchCount >= 2) {
            headerRowIndex = i;
            break;
          }
        }

        if (headerRowIndex === -1) {
          alert('Header tabel tidak ditemukan! Silakan cek sheet atau pilih baris header manual.');
          return;
        }

        // ambil maksimal 3 baris header
        let header1 = jsonData[headerRowIndex] || [];
        let header2 = jsonData[headerRowIndex + 1] || [];
        let header3 = jsonData[headerRowIndex + 2] || [];

        // --- 🆕 Fungsi bantu: isi cell kosong ke kanan (atasi efek merge Excel) ---
        function fillMergedCells(row) {
          let lastVal = '';
          return row.map(cell => {
            if (cell && cell.toString().trim() !== '') {
              lastVal = cell.toString().trim();
              return lastVal;
            }
            return lastVal; // isi ulang dengan nilai sebelumnya (efek merge horizontal)
          });
        }

        // --- 🆕 Terapkan perbaikan merge ke semua baris header ---
        header1 = fillMergedCells(header1);
        header2 = fillMergedCells(header2);
        header3 = fillMergedCells(header3);

        // deteksi berapa banyak baris header aktif
        const headerRows = [header1, header2, header3].filter(row =>
          row && row.some(c => c && c.toString().trim() !== '')
        );

        let headers = [];
        const dataStartIndex = headerRowIndex + headerRows.length;

        // --- 🆕 Gabungkan header multi-baris jadi satu nama kolom ---
        const maxLength = Math.max(header1.length, header2.length, header3.length);
        for (let i = 0; i < maxLength; i++) {
          const part1 = (header1[i] || '').trim();
          const part2 = (header2[i] || '').trim();
          const part3 = (header3[i] || '').trim();
          const full = [part1, part2, part3].filter(Boolean).join(' ');
          headers.push(full);
        }

        // ambil data setelah header
        const rows = jsonData
          .slice(dataStartIndex)
          .filter(r => r.some(c => c !== null && c !== ''));

        // --- 🔢 Filter hanya baris dengan NO >= 1 ---
        const headerIndexNo = headers.findIndex(h => h && h.toString().toUpperCase().includes('NO'));
        const filteredRows = rows.filter(r => {
          const noVal = parseInt(r[headerIndexNo]);
          return !isNaN(noVal) && noVal >= 1;
        });

        // tampilkan checkbox kolom
        columnsContainer.innerHTML = '';
        headers.forEach(header => {
          const label = document.createElement('label');
          label.classList.add('me-2');
          label.innerHTML = `<input type="checkbox" value="${header}" checked> ${header}`;
          columnsContainer.appendChild(label);
        });
        columnsContainer.parentElement.style.display = 'block';

        // tampilkan preview tabel
        showPreview(headers, filteredRows);

        // simpan data buat backend
        selectedSheetData = {
          headers,
          rows: filteredRows
        };
      });


      // Step 5: Preview Data
      function showPreview(headers, rows) {
        const thead = previewTable.querySelector('thead tr');
        const tbody = previewTable.querySelector('tbody');

        thead.innerHTML = '';
        tbody.innerHTML = '';

        headers.forEach(h => thead.innerHTML += `<th>${h}</th>`);

        rows.forEach(row => {
          const tr = document.createElement('tr');
          headers.forEach((h, i) => tr.innerHTML += `<td>${row[i] || ''}</td>`);
          tbody.appendChild(tr);
        });

        previewContainer.style.display = 'block';
        importContainer.style.display = 'flex';
      }

      // Step 6: Tombol import (dummy, nanti submit ke backend PHP)
      document.getElementById('importBtn').addEventListener('click', () => {
        const checkedCols = Array.from(columnsContainer.querySelectorAll('input:checked')).map(i => i.value);
        alert('Data akan diimport dengan kolom: ' + checkedCols.join(', '));
      });
    </script>

    <?php
    include '../../includes/footer.php';
    ?>