<?php
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../koneksi.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('ID siswa tidak valid.'); window.location='data_rapor.php';</script>";
    exit;
}

$id = (int) $_GET['id'];

$q = mysqli_query($koneksi, "
    SELECT s.*, k.nama_kelas, g.nama_guru AS wali_kelas
    FROM siswa s
    LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
    LEFT JOIN guru g ON k.id_guru = g.id_guru
    WHERE s.id_siswa = $id
    LIMIT 1
");

$data = mysqli_fetch_assoc($q);
if (!$data) {
    echo "<script>alert('Data siswa tidak ditemukan.'); window.location='data_rapor.php';</script>";
    exit;
}
?>

<main class="content">
  <div class="cards row" style="margin-top: -50px;">
    <div class="col-12">
      <div class="card shadow-sm p-4" style="border-radius: 15px;">

        <h4 class="fw-bold mb-3 text-center">Preview Rapor Siswa</h4>

        <table class="table table-bordered">
          <tr>
            <th width="180">Nama Siswa</th>
            <td><?= htmlspecialchars($data['nama_siswa']); ?></td>
          </tr>
          <tr>
            <th>NISN</th>
            <td><?= htmlspecialchars($data['no_induk_siswa']); ?></td>
          </tr>
          <tr>
            <th>No Absen</th>
            <td><?= htmlspecialchars($data['no_absen_siswa']); ?></td>
          </tr>
          <tr>
            <th>Kelas</th>
            <td><?= htmlspecialchars($data['nama_kelas'] ?? '-'); ?></td>
          </tr>
          <tr>
            <th>Wali Kelas</th>
            <td><?= htmlspecialchars($data['wali_kelas'] ?? '-'); ?></td>
          </tr>
          <tr>
            <th>Komentar</th>
            <td><?= nl2br(htmlspecialchars($data['komentar_siswa'])); ?></td>
          </tr>
        </table>

        <div class="d-flex justify-content-between mt-3">
          <a href="data_rapor.php" class="btn btn-danger">
            Kembali
          </a>
        </div>

      </div>
    </div>
  </div>
</main>

<?php include '../../includes/footer.php'; ?>
