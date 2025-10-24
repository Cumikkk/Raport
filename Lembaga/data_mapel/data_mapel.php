<?php
include '../../includes/header.php';
include '../../koneksi.php';
?>

<?php
// include header & koneksi awal sudah kamu punya
// Ubah query supaya ambil id juga
$query = "SELECT id_mata_pelajaran, nama_mata_pelajaran, kelompok_mata_pelajaran FROM mata_pelajaran";
$result = mysqli_query($koneksi, $query);

// Bentuk array mapel berdasarkan kategori (simpan objek lengkap)
$dataMapel = [];
$kategoriList = [];

while ($row = mysqli_fetch_assoc($result)) {
  $kategori = strtolower($row['kelompok_mata_pelajaran']);
  // simpan objek lengkap supaya JS bisa akses id & nama
  $dataMapel[$kategori][] = [
    'id_mata_pelajaran' => (int)$row['id_mata_pelajaran'],
    'nama_mata_pelajaran' => $row['nama_mata_pelajaran']
  ];
  $kategoriList[] = $kategori;
}

// Hapus duplikat kategori dan reindex agar aman
$kategoriList = array_values(array_unique($kategoriList));
?>


<body>
  <?php include '../../includes/navbar.php'; ?>

  <main class="content">
    <div class="cards row" style="margin-top:-50px;">
      <div class="col-12">
        <div class="card shadow-sm" style="border-radius:15px;">
          <div class="card-header bg-white border-0 p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
              <!-- Header Title -->
              <h5 class="fw-semibold fs-4 mb-0">Data Mata Pelajaran</h5>

              <!-- Tombol group -->
              <div class="d-flex flex-wrap gap-2 tombol-aksi">
                <a href="data_mapel_tambah.php" class="btn btn-primary btn-md d-flex align-items-center gap-1 p-2 pe-3 " style="border-radius: 5px;">
                  <i class="fa-solid fa-plus fa-lg"></i>
                  Tambah
                </a>

                <a href="data_mapel_import.php" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                  <i class="fa-solid fa-file-arrow-down fa-lg"></i>
                  <span>Import</span>
                </a>

                <a href="" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                  <i class="fa-solid fa-file-arrow-up fa-lg"></i>
                  <span>Export</span>
                </a>
              </div>
            </div>
          </div>
          <!-- Tabel Data -->
          <div class="card-body">
            <div class="card-header bg-primary">
              <ul class="nav nav-tabs nav-fill bg-primary" id="kategoriTabs" role="tablist">
                <?php
                $first = true;
                foreach ($kategoriList as $kategori) {
                  $active = $first ? 'active' : '';
                  $style = $first
                    ? 'style="border:none; color:black; font-weight:600;"'
                    : 'style="border:none; color:white; font-weight:600;"';
                  $first = false;
                  echo '
                  <li class="nav-item" role="presentation">
                    <a class="nav-link ' . $active . ' fw-semibold" data-category="' . $kategori . '" href="#" ' . $style . '>
                      ' . ucfirst($kategori) . '
                    </a>
                  </li>
                ';
                }
                ?>
              </ul>

              <script>
                // buat ubah warna text saat tab aktif
                const navLinks = document.querySelectorAll('#kategoriTabs .nav-link');

                navLinks.forEach(link => {
                  link.addEventListener('click', function() {
                    navLinks.forEach(l => l.classList.remove('active', 'text-dark'));
                    navLinks.forEach(l => l.style.color = 'white');

                    this.classList.add('active', 'text-dark');
                    this.style.color = 'black';
                  });
                });
              </script>
            </div>


            <div class="card-body">
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">

                <h5 class="card-title mb-3" id="judulKategori">Mata Pelajaran Wajib</h5>
                <!-- Search -->
                <div class="d-flex flex-wrap gap-2 tombol-aksi mb-3">
                  <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search"
                    style="width: 200px;">
                  <button id="searchBtn"
                    class="btn btn-outline-secondary btn-sm p-2 rounded-3 d-flex align-items-center justify-content-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                      class="bi bi-search" viewBox="0 0 16 16">
                      <path
                        d="M11 6a5 5 0 1 0-2.9 4.7l3.85 3.85a1 1 0 0 0 1.414-1.414l-3.85-3.85A4.978 4.978 0 0 0 11 6zM6 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
                    </svg>
                  </button>
                </div>
              </div>

              <div id="mapelContainer" class="list-group text-start">
                <!-- Mapel akan muncul di sini -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <script>
    // Ambil data dari PHP
    const dataMapel = <?= json_encode($dataMapel ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?>;
    const kategoriList = <?= json_encode($kategoriList ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?>;

    const tabs = document.querySelectorAll('#kategoriTabs .nav-link');
    const mapelContainer = document.getElementById('mapelContainer');
    const judulKategori = document.getElementById('judulKategori');

    // Fungsi menampilkan mapel sesuai kategori
    function showMapel(category) {
      mapelContainer.innerHTML = '';

      if (!dataMapel[category] || dataMapel[category].length === 0) {
        mapelContainer.innerHTML = '<p class="text-muted">Belum ada data mapel pada kategori ini.</p>';
        return;
      }

      dataMapel[category].forEach((mapel, index) => {
        const div = document.createElement('div');
        div.className = 'd-flex justify-content-between align-items-center border rounded p-2 bg-white shadow-sm mb-2';

        // Escape nama agar aman untuk dipakai di onclick
        const namaEscaped = JSON.stringify(mapel.nama_mata_pelajaran);

        div.innerHTML = `
        <div>
          <strong>${index + 1}. ${mapel.nama_mata_pelajaran}</strong>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-warning" onclick="editMapel(${mapel.id_mata_pelajaran})">
            <i class="bi bi-pencil-square"></i> Edit
          </button>
          <button class="btn btn-sm btn-danger" onclick="hapusMapel(${mapel.id_mata_pelajaran})">
            <i class="bi bi-trash"></i> Hapus
          </button>
        </div>
      `;
        mapelContainer.appendChild(div);
      });

      // Ganti judul di atas daftar mapel
      const kapital = category.charAt(0).toUpperCase() + category.slice(1);
      judulKategori.textContent = `Mata Pelajaran ${kapital}`;
    }

    // Fungsi tombol Edit → arahkan ke halaman edit_mapel.php
    function editMapel(id) {
      window.location.href = `data_mapel_edit.php?id=${id}`;
    }

    // Fungsi tombol Hapus → arahkan ke hapus_mapel.php
    function hapusMapel(id) {
      if (confirm("Yakin ingin menghapus ?")) {
        window.location.href = "hapus_mapel.php?id=" + id;
      }
    }


    // Event click untuk tiap tab
    tabs.forEach(tab => {
      tab.addEventListener('click', e => {
        e.preventDefault();
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        showMapel(tab.dataset.category);
      });
    });

    // Tampilkan data awal (kategori pertama dari PHP, jika ada)
    if (kategoriList.length > 0) {
      showMapel(kategoriList[0]);
    }
  </script>






  <style>
    /* RESPONSIVE KHUSUS UNTUK TOMBOL */
    @media (max-width: 768px) {
      .tombol-aksi {
        width: 100%;
        justify-content: center !important;
        margin-top: 10px;
      }

      .card-header h5 {
        width: 100%;
        text-align: center;
      }
    }
  </style>

  <?php include '../../includes/footer.php'; ?>