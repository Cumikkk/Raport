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
                        <h5 class="mb-1 fw-semibold fs-4" style=" text-align: center">Nilai Ekstrakulikuler</h5>

                        <!-- Tombol di kanan -->
                        <div class="ms-auto d-flex gap-2 action-buttons">
                            <a href="nilai_ekstra_tambah.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1 p-2 pe-3 fw-semibold" style="border-radius: 5px;">
                                <i class="fa-solid fa-plus fa-lg"></i>
                                Tambah
                            </a>

                            <a href="nilai_ekstra_import.php" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-file-arrow-down fa-lg"></i>
                                <span>Import</span>
                            </a>

                            <button id="exportBtn" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-file-arrow-up fa-lg"></i>
                                Export
                            </button>
                        </div>
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
                                <div class="table-responsive">
                                    <thead style="background-color:#1d52a2" class="text-center text-white">
                                        <tr>
                                            <th rowspan="2">No</th>
                                            <th rowspan="2">Nama</th>
                                            <th colspan="8">Ekstra Kurikuler</th>
                                            <th rowspan="2">Aksi</th>
                                        </tr>
                                        <tr>
                                            <th>Nama Ekstra</th>
                                            <th>Nilai</th>
                                            <th>Nama Ekstra</th>
                                            <th>Nilai</th>
                                            <th>Nama Ekstra</th>
                                            <th>Nilai</th>
                                            <th>Nama Ekstra</th>
                                            <th>Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Airlangga Gustav Arvizu L.</td>
                                            <td>Pramuka</td>
                                            <td>A</td>
                                            <td>Futsal</td>
                                            <td>B+</td>
                                            <td>Paskibra</td>
                                            <td>A</td>
                                            <td>PMR</td>
                                            <td>B</td>
                                            <td>
                                                <a href="nilai_ekstra_edit.php" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1">
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
                                </div>

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