<?php
ob_start();
require_once '../../koneksi.php';

// ===== DELETE SINGLE =====
$flash = '';
if (isset($_GET['hapus'])) {
  $id = (int)$_GET['hapus'];
  $stmt = mysqli_prepare($koneksi, "DELETE FROM kelas WHERE id_kelas = ?");
  mysqli_stmt_bind_param($stmt, 'i', $id);
  if (!mysqli_stmt_execute($stmt)) {
    // 1451: cannot delete/update: a parent row (FK constraint)
    $flash = 'gagal_hapus_fk';
  } else {
    $flash = 'deleted';
  }
  mysqli_stmt_close($stmt);
  header('Location: datakelas.php?msg=' . $flash);
  exit;
}

// ===== BULK DELETE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete']) && !empty($_POST['selected_ids'])) {
  $ids = array_map('intval', $_POST['selected_ids']);
  $placeholders = implode(',', array_fill(0, count($ids), '?'));
  $types = str_repeat('i', count($ids));
  $sql = "DELETE FROM kelas WHERE id_kelas IN ($placeholders)";
  $stmt = mysqli_prepare($koneksi, $sql);
  mysqli_stmt_bind_param($stmt, $types, ...$ids);
  if (!mysqli_stmt_execute($stmt)) {
    $flash = 'gagal_hapus_fk';
  } else {
    $flash = 'deleted';
  }
  mysqli_stmt_close($stmt);
  header('Location: datakelas.php?msg=' . $flash);
  exit;
}

// ===== LIST + FILTER (server-side, tetap dipakai kalau user submit form) =====
$q       = trim($_GET['q'] ?? '');
$tingkat = trim($_GET['tingkat'] ?? '');
$like    = "%{$q}%";
$tingkatParam = $tingkat ?: '';

$sql = "
  SELECT 
    k.id_kelas,
    k.nama_kelas,
    k.tingkat_kelas,
    g.nama_guru AS wali_kelas,
    (SELECT COUNT(*) FROM siswa s WHERE s.id_kelas = k.id_kelas) AS jumlah_siswa
  FROM kelas k
  LEFT JOIN guru g ON g.id_guru = k.id_guru
  WHERE (k.nama_kelas LIKE ? OR COALESCE(g.nama_guru,'') LIKE ?)
    AND (? = '' OR k.tingkat_kelas = ?)
  ORDER BY FIELD(k.tingkat_kelas,'X','XI','XII'), k.nama_kelas
";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'ssss', $like, $like, $tingkatParam, $tingkatParam);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$msg = $_GET['msg'] ?? '';

