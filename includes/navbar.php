<?php
// includes/navbar.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$current_url = $_SERVER['REQUEST_URI'] ?? '/';
$roleRaw = $_SESSION['role_user'] ?? '';
$roleKey = strtolower(trim($roleRaw));
$username_display = $_SESSION['username'] ?? 'Guest';

// Tentukan "Home" sesuai role
$homeLink = '/RAPORT/includes/dashboard.php';
if ($roleKey === 'guru') {
  $homeLink = '/RAPORT/includes/dashboard.php';
}
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

  <!-- Tombol logout desktop DIHILANGKAN -->
</header>

<!-- Overlay -->
<label for="menu-toggle" class="overlay"></label>

<!-- SIDEBAR -->
<aside class="sidebar">
  <nav class="menu">
    <div class="user-info" style="padding:12px 16px;">
      <strong><?= htmlspecialchars($username_display) ?></strong><br>
      <small style="color: #666;"><?= $roleRaw !== '' ? htmlspecialchars($roleRaw) : 'Guest' ?></small>
    </div>

    <!-- Beranda sesuai role -->
    <a href="<?= $homeLink ?>"
      class="home-link <?= str_contains($current_url, 'dashboard.php') || str_contains($current_url, 'datakelas.php') ? 'active' : '' ?>">
      <i class="fas fa-home"></i><span>Beranda</span>
    </a>

    <?php if ($roleKey === 'admin'): ?>
      <!-- MENU LEMBAGA -->
      <details <?= str_contains($current_url, '/Lembaga/') ? 'open' : '' ?>>
        <summary><span><i class="fas fa-building"></i> Lembaga</span><i class="fas fa-angle-right arrow"></i></summary>
        <ul>
          <li><a href="/RAPORT/Lembaga/data_sekolah/data_sekolah.php" class="<?= str_contains($current_url, 'data_sekolah') ? 'active' : '' ?>">Data Sekolah</a></li>
          <li><a href="/RAPORT/Lembaga/data_siswa/data_siswa.php" class="<?= str_contains($current_url, 'data_siswa') ? 'active' : '' ?>">Data Siswa</a></li>
          <li><a href="/RAPORT/Lembaga/data_guru/data_guru.php" class="<?= str_contains($current_url, 'data_guru') ? 'active' : '' ?>">Data Guru</a></li>
          <li><a href="/RAPORT/Lembaga/data_kelas/data_kelas.php" class="<?= str_contains($current_url, 'data_kelas') ? 'active' : '' ?>">Data Kelas</a></li>
          <li><a href="/RAPORT/Lembaga/data_semester/data_semester.php" class="<?= str_contains($current_url, 'data_semester') ? 'active' : '' ?>">Semester</a></li>
          <li><a href="/RAPORT/Lembaga/data_mapel/data_mapel.php" class="<?= str_contains($current_url, 'data_mapel') ? 'active' : '' ?>">Mata Pelajaran</a></li>
          <li><a href="/RAPORT/Lembaga/data_kurlum/data_kurlum.php" class="<?= str_contains($current_url, 'data_kurlum') ? 'active' : '' ?>">Kurikulum</a></li>
          <li><a href="/RAPORT/Lembaga/data_ekstra/data_ekstra.php" class="<?= str_contains($current_url, 'data_ekstra') ? 'active' : '' ?>">Ekstrakurikuler</a></li>
        </ul>
      </details>

      <!-- MENU RAPOR -->
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

      <!-- USER (ONE CLICK + ALWAYS BOLD) -->
      <a href="/RAPORT/tambah%20user/data_user.php"
        class="user-link <?= (str_contains($current_url, 'tambah_user') || str_contains($current_url, 'data_user')) ? 'active' : '' ?>">
        <i class="fas fa-user"></i><span>User</span>
      </a>

    <?php elseif ($roleKey === 'guru'): ?>
      <a href="/RAPORT/Lembaga/data_siswa/guru/data_siswa.php"
        class="<?= str_contains($current_url, 'data_siswa') ? 'active' : '' ?>">
        <i class="fas fa-users"></i><span>Data Siswa</span>
      </a>

    <?php else: ?>
      <a href="/RAPORT/login.php">Login</a>
    <?php endif; ?>

    <!-- LOGOUT -->
    <a href="/RAPORT/logout.php" class="logout-btn btn-danger" style="margin-top:12px; display:block; padding:8px 12px; border-radius:6px; color:#fff; text-decoration:none;">
      <i class="fas fa-right-from-bracket"></i> Logout
    </a>

  </nav>
</aside>

<style>
  /* === TOP LEVEL ITEM (Beranda, User, Summary) === */
  .menu>a,
  .menu>details>summary {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    margin: 4px 8px;
    border-radius: 6px;
    color: #111827;
    text-decoration: none;
    cursor: pointer;
  }

  /* Hilangkan icon bawaan details */
  .menu>details>summary::-webkit-details-marker {
    display: none;
  }

  .menu>details>summary .arrow {
    margin-left: auto;
  }

  /* Hover */
  .menu a:hover,
  .menu>details>summary:hover {
    background-color: rgba(29, 82, 162, 0.2);
    transition: 0.3s;
  }

  /* Active */
  .menu a.active {
    background-color: rgba(65, 188, 255, 0.3);
    border-left: 4px solid #1d52a2;
    font-weight: 600;
  }

  /* === PENTING: USER SELALU TEBAL === */
  .menu a.user-link {
    font-weight: 600 !important;
  }

  /* Dropdown items */
  details ul li a {
    display: block;
    padding: 6px 32px;
    border-radius: 4px;
  }

  /* Responsif */
  @media (max-width: 768px) {
    .sidebar {
      transform: translateX(-100%);
      transition: transform 0.3s ease;
    }

    #menu-toggle:checked~.sidebar {
      transform: translateX(0);
    }

    .overlay {
      position: fixed;
      inset: 0;
      background-color: rgba(0, 0, 0, 0.3);
      display: none;
    }

    #menu-toggle:checked~.overlay {
      display: block;
    }
  }
</style>
