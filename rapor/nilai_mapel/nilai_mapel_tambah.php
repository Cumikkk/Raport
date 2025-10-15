<?php
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="dk-page" style="margin-top: 50px;">
    <div class="dk-main">
        <div class="dk-content-box">
            <div class="container py-4">
                <h4 class="fw-bold mb-4">Tambah Data Nilai Mapel</h4>

                <form id="formEkstra">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Mapel</label>
                        <select class="form-select" id="namaSiswa" required>
                            <option value="">-- Pilih Mapel --</option>
                            <option value="1">Ayu Lestari</option>
                            <option value="2">Budi Santoso</option>
                            <option value="3">Citra Dewi</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Jenis</label>
                        <select class="form-select" id="namaEkstra" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Pramuka">Wajib</option>
                            <option value="PMR">Paket</option>
                            <option value="PMR">Paket</option>
                           
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nilai</label>
                        <select class="form-select" id="nilai" required>
                            <option value="">-- Pilih Nilai --</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-success">
              <i class="fa fa-save"></i> Simpan
            </button>
            <a href="mapel.php" class="btn btn-danger">
              <i class="fas fa-times"></i> Batal
            </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include '../../includes/footer.php';
?>