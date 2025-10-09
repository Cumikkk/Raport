<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard | Tambah Data Siswa</title>

  <!-- Font Awesome -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- CSS dashboard utama -->
  <link rel="stylesheet" href="../../assets/css/dashboard.css">

  <!-- Style tambahan halaman -->
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background-color: #f5f6fa;
    }

    .content {
      padding: 40px;
    }

    h2 {
      color: #004080;
      font-weight: 700;
      margin-bottom: 25px;
    }

    /* === CARD FORM === */
    .form-card {
      background: #fff;
      padding: 30px 35px;
      border-radius: 16px;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
      max-width: 700px;
      margin: 0 auto;
    }

    .form-card label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #333;
    }

    .form-card input {
      width: 100%;
      padding: 12px 14px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
      transition: 0.2s;
    }

    .form-card input:focus {
      border-color: #004080;
      box-shadow: 0 0 4px rgba(0, 64, 128, 0.3);
      outline: none;
    }

    .form-buttons {
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      margin-top: 10px;
    }

    .btn-simpan, .btn-batal {
      padding: 10px 18px;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      font-size: 15px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      text-decoration: none;
      transition: 0.2s;
    }

    .btn-simpan {
      background-color: #004080;
      color: white;
    }

    .btn-batal {
      background-color: #dc3545;
      color: white;
    }

    .btn-simpan:hover {
      background-color: #003366;
      transform: translateY(-1px);
    }

    .btn-batal:hover {
      background-color: #c82333;
      transform: translateY(-1px);
    }

    @media (max-width: 768px) {
      .content {
        padding: 20px;
      }

      .form-card {
        padding: 20px;
      }

      .form-buttons {
        flex-direction: column;
      }

      .btn-simpan, .btn-batal {
        width: 100%;
        justify-content: center;
      }
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
      <a href="../../index.php" class="home-link">
        <i class="fas fa-home"></i>
        <span>Beranda</span>
      </a>

      <details open>
        <summary><span><i class="fas fa-building"></i> Lembaga</span> <i class="fas fa-angle-right arrow"></i></summary>
        <ul>
          <li><a href="../data_sekolah/data_sekolah.php">Data Sekolah</a></li>
          <li><a href="data_siswa.php" class="active">Data Siswa</a></li>
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

      <a href="../../tambah-user.php" class="add-user">
        <span><i class="fas fa-user"></i> Tambah User</span>
        <i class="fas fa-plus plus-icon"></i>
      </a>

      <a href="../../logout.php" class="logout-btn mobile-only">
        <i class="fas fa-right-from-bracket"></i> Logout
      </a>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="content">
    <h2>Tambah Data Siswa</h2>

    <div class="form-card">
      <form method="post">
        <label>Absen</label>
        <input type="number" name="absen" required>

        <label>NIS</label>
        <input type="text" name="nis" required>

        <label>Nama</label>
        <input type="text" name="nama" required>

        <label>Wali Kelas</label>
        <input type="text" name="wali_kelas" required>

        <div class="form-buttons">
          <button type="submit" name="simpan" class="btn-simpan">
            <i class="fas fa-save"></i> Simpan
          </button>
          <a href="data_siswa.php" class="btn-batal">
            <i class="fas fa-times"></i> Batal
          </a>
        </div>
      </form>
    </div>
  </main>

</body>
</html>
