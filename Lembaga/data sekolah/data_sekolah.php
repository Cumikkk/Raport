<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard - Data Sekolah</title>

  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/../dashboard.css">

  <style>
    body {
      background-color: #f8f9fa;
    }

    .content {
      margin-left: 260px;
      padding: 20px;
    }

    .content h2 {
      text-align: center;
      margin-bottom: 20px;
      font-weight: 700;
    }

    /* === FLEXBOX UNTUK FORM DAN LOGO === */
    .container-flex {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-top: 20px;
      justify-content: space-between;
    }

    .form-section, .logo-section {
      background: #fff;
      border-radius: 10px;
      padding: 25px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .form-section {
      flex: 2;
      min-width: 320px;
    }

    .logo-section {
      flex: 1;
      min-width: 250px;
      text-align: center;
    }

    .logo-section img {
      width: 160px;
      height: auto;
      margin-bottom: 15px;
    }

    label {
      font-weight: 500;
      margin-bottom: 5px;
    }

    input, textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
    }

    .form-check {
      margin-bottom: 15px;
    }

    /* === TOMBOL UTAMA (SIMPAN & UPDATE) === */
    .btn-primary, .btn-success {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      color: white;
      cursor: pointer;
      transition: 0.3s ease;
    }

    .btn-primary {
      background-color: #007bff;
    }
    .btn-primary:hover {
      background-color: #0069d9;
    }

    .btn-success {
      background-color: #28a745;
      margin-top: 10px;
    }
    .btn-success:hover {
      background-color: #218838;
    }

    /* Responsif untuk HP */
    @media (max-width: 768px) {
      .content {
        margin-left: 0;
      }
      .container-flex {
        flex-direction: column;
      }
    }
  </style>
</head>

<body>
  <input type="checkbox" id="menu-toggle" />

  <!-- HEADER -->
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

  <!-- === KONTEN UTAMA === -->
  <main class="content">
    <h2>Data Sekolah</h2>
    <div class="container-flex">

      <!-- Form Kiri -->
      <div class="form-section">
        <form action="update_sekolah.php" method="POST" enctype="multipart/form-data">
          <label>Nama Sekolah</label>
          <input type="text" name="nama">

          <label>NPSN</label>
          <input type="text" name="npsn">

          <label>NSS</label>
          <input type="text" name="nss">

          <label>Kode POS</label>
          <input type="text" name="kode_pos">

          <label>Telepon</label>
          <input type="text" name="telepon">

          <label>Alamat</label>
          <textarea name="alamat"></textarea>

          <label>Email</label>
          <input type="email" name="email">

          <label>Website</label>
          <input type="text" name="website">

          <label>Kepala Sekolah</label>
          <input type="text" name="kepala">

          <label>NIP Kepala Sekolah</label>
          <input type="text" name="nip">

          <button type="submit" class="btn-primary">Simpan</button>
        </form>
      </div>

      <!-- Logo Kanan -->
      <div class="logo-section">
        <h5>Edit Logo Sekolah</h5>
        <img src="../../Gambar/logo-default.png" alt="Logo Sekolah">
        <input type="file" name="logo" class="form-control">
        <button class="btn-success">Update</button>
      </div>
    </div>
  </main>
</body>
</html>
