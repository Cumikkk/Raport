<?php
include '../../includes/header.php';
include '../../koneksi.php';
?>

<body>
    <?php include '../../includes/navbar.php'; ?>

    <main class="content">
        <div class="cards row" style="margin-top: -50px;">
            <div class="col-12">
                <div class="card shadow-sm mb-3" style="border-radius: 15px;">

                    <!-- Tabel Data Mapel -->
                    <div class="card-body">
                        <h5 class="fw-semibold fs-4" style=" text-align: center">Data Kurikulum</h5>
                        <!-- Form Pilih Kelas -->
                        <form method="GET" class="mb-3">
                            <label for="kelasSelect" class="form-label fw-semibold">Pilih Kelas</label>
                            <select id="kelasSelect" name="id_kelas" class="form-select" onchange="this.form.submit()">
                                <option value="" disabled selected>-- Pilih Kelas --</option>
                                <?php
                                $kelasQuery = mysqli_query($koneksi, "SELECT id_kelas, nama_kelas FROM kelas");
                                while ($k = mysqli_fetch_assoc($kelasQuery)) {
                                    $selected = (isset($_GET['id_kelas']) && $_GET['id_kelas'] == $k['id_kelas']) ? 'selected' : '';
                                    echo "<option value='{$k['id_kelas']}' $selected>{$k['nama_kelas']}</option>";
                                }
                                ?>
                            </select>
                        </form>
                    </div>

                </div>
                <!-- Daftar Mapel -->
                <?php
                if (isset($_GET['id_kelas'])) {
                    $id_kelas = $_GET['id_kelas'];

                    // ambil semua mapel dari tabel mata_pelajaran
                    $mapelQuery = mysqli_query($koneksi, "SELECT * FROM mata_pelajaran ORDER BY kelompok_mata_pelajaran, nama_mata_pelajaran ASC");

                    // simpan semua hasil ke array dulu
                    $mapelList = [];
                    while ($row = mysqli_fetch_assoc($mapelQuery)) {
                        $mapelList[] = $row;
                    }

                    $aktifMapel = []; // sementara kosong dulu

                    // kelompokkan mapel berdasarkan kategori
                    $kategoriMapel = [];
                    foreach ($mapelList as $m) {
                        $kategori = $m['kelompok_mata_pelajaran'] ?? 'Lainnya';
                        $kategoriMapel[$kategori][] = $m;
                    }

                    echo '<form method="POST" action="simpan_kurikulum.php">';
                    echo '<input type="hidden" name="id_kelas" value="' . $id_kelas . '">';

                    echo '<div class="row">'; // wrapper row

                    $count = 0;
                    foreach ($kategoriMapel as $kategori => $mapelPerKategori) {
                        echo '<div class="col-md-6 mb-3">'; // 2 card per baris
                        echo '
                            <div class="card shadow-sm">
                                <div class="card-header bg-primary text-white fw-bold">
                                    Kategori: ' . htmlspecialchars($kategori) . '
                                </div>
                                <div class="card-body bg-light">
                        ';

                        foreach ($mapelPerKategori as $m) {
                            $checked = in_array($m['id_mata_pelajaran'], $aktifMapel) ? 'checked' : '';
                            echo '
                                <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2 bg-white shadow-sm">
                                    <div><strong>' . htmlspecialchars($m['nama_mata_pelajaran']) . '</strong></div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="mapel[]" value="' . $m['id_mata_pelajaran'] . '" ' . $checked . '>
                                    </div>
                                </div>
                            ';
                        }

                        echo '
                                </div>
                            </div>
                        ';
                        echo '</div>'; // tutup col

                        $count++;
                        if ($count % 2 == 0) {
                            echo '</div><div class="row">'; // buat baris baru setiap 2 card
                        }
                    }

                    echo '</div>'; // tutup row terakhir
                    echo '<button type="submit" class="btn btn-success mt-3"><i class="fa fa-save"></i> Simpan Perubahan</button>';
                    echo '</form>';
                } else {
                    echo '<p class="text-muted">Pilih kelas terlebih dahulu untuk menampilkan daftar mapel.</p>';
                }
                ?>

            </div>
        </div>
    </main>

    <style>
        /* Tambahan CSS Responsif */
        @media (max-width: 768px) {
            .top-bar {
                flex-direction: column !important;
                align-items: flex-center !important;
            }

            .action-buttons {
                margin-top: 10px;
                width: 100%;
                justify-content: center !important;
                flex-wrap: wrap;
            }

            .h5 {
                justify-content: center;
            }

            .action-buttons a,
            .action-buttons button {
                width: auto;
            }
        }
    </style>

    <?php include '../../includes/footer.php'; ?>
    </style>