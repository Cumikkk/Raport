<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard</title>

  <!-- Font Awesome -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

  <!-- checkbox hack -->
  <input type="checkbox" id="menu-toggle" />

  <!-- TOPBAR -->
  <header class="topbar">
    <label for="menu-toggle" class="hamburger">
      <i class="fas fa-bars"></i>
      <i class="fas fa-times close-icon"></i>
    </label>

    <div class="topbar-center">
      <img src="Gambar/Tanpa judul (300 x 138 piksel).png" alt="Logo" class="dashboard-logo">
    </div>

    <a href="logout.php" class="logout-btn desktop-only">
      <i class="fas fa-right-from-bracket"></i> Logout
    </a>
  </header>

  <!-- overlay -->
  <label for="menu-toggle" class="overlay"></label>

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <nav class="menu">
      <!-- ✅ Menu Beranda -->
      <a href="index.php" class="home-link">
        <i class="fas fa-home"></i>
        <span>Beranda</span>
      </a>

      <details>
        <summary><span><i class="fas fa-building"></i> Lembaga</span> <i class="fas fa-angle-right arrow"></i></summary>
        <ul>
          <li><a href="#">Data Sekolah</a></li>
          <li><a href="lembaga/data siswa/data_siswa.php">Data Siswa</a></li>
          <li><a href="#">Kelas</a></li>
          <li><a href="#">Semester Ganjil/Genap</a></li>
          <li><a href="#">Mata Pelajaran</a></li>
        </ul>
      </details>

      <details>
        <summary><span><i class="fas fa-book"></i> Rapor</span> <i class="fas fa-angle-right arrow"></i></summary>
        <ul>
          <li><a href="#">Peraturan Cetak</a></li>
          <li><a href="#">Ekstrakurikuler</a></li>
          <li><a href="#">Nilai Mapel</a></li>
          <li><a href="#">Absensi</a></li>
          <li><a href="#">Cetak Rapor</a></li>
        </ul>
      </details>

      <a href="tambah-user.php" class="add-user">
        <span><i class="fas fa-user"></i> Tambah User</span>
        <i class="fas fa-plus plus-icon"></i>
      </a>

      <a href="logout.php" class="logout-btn mobile-only">
        <i class="fas fa-right-from-bracket"></i> Logout
      </a>
    </nav>
  </aside>

<main class="content">
  <div class="cards row">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Data Guru</h5>
        </div>
        <div class="mt-3 bg-white d-flex justify-content-between align-items-center flex-wrap">
          <div class="d-flex align-items-center gap-2">
            <input type="text" class="form-control form-control-sm" placeholder="Cari nama guru..." style="width: 200px;">
            <button class="btn btn-secondary btn-sm">
              <i class="bi bi-search"></i> Cari
            </button>
          </div>

          <a href="data_guru_tambah.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Tambah Guru
          </a>
        </div>

        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
              <thead style="background-color: #14532d">
                <tr class="text-center">
                  <th>No</th>
                  <th>Nama Guru</th>
                  <th>Mata Pelajaran</th>
                  <th>No. Telepon</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>1</td>
                  <td>Ahmad Fauzi</td>
                  <td>Matematika</td>
                  <td>0812-3456-7890</td>
                  <td class="text-center">
                    <a href="edit_guru.php?id=1" class="btn btn-warning btn-sm">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="hapus_guru.php?id=1" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?');">
                      <i class="bi bi-trash"></i>
                    </a>
                  </td>
                </tr>
                <!-- Tambahkan data lain dari database di sini -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

</body>

</html>