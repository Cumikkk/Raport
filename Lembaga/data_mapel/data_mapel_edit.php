<?php
include '../../includes/header.php';
?>

<body>

<?php
include '../../includes/navbar.php';
include '../../koneksi.php';

if (!isset($_GET['id'])) {
  echo "<script>alert('ID mapel tidak ditemukan!'); window.location.href='data_mapel.php';</script>";
  exit;
}

$id_mapel = $_GET['id'];

// Ambil data mapel berdasarkan ID
$query = "SELECT * FROM mata_pelajaran WHERE id_mata_pelajaran = '$id_mapel'";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
  echo "<script>alert('Data tidak ditemukan!'); window.location.href='data_mapel.php';</script>";
  exit;
}
?>

<div class="dk-page" style="margin-top: 50px;">
  <div class="dk-main">
    <div class="dk-content-box">
      <div class="container py-4">
        <h4 class="fw-bold mb-4">Edit Data Mapel</h4>

        <form action="mapel_edit_proses.php" method="POST">
          <input type="hidden" name="id_mapel" value="<?= $data['id_mata_pelajaran']; ?>">

          <div class="mb-3">
            <label class="form-label fw-semibold">Kode Mapel</label>
            <input type="text" name="kode_mapel" class="form-control" 
                   value="<?= $data['kode_mata_pelajaran']; ?>" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Mata Pelajaran</label>
            <input type="text" name="nama_mapel" class="form-control" 
                   value="<?= $data['nama_mata_pelajaran']; ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Jenis Mata Pelajaran</label>
            <select name="jenis_mapel" class="form-select" required>
              <option value="Wajib" <?= ($data['kelompok_mata_pelajaran'] == 'Wajib') ? 'selected' : ''; ?>>Wajib</option>
              <option value="Pilihan" <?= ($data['kelompok_mata_pelajaran'] == 'Pilihan') ? 'selected' : ''; ?>>Pilihan</option>              
              <option value="Peminatan" <?= ($data['kelompok_mata_pelajaran'] == 'Peminatan') ? 'selected' : ''; ?>>Peminatan</option>
              <option value="Lokal" <?= ($data['kelompok_mata_pelajaran'] == 'Lokal') ? 'selected' : ''; ?>>Lokal</option>
            </select>
          </div>

          <div class="d-flex flex-wrap gap-2 justify-content-between">
            <button type="submit" class="btn btn-success">
              <i class="fa fa-save"></i> Update
            </button>
            <a href="data_mapel.php" class="btn btn-danger">
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
