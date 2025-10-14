<?php
include '../../includes/header.php';
?>

<body>
    <?php include '../../includes/navbar.php'; ?>

    <main class="content">
        <div class="cards row" style="margin-top: -50px;">
            <div class="col-12">
                <div class="card shadow-sm" style="border-radius: 15px;">
                    <div class="mt-0 d-flex align-items-center flex-wrap mb-0 p-3 top-bar">
                        <!-- Judul di kiri -->
                        <h5 class="mb-1 fw-semibold fs-4" style=" text-align: center">Nilai Mata Pelajaran - Bahasa Inggris</h5>

                    </div>

                    <!-- Filter Kelas & Search -->
<div class="ms-3 me-3 bg-white d-flex justify-content-between align-items-center flex-wrap p-3 gap-3 rounded shadow-sm">
  
  <!-- Pilih Kelas -->
  <div class="d-flex align-items-center gap-2">
    <label for="selectKelas" class="fw-semibold">Kelas:</label>
    <select id="selectKelas" class="form-select form-select-sm" style="width: 180px;">
      <option value="">-- Pilih Kelas --</option>
      <option value="1A">1A</option>
      <option value="1B">1B</option>
      <option value="2A">2A</option>
      <option value="2B">2B</option>
      <option value="3A">3A</option>
      <option value="3B">3B</option>
    </select>
  </div>

  <!-- Search -->
  <div class="d-flex align-items-center gap-2">
    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Cari nama siswa..." style="width: 220px;">
    <button id="searchBtn" class="btn btn-outline-secondary btn-sm p-2 rounded-3 d-flex align-items-center justify-content-center">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
        <path d="M11 6a5 5 0 1 0-2.9 4.7l3.85 3.85a1 1 0 0 0 1.414-1.414l-3.85-3.85A4.978 4.978 0 0 0 11 6zM6 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
      </svg>
    </button>
  </div>

</div>


                    <!-- Tabel nilai Mapel ambil data mapel -->
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <table class="table table-bordered text-center align-middle">
                                    <thead style="background-color:#1d52a2" class="text-white">
                                        <tr>
                                            <th rowspan="3">NO.</th>
                                            <th rowspan="3">NAMA</th>
                                            <th colspan="16">FORMATIF</th>
                                            <th colspan="4">SUMATIF</th>
                                            <th rowspan="3">SUMATIF<br>TENGAH<br>SEMESTER</th>
                                            <th rowspan="3">AKSI</th>
                                        </tr>
                                        <tr>
                                            <th colspan="4">LINGKUP MATERI 1</th>
                                            <th colspan="4">LINGKUP MATERI 2</th>
                                            <th colspan="4">LINGKUP MATERI 3</th>
                                            <th colspan="4">LINGKUP MATERI 4</th>
                                            <th colspan="4">LINGKUP MATERI</th>
                                        </tr>
                                        <tr>
                                            <th>TP1</th>
                                            <th>TP2</th>
                                            <th>TP3</th>
                                            <th>TP4</th>
                                            <th>TP1</th>
                                            <th>TP2</th>
                                            <th>TP3</th>
                                            <th>TP4</th>
                                            <th>TP1</th>
                                            <th>TP2</th>
                                            <th>TP3</th>
                                            <th>TP4</th>
                                            <th>TP1</th>
                                            <th>TP2</th>
                                            <th>TP3</th>
                                            <th>TP4</th>
                                            <th>LM1</th>
                                            <th>LM2</th>
                                            <th>LM3</th>
                                            <th>LM4</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Airlangga Gustav Arvizu L.</td>
                                            <td>100</td>
                                            <td>100</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>100</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>93</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>32</td>
                                            <td>
                                                <a href="#" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </a>
                                                <a href="hapus_mapel.php?id=1"
                                                    class="btn btn-danger btn-sm me-1 d-inline-flex align-items-center justify-content-center gap-1 px-2 py-1"
                                                    style="font-size: 15px;"
                                                    onclick="return confirm('Yakin ingin menghapus data ini?');">
                                                    <i class="bi bi-trash" style="font-size: 15px;"></i>
                                                    <span>Del</span>
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

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
</body>