<?php
include '../../koneksi.php';
include '../../includes/header.php';

$query = mysqli_query($koneksi, "SELECT DISTINCT nama_ekstrakurikuler FROM ekstrakurikuler ORDER BY nama_ekstrakurikuler ASC");

?>

<body>
    <?php include '../../includes/navbar.php'; ?>

    <main class="content">
        <div class="cards row" style="margin-top: -50px;">
            <div class="col-12">
                <div class="card shadow-sm" style="border-radius: 15px;">
                    <div class="mt-0 d-flex align-items-center flex-wrap mb-0 p-3 top-bar">
                        <!-- Judul di kiri -->
                        <h5 class="mb-1 fw-semibold fs-4" style=" text-align: center">Nilai Ekstrakulikuler</h5>

                        <!-- Tombol di kanan -->
                        <div class="ms-auto d-flex gap-2 action-buttons">
                            <button class="btn btn-primary btn-md px-3 py-2 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambahEkstra">
                                <i class="fa fa-plus"></i> Tambah
                            </button>


                            <a href="nilai_ekstra_import.php" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-file-arrow-down fa-lg"></i>
                                <span>Import</span>
                            </a>

                            <button id="exportBtn" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-file-arrow-up fa-lg"></i>
                                Export
                            </button>
                        </div>
                    </div>

                    <!-- Filter Kelas & Search -->
                    <div class="ms-3 me-3 bg-white d-flex justify-content-between align-items-center flex-wrap p-3 gap-3 rounded shadow-sm">

                        <!-- Filter Ekstrakurikuler -->
                        <div class="d-flex align-items-center gap-2">
                            <label for="selectEkstra" class="fw-semibold">Ekstrakurikuler :</label>
                            <select id="selectEkstra" class="form-select form-select-sm" style="width: 180px;">
                                <option value="">All</option>
                                <?php while ($ek = mysqli_fetch_assoc($query)): ?>
                                    <option value="<?= htmlspecialchars(strtolower($ek['nama_ekstrakurikuler'])) ?>">
                                        <?= htmlspecialchars($ek['nama_ekstrakurikuler']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Search -->
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Cari siswa atau kelas..." style="width: 220px;">
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="toggleEdit">
                            <label class="form-check-label fw-semibold" for="editToggle">Mode Edit</label>
                        </div>

                    </div>

                    <div class="card-body">
                        <?php
                        include '../../koneksi.php';
                        $no = 1;

                        $query = "
                            SELECT 
                                n.id_nilai_ekstrakurikuler,
                                s.nama_siswa,
                                k.nama_kelas,
                                e.nama_ekstrakurikuler,
                                n.nilai_ekstrakurikuler
                            FROM nilai_ekstrakurikuler AS n
                            LEFT JOIN siswa AS s ON n.id_siswa = s.id_siswa
                            LEFT JOIN kelas AS k ON s.id_kelas = k.id_kelas
                            LEFT JOIN ekstrakurikuler AS e ON n.id_ekstrakurikuler = e.id_ekstrakurikuler
                            ORDER BY k.nama_kelas, s.nama_siswa
                        ";
                        $result = mysqli_query($koneksi, $query);

                        // ambil semua ekstra utk select
                        $ekstra = mysqli_query($koneksi, "SELECT id_ekstrakurikuler, nama_ekstrakurikuler FROM ekstrakurikuler");
                        $ekstra_list = [];
                        while ($e = mysqli_fetch_assoc($ekstra)) {
                            $ekstra_list[] = $e;
                        }
                        ?>

                        <form method="POST" action="simpan_nilai_ekstra.php">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead style="background-color:#1d52a2" class="text-center text-white">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Siswa</th>
                                            <th>Kelas</th>
                                            <th>Ekstrakurikuler</th>
                                            <th>Nilai</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        <?php if (mysqli_num_rows($result) > 0): ?>
                                            <?php $no = 1; ?>
                                            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="id[]" value="<?= $row['id_nilai_ekstrakurikuler'] ?>">
                                                        <?= $no++ ?>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                            value="<?= htmlspecialchars($row['nama_siswa']) ?>" disabled>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                            value="<?= htmlspecialchars($row['nama_kelas']) ?>" style="width:100px;" disabled>
                                                    </td>
                                                    <td>
                                                        <select name="nama_ekstra[]" class="form-select form-select-sm" disabled>
                                                            <?php foreach ($ekstra_list as $e) : ?>
                                                                <option value="<?= $e['id_ekstrakurikuler'] ?>"
                                                                    <?= ($e['nama_ekstrakurikuler'] == $row['nama_ekstrakurikuler']) ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($e['nama_ekstrakurikuler']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="nilai[]" class="form-select form-select-sm" disabled>
                                                            <?php
                                                            $opsiNilai = ['A', 'B', 'C', 'D'];
                                                            foreach ($opsiNilai as $n) :
                                                            ?>
                                                                <option value="<?= $n ?>" <?= ($row['nilai_ekstrakurikuler'] == $n) ? 'selected' : '' ?>>
                                                                    <?= $n ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="hapus_nilai_ekstra.php?id=<?= $row['id_nilai_ekstrakurikuler'] ?>"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Yakin ingin menghapus data ini?');">
                                                            <i class="bi bi-trash"></i> Hapus
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Tidak ada data</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-save"></i> Simpan
                                    </button>
                                </div>
                            <?php endif; ?>
                        </form>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const selectEkstra = document.getElementById('selectEkstra');
                                const searchInput = document.getElementById('searchInput');
                                const searchBtn = document.getElementById('searchBtn');
                                const tbody = document.querySelector('tbody');
                                const rows = tbody.querySelectorAll('tr');

                                function getInputValue(td) {
                                    const input = td.querySelector('input');
                                    const select = td.querySelector('select');
                                    if (input) return (input.value || '').toLowerCase();
                                    if (select) {
                                        const selected = select.options[select.selectedIndex];
                                        return selected ? selected.text.toLowerCase() : '';
                                    }
                                    return '';
                                }

                                function filterTable() {
                                    const selectedEkstra = selectEkstra.value.toLowerCase();
                                    const searchText = searchInput.value.toLowerCase();
                                    let visibleCount = 0;

                                    rows.forEach(row => {
                                        const nama = getInputValue(row.cells[1]);
                                        const kelas = getInputValue(row.cells[2]);
                                        const ekstra = getInputValue(row.cells[3], 'select');

                                        const matchEkstra = !selectedEkstra || ekstra.includes(selectedEkstra);
                                        const matchSearch = !searchText || nama.includes(searchText) || kelas.includes(searchText);

                                        if (matchEkstra && matchSearch) {
                                            row.style.display = '';
                                            visibleCount++;
                                        } else {
                                            row.style.display = 'none';
                                        }
                                    });

                                    // Baris "tidak ada data"
                                    let emptyRow = document.getElementById("noDataRow");
                                    if (!emptyRow) {
                                        emptyRow = document.createElement("tr");
                                        emptyRow.id = "noDataRow";
                                        emptyRow.innerHTML = `<td colspan="6" class="text-center text-muted">Tidak ada data ditemukan</td>`;
                                        tbody.appendChild(emptyRow);
                                    }
                                    emptyRow.style.display = visibleCount === 0 ? '' : 'none';
                                }

                                // Event listener
                                selectEkstra.addEventListener('change', filterTable);
                                searchInput.addEventListener('input', filterTable);
                                searchBtn.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    filterTable();
                                });
                            });
                        </script>


                        <script>
                            const toggleEdit = document.getElementById("toggleEdit");

                            if (localStorage.getItem("editMode") === "on") {
                                toggleEdit.checked = true;
                            }

                            function setFormState() {
                                const isEdit = toggleEdit.checked;
                                localStorage.setItem("editMode", isEdit ? "on" : "off");

                                // aktif/nonaktifkan hanya select nama_ekstra & nilai
                                document.querySelectorAll("select[name='nama_ekstra[]'], select[name='nilai[]']").forEach(select => {
                                    select.disabled = !isEdit;
                                });
                            }

                            toggleEdit.addEventListener("change", setFormState);
                            // panggil saat halaman pertama kali dibuka
                            setFormState();
                        </script>

                    </div>
                </div>
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
    <div class="modal fade" id="modalTambahEkstra" tabindex="-1" aria-labelledby="modalTambahEkstraLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 10px;">
                <div class="modal-header" style="background-color: #0d6efd; color: white; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                    <h5 class="modal-title fw-semibold" id="modalTambahEkstraLabel">
                        <i class="fa fa-star"></i> Tambah Nilai Ekstrakurikuler
                    </h5>
                </div>

                <div class="modal-body">
                    <form action="nilai_ekstra_tambah_proses.php" method="POST">

                        <!-- Nama Siswa -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Siswa</label>
                            <select name="id_siswa" class="form-select" required>
                                <option value="" selected disabled>-- Pilih Siswa --</option>
                                <?php
                                $querySiswa = mysqli_query($koneksi, "SELECT id_siswa, nama_siswa FROM siswa ORDER BY nama_siswa ASC");
                                while ($siswa = mysqli_fetch_assoc($querySiswa)) {
                                    echo "<option value='{$siswa['id_siswa']}'>{$siswa['nama_siswa']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Semester -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Semester</label>
                            <select name="id_semester" class="form-select" required>
                                <option value="" selected disabled>-- Pilih Semester --</option>
                                <?php
                                // Ambil data semester dari tabel semester
                                $query_semester = mysqli_query($koneksi, "SELECT * FROM semester ORDER BY id_semester ASC");
                                while ($sem = mysqli_fetch_assoc($query_semester)) {
                                    echo "<option value='{$sem['id_semester']}'>{$sem['nama_semester']} - {$sem['tahun_ajaran']}</option>";
                                }
                                ?>
                            </select>
                        </div>


                        <!-- Ekstrakurikuler -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Ekstrakurikuler</label>
                            <select name="id_ekstra" class="form-select" required>
                                <option value="" selected disabled>-- Pilih Ekstrakurikuler --</option>
                                <?php
                                $queryEkstra = mysqli_query($koneksi, "SELECT id_ekstrakurikuler, nama_ekstrakurikuler FROM ekstrakurikuler ORDER BY nama_ekstrakurikuler ASC");
                                while ($ekstra = mysqli_fetch_assoc($queryEkstra)) {
                                    echo "<option value='{$ekstra['id_ekstrakurikuler']}'>{$ekstra['nama_ekstrakurikuler']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Nilai -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nilai</label>
                            <select name="nilai_ekstrakurikuler" class="form-select" required>
                                <option value="" selected disabled>-- Pilih Nilai --</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>

                        <!-- Tombol -->
                        <div class="modal-footer">
                            <div class="d-flex w-100 gap-2">
                                <button type="submit" class="btn btn-success w-50">
                                    <i class="fa fa-save"></i> Simpan
                                </button>
                                <button type="button" class="btn btn-danger w-50" data-bs-dismiss="modal">
                                    <i class="fa fa-times"></i> Batal
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>