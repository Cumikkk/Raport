<?php
include '../../koneksi.php';

// ====== PROSES DATA (MODE AJAX TANPA OUTPUT HTML) ======
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'] ?? '';

$joinCrLatest = "
  LEFT JOIN (
    SELECT crx.id_siswa, crx.catatan_wali_kelas
    FROM cetak_rapor crx
    INNER JOIN (
      SELECT id_siswa, MAX(id_cetak_rapor) AS max_id
      FROM cetak_rapor
      GROUP BY id_siswa
    ) last ON last.id_siswa = crx.id_siswa AND last.max_id = crx.id_cetak_rapor
  ) cr ON s.id_siswa = cr.id_siswa
";

// ===== Parameter =====
$q       = isset($_GET['q']) ? trim($_GET['q']) : '';
$perPage = isset($_GET['per']) ? (int)$_GET['per'] : 10;
$perPage = ($perPage >= 1 && $perPage <= 100) ? $perPage : 10;
$page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page    = max(1, $page);

// ===== Hitung total =====
if ($q !== '') {
  // cari di nama, absen, nisn
  $sqlCount = "SELECT COUNT(*) AS total FROM siswa s 
               LEFT JOIN kelas k ON s.id_kelas = k.id_kelas 
               $joinCrLatest
               WHERE (s.nama_siswa LIKE CONCAT('%', ?, '%')
                      OR s.no_absen_siswa LIKE CONCAT('%', ?, '%')
                      OR s.no_induk_siswa LIKE CONCAT('%', ?, '%'))";
  $stmtC = $koneksi->prepare($sqlCount);
  if ($stmtC === false) {
    die('Prepare failed: ' . $koneksi->error);
  }
  $stmtC->bind_param('sss', $q, $q, $q);
} else {
  $sqlCount = "SELECT COUNT(*) AS total FROM siswa s
               LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
               $joinCrLatest";
  $stmtC = $koneksi->prepare($sqlCount);
  if ($stmtC === false) {
    die('Prepare failed: ' . $koneksi->error);
  }
}
$stmtC->execute();
$totalRow = $stmtC->get_result()->fetch_assoc();
$total = (int)($totalRow['total'] ?? 0);
$totalPages = max(1, ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$stmtC->close();

// ===== Ambil data halaman ini =====
if ($q !== '') {
  $sql = "SELECT s.*, k.nama_kelas, cr.catatan_wali_kelas
          FROM siswa s
          LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
          $joinCrLatest
          WHERE (s.nama_siswa LIKE CONCAT('%', ?, '%')
                 OR s.no_absen_siswa LIKE CONCAT('%', ?, '%')
                 OR s.no_induk_siswa LIKE CONCAT('%', ?, '%'))
          ORDER BY s.no_absen_siswa ASC
          LIMIT ? OFFSET ?";
  $stmt = $koneksi->prepare($sql);
  if ($stmt === false) {
    die('Prepare failed: ' . $koneksi->error);
  }
  // tiga placeholder LIKE (s), lalu dua integer (i)
  $stmt->bind_param('sssii', $q, $q, $q, $perPage, $offset);
} else {
  $sql = "SELECT s.*, k.nama_kelas, cr.catatan_wali_kelas
          FROM siswa s
          LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
          $joinCrLatest
          ORDER BY s.no_absen_siswa ASC
          LIMIT ? OFFSET ?";
  $stmt = $koneksi->prepare($sql);
  if ($stmt === false) {
    die('Prepare failed: ' . $koneksi->error);
  }
  $stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($r = $result->fetch_assoc()) {
  $rows[] = $r;
}

// ===== Mode AJAX =====
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    'ok' => true,
    'csrf' => $csrf,
    'data' => $rows,
    'page' => $page,
    'per' => $perPage,
    'total' => $total,
    'totalPages' => $totalPages,
    'q' => $q
  ]);
  exit;
}

