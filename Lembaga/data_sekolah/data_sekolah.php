<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard</title>

  <!-- Font Awesome -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="../../assets/css/dashboard.css">
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
      <img src="../../Gambar/Tanpa judul (300 x 138 piksel).png" alt="Logo" class="dashboard-logo">
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
          <li><a href="Lembaga/data_sekolah/data_sekolah.php">Data Sekolah</a></li>
          <li><a href="Lembaga/data_siswa/data_siswa.php">Data Siswa</a></li>
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
  <h4 class="mb-4">Data Sekolah</h4>

  <div class="row">
    <!-- Kolom kiri -->
    <div class="col-lg-8">
      <div class="card p-5 mb-4 shadow-sm border-0">
        <h5 class="mb-4 fw-bold">Edit Data Sekolah</h5>
        <form action="update_data_sekolah.php" method="POST">
          
          <div class="mb-4">
            <label class="form-label fs-5">Nama Sekolah</label>
            <input type="text" name="nama_sekolah" class="form-control form-control-lg" 
                   value="<?= $data['nama_sekolah'] ?? '' ?>" required>
          </div>

          <div class="row">
            <div class="col-md-6 mb-4">
              <label class="form-label fs-5">NPSN</label>
              <input type="text" name="npsn" class="form-control form-control-lg" 
                     value="<?= $data['npsn'] ?? '' ?>">
            </div>
            <div class="col-md-6 mb-4">
              <label class="form-label fs-5">NSS</label>
              <input type="text" name="nss" class="form-control form-control-lg" 
                     value="<?= $data['nss'] ?? '' ?>">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-4">
              <label class="form-label fs-5">Kode POS</label>
              <input type="text" name="kode_pos" class="form-control form-control-lg" 
                     value="<?= $data['kode_pos'] ?? '' ?>">
            </div>
            <div class="col-md-6 mb-4">
              <label class="form-label fs-5">Telepon</label>
              <input type="text" name="telepon" class="form-control form-control-lg" 
                     value="<?= $data['telepon'] ?? '' ?>">
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fs-5">Alamat</label>
            <textarea name="alamat" class="form-control form-control-lg" rows="3"><?= $data['alamat'] ?? '' ?></textarea>
          </div>

          <div class="mb-4">
            <label class="form-label fs-5">Email</label>
            <input type="email" name="email" class="form-control form-control-lg" 
                   value="<?= $data['email'] ?? '' ?>">
          </div>

          <div class="mb-4">
            <label class="form-label fs-5">Website</label>
            <input type="text" name="website" class="form-control form-control-lg" 
                   value="<?= $data['website'] ?? '' ?>">
          </div>

          <div class="mb-4">
            <label class="form-label fs-5">Kepala Sekolah</label>
            <input type="text" name="kepala_sekolah" class="form-control form-control-lg" 
                   value="<?= $data['kepala_sekolah'] ?? '' ?>">
          </div>

          <div class="mb-4">
            <label class="form-label fs-5">NIP Kepala Sekolah</label>
            <input type="text" name="nip_kepala_sekolah" class="form-control form-control-lg" 
                   value="<?= $data['nip_kepala_sekolah'] ?? '' ?>">
          </div>

          <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" value="1" id="confirm" required>
            <label class="form-check-label fs-6" for="confirm">
              Saya yakin akan mengubah data tersebut
            </label>
          </div>

          <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fs-5">💾 Simpan Perubahan</button>
        </form>
      </div>
    </div>

    <!-- Kolom kanan -->
    <div class="col-lg-4">
      <div class="card p-5 mb-4 shadow-sm border-0">
        <h5 class="mb-4 fw-bold">Edit Logo Sekolah</h5>
        <div class="text-center mb-4">
          <img src="uploads/<?= $data['logo'] ?? 'default.png' ?>" 
               class="logo-preview mb-3 rounded shadow-sm" 
               alt="Logo Sekolah" 
               style="max-width: 180px; height: auto;">
        </div>

        <form action="update_logo_sekolah.php" method="POST" enctype="multipart/form-data">
          <div class="mb-4">
            <label class="form-label fs-5">Ganti Logo Sekolah</label>
            <input type="file" name="logo" class="form-control form-control-lg" accept="image/*" required>
          </div>
          <button type="submit" class="btn btn-success btn-lg w-100 py-3 fs-5">🖼️ Update Logo</button>
        </form>
      </div>
    </div>
  </div>
</div>
     <label class="form-label">Alamat</label>
              <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($data['alamat'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control"
                value="<?= htmlspecialchars($data['email'] ?? '') ?>">
            </div>

            <div class="mb-3">
              <label class="form-label">Website</label>
              <input type="text" name="website" class="form-control"
                value="<?= htmlspecialchars($data['website'] ?? '') ?>">
            </div>

            <div class="mb-3">
              <label class="form-label">Kepala Sekolah</label>
              <input type="text" name="kepala_sekolah" class="form-control"
                value="<?= htmlspecialchars($data['kepala_sekolah'] ?? '') ?>">
            </div>

            <div class="mb-3">
              <label class="form-label">NIP Kepala Sekolah</label>
              <input type="text" name="nip_kepala_sekolah" class="form-control"
                value="<?= htmlspecialchars($data['nip_kepala_sekolah'] ?? '') ?>">
            </div>

            <div class="form-check mb-3" style="margin-top:10px;">
              <input class="form-check-input" type="checkbox" value="1" id="confirm" required>
              <label class="form-check-label" for="confirm">
                Saya yakin akan mengubah data tersebut
              </label>
            </div>

            <button type="submit" class="btn btn-primary w-100" 
                    style="background:#004080; color:white; border:none; border-radius:6px;">
              Simpan
            </button>
          </form>
        </div>
      </div>

      <!-- Kolom kanan -->
      <div class="col-md-4" style="flex:1;">
        <div class="card p-4 mb-3" style="border-radius:10px; background:#fff; box-shadow:0 3px 8px rgba(0,0,0,0.1);">
          <h5 class="mb-3">Edit Logo Sekolah</h5>
          <div class="text-center mb-3">
            <img src="uploads/<?= htmlspecialchars($data['logo'] ?? 'default.png') ?>" 
                 class="logo-preview mb-2" 
                 alt="Logo Sekolah" 
                 style="width:120px; height:120px; object-fit:cover; border-radius:10px; border:2px solid #ccc;">
          </div>

          <form action="update_logo_sekolah.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label class="form-label">Ganti logo sekolah</label>
              <input type="file" name="logo" class="form-control" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-success w-100" 
                    style="background:#28a745; color:white; border:none; border-radius:6px;">
              Update
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
