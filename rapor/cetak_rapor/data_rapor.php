<?php
include '../../includes/header.php';
?>

<body>

    <?php
    include '../../includes/navbar.php';
    ?>

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

        /* === BAGIAN ATAS (Filter, Cari, Tambah) === */
        .top-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 25px;
        }

        .left-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 15px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        .filter-group select {
            min-width: 150px;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background: #fff;
            font-size: 14px;
        }

        .search-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-form input {
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn-main,
        .btn-export,
        .btn-add,
        .search-form button,
        .btn-delete-selected {
            background: #1d52a2;
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

        .btn-main:hover,
        .btn-export:hover,
        .btn-add:hover,
        .btn-delete-selected:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* === TABEL DATA SISWA === */
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        thead {
            background: #004080;
            color: #fff;
        }

        th,
        td {
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
        .btn-detail,
        .btn-edit,
        .btn-delete {
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

        .btn-detail {
            background: #17a2b8;
        }

        .btn-edit {
            background: #ffc107;
            color: #000;
        }

        .btn-delete {
            background: #dc3545;
        }

        .btn-detail:hover {
            background: #138496;
        }

        .btn-edit:hover {
            background: #e0a800;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .action-btns {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* === PAGINATION === */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 25px;
            gap: 8px;
        }

        .pagination a,
        .pagination span {
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

        /* === MODAL POPUP === */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            width: 90%;
            max-width: 500px;
            position: relative;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.3s ease;
        }

        .modal-content h3 {
            margin-bottom: 20px;
            color: #004080;
            text-align: center;
        }

        .modal-content p {
            margin: 8px 0;
            font-size: 15px;
        }

        .close-modal {
            position: absolute;
            top: 12px;
            right: 15px;
            background: none;
            border: none;
            font-size: 22px;
            color: #333;
            cursor: pointer;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* jaga layout tabel */
        .content {
            overflow-x: auto;
        }
    </style>

    <!-- MAIN -->
    <main class="content">
        <h2>Cetak Rapor Siswa</h2>

        <!-- BAGIAN ATAS: Filter + Cari + Tombol -->
        <div class="top-actions">
            <div class="left-actions">
                <!-- Filter Tingkat -->
                <div class="filter-group">
                    <label for="tingkat" class="form-label fw-semibold">Tingkat</label>
                    <select id="tingkat" class="form-select dk-select">
                        <option>--Pilih--</option>
                        <option>X</option>
                        <option>XI</option>
                        <option>XII</option>
                    </select>
                </div>

                <!-- Search -->
                <form class="search-form" method="GET" action="">
                    <input type="text" name="search" placeholder="Cari nama siswa...">
                    <button type="submit"><i class="fas fa-search"></i>Cari</button>
                </form>
            </div>

            <!-- Tombol Cetak Semua -->
            <a href="tambah_siswa.php" class="btn-add">Cetak Semua Rapor</a>
        </div>

        <!-- Form Data Siswa -->
        <form action="hapus_pilihan.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus data yang dipilih?')">
            <table>
                <thead>
                    <tr>
                        <th style="text-align: center;"><input type="checkbox" id="selectAll"></th>
                        <th style="text-align: center;">Absen</th>
                        <th style="text-align: center;">NISN</th>
                        <th style="text-align: center;">Nama</th>
                        <th style="text-align: center;">L/P</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: center;"><input type="checkbox" name="selected[]" value="1"></td>
                        <td style="text-align: center;">1</td>
                        <td style="text-align: center;">23423016</td>
                        <td style="text-align: center;">M. Fahrul Alfanani</td>
                        <td style="text-align: center;">L</td>
                        <td class="action-btns" style="justify-content: center;">
                            <button type="button" class="btn-detail">Cetak</button>
                            <a href="edit_rapor.php?id=1" class="btn-edit">Edit</a>
                            <a href="hapus_rapor.php?id=1" class="btn-delete"
                                onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination + Tombol Hapus -->
            <div class="bottom-bar">
                <div class="left">
                    <button type="submit" class="btn-delete-selected">
                        <i class="fas fa-trash"></i> Hapus Pilihan
                    </button>
                </div>
                <div class="center pagination">
                    <a href="#">&lt;</a>
                    <a href="#" class="active">1</a>
                    <a href="#">2</a>
                    <a href="#">3</a>
                    <a href="#">&gt;</a>
                </div>
                <div class="right"></div>
            </div>
        </form>
    </main>

    <script>
        function closeModal() {
            document.getElementById('detailModal').style.display = 'none';
        }

        window.onclick = function(e) {
            const modal = document.getElementById('detailModal');
            if (e.target === modal) modal.style.display = 'none';
        };

        // === SELECT ALL CHECKBOX ===
        const selectAll = document.getElementById("selectAll");
        if (selectAll) {
            selectAll.addEventListener("change", function() {
                const checkboxes = document.querySelectorAll('input[name="selected[]"]');
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
            });
        }
    </script>

    <?php include '../../includes/footer.php'; ?>