<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard - Data Siswa</title>

  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/../dashboard.css">

  <style>
    .content {
      margin-left: 260px;
      padding: 20px;
    }

    .content h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    form.import-form, form.search-form {
      text-align: center;
      margin-bottom: 20px;
    }

    table {
      border-collapse: collapse;
      width: 90%;
      margin: 0 auto;
    }

    th, td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: center;
    }

    th {
      background-color: #4CAF50;
      color: white;
    }

    a.btn-export, a.btn-add {
      background-color: #4CAF50;
      color: white;
      padding: 8px 12px;
      text-decoration: none;
      border-radius: 5px;
      margin-left: 10px;
    }

    a.btn-add {
      background-color: #2196F3;
    }

    a.btn-add:hover {
      background-color: #0b7dda;
    }

    a.btn-export:hover {
      background-color: #45a049;
    }

    button[type="submit"] {
      background-color: #2196F3;
      color: white;
      padding: 8px 12px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    button[type="submit"]:hover {
      background-color: #0b7dda;
    }

    input[type="text"] {
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 5px;
      width: 250px;
    }

    .btn {
      padding: 5px 10px;
      border-radius: 5px;
      text-decoration: none;
      color: white;
    }

    .btn-warning { background-color: #ff9800; }
    .btn-danger { background-color: #f44336; }
    .btn-warning:hover { background-color: #e68a00; }
    .btn-danger:hover { background-color: #da190b; }

    .action-bar {
      text-align: center;
      margin-bottom: 15px;
    }
  </style>
</head>

<body>
  <input type="checkbox" id="menu-toggle" />

  <header class="topbar">
    <label for="menu-toggle" class="hamburger">
      <i class="fas fa-bars"></i>
      <i class="fas fa-times close-icon"></i>
    </label>

    <div class="topbar-center">
      <!-- Ganti path gambar sesuai lokasi file kamu -->
      <img src="../../Gambar/Tanpa judul (300 x 138 piksel).png" alt="Logo" class="dashboard-logo">
    </div>

    <a href="../../logout.php" class="logout-btn desktop-only">
      <i class="fas fa-right-from-bracket"></i> Logout
    </a>
  </header>

  <label for="menu-toggle" class="overlay"></label>

  <aside class="sidebar">
    <nav class="menu">
      <a href="../../dashboard.php" class="home-link">
        <i class="fas fa-home"></i>
        <span>Beranda</span>
      </a>

      <details open>
        <summary><span><i class="fas fa-building"></i> Lembaga</span> <i class="fas fa-angle-right arrow"></i></summary>
        <ul>
          <li><a href="../data sekolah/data_sekolah.php">Data Sekolah</a></li>
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
    <h2>📚 Data Siswa</h2>

    <!-- Form Import & Export -->
    <form class="import-form" action="import.php" method="post" enctype="multipart/form-data">
      <input type="file" name="file_excel" required>
      <button type="submit"><i class="fas fa-file-import"></i> Import Excel</button>
      <a href="export.php" class="btn-export"><i class="fas fa-file-export"></i> Export Excel</a>
    </form>

    <!-- 🔍 Form Pencarian -->
    <form class="search-form" method="GET" action="">
      <input type="text" name="search" placeholder="Cari nama siswa..." value="">
      <button type="submit"><i class="fas fa-search"></i> Cari</button>
    </form>

    <!-- ➕ Tombol Tambah Siswa -->
    <div class="action-bar">
      <a href="tambah_siswa.php" class="btn-add"><i class="fas fa-user-plus"></i> Tambah Siswa</a>
    </div>

    <!-- Tabel Data -->
    <table>
      <thead>
        <tr style="background-color: #000000ff; color: white; text-align: center;">
          <th>Absen</th>
          <th>NIS</th>
          <th>Nama</th>
          <th>Wali Kelas</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </main>
</body>
</html>
