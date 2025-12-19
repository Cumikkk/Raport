<?php
ob_start();
require_once '../../koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'] ?? '';

/* =========================
 *  PROSES: DELETE SINGLE
 * ========================= */
$flash = '';
if (isset($_GET['hapus'])) {
  $id = (int)$_GET['hapus'];
  $stmt = mysqli_prepare($koneksi, "DELETE FROM kelas WHERE id_kelas = ?");
  mysqli_stmt_bind_param($stmt, 'i', $id);
  if (!mysqli_stmt_execute($stmt)) {
    $flash = 'gagal_hapus_fk';
  } else {
    $flash = 'deleted';
  }
  mysqli_stmt_close($stmt);
  header('Location: datakelas.php?msg=' . $flash);
  exit;
}

/* =========================
 *  PROSES: BULK DELETE
 * ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete']) && !empty($_POST['selected_ids'])) {
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    header('Location: datakelas.php?msg=csrf_invalid');
    exit;
  }

  $ids = array_map('intval', $_POST['selected_ids']);
  $ids = array_values(array_filter($ids, fn($v) => $v > 0));
  if (count($ids) === 0) {
    header('Location: datakelas.php?msg=deleted');
    exit;
  }

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

/* =========================
 *  PROSES: TAMBAH (MODAL)
 * ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah_kelas') {
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    header('Location: datakelas.php?msg=csrf_invalid');
    exit;
  }

  $nama_kelas   = trim($_POST['nama_kelas'] ?? '');
  $tingkat      = trim($_POST['tingkat_kelas'] ?? '');
  $id_guru_raw  = (int)($_POST['id_guru'] ?? 0);
  $id_guru      = $id_guru_raw > 0 ? $id_guru_raw : 0;

  $allowedTingkat = ['X','XI','XII'];
  if ($nama_kelas === '' || !in_array($tingkat, $allowedTingkat, true)) {
    header('Location: datakelas.php?msg=invalid');
    exit;
  }

  $sqlIns = "INSERT INTO kelas (id_guru, tingkat_kelas, nama_kelas)
             VALUES (NULLIF(?,0), ?, ?)";
  $stmt = mysqli_prepare($koneksi, $sqlIns);
  mysqli_stmt_bind_param($stmt, 'iss', $id_guru, $tingkat, $nama_kelas);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  header('Location: datakelas.php?msg=saved');
  exit;
}

/* =========================
 *  PROSES: EDIT (MODAL)
 * ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit_kelas') {
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    header('Location: datakelas.php?msg=csrf_invalid');
    exit;
  }

  $id_kelas     = (int)($_POST['id_kelas'] ?? 0);
  $nama_kelas   = trim($_POST['nama_kelas'] ?? '');
  $tingkat      = trim($_POST['tingkat_kelas'] ?? '');
  $id_guru_raw  = (int)($_POST['id_guru'] ?? 0);
  $id_guru      = $id_guru_raw > 0 ? $id_guru_raw : 0;

  $allowedTingkat = ['X','XI','XII'];
  if ($id_kelas <= 0 || $nama_kelas === '' || !in_array($tingkat, $allowedTingkat, true)) {
    header('Location: datakelas.php?msg=invalid');
    exit;
  }

  $sqlUp = "UPDATE kelas
            SET id_guru = NULLIF(?,0),
                tingkat_kelas = ?,
                nama_kelas = ?
            WHERE id_kelas = ?";
  $stmt = mysqli_prepare($koneksi, $sqlUp);
  mysqli_stmt_bind_param($stmt, 'issi', $id_guru, $tingkat, $nama_kelas, $id_kelas);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  header('Location: datakelas.php?msg=updated');
  exit;
}

/* =========================
 *  DATA GURU (UNTUK DROPDOWN MODAL)
 * ========================= */
$guruList = [];
$resGuru = mysqli_query($koneksi, "SELECT id_guru, nama_guru FROM guru ORDER BY nama_guru ASC");
while ($g = mysqli_fetch_assoc($resGuru)) {
  $guruList[] = $g;
}

/* =========================
 *  LIST + FILTER (server)
 * ========================= */
$q       = trim($_GET['q'] ?? '');
$tingkat = trim($_GET['tingkat'] ?? '');
$like    = "%{$q}%";
$tingkatParam = $tingkat ?: '';

