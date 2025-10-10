<?php
include '../include/header.php';
include '../include/navbar.php';
?>

<div class="dk-page">
    <div class="dk-main">
        <!-- KOTAK PUTIH PEMBATAS -->
        <div class="dk-content-box">
            <div class="container-fluid py-3">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                    <div>
                        <h4 class="fw-bold mb-0">Data Kelas</h4>
                        <small class="text-muted">Master Data</small>
                    </div>
                    <div class="mt-2 mt-md-0">
                        <button class="btn btn-primary btn-sm me-2 dk-btn dk-btn-primary"><i class="fa fa-plus"></i> Tambah</button>
                        <button class="btn btn-success btn-sm dk-btn dk-btn-success"><i class="fa fa-plus"></i> Tambah Sekaligus</button>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="tingkat" class="form-label fw-semibold">Tingkat</label>
                        <select id="tingkat" class="form-select dk-select">
                            <option>--Pilih--</option>
                            <option>X</option>
                            <option>XI</option>
                            <option>XII</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="dataKelas" class="table dk-table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Kelas</th>
                                <th>Jumlah Siswa</th>
                                <th>Wali Kelas</th>
                                <th>Tingkat</th>
                                <th>Jurusan</th>
                                <th>Jenis</th>
                                <th>Kurikulum</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>XII IPA 1</td>
                                <td>23</td>
                                <td>Irsyal Velani, S.Si.</td>
                                <td>XII</td>
                                <td>UMUM</td>
                                <td>Paket</td>
                                <td>Kurmer</td>
                                <td>
                                    <button class="btn btn-success btn-sm me-1">Edit</button>
                                    <button class="btn btn-danger btn-sm">Del</button>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>XII IPA 2</td>
                                <td>0</td>
                                <td>M. Masyfu’ Auliya’Ihaq, S.Pd</td>
                                <td>XII</td>
                                <td>UMUM</td>
                                <td>Paket</td>
                                <td>Kurmer</td>
                                <td>
                                    <button class="btn btn-success btn-sm me-1">Edit</button>
                                    <button class="btn btn-danger btn-sm">Del</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include '../include/Footer.php';
?>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#dataKelas').DataTable({
            responsive: true, // biar otomatis menyesuaikan layar, tanpa scroll bar
            language: {
                lengthMenu: "Tampilkan _MENU_ entri",
                zeroRecords: "Data tidak ditemukan",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                infoEmpty: "Tidak ada data",
                search: "Cari:",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Next",
                    previous: "Prev"
                }
            }
        });
    });
</script>