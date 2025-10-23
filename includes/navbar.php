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

  <a href="/RAPORT/logout.php" class="logout-btn btn-danger desktop-only">
    <i class="fas fa-right-from-bracket"></i> Logout
  </a>
</header>

<!-- Overlay -->
<label for="menu-toggle" class="overlay"></label>

<!-- SIDEBAR -->
<aside class="sidebar">
  <nav class="menu">
    <?php
      $current_url = $_SERVER['REQUEST_URI'];
      $is_lembaga = str_contains($current_url, '/Lembaga/');
      $is_rapor   = str_contains($current_url, '/Rapor/');
    ?>

    <a href="/RAPORT/includes/dashboard.php" class="home-link <?= str_contains($current_url, 'dashboard.php') ? 'active' : '' ?>">
      <i class="fas fa-home"></i><span>Beranda</span>
    </a>

    <!-- Menu Lembaga -->
    <details <?= $is_lembaga ? 'open' : '' ?>>
      <summary><span><i class="fas fa-building"></i> Lembaga</span><i class="fas fa-angle-right arrow"></i></summary>
      <ul>
        <li><a href="/RAPORT/Lembaga/data_sekolah/data_sekolah.php" class="<?= str_contains($current_url, 'data_sekolah') ? 'active' : '' ?>">Data Sekolah</a></li>
        <li><a href="/RAPORT/Lembaga/data_siswa/data_siswa.php" class="<?= str_contains($current_url, 'data_siswa') ? 'active' : '' ?>">Data Siswa</a></li>
        <li><a href="/RAPORT/Lembaga/data_guru/data_guru.php" class="<?= str_contains($current_url, 'data_guru') ? 'active' : '' ?>">Data Guru</a></li>
        <li><a href="/RAPORT/Lembaga/data_kelas/datakelas.php" class="<?= str_contains($current_url, 'data_kelas') ? 'active' : '' ?>">Data Kelas</a></li>
        <li><a href="/RAPORT/Lembaga/data_semester/data_semester.php" class="<?= str_contains($current_url, 'data_semester') ? 'active' : '' ?>">Semester Ganjil/Genap</a></li>
        <li><a href="/RAPORT/Lembaga/data_mapel/data_mapel.php" class="<?= str_contains($current_url, 'data_mapel') ? 'active' : '' ?>">Mata Pelajaran</a></li>
        <li><a href="/RAPORT/Lembaga/data_ekstra/data_ekstra.php" class="<?= str_contains($current_url, 'data_ekstra') ? 'active' : '' ?>">Ekstrakurikuler</a></li>
      </ul>
    </details>

    <!-- Menu Rapor -->
    <details <?= $is_rapor ? 'open' : '' ?>>
      <summary><span><i class="fas fa-book"></i> Rapor</span><i class="fas fa-angle-right arrow"></i></summary>
      <ul>
        <li><a href="/RAPORT/Rapor/pengaturan_cetak_rapor/pengaturan_cetak_rapor.php" class="<?= str_contains($current_url, 'pengaturan_cetak_rapor') ? 'active' : '' ?>">Peraturan Cetak</a></li>
        <li><a href="/RAPORT/Rapor/nilai_mapel/mapel.php" class="<?= str_contains($current_url, 'nilai_mapel') ? 'active' : '' ?>">Nilai Mapel</a></li>
        <li><a href="/RAPORT/Rapor/absensi/data_absensi.php" class="<?= str_contains($current_url, 'data_absensi') ? 'active' : '' ?>">Absensi</a></li>
        <li><a href="/RAPORT/Rapor/nilai_ekstra/nilai_ekstra.php" class="<?= str_contains($current_url, 'nilai_ekstra') ? 'active' : '' ?>">Nilai Ekstrakurikuler</a></li>
        <li><a href="/RAPORT/Rapor/cetak_rapor/data_rapor.php" class="<?= str_contains($current_url, 'data_rapor') ? 'active' : '' ?>">Cetak Rapor</a></li>
      </ul>
    </details>

    <a href="/RAPORT/tambah user/tambah_user.php" class="add-user <?= str_contains($current_url, 'tambah_user') ? 'active' : '' ?>">
      <span><i class="fas fa-user"></i> Tambah User</span>
      <i class="fas fa-plus plus-icon"></i>
    </a>

    <a href="/RAPORT/logout.php" class="logout-btn btn-danger mobile-only">
      <i class="fas fa-right-from-bracket"></i> Logout
    </a>
  </nav>
</aside>

<!-- Tambahkan CSS highlight -->
<style>
  /* efek hover */
  .menu a:hover {
    background-color: rgba(29, 82, 162, 0.2); /* biru samar saat hover */
    transition: 0.3s;
  }

  /* efek aktif */
  .menu a.active {
    background-color: rgba(29, 82, 162, 0.3); /* biru samar */
    border-left: 4px solid #1d52a2;
    font-weight: 600;
  }

  .menu a.active:hover {
    background-color: rgba(29, 82, 162, 0.4);
  }

  /* agar efek juga berlaku di submenu */
  details ul li a {
    display: block;
    padding: 6px 16px;
    border-radius: 4px;
  }
</style>