$sql = "
  SELECT
    k.id_kelas,
    k.id_guru,
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
          <?php elseif ($msg === 'csrf_invalid'): ?>
            <div class="alert alert-danger">Sesi tidak valid (CSRF). Silakan refresh halaman.</div>
          <?php elseif ($msg === 'invalid'): ?>
            <div class="alert alert-warning">Data tidak valid. Pastikan Nama Kelas & Tingkat terisi benar.</div>
          <?php endif; ?>

          <!-- HEADER + SEARCH + TOMBOL -->
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h4 class="fw-bold mb-0">Data Kelas</h4>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

              <!-- FORM SEARCH (kiri) -> TANPA TOMBOL SEARCH -->
              <form class="d-flex align-items-center gap-2 kelas-search-form" role="search" method="GET">
                <input
                  id="qInput"
                  name="q"
                  value="<?= htmlspecialchars($q) ?>"
                  class="form-control"
                  type="search"
                  placeholder="Cari kelas/wali..."
                  aria-label="Search"
                  style="max-width:220px;">

                <select id="tingkatSelect" name="tingkat" class="form-select" style="max-width:140px;">
                  <option value="">Semua</option>
                  <option value="X"  <?= $tingkat === 'X'  ? 'selected' : ''; ?>>X</option>
                  <option value="XI" <?= $tingkat === 'XI' ? 'selected' : ''; ?>>XI</option>
                  <option value="XII"<?= $tingkat === 'XII'? 'selected' : ''; ?>>XII</option>
                </select>
              </form>

              <!-- TOMBOL AKSI (kanan) -->
              <div class="d-flex flex-wrap gap-2 button-group">
                <button type="button"
                        class="btn btn-primary btn-md px-3 py-2 d-flex align-items-center gap-2"
                        data-bs-toggle="modal"
                        data-bs-target="#modalTambahKelas">
                  <i class="fa-solid fa-plus fa-lg"></i> Tambah
                </button>

                <button type="button"
                        class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2"
                        data-bs-toggle="modal"
                        data-bs-target="#modalImportKelas">
                  <i class="fa-solid fa-file-arrow-down fa-lg"></i> Import
                </button>

                <button id="exportBtn"
                        class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2"
                        type="button"
                        onclick="window.location='export.php'">
                  <i class="fa-solid fa-file-arrow-up fa-lg"></i> Export
                </button>
              </div>
            </div>
          </div>

          <!-- TABEL DATA -->
          <form method="POST" onsubmit="return confirm('Yakin ingin menghapus data terpilih?');">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

            <div class="table-responsive">
              <table id="dataKelas" class="table dk-table table-bordered table-striped align-middle w-100">
                <thead class="table-light">
                  <tr style="color:white">
                    <!-- checkbox SELECT ALL tetap ada -->
                    <th style="width:34px;">
                      <input type="checkbox" id="selectAll">
                    </th>
                    <th style="width:60px;">No</th>
                    <th>Nama Kelas</th>
                    <th style="width:140px;">Jumlah Siswa</th>
                    <th>Wali Kelas</th>
                    <th style="width:80px;">Tingkat</th>
                    <th style="width:160px;">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                      <td>
                        <input type="checkbox"
                               class="row-checkbox"
                               name="selected_ids[]"
                               value="<?= (int)$row['id_kelas'] ?>">
                      </td>
                      <td><?= $no++ ?></td>
                      <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                      <td><?= (int)$row['jumlah_siswa'] ?></td>
                      <td><?= htmlspecialchars($row['wali_kelas'] ?? '-') ?></td>
                      <td><?= htmlspecialchars($row['tingkat_kelas']) ?></td>
                      <td>
                        <button type="button"
                                class="btn btn-warning btn-sm me-1 btn-edit-kelas"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditKelas"
                                data-id="<?= (int)$row['id_kelas'] ?>"
                                data-nama="<?= htmlspecialchars($row['nama_kelas'], ENT_QUOTES) ?>"
                                data-tingkat="<?= htmlspecialchars($row['tingkat_kelas'], ENT_QUOTES) ?>"
                                data-idguru="<?= (int)($row['id_guru'] ?? 0) ?>">
                          Edit
                        </button>

                        <a class="btn btn-danger btn-sm"
                           href="datakelas.php?hapus=<?= (int)$row['id_kelas'] ?>"
                           onclick="return confirm('Hapus kelas ini?');">Del</a>
                      </td>
                    </tr>
                  <?php endwhile; mysqli_stmt_close($stmt); ?>
                </tbody>
              </table>
            </div>

            <!-- BAGIAN "PILIH SEMUA" BAWAH DIHAPUS -->
            <div class="d-flex justify-content-start mt-3 mb-3">
              <button name="bulk_delete" value="1" class="btn btn-danger btn-sm" id="deleteSelected" disabled>
                <i class="fa fa-trash"></i> Hapus Terpilih
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>

  <!-- =======================
       MODAL TAMBAH KELAS
  ======================== -->
  <div class="modal fade" id="modalTambahKelas" tabindex="-1" aria-labelledby="modalTambahKelasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content" style="border-radius: 12px; overflow:hidden;">
        <div class="modal-header" style="background-color:#0d6efd; color:#fff;">
          <h5 class="modal-title d-flex align-items-center" id="modalTambahKelasLabel">
            <i class="fa-solid fa-plus me-2"></i> Tambah Data Kelas
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form method="POST" action="datakelas.php">
          <input type="hidden" name="aksi" value="tambah_kelas">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Nama Kelas</label>
              <input type="text" name="nama_kelas" class="form-control" placeholder="mis. XII IPA 1" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Tingkat</label>
              <select name="tingkat_kelas" class="form-select" required>
                <option value="" selected disabled>Pilih Tingkat</option>
                <option value="X">X</option>
                <option value="XI">XI</option>
                <option value="XII">XII</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Wali Kelas (opsional)</label>
              <select name="id_guru" class="form-select">
                <option value="0">— Tidak Ada —</option>
                <?php foreach ($guruList as $g): ?>
                  <option value="<?= (int)$g['id_guru'] ?>"><?= htmlspecialchars($g['nama_guru']) ?></option>
                <?php endforeach; ?>
              </select>
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

  <!-- =======================
       MODAL EDIT KELAS
  ======================== -->
  <div class="modal fade" id="modalEditKelas" tabindex="-1" aria-labelledby="modalEditKelasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content" style="border-radius: 12px; overflow:hidden;">
        <div class="modal-header" style="background-color:#ffc107; color:#111;">
          <h5 class="modal-title d-flex align-items-center" id="modalEditKelasLabel">
            <i class="fa-solid fa-pen-to-square me-2"></i> Edit Data Kelas
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form method="POST" action="datakelas.php">
          <input type="hidden" name="aksi" value="edit_kelas">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="id_kelas" id="edit_id_kelas" value="">

          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Nama Kelas</label>
              <input type="text" name="nama_kelas" id="edit_nama_kelas" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Tingkat</label>
              <select name="tingkat_kelas" id="edit_tingkat_kelas" class="form-select" required>
                <option value="X">X</option>
                <option value="XI">XI</option>
                <option value="XII">XII</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Wali Kelas (opsional)</label>
              <select name="id_guru" id="edit_id_guru" class="form-select">
                <option value="0">— Tidak Ada —</option>
                <?php foreach ($guruList as $g): ?>
                  <option value="<?= (int)$g['id_guru'] ?>"><?= htmlspecialchars($g['nama_guru']) ?></option>
                <?php endforeach; ?>
              </select>
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

  <!-- MODAL IMPORT KELAS -->
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
              <small class="text-muted">Gunakan template Excel yang sudah disediakan agar kolom sesuai.</small>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Catatan</label>
              <ul class="mb-0 ps-3">
                <li>Pastikan nama kelas tidak kosong.</li>
                <li>Tingkat kelas hanya boleh X, XI, atau XII.</li>
                <li>Wali kelas harus sudah terdaftar di data guru (opsional).</li>
              </ul>
            </div>
          </div>

          <div class="modal-footer d-flex justify-content-between">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
              <i class="fa fa-times"></i> Batal
            </button>
            <button type="submit" class="btn btn-success">
              <i class="fa fa-upload"></i> Upload &amp; Proses
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <style>
    @media (max-width: 576px) {
      h4.fw-bold { text-align: center; width: 100%; }

      .kelas-search-form {
        width: 100%;
        justify-content: center !important;
        flex-wrap: wrap;
        margin-bottom: 8px;
      }

      .kelas-search-form input,
      .kelas-search-form select {
        width: 100% !important;
        max-width: 100% !important;
      }

      .button-group { width: 100%; justify-content: center !important; }
    }

    .row-highlight { background-color: #d4edda !important; }
  </style>

  <script>
    // ====== SELECT ALL (yang di header) TETAP ADA ======
    document.addEventListener('DOMContentLoaded', () => {
      const selectAll  = document.getElementById('selectAll');
      const deleteBtn  = document.getElementById('deleteSelected');

      function getRowChecks() {
        return Array.from(document.querySelectorAll('.row-checkbox'));
      }

      function updateDeleteButton() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        if (deleteBtn) deleteBtn.disabled = checkedCount === 0;
      }

      function syncSelectAllState() {
        const checks = getRowChecks();
        if (!selectAll) return;
        if (checks.length === 0) {
          selectAll.checked = false;
          selectAll.indeterminate = false;
          return;
        }
        const allChecked = checks.every(cb => cb.checked);
        const anyChecked = checks.some(cb => cb.checked);
        selectAll.checked = allChecked;
        selectAll.indeterminate = (!allChecked && anyChecked);
      }

      function bindRowCheckboxes() {
        getRowChecks().forEach(cb => {
          cb.addEventListener('change', () => {
            updateDeleteButton();
            syncSelectAllState();
          });
        });
      }

      if (selectAll) {
        selectAll.addEventListener('change', () => {
          const checks = getRowChecks();
          checks.forEach(cb => cb.checked = selectAll.checked);
          updateDeleteButton();
          syncSelectAllState();
        });
      }

      bindRowCheckboxes();
      updateDeleteButton();
      syncSelectAllState();
    });

    // ====== LIVE SEARCH + HIGHLIGHT (tanpa tombol search) ======
    document.addEventListener('DOMContentLoaded', () => {
      const form   = document.querySelector('.kelas-search-form');
      const input  = document.getElementById('qInput');
      const select = document.getElementById('tingkatSelect');

      if (select && form) {
        select.addEventListener('change', () => form.submit());
      }

      const tbody = document.querySelector('#dataKelas tbody');
      if (!input || !tbody) return;

      const rows = Array.from(tbody.querySelectorAll('tr'));
      const debounce = (fn, delay = 120) => {
        let t;
        return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
      };

      function applyFilter() {
        const term = (input.value || '').trim().toLowerCase();
        let nomor = 1;

        rows.forEach(tr => {
          tr.classList.remove('row-highlight');
          const tds = tr.querySelectorAll('td');
          if (!tds.length) return;

          const namaKelas = (tds[2].textContent || '').toLowerCase();
          const wali      = (tds[4].textContent || '').toLowerCase();

          const cocok = term === '' || namaKelas.includes(term) || wali.includes(term);
          tr.style.display = cocok ? '' : 'none';

          if (cocok) {
            tds[1].textContent = nomor++;
            if (term !== '') tr.classList.add('row-highlight');
          }
        });

        // setelah filter, sinkron selectAll
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
          const checks = Array.from(document.querySelectorAll('.row-checkbox'));
          const visibleChecks = checks.filter(cb => cb.closest('tr') && cb.closest('tr').style.display !== 'none');
          if (visibleChecks.length === 0) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
          } else {
            const allChecked = visibleChecks.every(cb => cb.checked);
            const anyChecked = visibleChecks.some(cb => cb.checked);
            selectAll.checked = allChecked;
            selectAll.indeterminate = (!allChecked && anyChecked);
          }
        }
      }

      applyFilter();
      input.addEventListener('input', debounce(applyFilter, 120));
    });

    // ====== ISI MODAL EDIT DARI DATA BARIS ======
    document.addEventListener('DOMContentLoaded', () => {
      const editButtons = document.querySelectorAll('.btn-edit-kelas');
      const idEl    = document.getElementById('edit_id_kelas');
      const namaEl  = document.getElementById('edit_nama_kelas');
      const tingkatEl = document.getElementById('edit_tingkat_kelas');
      const guruEl  = document.getElementById('edit_id_guru');

      editButtons.forEach(btn => {
        btn.addEventListener('click', () => {
          const id      = btn.getAttribute('data-id') || '';
          const nama    = btn.getAttribute('data-nama') || '';
          const tingkat = btn.getAttribute('data-tingkat') || 'X';
          const idguru  = btn.getAttribute('data-idguru') || '0';

          if (idEl) idEl.value = id;
          if (namaEl) namaEl.value = nama;
          if (tingkatEl) tingkatEl.value = tingkat;
          if (guruEl) guruEl.value = idguru;
        });
      });
    });
  </script>

  <?php include '../../includes/footer.php'; ?>
</body>
