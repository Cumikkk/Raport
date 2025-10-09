<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard | Data Siswa</title>

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
      padding: 30px;
    }

    h2 {
      color: #004080;
      font-weight: 700;
      margin-bottom: 25px;
    }

    /* === Bagian Atas (Import, Export, Search, Tambah) === */
    .top-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 25px;
    }

    .import-export, .search-form {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    .import-export input[type="file"] {
      border: 1px solid #ccc;
      padding: 6px;
      border-radius: 6px;
      background: white;
      font-size: 14px;
    }

    .btn-main, .btn-export, .btn-add, .search-form button, .btn-delete-selected {
      background: #004080;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 10px 18px;
      font-weight: 600;
      font-size: 15px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: 0.2s;
      text-decoration: none;
    }

    .btn-export {
      background: #007bff;
    }

    .btn-add {
      background: #28a745;
    }

    .btn-delete-selected {
      background: #dc3545;
      margin-top: 15px;
    }

    .btn-main:hover, .btn-export:hover, .btn-add:hover, .btn-delete-selected:hover {
      opacity: 0.9;
      transform: translateY(-1px);
    }

    .search-form input {
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }

    /* === TABEL DATA SISWA === */
    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    thead {
      background: #004080;
      color: #fff;
    }

    th, td {
      padding: 12px 15px;
      text-align: left;
      border: 1px solid #ccc;
    }

    tbody tr:nth-child(even) {
      background: #f9f9f9;
    }

    tbody tr:hover {
      background: #eef3ff;
    }

    /* Tombol Aksi */
    .btn-detail, .btn-edit, .btn-delete {
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      color: #fff;
      font-weight: 600;
      font-size: 13px;
      cursor: pointer;
      text-decoration: none;
      transition: 0.2s;
    }

    .btn-detail { background: #17a2b8; } /* biru muda */
    .btn-edit { background: #ffc107; }   /* kuning */
    .btn-delete { background: #dc3545; } /* merah */

    .btn-detail:hover { background: #138496; }
    .btn-edit:hover { background: #e0a800; }
    .btn-delete:hover { background: #c82333; }

    .action-btns {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    th input[type="checkbox"],
    td input[type="checkbox"] {
      transform: scale(1.2);
      cursor: pointer;
    }

    /* === PAGINATION === */
    .pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-top: 25px;
      gap: 8px;
    }

    .pagination a, .pagination span {
      display: inline-block;
      padding: 8px 14px;
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 6px;
      color: #004080;
      font-weight: 600;
      text-decoration: none;
      transition: 0.2s;
    }

    .pagination a:hover {
      background: #004080;
      color: #fff;
    }

    .pagination .active {
      background: #004080;
      color: #fff;
      border-color: #004080;
    }

    @media (max-width: 768px) {
      .top-actions {
        flex-direction: column;
        align-items: stretch;
      }
      .import-export, .search-form {
        width: 100%;
        justify-content: space-between;
      }
      .btn-main, .btn-export, .btn-add, .search-form button, .btn-delete-selected {
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

      details>
        <summary><span><i class="fas fa-building"></i> Lembaga</span> <i class="fas fa-angle-right arrow"></i></summary>
        <ul>
          <li><a href="../data_sekolah/data_sekolah.php">Data Sekolah</a></li>
          <li><a href="../data_siswa/data_siswa.php">Data Siswa</a></li>
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
      <!-- Import & Export -->
      <form class="import-export" action="import.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file_excel" required>
        <button type="submit" class="btn-main"><i class="fas fa-file-import"></i> Import</button>
        <a href="export.php" class="btn-export"><i class="fas fa-file-export"></i> Export</a>
      </form>

      <!-- Search -->
      <form class="search-form" method="GET" action="">
        <input type="text" name="search" placeholder="Cari nama siswa...">
        <button type="submit"><i class="fas fa-search"></i> Cari</button>
      </form>

      <!-- Tambah Siswa -->
      <a href="tambah_siswa.php" class="btn-add"><i class="fas fa-user-plus"></i> Tambah Siswa</a>
    </div>

    <!-- Form untuk hapus banyak -->
    <form id="bulkDeleteForm" method="POST" action="hapus_banyak.php" onsubmit="return confirm('Yakin ingin menghapus data terpilih?')">

      <table>
        <thead>
          <tr>
            <th><input type="checkbox" id="selectAll"></th>
            <th>Absen</th>
            <th>NIS</th>
            <th>Nama</th>
            <th>Wali Kelas</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="checkbox" name="selected[]" value="1"></td>
            <td>1</td>
            <td>20250101</td>
            <td>Ahmad Fauzi</td>
            <td>Ust. Rudi</td>
            <td class="action-btns">
              <a href="detail_siswa.php?id=1" class="btn-detail">Detail</a>
              <a href="edit_siswa.php?id=1" class="btn-edit">Edit</a>
              <a href="hapus_siswa.php?id=1" class="btn-delete" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
            </td>
          </tr>
          <!-- Tambahkan data lainnya -->
        </tbody>
      </table>

      <!-- Tombol hapus di bawah tabel -->
      <button type="submit" class="btn-delete-selected">
        <i class="fas fa-trash"></i> Hapus Terpilih
      </button>
    </form>

    <!-- Pagination -->
    <div class="pagination">
      <a href="#">&lt;</a>
      <a href="#" class="active">1</a>
      <a href="#">2</a>
      <a href="#">3</a>
      <a href="#">&gt;</a>
    </div>
  </main>

  <script>
    document.getElementById('selectAll').addEventListener('click', function() {
      const checkboxes = document.querySelectorAll('input[name="selected[]"]');
      checkboxes.forEach(cb => cb.checked = this.checked);
    });
  </script>

</body>
</html>
