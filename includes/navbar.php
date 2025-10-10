<!-- checkbox hack -->
<input type="checkbox" id="menu-toggle" />

<!-- TOPBAR -->
<header class="topbar">
    <label for="menu-toggle" class="hamburger">
        <i class="fas fa-bars"></i>
        <i class="fas fa-times close-icon"></i>
    </label>

    <div class="topbar-center">
        <<<<<<< HEAD:include/navbar.php
            <img src="../assets/img/logo/logo E-Rapor For Navbar.png" style="margin-left:8px; " alt="Logo" class="dashboard-logo">
            =======
            <img src="Gambar/Tanpa judul (300 x 138 piksel).png" alt="Logo" class="dashboard-logo">
            >>>>>>> fd25e43bb3f8abd4861874878913243d0e4b979e:dashboard.php
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
        <a href="../include/dashboard.php" class="home-link">
            <i class="fas fa-home"></i>
            <span>Beranda</span>
        </a>

        <details>
            <summary><span><i class="fas fa-building"></i> Lembaga</span> <i class="fas fa-angle-right arrow"></i></summary>
            <ul>
                <li><a href="#">Data Sekolah</a></li>
                <<<<<<< HEAD:include/navbar.php
                    <li><a href="#">Data Siswa</a></li>
                    <li><a href="#">Data Guru</a></li>
                    <li><a href="../sidebar/datakelas.php">Data Kelas</a></li>
                    <li><a href="#">Data Semester Ganjil/Genap</a></li>
                    <li><a href="#">Data Mata Pelajaran</a></li>
                    =======
                    <li><a href="lembaga/data siswa/data_siswa.php">Data Siswa</a></li>
                    <li><a href="#">Kelas</a></li>
                    <li><a href="#">Semester Ganjil/Genap</a></li>
                    <li><a href="#">Mata Pelajaran</a></li>
                    >>>>>>> fd25e43bb3f8abd4861874878913243d0e4b979e:dashboard.php
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