<?php
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="dk-page">
    <div class="dk-main">
        <div class="dk-content-box">
            <div class="container py-4">
                <h4 class="fw-bold mb-4">Tambah Guru</h4>

                <form id="editForm">
      <div class="mb-3">
        <label class="form-label">Nama Siswa</label>
        <select class="form-select" id="namaSiswa" required disabled>
          <option value="">-- Pilih Siswa --</option>
          <option value="1">Ayu Lestari</option>
          <option value="2">Budi Santoso</option>
          <option value="3">Citra Dewi</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Nama Ekstrakurikuler</label>
        <select class="form-select" id="namaEkstra" required disabled>
          <option value="">-- Pilih Ekstrakurikuler --</option>
          <option value="Pramuka" selected>Pramuka</option>
          <option value="PMR">PMR</option>
          <option value="Paskibra">Paskibra</option>
          <option value="Tari">Tari</option>
          <option value="Futsal">Futsal</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Nilai</label>
        <select class="form-select" id="nilai" required>
          <option value="">-- Pilih Nilai --</option>
          <option value="A" selected>A</option>
          <option value="B">B</option>
          <option value="C">C</option>
          <option value="D">D</option>
        </select>
      </div>

      <div class="d-flex justify-content-between mt-4">
        <button type="submit" class="btn btn-success">Update</button>
        <button type="button" class="btn btn-secondary" onclick="window.history.back()">Kembali</button>
      </div>
    </form>
            </div>
        </div>
    </div>
</div>

<?php
include '../../includes/footer.php';
?>