include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<main class="content">
  <div class="cards row" style="margin-top: -50px;">
    <div class="col-12">
      <div class="card shadow-sm" style="border-radius: 15px;">

        <!-- NOTIF: tampil di atas kotak data jika redirect dari add_siswa -->
        <div id="notifContainer">
          <?php if (isset($_GET['msg']) && $_GET['msg'] === 'saved'): ?>
            <div id="notifSaved" class="alert alert-success mx-3 mt-3">
              Data siswa berhasil ditambahkan.
            </div>
            <script>
              // auto-hide setelah 5 detik (server redirect case)
              setTimeout(() => {
                const n = document.getElementById('notifSaved');
                if (n) n.style.display = 'none';
              }, 5000);
            </script>
          <?php endif; ?>
        </div>

        <!-- TOP BAR: Judul + Search + Filter + Tombol -->
        <div class="mt-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between p-3 top-bar">
          <div class="d-flex flex-column align-items-md-start align-items-center text-md-start text-center mb-2 mb-md-0">
            <h5 class="mb-2 fw-semibold fs-4">Data Siswa</h5>

            <!-- PENCARIAN DI BAWAH JUDUL -->
            <form id="searchForm"
                  class="d-flex flex-wrap align-items-center gap-2 mb-2 mt-1">
              <input type="text"
                     id="searchInput"
                     class="form-control form-control-sm"
                     placeholder="Masukan kata kunci"
                     style="width:200px;"
                     value="<?= htmlspecialchars($q) ?>">
              <select id="perSelect" class="form-select form-select-sm" style="width:120px;">
                <?php foreach ([10, 20, 50, 100] as $opt): ?>
                  <option value="<?= $opt ?>" <?= $perPage === $opt ? 'selected' : '' ?>><?= $opt ?>/hal</option>
                <?php endforeach; ?>
              </select>
            </form>

            <!-- FILTER TINGKAT & KELAS -->
            <div class="filter-container d-flex flex-column gap-2 mt-1">
              <div class="filter-group d-flex align-items-center gap-2">
                <label class="form-label fw-semibold mb-0">Tingkat</label>
                <select id="filterTingkat" class="form-select dk-select" style="width: 120px;">
                  <option value="">-- Semua --</option>
                  <option>X</option>
                  <option>XI</option>
                  <option>XII</option>
                </select>
              </div>
              <div class="filter-group d-flex align-items-center gap-2">
                <label class="form-label fw-semibold mb-0">Kelas</label>
                <select id="filterKelas" class="form-select dk-select" style="width: 140px;">
                  <option value="">-- Semua --</option>
                  <?php
                  $kelasQuery = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                  while ($k = mysqli_fetch_assoc($kelasQuery)) {
                    echo '<option value="'.htmlspecialchars($k['id_kelas']).'">'.htmlspecialchars($k['nama_kelas']).'</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
          </div>

          <div class="d-flex gap-2 flex-wrap justify-content-md-end justify-content-center mt-3 mt-md-0 action-buttons">
            <!-- tombol tambahkan: buka modal via JS -->
            <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1 px-3 fw-semibold"
                    id="openTambahModal">
              <i class="fa-solid fa-plus"></i> Tambah
            </button>

            <!-- IMPORT: modal -->
            <button type="button"
                    class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2"
                    data-bs-toggle="modal" data-bs-target="#modalImportSiswa">
              <i class="fa-solid fa-file-arrow-down"></i> Import
            </button>

            <button id="exportBtn" class="btn btn-success btn-md px-3 py-2 d-flex align-items-center gap-2">
              <i class="fa-solid fa-file-arrow-up"></i> Export
            </button>
          </div>
        </div>

        <div class="card-body">
          <form id="formDeleteMultiple" action="hapus_siswa_multiple.php" method="POST">
            <div class="table-responsive">
              <table class="table table-bordered table-striped align-middle">
                <thead style="background-color:#1d52a2" class="text-center text-white">
                  <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>Absen</th>
                    <th>Nama</th>
                    <th>NISN</th>
                    <th>Kelas</th>
                    <th>Komentar</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody id="tbodyData">
                  <?php
                  $no = $offset + 1;
                  foreach ($rows as $data):
                    $catatanRaw = $data['catatan_wali_kelas'] ?? '';
                  ?>
                  <tr
                    data-kelas="<?= htmlspecialchars($data['id_kelas']) ?>"
                    data-tingkat="<?= htmlspecialchars($data['tingkat'] ?? '') ?>"
                    data-id="<?= (int)$data['id_siswa'] ?>"
                    data-nama="<?= htmlspecialchars($data['nama_siswa']) ?>"
                    data-nisn="<?= htmlspecialchars($data['no_induk_siswa']) ?>"
                    data-absen="<?= htmlspecialchars($data['no_absen_siswa']) ?>"
                    data-id_kelas="<?= htmlspecialchars($data['id_kelas']) ?>"
                    data-catatan="<?= htmlspecialchars($catatanRaw) ?>"
                  >
                    <td class="text-center">
                      <input type="checkbox" name="id_siswa[]" class="row-check" value="<?= (int)$data['id_siswa'] ?>">
                    </td>
                    <td class="text-center"><?= htmlspecialchars($data['no_absen_siswa']) ?></td>
                    <td><?= htmlspecialchars($data['nama_siswa']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($data['no_induk_siswa']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($data['nama_kelas'] ?? '-') ?></td>
                    <td><?= nl2br(htmlspecialchars($catatanRaw)) ?></td>
                    <td class="text-center">
                      <!-- Edit via modal -->
                      <button type="button" class="btn btn-warning btn-sm btn-edit-siswa">
                        <i class="bi bi-pencil-square"></i> Edit
                      </button>
                      <a href="hapus_siswa.php?id=<?= (int)$data['id_siswa'] ?>" onclick="return confirm('Yakin ingin menghapus data?');" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i> Del
                      </a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <button type="submit" id="deleteSelected" class="btn btn-danger btn-sm mt-2" disabled><i class="bi bi-trash"></i> Hapus Terpilih</button>
          </form>

          <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center" id="pagination"></ul>
            <p class="text-center text-muted mt-2 mb-0" id="pageInfo"></p>
          </nav>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- ========================= -->
<!-- MODAL: Tambah Siswa -->
<!-- ========================= -->
<div class="modal fade" id="modalTambahSiswa" tabindex="-1" aria-labelledby="modalTambahSiswaLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahSiswaLabel">Tambah Data Siswa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="formTambahSiswa" autocomplete="off">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Siswa</label>
            <input type="text" name="nama_siswa" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">NISN</label>
            <input type="text" name="no_induk_siswa" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Absen</label>
            <input type="text" name="no_absen_siswa" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Kelas</label>
            <select name="id_kelas" class="form-select" required>
              <option value="" selected disabled>-- Pilih Kelas --</option>
              <?php
              // ambil ulang kelas untuk modal (safe)
              $kelasQuery2 = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
              while ($kk = mysqli_fetch_assoc($kelasQuery2)) {
                echo '<option value="'.htmlspecialchars($kk['id_kelas']).'">'.htmlspecialchars($kk['nama_kelas']).'</option>';
              }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Catatan Wali Kelas</label>
            <textarea name="catatan_wali_kelas" class="form-control" rows="3"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ========================= -->
<!-- MODAL: Edit Siswa -->
<!-- ========================= -->
<div class="modal fade" id="modalEditSiswa" tabindex="-1" aria-labelledby="modalEditSiswaLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditSiswaLabel">Edit Data Siswa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Form kirim ke edit_siswa.php (fungsi saja) -->
      <form id="formEditSiswa" method="POST" action="edit_siswa.php" autocomplete="off">
        <div class="modal-body">
          <input type="hidden" name="id_siswa" id="edit_id_siswa">

          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Siswa</label>
            <input type="text" name="nama_siswa" id="edit_nama_siswa" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">NISN</label>
            <input type="text" name="no_induk_siswa" id="edit_no_induk_siswa" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Absen</label>
            <input type="text" name="no_absen_siswa" id="edit_no_absen_siswa" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Kelas</label>
            <select name="id_kelas" id="edit_id_kelas" class="form-select" required>
              <option value="" disabled>-- Pilih Kelas --</option>
              <?php
              $kelasQuery3 = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
              while ($kx = mysqli_fetch_assoc($kelasQuery3)) {
                echo '<option value="'.htmlspecialchars($kx['id_kelas']).'">'.htmlspecialchars($kx['nama_kelas']).'</option>';
              }
              ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Catatan Wali Kelas</label>
            <textarea name="catatan_wali_kelas" id="edit_catatan_wali_kelas" class="form-control" rows="3"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ========================= -->
<!-- MODAL: Import Siswa -->
<!-- ========================= -->
<div class="modal fade" id="modalImportSiswa" tabindex="-1" aria-labelledby="modalImportSiswaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <div>
          <h5 class="modal-title fw-semibold" id="modalImportSiswaLabel">Import Data Siswa</h5>
          <p class="mb-0 text-muted" style="font-size: 13px;">
            Gunakan template resmi agar susunan kolom sesuai dengan sistem.
          </p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <!-- Form import -->
      <form id="formImportSiswa"
            action="import_siswa.php"
            method="POST"
            enctype="multipart/form-data"
            autocomplete="off">
        <div class="modal-body pt-3">

          <!-- Info box langkah-langkah -->
          <div class="mb-3 p-3 rounded-3" style="background:#f9fafb;border:1px solid #e5e7eb;">
            <div class="d-flex align-items-start gap-2">
              <div class="mt-1">
                <i class="fa-solid fa-circle-info" style="color:#0a4db3;"></i>
              </div>
              <div style="font-size:13px;">
                <strong>Langkah import data siswa:</strong>
                <ol class="mb-1 ps-3" style="padding-left:18px;">
                  <li>Download template Excel terlebih dahulu.</li>
                  <li>Isi data siswa sesuai kolom yang tersedia.</li>
                  <li>Upload kembali file Excel tersebut di form ini.</li>
                </ol>
                <span class="text-muted">
                  Contoh struktur kolom template:
                  <strong>A: nomor</strong>,
                  <strong>B: nama siswa</strong>,
                  <strong>C: NISN</strong>,
                  <strong>D: absen</strong>,
                  <strong>E: kelas</strong>.
                  Silakan sesuaikan dengan template yang kamu gunakan.
                </span>
              </div>
            </div>
          </div>

          <!-- Baris: tombol download template -->
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
            <span class="text-muted" style="font-size:13px;">
              Klik tombol di samping untuk mengunduh template Excel.
            </span>
            <a
              href="../../assets/templates/template_data_siswa.xlsx"
              class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2"
              download>
              <i class="fa-solid fa-file-excel"></i>
              <span>Download Template</span>
            </a>
          </div>

          <hr class="my-2">

          <!-- Input file -->
          <div class="mb-2">
            <label for="excelFile" class="form-label fw-semibold mb-1">
              Upload File Excel
            </label>
            <div class="position-relative d-flex align-items-center">
              <input
                type="file"
                class="form-control"
                id="excelFile"
                name="excelFile"
                accept=".xlsx,.xls"
                style="padding-right:35px;"
                required
                onchange="toggleClearButtonSiswaImport()">

              <button
                type="button"
                id="clearFileBtnSiswaImport"
                onclick="clearFileSiswaImport()"
                title="Hapus file"
                style="
                  position:absolute;
                  right:10px;
                  background:none;
                  border:none;
                  color:#6c757d;
                  font-size:20px;
                  line-height:1;
                  display:none;
                  cursor:pointer;
                ">
                &times;
              </button>
            </div>
            <small class="text-muted d-block mt-1" style="font-size:12px;">
              Format yang didukung: <strong>.xlsx</strong> atau <strong>.xls</strong>.
              Pastikan tidak mengubah urutan kolom di template.
            </small>
          </div>

        </div><!-- /.modal-body -->

        <div class="modal-footer d-flex justify-content-between">
          <button type="button"
                  class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"
                  data-bs-dismiss="modal">
            <i class="fa fa-times"></i> Batal
          </button>
          <button type="submit"
                  id="btnSubmitImportSiswa"
                  class="btn btn-warning d-inline-flex align-items-center gap-2">
            <i class="fas fa-upload"></i> Upload &amp; Proses
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// ====== HELPER IMPORT SISWA ======
function toggleClearButtonSiswaImport() {
  const fileInput = document.getElementById('excelFile');
  const clearBtn  = document.getElementById('clearFileBtnSiswaImport');
  if (!fileInput || !clearBtn) return;
  clearBtn.style.display = fileInput.files.length > 0 ? 'block' : 'none';
}

function clearFileSiswaImport() {
  const fileInput = document.getElementById('excelFile');
  const clearBtn  = document.getElementById('clearFileBtnSiswaImport');
  if (!fileInput || !clearBtn) return;
  fileInput.value = '';
  clearBtn.style.display = 'none';
}
</script>

<script>
(function() {
  const tbody = document.getElementById('tbodyData');
  const pagUl = document.getElementById('pagination');
  const pageInfo = document.getElementById('pageInfo');
  const input = document.getElementById('searchInput');
  const perSel = document.getElementById('perSelect');
  const deleteBtn = document.getElementById('deleteSelected');
  const selectAll = document.getElementById('selectAll');
  const formDelete = document.getElementById('formDeleteMultiple');
  let currentPage = <?= (int)$page ?>;
  let typingTimer;

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.innerText = str ?? '';
    return div.innerHTML;
  }

  function highlightText(text, keyword) {
    if (!keyword) return escapeHtml(text);
    const pattern = new RegExp(`(${keyword})`, 'gi');
    return escapeHtml(text).replace(pattern, '<span class="highlight">$1</span>');
  }

  function renderRows(data, startNumber, keyword = '') {
    if (!data || data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Data Tidak di Temukan</td></tr>';
      deleteBtn.disabled = true;
      return;
    }

    let html = '';
    for (const r of data) {
      const nama = highlightText(r.nama_siswa || '', keyword);
      const nisn = highlightText(r.no_induk_siswa || '', keyword);
      const komentar = highlightText(r.catatan_wali_kelas || '', keyword);
      const catatanRaw = r.catatan_wali_kelas || '';

      html += `
        <tr
          data-kelas="${r.id_kelas || ''}"
          data-tingkat="${escapeHtml(r.tingkat || '')}"
          data-id="${r.id_siswa}"
          data-nama="${escapeHtml(r.nama_siswa || '')}"
          data-nisn="${escapeHtml(r.no_induk_siswa || '')}"
          data-absen="${escapeHtml(r.no_absen_siswa || '')}"
          data-id_kelas="${escapeHtml(r.id_kelas || '')}"
          data-catatan="${escapeHtml(catatanRaw)}"
        >
          <td class="text-center"><input type="checkbox" class="row-check" name="id_siswa[]" value="${r.id_siswa}"></td>
          <td class="text-center">${escapeHtml(r.no_absen_siswa || '')}</td>
          <td>${nama}</td>
          <td class="text-center">${nisn}</td>
          <td class="text-center">${escapeHtml(r.nama_kelas || '-')}</td>
          <td>${komentar}</td>
          <td class="text-center">
            <button type="button" class="btn btn-warning btn-sm btn-edit-siswa">
              <i class="bi bi-pencil-square"></i> Edit
            </button>
            <a href="hapus_siswa.php?id=${r.id_siswa}" onclick="return confirm('Yakin ingin menghapus data?');" class="btn btn-danger btn-sm">
              <i class="bi bi-trash"></i> Del
            </a>
          </td>
        </tr>`;
    }
    tbody.innerHTML = html;

    if (keyword) {
      const rows = tbody.querySelectorAll('tr');
      rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        if (text.includes(keyword.toLowerCase())) {
          row.classList.add('row-highlight');
        } else {
          row.classList.remove('row-highlight');
        }
      });
    }

    initCheckboxEvents();
  }

  function renderPagination(page, totalPages, total, showed, per) {
    const start = Math.max(1, page - 2);
    const end = Math.min(totalPages, page + 2);
    let html = '';
    const makeLi = (disabled, target, text, active = false) => {
      const cls = ['page-item', disabled ? 'disabled' : '', active ? 'active' : ''].filter(Boolean).join(' ');
      const aAttr = disabled ? 'tabindex="-1"' : `data-page="${target}"`;
      return `<li class="${cls}"><a class="page-link" href="#" ${aAttr}>${text}</a></li>`;
    };
    html += makeLi(page <= 1, 1, '« First');
    html += makeLi(page <= 1, Math.max(1, page - 1), '‹ Prev');
    for (let i = start; i <= end; i++) html += makeLi(false, i, String(i), i === page);
    html += makeLi(page >= totalPages, Math.min(totalPages, page + 1), 'Next ›');
    html += makeLi(page >= totalPages, totalPages, 'Last »');
    pagUl.innerHTML = html;
    pageInfo.innerHTML = `Menampilkan <strong>${showed}</strong> dari <strong>${total}</strong> data • Halaman <strong>${page}</strong> / <strong>${totalPages}</strong>`;
    [...pagUl.querySelectorAll('a[data-page]')].forEach(a => {
      a.addEventListener('click', e => {
        e.preventDefault();
        currentPage = Number(a.getAttribute('data-page'));
        doSearch();
      });
    });
  }

  async function doSearch() {
    const q = input.value.trim();
    const per = Number(perSel.value || 10);
    const page = currentPage;
    const params = new URLSearchParams({ ajax: '1', q, per, page });
    try {
      const res = await fetch(`?${params.toString()}`);
      const json = await res.json();
      if (!json.ok) return;
      const startNumber = (json.page - 1) * json.per + 1;
      renderRows(json.data, startNumber, q);
      renderPagination(json.page, json.totalPages, json.total, json.data.length, json.per);
    } catch (e) {
      console.error(e);
    }
  }

  function initCheckboxEvents() {
    const allCheckboxes = tbody.querySelectorAll('.row-check');

    allCheckboxes.forEach(cb => {
      cb.addEventListener('change', () => {
        const anyChecked = tbody.querySelectorAll('.row-check:checked').length > 0;
        deleteBtn.disabled = !anyChecked;
        selectAll.checked = allCheckboxes.length > 0 && [...allCheckboxes].every(c => c.checked);
      });
    });

    selectAll.addEventListener('change', () => {
      const checked = selectAll.checked;
      allCheckboxes.forEach(cb => cb.checked = checked);
      deleteBtn.disabled = !checked;
    });
  }

  formDelete.addEventListener('submit', function(e) {
    const checked = tbody.querySelectorAll('.row-check:checked').length;
    if (checked === 0) {
      e.preventDefault();
      alert('Tidak ada data yang dipilih.');
      return false;
    }
    if (!confirm('Yakin ingin menghapus data terpilih?')) {
      e.preventDefault();
      return false;
    }
  });

  input.addEventListener('input', () => {
    clearTimeout(typingTimer);
    currentPage = 1;
    typingTimer = setTimeout(doSearch, 250);
  });
  perSel.addEventListener('change', () => { currentPage = 1; doSearch(); });

  // jalankan pertama kali
  doSearch();

  // ----------  MODAL: open handler (Tambah Siswa) ----------
  document.addEventListener('DOMContentLoaded', function() {
    const btnOpen = document.getElementById('openTambahModal');
    const modalEl = document.getElementById('modalTambahSiswa');
    if (btnOpen && modalEl && typeof bootstrap !== 'undefined') {
      const modalInstance = new bootstrap.Modal(modalEl);
      btnOpen.addEventListener('click', () => modalInstance.show());
    }
  });

  // ----------  MODAL: Edit Siswa ----------
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-edit-siswa');
    if (!btn) return;

    const row = btn.closest('tr');
    if (!row) return;

    const id      = row.dataset.id || '';
    const nama    = row.dataset.nama || '';
    const nisn    = row.dataset.nisn || '';
    const absen   = row.dataset.absen || '';
    const idKelas = row.dataset.id_kelas || '';
    const catatan = row.dataset.catatan || '';

    document.getElementById('edit_id_siswa').value           = id;
    document.getElementById('edit_nama_siswa').value         = nama;
    document.getElementById('edit_no_induk_siswa').value     = nisn;
    document.getElementById('edit_no_absen_siswa').value     = absen;
    document.getElementById('edit_catatan_wali_kelas').value = catatan;
    document.getElementById('edit_id_kelas').value           = idKelas;

    const modalEl = document.getElementById('modalEditSiswa');
    if (modalEl && typeof bootstrap !== 'undefined') {
      const modalInstance = new bootstrap.Modal(modalEl);
      modalInstance.show();
    }
  });

  // ----------  MODAL FORM SUBMIT (Tambah Siswa via AJAX) ----------
  document.addEventListener('submit', async function(e) {
    if (e.target && e.target.id === 'formTambahSiswa') {
      e.preventDefault();
      const form = e.target;
      const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('button.btn-success');
      if (submitBtn) submitBtn.disabled = true;

      try {
        const fd = new FormData(form);
        const resp = await fetch('tambah_siswa.php', { method: 'POST', body: fd });
        const text = await resp.text();
        let json;
        try {
          json = JSON.parse(text);
        } catch (err) {
          console.error('Response not JSON:', text);
          alert('Terjadi kesalahan server. Cek console network dan PHP error log.');
          if (submitBtn) submitBtn.disabled = false;
          return;
        }

        if (json.status === 'success') {
          const notifWrap = document.getElementById('notifContainer');
          if (notifWrap) {
            const el = document.createElement('div');
            el.className = 'alert alert-success mx-3 mt-3';
            el.id = 'notifSavedAjax';
            el.innerText = 'Data siswa berhasil ditambahkan.';
            notifWrap.appendChild(el);
            setTimeout(() => { if (el) el.remove(); }, 4000);
          }

          const modalEl = document.getElementById('modalTambahSiswa');
          const bsModal = bootstrap.Modal.getInstance(modalEl);
          if (bsModal) bsModal.hide();

          form.reset();

          currentPage = 1;
          doSearch();
        } else {
          alert('Gagal menyimpan: ' + (json.msg || 'unknown'));
        }
      } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan jaringan. Cek console.');
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    }
  });

  // ----------  VALIDASI IMPORT SISWA ----------
  const formImportSiswa = document.getElementById('formImportSiswa');
  const btnSubmitImportSiswa = document.getElementById('btnSubmitImportSiswa');
  if (formImportSiswa && btnSubmitImportSiswa) {
    btnSubmitImportSiswa.addEventListener('click', (e) => {
      if (!formImportSiswa.checkValidity()) {
        e.preventDefault();
        formImportSiswa.reportValidity();
      }
    });
  }

})();
</script>

<style>
.highlight {
  background-color: #d4edda;
  padding: 1px 2px;
  border-radius: 3px;
}
.row-highlight {
  background-color: #d4edda !important;
  transition: background-color 0.3s ease;
}
.filter-container { display:flex; flex-direction:column; gap:10px; }
.search-input { padding-right:30px; }

@media (max-width: 768px) {
  #pagination { flex-wrap: wrap !important; justify-content: center !important; gap: 4px !important; margin-top: 10px; }
  #pagination .page-link { padding: 4px 8px; font-size: 13px; }
  #pageInfo { font-size: 13px; margin-top: 5px; white-space: normal; }
  nav[aria-label="Page navigation"] { overflow: visible !important; position: relative; z-index: 10; }
  .table-responsive { overflow-x: auto; margin-bottom: 0 !important; }
  .top-bar{ flex-direction: column !important; align-items: center !important; text-align: center; }
  .filter-container{ width: 100%; align-items: center !important; margin-top: 10px; }
  .filter-group{ justify-content: center !important; width: 100%; }
  .dk-select{ width: 100% !important; }
  .action-buttons{ justify-content: center !important; margin-top: 10px; }
}
</style>
