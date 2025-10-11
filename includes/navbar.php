<!-- Checkbox hack -->
<input type="checkbox" id="menu-toggle" />

<!-- TOPBAR -->
<header class="topbar">
  <label for="menu-toggle" class="hamburger">
    <i class="fas fa-bars"></i>
    <i class="fas fa-times close-icon"></i>
  </label>

  <div class="topbar-center">
    <img src="/RAPORT/assets/img/logo/logo navbar.png" alt="Logo" class="dashboard-logo">
  </div>

  <a href="/RAPORT/logout.php" class="logout-btn desktop-only">
    <i class="fas fa-right-from-bracket"></i> Logout
  </a>
</header>

<!-- Overlay -->
<label for="menu-toggle" class="overlay"></label>

<!-- SIDEBAR -->
<aside class="sidebar">
  <nav class="menu">
    <a href="/RAPORT/includes/dashboard.php" class="home-link">
      <i class="fas fa-home"></i><span>Beranda</span>
    </a>

    <details>
      <summary><span><i class="fas fa-building"></i> Lembaga</span><i class="fas fa-angle-right arrow"></i></summary>
      <ul>
        <li><a href="/RAPORT/Lembaga/data_sekolah/data_sekolah.php">Data Sekolah</a></li>
        <li><a href="/RAPORT/Lembaga/data_siswa/data_siswa.php">Data Siswa</a></li>
        <li><a href="/RAPORT/Lembaga/data_guru/data_guru.php">Data Guru</a></li>
        <li><a href="/RAPORT/Lembaga/data_kelas/datakelas.php">Data Kelas</a></li>
        <li><a href="/RAPORT/Lembaga/data_kelas/datakelas.php">Semester Ganjil/Genap</a></li>
        <li><a href="/RAPORT/Lembaga/data_mapel/data_mapel.php">Mata Pelajaran</a></li>
        <li><a href="/RAPORT/Lembaga/data_ekstra/data_ekstra.php">Ekstrakurikuler</a></li>
      </ul>
    </details>

    <details>
      <summary><span><i class="fas fa-book"></i> Rapor</span><i class="fas fa-angle-right arrow"></i></summary>
      <ul>
        <li><a href="/RAPORT/Lembaga/data_sekolah/data_sekolah.php">Peraturan Cetak</a></li>
        <li><a href="/RAPORT/Lembaga/data_siswa/data_siswa.php">Nilai Mapel</a></li>
        <li><a href="/RAPORT/Lembaga/data_guru/data_guru.php">Absensi</a></li>
        <li><a href="/RAPORT/Lembaga/data_kelas/datakelas.php">Nilai Ekstrakurikuler</a></li>
        <li><a href="/RAPORT/Lembaga/data_kelas/datakelas.php">Cetak Rapor</a></li>
      </ul>
    </details>

    <a href="/RAPORT/tambah user/tambah_user.php" class="add-user">
      <span><i class="fas fa-user"></i> Tambah User</span>
      <i class="fas fa-plus plus-icon"></i>
    </a>

    <a href="/RAPORT/logout.php" class="logout-btn mobile-only">
      <i class="fas fa-right-from-bracket"></i> Logout
    </a>
  </nav>
</aside>
