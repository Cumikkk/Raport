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
    body {
      background-color: #f8f9fa;
    }

    .content {
      margin-left: 260px;
      padding: 20px;
    }

    .content h2 {
      text-align: center;
      margin-bottom: 25px;
      font-weight: 700;
    }

    /* ======== BAR ATAS (IMPORT, EXPORT, SEARCH, TAMBAH SISWA) ======== */
    .top-actions {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
      gap: 10px;
      background: #fff;
      padding: 15px 20px;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      margin-bottom: 25px;
    }

    /* Bagian kiri: import & export */
    .import-export {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    .import-export input[type="file"] {
      border: 1px solid #ccc;
      border-radius: 6px;
      padding: 6px;
      background: #f8f8f8;
    }

    .import-export button,
    .import-export a {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 8px 14px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      transition: 0.3s;
      text-decoration: none;
    }

    .import-export button:hover,
    .import-export a:hover {
      background-color: #0056b3;
    }

    .import-export a.btn-export {
      background-color: #28a745;
    }
    .import-export a.btn-export:hover {
      background-color: #1f8c3d;
    }

    /* Bagian tengah: search */
    .search-form {
      display: flex;
      align-items: center;
      gap: 10px;
      flex: 1;
      justify-content: center;
    }

    .search-form input {
      padding: 8px 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      width: 220px;
    }

    .search-form button {
      background-color: #17a2b8;
      color: white;
      border: none;
      padding: 8px 14px;
      border-radius: 6px;
      cursor: pointer;
      transition: 0.3s;
    }
    .search-form button:hover {
      background-color: #138496;
    }

    /* Bagian kanan: tambah siswa */
    .btn-add {
      background-color: #ffc107;
      color: black;
      padding: 8px 14px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      transition: 0.3s;
    }

    .btn-add:hover {
      background-color: #e0a800;
      color: white;
    }

    /* ======== TABEL ======== */
    table {
      border-collapse: collapse;
      width: 100%;
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 1px 6px rgba(0,0,0,0.1);
    }

    th, td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: center;
    }

    th {
      background-color: #343a40;
      color: white;
    }

    .btn-warning { background-color: #ff9800; color: white; padding: 6px 10px; border-radius: 4px; text-decoration: none; }
    .btn-danger { background-color: #dc3545; color: white; padding: 6px 10px; border-radius: 4px; text-decoration: none; }
    .btn-warning:hover { background-color: #e68900; }
    .btn-danger:hover { background-color: #c82333; }

    @media (max-width: 768px) {
      .content {
        margin-left: 0;
      }
      .top-actions {
        flex-direction: column;
        align-items: stretch;
      }
      .search-form {
        justify-content: flex-start;
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

  <!-- MAIN -->
  <main class="content">
    <h2>Data Siswa</h2>

    <div class="top-actions">
      <!-- Kiri: Import & Export -->
      <form class="import-export" action="import.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file_excel" required>
        <button type="submit"><i class="fas fa-file-import"></i> Import</button>
        <a href="export.php" class="btn-export"><i class="fas fa-file-export"></i> Export</a>
      </form>

      <!-- Tengah: Search -->
      <form class="search-form" method="GET" action="">
        <input type="text" name="search" placeholder="Cari nama siswa...">
        <button type="submit"><i class="fas fa-search"></i> Cari</button>
      </form>

      <!-- Kanan: Tambah Siswa -->
      <a href="tambah_siswa.php" class="btn-add"><i class="fas fa-user-plus"></i> Tambah Siswa</a>
    </div>

    <!-- Tabel Data -->
    <table>
      <thead>
        <tr>
          <th>Absen</th>
          <th>NIS</th>
          <th>Nama</th>
          <th>Wali Kelas</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <!-- Data siswa di sini -->
      </tbody>
    </table>
  </main>
</body>
</html>