include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<body>

  <div class="dk-page">
    <div class="dk-main">
      <div class="dk-content-box">
        <div class="container-fluid py-3">

          <!-- ALERT -->
          <?php if ($msg === 'saved'): ?>
            <div class="alert alert-success">Data berhasil disimpan.</div>
          <?php elseif ($msg === 'updated'): ?>
            <div class="alert alert-success">Data berhasil diperbarui.</div>
          <?php elseif ($msg === 'deleted'): ?>
            <div class="alert alert-success">Data berhasil dihapus.</div>
          <?php elseif ($msg === 'gagal_hapus_fk'): ?>
            <div class="alert alert-warning">
              Tidak bisa menghapus karena kelas masih dipakai (ada siswa terkait). Kosongkan dulu siswa pada kelas tersebut.
            </div>
          <?php endif; ?>

          <!-- HEADER + CARI + TOMBOL -->
          <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-2 mb-sm-0">Data Kelas</h4>

            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 search-group w-100 w-sm-auto">
              <form class="d-flex flex-nowrap flex-grow-1 flex-sm-grow-0" role="search" method="GET">
                <input name="q" value="<?= htmlspecialchars($q) ?>" class="form-control me-2" type="search"
                  placeholder="Cari kelas/wali..." aria-label="Search" style="max-width:200px; flex-grow:1;">
                <select name="tingkat" class="form-select me-2" style="max-width:140px;">
                  <option value="">Semua</option>
                  <option value="X"  <?= $tingkat === 'X'  ? 'selected' : ''; ?>>X</option>
                  <option value="XI" <?= $tingkat === 'XI' ? 'selected' : ''; ?>>XI</option>
                  <option value="XII"<?= $tingkat === 'XII'? 'selected' : ''; ?>>XII</option>
                </select>
                <button class="btn btn-outline-secondary btn-md" type="submit">
                  <i class="fa fa-search"></i>
                </button>
              </form>

              <div class="d-flex flex-wrap gap-2 mt-2 mt-sm-0 button-group">
                <!-- Tambah -->
                <a href="tambah_data.php" class="btn btn-primary btn-md px-3 py-2 d-flex align-items-center gap-2">
                  <i class="fa-solid fa-plus fa-lg"></i> Tambah
                </a>

                <!-- Import → buka modal -->
                <button type="button"
                        class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2"
                        data-bs-toggle="modal"
                        data-bs-target="#modalImportKelas">
                  <i class="fa-solid fa-file-arrow-down fa-lg"></i> Import
                </button>

                <!-- Export -->
                <button id="exportBtn" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2" type="button"
                  onclick="window.location='export.php'">
                  <i class="fa-solid fa-file-arrow-up fa-lg"></i> Export
                </button>
              </div>
            </div>
          </div>

          <!-- TABEL DATA -->
          <form method="POST" onsubmit="return confirm('Yakin ingin menghapus data terpilih?');">
            <div class="table-responsive">
              <table id="dataKelas" class="table dk-table table-bordered table-striped align-middle w-100">
                <thead class="table-light">
                  <tr style="color:white">
                    <th style="width:34px;"><input type="checkbox" id="selectAll"></th>
                    <th style="width:60px;">No</th>
                    <th>Nama Kelas</th>
                    <th style="width:140px;">Jumlah Siswa</th>
                    <th>Wali Kelas</th>
                    <th style="width:80px;">Tingkat</th>
                    <th style="width:160px;">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $no = 1;
                  while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                      <td><input type="checkbox" class="row-checkbox" name="selected_ids[]" value="<?= (int)$row['id_kelas'] ?>"></td>
                      <td><?= $no++ ?></td>
                      <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                      <td><?= (int)$row['jumlah_siswa'] ?></td>
                      <td><?= htmlspecialchars($row['wali_kelas'] ?? '-') ?></td>
                      <td><?= htmlspecialchars($row['tingkat_kelas']) ?></td>
                      <td>
                        <a class="btn btn-warning btn-sm me-1" href="edit_data.php?id=<?= (int)$row['id_kelas'] ?>">Edit</a>
                        <a class="btn btn-danger btn-sm"
                          href="datakelas.php?hapus=<?= (int)$row['id_kelas'] ?>"
                          onclick="return confirm('Hapus kelas ini?');">Del</a>
                      </td>
                    </tr>
                  <?php endwhile;
                  mysqli_stmt_close($stmt); ?>
                </tbody>
              </table>
            </div>

            <div id="selectArea" class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 mb-3">
              <div class="d-flex align-items-center gap-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="selectAll2" style="box-shadow:none">
                  <label class="form-check-label fw-semibold" for="selectAll2">Pilih Semua</label>
                </div>
                <button name="bulk_delete" value="1" class="btn btn-danger btn-sm" id="deleteSelected" disabled>
                  <i class="fa fa-trash"></i> Hapus Terpilih
                </button>
              </div>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL IMPORT (tampil seperti form tambah data kelas) -->
  <div class="modal fade" id="modalImportKelas" tabindex="-1" aria-labelledby="modalImportKelasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content" style="border-radius: 12px; overflow:hidden;">
        <div class="modal-header" style="background-color:#0d6efd; color:#fff;">
          <h5 class="modal-title d-flex align-items-center" id="modalImportKelasLabel">
            <i class="fa fa-upload me-2"></i> Import Data Kelas
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="import.php" method="post" enctype="multipart/form-data">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">File Excel</label>
              <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls" required>
              <small class="text-muted">
                Gunakan template Excel yang sudah disediakan agar kolom sesuai.
              </small>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Catatan</label>
              <ul class="mb-0 ps-3">
                <li>.</li>
                <li>.</li>
                <li>.</li>
              </ul>
            </div>
          </div>

          <div class="modal-footer d-flex justify-content-between">
            <button type="submit" class="btn btn-success">
              <i class="fa fa-save"></i> Simpan
            </button>
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
              <i class="fa fa-times"></i> Batal
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- CSS RESPONSIVE + HIGHLIGHT -->
  <style>
    @media (max-width: 576px) {
      h4.fw-bold {
        text-align: center;
        width: 100%;
        margin-bottom: 12px !important;
      }

      .search-group {
        flex-direction: column;
        align-items: center !important;
        width: 100%;
        gap: 12px;
      }

      .button-group {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        width: 100%;
      }

      .mb-3 {
        width: 100%;
      }
    }

    /* Warna highlight hasil pencarian (seperti data_guru) */
    .row-highlight {
      background-color: #d4edda !important;
    }
  </style>

  <!-- SCRIPT -->
  <script>
    // ====== BULK CHECKBOX ======
    document.addEventListener('DOMContentLoaded', () => {
      const selectAll  = document.getElementById('selectAll');
      const selectAll2 = document.getElementById('selectAll2');
      const checkboxes = document.querySelectorAll('.row-checkbox');
      const deleteBtn  = document.getElementById('deleteSelected');

      function updateDeleteButton() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        if (deleteBtn) deleteBtn.disabled = checkedCount === 0;
      }

      function toggleAll(checked) {
        checkboxes.forEach(cb => cb.checked = checked);
        updateDeleteButton();
      }

      if (selectAll)  selectAll.addEventListener('change', e => toggleAll(e.target.checked));
      if (selectAll2) selectAll2.addEventListener('change', e => toggleAll(e.target.checked));
      checkboxes.forEach(cb => cb.addEventListener('change', updateDeleteButton));
    });

    // ====== LIVE SEARCH + HIGHLIGHT BARIS ======
    document.addEventListener('DOMContentLoaded', () => {
      const input = document.querySelector('input[name="q"]');
      const tbody = document.querySelector('#dataKelas tbody');
      if (!input || !tbody) return;

      const rows = Array.from(tbody.querySelectorAll('tr'));

      const debounce = (fn, delay = 120) => {
        let t;
        return (...args) => {
          clearTimeout(t);
          t = setTimeout(() => fn(...args), delay);
        };
      };

      function applyFilter() {
        const term = (input.value || '').trim().toLowerCase();
        let nomor = 1;

        rows.forEach(tr => {
          tr.classList.remove('row-highlight');
          const tds = tr.querySelectorAll('td');
          if (!tds.length) return;

          const namaKelas = (tds[2].textContent || '').toLowerCase(); // kolom "Nama Kelas"
          const wali      = (tds[4].textContent || '').toLowerCase(); // kolom "Wali Kelas"

          const cocok = term === '' || namaKelas.includes(term) || wali.includes(term);

          tr.style.display = cocok ? '' : 'none';

          if (cocok) {
            // renumber kolom "No"
            tds[1].textContent = nomor++;

            // highlight kalau cocok
            if (term !== '' && (namaKelas.includes(term) || wali.includes(term))) {
              tr.classList.add('row-highlight');
            }
          }
        });
      }

      // jalan pertama kali (kalau ada ?q= di URL)
      applyFilter();

      // live search saat mengetik
      input.addEventListener('input', debounce(applyFilter, 120));
    });
  </script>

  <?php include '../../includes/footer.php'; ?>
</body>
