<?php
include '../../includes/header.php';
?>

<body>
    <?php
    include '../../includes/navbar.php';
    ?>

    <main class="content">
        <div class="cards row" style="margin-top: -50px;">
            <div class="col-12">
                <div class="d-flex justify-content align-items-center mb-2">
                    <!-- Tombol Back / Icon -->
                    <a href="data_mapel.php" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-arrow-left-circle" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z" />
                        </svg>

                    </a>
                    <span class="ms-2 fw-semibold">Back</span>
                </div>
                <div class="card shadow-sm" style="border-radius: 15px;">
                    <div class="mt-0 d-flex align-items-center flex-wrap mb-0 p-3">
                        <!-- Judul di kiri -->
                        <h5 class="mb-1 fw-semibold fs-4">Edit Data Guru</h5>
                    </div>

                    <!-- Tabel Edit Data Mapel -->
                    <div class="card-body">
                        <form>
                            <!-- Nama Mapel -->
                            <div class="mb-3">
                                <label for="namaMapel" class="form-label">Nama Mapel</label>
                                <input type="text" class="form-control" id="namaMapel" placeholder="Masukkan nama mapel" value="Bahasa Inggris">
                            </div>

                            <!-- Jenis mapel -->
                            <div class="mb-3">
                                <label for="jenis" class="form-label">Jabatan</label>
                                <select class="form-select" id="jenis">
                                    <option selected>Pilih Jenis Mapel</option>
                                    <option value="1" selected>Wajib</option>
                                    <option value="2">Pilihan</option>
                                </select>
                            </div>

                            <!-- Tombol Simpan -->
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary rounded-3">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>