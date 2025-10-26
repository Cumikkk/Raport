<?php
// includes/navbar.php
// Pastikan header.php sudah di-include sebelumnya sehingga session sudah aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_url = $_SERVER['REQUEST_URI'];
$role = $_SESSION['role'] ?? ''; // '' jika belum login
$username_display = $_SESSION['nama_lengkap_user'] ?? $_SESSION['username'] ?? 'Guest';
?>

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

  <!-- Tombol logout desktop -->
  <a href="/RAPORT/logout.php" class="logout-btn btn-danger desktop-only" style="text-decoration:none;padding:8px 12px;border-radius:6px;color:#fff;">
    <i class="fas fa-right-from-bracket"></i> Logout
  </a>
</header>

<!-- Overlay -->
<label for="menu-toggle" class="overlay"></label>

<!-- SIDEBAR -->
<aside class="sidebar">
  <nav class="menu">
    <div class="user-info" style="padding:12px 16px;">
      <strong><?= htmlspecialchars($username_display) ?></strong><br>
      <small style="color: #666;"><?= $role ? ucfirst($role) : 'Guest' ?></small>
    </div>

    <a href="/RAPORT/includes/dashboard.php" class="home-link <?= str_contains($current_url, 'dashboard.php') ? 'active' : '' ?>">
      <i class="fas fa-home"></i><span>Beranda</span>
    </a>

<?php if ($role === 'admin'): ?>
    <!-- Admin: semua menu -->
    <details <?= str_contains($current_url, '/Lembaga/') ? 'open' : '' ?>>
      <summary><span><i class="fas fa-building"></i> Lembaga</span><i class="fas fa-angle-right arrow"></i></summary>
      <ul>
        <li><a href="/RAPORT/Lembaga/data_sekolah/data_sekolah.php" class="<?= str_contains($current_url, 'data_sekolah') ? 'active' : '' ?>">Data Sekolah</a></li>
        <li><a href="/RAPORT/Lembaga/data_siswa/data_siswa.php" class="<?= str_contains($current_url, 'data_siswa') ? 'active' : '' ?>">Data Siswa</a></li>
        <li><a href="/RAPORT/Lembaga/data_guru/data_guru.php" class="<?= str_contains($current_url, 'data_guru') ? 'active' : '' ?>">Data Guru</a></li>
        <li><a href="/RAPORT/Lembaga/data_kelas/datakelas.php" class="<?= str_contains($current_url, 'data_kelas') ? 'active' : '' ?>">Data Kelas</a></li>
        <li><a href="/RAPORT/Lembaga/data_semester/data_semester.php" class="<?= str_contains($current_url, 'data_semester') ? 'active' : '' ?>">Semester</a></li>
        <li><a href="/RAPORT/Lembaga/data_mapel/data_mapel.php" class="<?= str_contains($current_url, 'data_mapel') ? 'active' : '' ?>">Mata Pelajaran</a></li>
        <li><a href="/RAPORT/Lembaga/data_kurlum/data_kurlum.php" class="<?= str_contains($current_url, 'data_kurlum') ? 'active' : '' ?>">Kurikulum</a></li>
        <li><a href="/RAPORT/Lembaga/data_ekstra/data_ekstra.php" class="<?= str_contains($current_url, 'data_ekstra') ? 'active' : '' ?>">Ekstrakurikuler</a></li>
      </ul>
    </details>

    <details <?= str_contains($current_url, '/Rapor/') ? 'open' : '' ?>>
      <summary><span><i class="fas fa-book"></i> Rapor</span><i class="fas fa-angle-right arrow"></i></summary>
      <ul>
        <li><a href="/RAPORT/Rapor/pengaturan_cetak_rapor/pengaturan_cetak_rapor.php" class="<?= str_contains($current_url, 'pengaturan_cetak_rapor') ? 'active' : '' ?>">Peraturan Cetak</a></li>
        <li><a href="/RAPORT/Rapor/nilai_mapel/mapel.php" class="<?= str_contains($current_url, 'nilai_mapel') ? 'active' : '' ?>">Nilai Mapel</a></li>
        <li><a href="/RAPORT/Rapor/absensi/data_absensi.php" class="<?= str_contains($current_url, 'data_absensi') ? 'active' : '' ?>">Absensi</a></li>
        <li><a href="/RAPORT/Rapor/nilai_ekstra/nilai_ekstra.php" class="<?= str_contains($current_url, 'nilai_ekstra') ? 'active' : '' ?>">Nilai Ekstrakurikuler</a></li>
        <li><a href="/RAPORT/Rapor/cetak_rapor/data_rapor.php" class="<?= str_contains($current_url, 'data_rapor') ? 'active' : '' ?>">Cetak Rapor</a></li>
      </ul>
    </details>

    <a href="/RAPORT/tambah%20user/tambah_user.php" class="add-user <?= str_contains($current_url, 'tambah_user') ? 'active' : '' ?>">
      <span><i class="fas fa-user"></i> Tambah User</span>
      <i class="fas fa-plus plus-icon"></i>
    </a>

<?php elseif ($role === 'guru'): ?>
    <!-- Guru: hanya Data Kelas -->
    <details <?= str_contains($current_url, '/Lembaga/') ? 'open' : '' ?>>
      <summary><span><i class="fas fa-building"></i> Lembaga</span><i class="fas fa-angle-right arrow"></i></summary>
      <ul>
        <li><a href="/RAPORT/Lembaga/data_kelas/datakelas.php" class="<?= str_contains($current_url, 'data_kelas') ? 'active' : '' ?>">Data Kelas</a></li>
      </ul>
    </details>

<?php else: ?>
    <!-- Guest / belum login -->
    <a href="/RAPORT/login.php">Login</a>
<?php endif; ?>

    <!-- Logout (selalu tampil agar user bisa keluar) -->
    <a href="/RAPORT/logout.php" class="logout-btn btn-danger" style="margin-top:12px; display:block; padding:8px 12px; border-radius:6px; color:#fff; text-decoration:none;">
      <i class="fas fa-right-from-bracket"></i> Logout
    </a>
  </nav>
</aside>

<style>
  /* efek hover */
  .menu a:hover {
    background-color: rgba(29, 82, 162, 0.2);
    transition: 0.3s;
  }

  /* efek aktif */
  .menu a.active {
    background-color: rgba(29, 82, 162, 0.3);
    border-left: 4px solid #1d52a2;
    font-weight: 600;
  }

  .menu a.active:hover { background-color: rgba(29, 82, 162, 0.4); }
  details ul li a { display:block; padding:6px 16px; border-radius:4px; }
</style>
