<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Data Sekolah - E-Raport</title>

  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/../dashboard.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    .content {
      margin-left: 260px;
      padding: 25px;
      background-color: #f9f9fb;
      min-height: 100vh;
    }
    .card {
      border-radius: 12px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .form-label { font-weight: 500; }
    .logo-preview {
      width: 150px;
      height: 150px;
      object-fit: contain;
      border: 1px solid #ddd;
      border-radius: 10px;
      background-color: #fff;
      padding: 10px;
    }
    .content h4 {
      font-weight: bold;
      margin-bottom: 25px;
    }
  </style>
</head>

<body>
  <input type="checkbox" id="menu-toggle" />

  <!-- TOPBAR -->
  <header class="topbar">
    <label for="menu-toggle" class="hamburger">
      <i class="fas fa-bars"></i>
      <i class="fas fa-times close-icon"></i>
    </label>

    <div class="topbar-center">
      <img src="../../Gambar/Tanpa judul (300 x 138 piksel).png" alt="Logo" class="dashboard-logo">
    </div>

    <a href="../../logout.php" class="logout-btn desktop-only">
      <i class="fas fa-right-from-bracket"></i> Logout
    </a>
  </header>

  <label for="menu-toggle" class="overlay"></label>

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <nav class="menu">
      <a href="../../dashboard.php" class="home-link">
        <i class="fas fa-home"></i>
        <span>Beranda</span>
      </a>

      <details open>
        <summary><span><i class="fas fa-building"></i> Lembaga</span> <i class="fas fa-angle-right arrow"></i></summary>
        <ul>
          <li><a href="data_sekolah.php" class="active">Data Sekolah</a></li>
          <li><a href="../data siswa/data_siswa.php">Data Siswa</a></li>
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

  <!-- MAIN CONTENT -->
  <div class="content">
    <h4>Data Sekolah</h4>

    <div class="row">
      <!-- Kolom kiri -->
      <div class="col-md-8">
        <div class="card p-4 mb-3">
          <h5 class="mb-3">Edit Data Sekolah</h5>
          <form action="update_data_sekolah.php" method="POST">
            <div class="mb-3">
              <label class="form-label">Nama Sekolah</label>
              <input type="text" name="nama_sekolah" class="form-control" value="<?= $data['nama_sekolah'] ?? '' ?>" required>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">NPSN</label>
                <input type="text" name="npsn" class="form-control" value="<?= $data['npsn'] ?? '' ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">NSS</label>
                <input type="text" name="nss" class="form-control" value="<?= $data['nss'] ?? '' ?>">
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Kode POS</label>
                <input type="text" name="kode_pos" class="form-control" value="<?= $data['kode_pos'] ?? '' ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Telepon</label>
                <input type="text" name="telepon" class="form-control" value="<?= $data['telepon'] ?? '' ?>">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Alamat</label>
              <textarea name="alamat" class="form-control" rows="2"><?= $data['alamat'] ?? '' ?></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" value="<?= $data['email'] ?? '' ?>">
            </div>

            <div class="mb-3">
              <label class="form-label">Website</label>
              <input type="text" name="website" class="form-control" value="<?= $data['website'] ?? '' ?>">
            </div>

            <div class="mb-3">
              <label class="form-label">Kepala Sekolah</label>
              <input type="text" name="kepala_sekolah" class="form-control" value="<?= $data['kepala_sekolah'] ?? '' ?>">
            </div>

            <div class="mb-3">
              <label class="form-label">NIP Kepala Sekolah</label>
              <input type="text" name="nip_kepala_sekolah" class="form-control" value="<?= $data['nip_kepala_sekolah'] ?? '' ?>">
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" value="1" id="confirm" required>
              <label class="form-check-label" for="confirm">
                Saya yakin akan mengubah data tersebut
              </label>
            </div>

            <button type="submit" class="btn btn-primary w-100">Simpan</button>
          </form>
        </div>
      </div>

      <!-- Kolom kanan -->
      <div class="col-md-4">
        <div class="card p-4 mb-3">
          <h5 class="mb-3">Edit Logo Sekolah</h5>
          <div class="text-center mb-3">
            <img src="uploads/<?= $data['logo'] ?? 'default.png' ?>" class="logo-preview mb-2" alt="Logo Sekolah">
          </div>

          <form action="update_logo_sekolah.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label class="form-label">Ganti logo sekolah</label>
              <input type="file" name="logo" class="form-control" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Update</button>
          </form>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
