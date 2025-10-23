<?php
include '../../includes/header.php';
include '../../koneksi.php';

include '../../koneksi.php';

if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $query = "SELECT * FROM ekstrakurikuler WHERE id_ekstrakurikuler = '$id'";
  $result = mysqli_query($koneksi, $query);
  $data = mysqli_fetch_assoc($result);

  if (!$data) {
    echo "<script>
            alert('Data tidak ditemukan!');
            window.location='data_ekstra.php';
          </script>";
    exit;
  }
} else {
  echo "<script>
          alert('ID tidak ditemukan!');
          window.location='data_ekstra.php';
        </script>";
  exit;
}
?>

<body>

<?php
include '../../includes/navbar.php';
?>

<div class="dk-page" style="margin-top: 50px;">
  <div class="dk-main">
    <div class="dk-content-box">
      <div class="container py-4">
        <h4 class="fw-bold mb-4">Edit Ekstrakurikuler</h4>

        <!-- === FORM EDIT === -->
<form action="ekstra_edit_proses.php" method="POST">
  <input type="hidden" name="id" value="<?= $data['id_ekstrakurikuler']; ?>">

  <div class="mb-3">
    <label class="form-label fw-semibold">Nama Ekstrakurikuler</label>
    <input 
      type="text" 
      name="nama_ekstra" 
      class="form-control" 
      placeholder="Nama Ekstrakurikuler" 
      value="<?= htmlspecialchars($data['nama_ekstrakurikuler']); ?>" 
      required
    >
  </div>

  <div class="d-flex flex-wrap gap-2 justify-content-between">
    <button type="submit" class="btn btn-success">
      <i class="fa fa-save"></i> Update
    </button>
    <a href="data_ekstra.php" class="btn btn-danger">
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
