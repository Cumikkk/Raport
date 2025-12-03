<?php 
// ===== BACKEND (fix: hide jejak + aman dari htmlspecialchars(NULL)) =====
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
require_once __DIR__ . '/../../koneksi.php';
mysqli_set_charset($koneksi, 'utf8mb4');

// Helper aman untuk cetak nilai (hindari warning PHP 8.1+)
function safe($v) {
  if ($v === null) return '';
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Ambil id mapel dari query string
$id_mapel = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_mapel <= 0) {
  echo "<script>alert('Parameter id mapel tidak valid.');location.href='mapel.php';</script>";
  exit;
}

// Ambil daftar semester
$semester_list = [];
$qSem = $koneksi->query("
  SELECT id_semester, COALESCE(nama_semester, CONCAT('Semester ', id_semester)) AS nama_semester 
  FROM semester 
  ORDER BY id_semester ASC
");
while ($row = $qSem->fetch_assoc()) $semester_list[] = $row;
$qSem->close();

// Tentukan semester aktif (default: terakhir)
$id_semester = isset($_GET['id_semester']) && is_numeric($_GET['id_semester'])
  ? (int)$_GET['id_semester']
  : ($semester_list ? (int)end($semester_list)['id_semester'] : 1);

// Ambil nama mapel
$mapel_nama = 'Tidak Diketahui';
$qMap = $koneksi->prepare("SELECT nama_mata_pelajaran FROM mata_pelajaran WHERE id_mata_pelajaran = ? LIMIT 1");
$qMap->bind_param('i', $id_mapel);
$qMap->execute();
$resMap = $qMap->get_result()->fetch_assoc();
if ($resMap) $mapel_nama = $resMap['nama_mata_pelajaran'];
$qMap->close();

/* ============================
 * INLINE SAVE (MODE EDIT)
 * ============================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mode']) && $_POST['mode'] === 'save_inline') {
  $dataNilai = isset($_POST['nilai']) && is_array($_POST['nilai']) ? $_POST['nilai'] : [];

  // daftar kolom di tabel nilai_mata_pelajaran
  $cols = [
    'tp1_lm1','tp2_lm1','tp3_lm1','tp4_lm1','sumatif_lm1',
    'tp1_lm2','tp2_lm2','tp3_lm2','tp4_lm2','sumatif_lm2',
    'tp1_lm3','tp2_lm3','tp3_lm3','tp4_lm3','sumatif_lm3',
    'tp1_lm4','tp2_lm4','tp3_lm4','tp4_lm4','sumatif_lm4',
    'sumatif_tengah_semester'
  ];

  if (!empty($dataNilai)) {
    // buat query UPDATE dengan NULLIF supaya input kosong jadi NULL
    $setParts = [];
    foreach ($cols as $c) {
      $setParts[] = "$c = NULLIF(?, '')";
    }
    $sqlUpd = "
      UPDATE nilai_mata_pelajaran SET
        ".implode(', ', $setParts)."
      WHERE id_siswa = ? AND id_mata_pelajaran = ? AND id_semester = ?
    ";
    $stmtUpd = $koneksi->prepare($sqlUpd);

    $types = str_repeat('s', count($cols)).'iii';

    foreach ($dataNilai as $id_siswa => $kolom) {
      // siapkan parameter sesuai urutan kolom
      $params = [];
      foreach ($cols as $c) {
        $params[] = isset($kolom[$c]) ? trim((string)$kolom[$c]) : '';
      }
      $params[] = (int)$id_siswa;
      $params[] = $id_mapel;
      $params[] = $id_semester;

      $stmtUpd->bind_param($types, ...$params);
      $stmtUpd->execute();
    }
    $stmtUpd->close();
  }

  header("Location: nilai_mapel.php?id={$id_mapel}&id_semester={$id_semester}&msg=edit_success");
  exit;
}

/* ============================
 * FLAG NOTIFIKASI (ADD / EDIT / DELETE / IMPORT)
 * ============================ */

// Baca pola utama ?msg=
$status = $_GET['msg'] ?? '';

// Tambahan: kalau file lain pakai ?add_success=1, ?edit_success=1, ?delete_success=1
if ($status === '') {
  if (isset($_GET['add_success'])) {
    $status = 'add_success';
  } elseif (isset($_GET['edit_success'])) {
    $status = 'edit_success';
  } elseif (isset($_GET['delete_success'])) {
    $status = 'delete_success';
  }
}

// Flag import
$import_ok = isset($_GET['import_ok']) ? (int)$_GET['import_ok'] : 0;
$okCount   = isset($_GET['ok'])     ? (int)$_GET['ok']     : 0;
$updCount  = isset($_GET['update']) ? (int)$_GET['update'] : 0;
$skipCount = isset($_GET['skip'])   ? (int)$_GET['skip']   : 0;
$errCount  = isset($_GET['err'])    ? (int)$_GET['err']    : 0;

// Jika ada import_ok=1, override status khusus import
if ($import_ok === 1) {
  $status = 'import_success';
}

/* ============================
 * BULK DELETE
 * ============================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'bulk_delete') {
  $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? array_map('intval', $_POST['ids']) : [];

  if (!empty($ids)) {
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));   // untuk id_siswa

    $sqlDel = "DELETE FROM nilai_mata_pelajaran 
               WHERE id_siswa IN ($marks) AND id_mata_pelajaran = ? AND id_semester = ?";
    $stmt = $koneksi->prepare($sqlDel);

    // tambahkan 2 parameter lagi (id_mapel & id_semester)
    $types .= 'ii';
    $params = $ids;          // mulai dari array id_siswa
    $params[] = $id_mapel;   // tambahkan id_mapel
    $params[] = $id_semester;// tambahkan id_semester

    // unpack sekali saja, dan di akhir
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();
  }

  header("Location: nilai_mapel.php?id=$id_mapel&id_semester=$id_semester&msg=delete_success");
  exit;
}

/* ============================
 * PAGINATION
 * ============================ */
$perPage = isset($_GET['per']) ? (int)$_GET['per'] : 10;
if ($perPage < 1 || $perPage > 100) $perPage = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Hitung total baris
$sqlCount = "
  SELECT COUNT(*) AS jml
  FROM nilai_mata_pelajaran n
  INNER JOIN siswa s ON s.id_siswa = n.id_siswa
  WHERE n.id_mata_pelajaran = ? AND n.id_semester = ?
";
$stmtCount = $koneksi->prepare($sqlCount);
$stmtCount->bind_param('ii', $id_mapel, $id_semester);
$stmtCount->execute();
$totalRows = (int)$stmtCount->get_result()->fetch_assoc()['jml'];
$stmtCount->close();

$totalPages = $totalRows > 0 ? (int)ceil($totalRows / $perPage) : 1;
if ($page > $totalPages) $page = $totalPages;

$offset = ($page - 1) * $perPage;

/* ============================
 * AMBIL DATA NILAI (LIMIT/OFFSET)
 * ============================ */
$rows = [];
$sqlData = "
  SELECT 
    s.id_siswa,
    s.nama_siswa,
    s.no_absen_siswa,
    n.tp1_lm1, n.tp2_lm1, n.tp3_lm1, n.tp4_lm1, n.sumatif_lm1,
    n.tp1_lm2, n.tp2_lm2, n.tp3_lm2, n.tp4_lm2, n.sumatif_lm2,
    n.tp1_lm3, n.tp2_lm3, n.tp3_lm3, n.tp4_lm3, n.sumatif_lm3,
    n.tp1_lm4, n.tp2_lm4, n.tp3_lm4, n.tp4_lm4, n.sumatif_lm4,
    n.sumatif_tengah_semester
  FROM nilai_mata_pelajaran n
  INNER JOIN siswa s 
    ON s.id_siswa = n.id_siswa
  WHERE n.id_mata_pelajaran = ?
    AND n.id_semester = ?
  GROUP BY 
    s.id_siswa, s.nama_siswa, s.no_absen_siswa,
    n.tp1_lm1, n.tp2_lm1, n.tp3_lm1, n.tp4_lm1, n.sumatif_lm1,
    n.tp1_lm2, n.tp2_lm2, n.tp3_lm2, n.tp4_lm2, n.sumatif_lm2,
    n.tp1_lm3, n.tp2_lm3, n.tp3_lm3, n.tp4_lm3, n.sumatif_lm3,
    n.tp1_lm4, n.tp2_lm4, n.tp3_lm4, n.tp4_lm4, n.sumatif_lm4,
    n.sumatif_tengah_semester
  ORDER BY (s.no_absen_siswa + 0), s.nama_siswa
  LIMIT $perPage OFFSET $offset
";
$q = $koneksi->prepare($sqlData);
$q->bind_param('ii', $id_mapel, $id_semester);
$q->execute();
$res = $q->get_result();
while ($r = $res->fetch_assoc()) $rows[] = $r;
$q->close();
?>

<?php include '../../includes/header.php'; ?>

<body>
<?php include '../../includes/navbar.php'; ?>

<main class="content">
  <div class="cards row" style="margin-top:-50px;">
    <div class="col-12">
      <div class="card shadow-sm" style="border-radius:15px;">

        <!-- JUDUL -->
        <div class="mt-0 d-flex align-items-center flex-wrap mb-0 p-3 top-bar">
          <h5 class="mb-1 fw-semibold fs-4 text-center">
            Nilai Mata Pelajaran - <?= safe($mapel_nama); ?>
          </h5>
        </div>

        <!-- BAR ATAS (filter & tombol) -->
        <div class="ms-3 me-3 bg-white d-flex justify-content-between align-items-center flex-wrap p-3 gap-3 rounded shadow-sm">
          <!-- dropdown semester tersembunyi (tidak ubah tampilan) -->
          <form method="get" style="display:none">
            <input type="hidden" name="id" value="<?= safe($id_mapel); ?>">
            <select name="id_semester" onchange="this.form.submit()">
              <?php foreach ($semester_list as $sem): ?>
                <option value="<?= $sem['id_semester']; ?>" <?= ($sem['id_semester'] == $id_semester) ? 'selected' : ''; ?>>
                  <?= safe($sem['nama_semester']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>

          <div class="d-flex align-items-center gap-2">
            <label for="selectKelas" class="fw-semibold mb-0">Kelas:</label>
            <select id="selectKelas" class="form-select form-select-sm" style="width: 180px;">
              <option value="">-- Pilih Kelas --</option>
              <option value="X">X</option>
              <option value="XI">XI</option>
              <option value="XII">XII</option>
            </select>
          </div>

          <div class="d-flex align-items-center gap-2">
            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Cari nama siswa..." style="width: 220px;">
            <button id="searchBtn" class="btn btn-outline-secondary btn-sm p-2 rounded-3 d-flex align-items-center justify-content-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                <path d="M11 6a5 5 0 1 0-2.9 4.7l3.85 3.85a1 1 0 0 0 1.414-1.414l-3.85-3.85A4.978 4.978 0 0 0 11 6zM6 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
              </svg>
            </button>
          </div>

          <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2">
              <!-- MODE EDIT SWITCH -->
              <span class="fw-semibold mb-0">Mode Edit</span>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="modeEditToggle">
              </div>
            </div>

            <div class="d-flex align-items-center gap-2">
              <!-- Tambah -->
              <a href="tambah_nilai.php?id=<?= urlencode($id_mapel); ?>&id_semester=<?= urlencode($id_semester); ?>"
                class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-plus"></i><span>Tambah</span>
              </a>

              <!-- Import -->
              <a href="import_nilai.php?id=<?= urlencode($id_mapel); ?>&id_semester=<?= urlencode($id_semester); ?>"
                class="btn btn-success btn-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-download"></i><span>Import</span>
              </a>

              <!-- Export -->
              <a href="nilai_mapel_export2.php?id=<?= urlencode($id_mapel) ?>&id_semester=<?= urlencode($id_semester) ?>"
                class="btn btn-success btn-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-excel"></i><span>Export</span>
              </a>
            </div>
          </div>
        </div>

        <!-- ALERT (di bawah bar putih, mirip data_absensi) -->
        <div id="alertArea">
          <?php if ($status === 'add_success'): ?>
            <div class="alert alert-success mx-3 mt-3 mb-0 alert-dismissible fade show" role="alert">
              Data nilai siswa berhasil <strong>ditambahkan</strong>.
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

          <?php elseif ($status === 'edit_success'): ?>
            <div class="alert alert-success mx-3 mt-3 mb-0 alert-dismissible fade show" role="alert">
              Data nilai siswa berhasil <strong>diperbarui</strong>.
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

          <?php elseif ($status === 'delete_success'): ?>
            <div class="alert alert-danger mx-3 mt-3 mb-0 alert-dismissible fade show" role="alert">
              Data nilai siswa berhasil <strong>dihapus</strong>.
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

          <?php elseif ($status === 'import_success'): ?>
            <?php
              $detail = [];
              if ($okCount   > 0) $detail[] = $okCount   . ' baris baru';
              if ($updCount  > 0) $detail[] = $updCount  . ' baris diperbarui';
              if ($skipCount > 0) $detail[] = $skipCount . ' baris dilewati';
              if ($errCount  > 0) $detail[] = $errCount  . ' baris error';
              $textDetail = $detail ? ' ('.implode(', ', $detail).')' : '';
              $alertClassImport = $errCount > 0 ? 'alert-danger' : 'alert-success';
            ?>
            <div class="alert <?= $alertClassImport ?> mx-3 mt-3 mb-0 alert-dismissible fade show" role="alert">
              Import nilai selesai<?= safe($textDetail) ?>.
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>
        </div>

        <!-- Tabel nilai -->
        <div class="card-body">
          <div class="table-responsive">
            <form method="post" id="bulkForm">
              <input type="hidden" name="aksi" value="bulk_delete">
              <table id="nilaiTable" class="table table-bordered text-center align-middle">
                <thead style="background-color:#1d52a2" class="text-white">
                  <tr>
                    <th rowspan="3"><input type="checkbox" id="selectAll"></th>
                    <th rowspan="3">NO.</th>
                    <th rowspan="3">NAMA</th>
                    <th colspan="16">FORMATIF</th>
                    <th colspan="4">SUMATIF</th>
                    <th rowspan="3">SUMATIF<br>TENGAH<br>SEMESTER</th>
                    <th rowspan="3">AKSI</th>
                  </tr>
                  <tr>
                    <th colspan="4">LINGKUP MATERI 1</th>
                    <th colspan="4">LINGKUP MATERI 2</th>
                    <th colspan="4">LINGKUP MATERI 3</th>
                    <th colspan="4">LINGKUP MATERI 4</th>
                    <th colspan="4">LINGKUP MATERI</th>
                  </tr>
                  <tr>
                    <th>TP1</th><th>TP2</th><th>TP3</th><th>TP4</th>
                    <th>TP1</th><th>TP2</th><th>TP3</th><th>TP4</th>
                    <th>TP1</th><th>TP2</th><th>TP3</th><th>TP4</th>
                    <th>TP1</th><th>TP2</th><th>TP3</th><th>TP4</th>
                    <th>LM1</th><th>LM2</th><th>LM3</th><th>LM4</th>
                  </tr>
                </thead>
                <tbody id="nilaiBody">
                  <?php if (count($rows) === 0): ?>
                    <tr><td colspan="25" class="text-center text-muted">Belum ada data nilai untuk semester ini.</td></tr>
                  <?php else: 
                    $no = $offset + 1;
                    foreach ($rows as $r): ?>
                    <tr>
                      <td><input type="checkbox" class="row-check" name="ids[]" value="<?= $r['id_siswa'] ?>"></td>
                      <td><?= $no++; ?></td>
                      <td><?= safe($r['nama_siswa']); ?></td>

                      <!-- LM1 -->
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][tp1_lm1]" value="<?= safe($r['tp1_lm1']); ?>" disabled></td>
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][tp2_lm1]" value="<?= safe($r['tp2_lm1']); ?>" disabled></td>
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][tp3_lm1]" value="<?= safe($r['tp3_lm1']); ?>" disabled></td>
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][tp4_lm1]" value="<?= safe($r['tp4_lm1']); ?>" disabled></td>

                      <!-- LM2 -->
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][tp1_lm2]" value="<?= safe($r['tp1_lm2']); ?>" disabled></td>
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][tp2_lm2]" value="<?= safe($r['tp2_lm2']); ?>" disabled></td>
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][tp3_lm2]" value="<?= safe($r['tp3_lm2']); ?>" disabled></td>
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][tp4_lm2]" value="<?= safe($r['tp4_lm2']); ?>" disabled></td>

                      <!-- LM3 -->
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][tp1_lm3]" value="<?= safe($r['tp1_lm3']); ?>" disabled></td>
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][tp2_lm3]" value="<?= safe($r['tp2_lm3']); ?>" disabled></td>
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][tp3_lm3]" value="<?= safe($r['tp3_lm3']); ?>" disabled></td>
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][tp4_lm3]" value="<?= safe($r['tp4_lm3']); ?>" disabled></td>

                      <!-- LM4 -->
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][tp1_lm4]" value="<?= safe($r['tp1_lm4']); ?>" disabled></td>
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][tp2_lm4]" value="<?= safe($r['tp2_lm4']); ?>" disabled></td>
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][tp3_lm4]" value="<?= safe($r['tp3_lm4']); ?>" disabled></td>
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][tp4_lm4]" value="<?= safe($r['tp4_lm4']); ?>" disabled></td>

                      <!-- SUMATIF LM -->
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][sumatif_lm1]" value="<?= safe($r['sumatif_lm1']); ?>" disabled></td>
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][sumatif_lm2]" value="<?= safe($r['sumatif_lm2']); ?>" disabled></td>
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][sumatif_lm3]" value="<?= safe($r['sumatif_lm3']); ?>" disabled></td>
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][sumatif_lm4]" value="<?= safe($r['sumatif_lm4']); ?>" disabled></td>

                      <!-- SUMATIF TENGAH SEMESTER -->
                      <td><input class="form-control form-control-sm nilai-input" type="text" name="nilai[<?= $r['id_siswa'] ?>][sumatif_tengah_semester]" value="<?= safe($r['sumatif_tengah_semester']); ?>" disabled></td>

                      <td>
                        <a href="hapus_nilai.php?id_siswa=<?= urlencode($r['id_siswa']); ?>&id_mapel=<?= urlencode($id_mapel); ?>&id_semester=<?= urlencode($id_semester); ?>"
                           class="btn btn-danger btn-sm d-inline-flex align-items-center justify-content-center gap-1 px-2 py-1"
                           onclick="return confirm('Yakin ingin menghapus nilai siswa ini?');">
                          <i class="bi bi-trash"></i><span>Del</span>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </form>
          </div>

          <!-- BAR BAWAH: HAPUS TERPILIH & SIMPAN (KANAN) -->
          <div class="mt-2 d-flex justify-content-between align-items-center">
            <div>
              <button id="deleteSelected" class="btn btn-danger btn-sm me-2" disabled>
                <i class="bi bi-trash"></i> Hapus Terpilih
              </button>
            </div>
            <button type="button" id="saveAllBtn" class="btn btn-secondary btn-sm" disabled>
              <i class="bi bi-save"></i> Simpan
            </button>
          </div>

          <!-- PAGINATION (DITENGAH) -->
          <div class="mt-3 d-flex justify-content-center">
            <div class="d-flex flex-column align-items-center text-center">
              <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-1">
                  <?php
                  // helper buat link
                  function page_link($pageLabel, $pageNumber, $disabled, $id_mapel, $id_semester, $perPage) {
                    $href  = $disabled ? '#' : "nilai_mapel.php?id={$id_mapel}&id_semester={$id_semester}&page={$pageNumber}&per={$perPage}";
                    $class = 'page-item';
                    if ($disabled) $class .= ' disabled';
                    $active = ($pageLabel === (string)$pageNumber && !$disabled) ? ' active' : '';
                    echo '<li class="'.$class.$active.'"><a class="page-link" href="'.$href.'">'.$pageLabel.'</a></li>';
                  }

                  $isFirst = ($page <= 1);
                  $isLast  = ($page >= $totalPages);

                  // First & Prev
                  page_link('« First', 1, $isFirst, $id_mapel, $id_semester, $perPage);
                  page_link('‹ Prev', max(1, $page-1), $isFirst, $id_mapel, $id_semester, $perPage);

                  // Current page
                  page_link((string)$page, $page, false, $id_mapel, $id_semester, $perPage);

                  // Next & Last
                  page_link('Next ›', min($totalPages, $page+1), $isLast, $id_mapel, $id_semester, $perPage);
                  page_link('Last »', $totalPages, $isLast, $id_mapel, $id_semester, $perPage);
                  ?>
                </ul>
              </nav>
              <small class="text-muted">
                Menampilkan <?= count($rows); ?> dari <?= $totalRows; ?> data • Halaman <?= $page; ?> / <?= $totalPages; ?>
              </small>
            </div>
          </div>

          <!-- Tombol Back -->
          <div class="mt-3 d-flex justify-content-start">
            <a href="mapel.php" class="btn btn-danger px-4 py-2 d-flex align-items-center gap-2" style="border-radius: 6px;">
              <i class="bi bi-arrow-left-circle"></i>Back
            </a>
          </div>

        </div>

      </div>
    </div>
  </div>
</main>

<!-- ===== LIVE SEARCH TANPA ENTER (+ auto-renumber kolom NO. di halaman aktif) ===== -->
<script>
(function () {
  const input = document.getElementById('searchInput');
  const btn   = document.getElementById('searchBtn');
  const body  = document.getElementById('nilaiBody');

  const debounce = (fn, delay = 120) => {
    let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
  };

  function filter() {
    const q = (input.value || '').trim().toLowerCase();
    const rows = Array.from(body.querySelectorAll('tr'));
    let visible = 0;

    rows.forEach(tr => {
      const tds = tr.querySelectorAll('td');
      if (tds.length < 3) return; // baris placeholder

      const nama = (tds[2].textContent || '').toLowerCase(); // kolom NAMA (setelah checkbox & NO)
      const match = !q || nama.includes(q);
      tr.style.display = match ? '' : 'none';
      if (match) {
        visible++;
        tds[1].textContent = visible; // kolom NO. di halaman aktif
      }
    });
  }

  if (input) input.addEventListener('input', debounce(filter, 120));
  if (btn) btn.addEventListener('click', (e) => { e.preventDefault(); filter(); input.focus(); });

  // Normalisasi penomoran awal
  filter();
})();
</script>

<!-- AUTO HIDE ALERT 5 DETIK -->
<script>
  window.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('#alertArea .alert');
    if (!alerts.length) return;
    setTimeout(() => {
      alerts.forEach(a => {
        a.style.transition = 'opacity 0.5s ease';
        a.style.opacity = '0';
        setTimeout(() => { if (a.parentNode) a.parentNode.remove(); }, 600);
      });
    }, 5000); // 5 detik
  });
</script>

<!-- SCRIPT CHECKBOX / BULK + MODE EDIT + SIMPAN -->
<script>
  const selectAll = document.getElementById('selectAll');
  const deleteBtn = document.getElementById('deleteSelected');
  const bulkForm  = document.getElementById('bulkForm');
  const modeToggle = document.getElementById('modeEditToggle');
  const saveAllBtn = document.getElementById('saveAllBtn');

  function toggleDeleteButton() {
    const any = [...document.querySelectorAll('.row-check')].some(c => c.checked);
    if (deleteBtn) deleteBtn.disabled = !any;
  }

  if (selectAll) {
    selectAll.addEventListener('change', function () {
      document.querySelectorAll('.row-check').forEach(chk => chk.checked = this.checked);
      toggleDeleteButton();
    });
  }

  document.addEventListener('change', (e) => {
    if (e.target.classList.contains('row-check')) {
      const all = [...document.querySelectorAll('.row-check')];
      if (selectAll) {
        selectAll.checked = all.length > 0 && all.every(c => c.checked);
      }
      toggleDeleteButton();
    }
  });

  if (deleteBtn) {
    deleteBtn.addEventListener('click', function () {
      if (this.disabled) return;
      if (confirm('Yakin ingin menghapus nilai terpilih?')) {
        bulkForm.submit();
      }
    });
  }

  // === MODE EDIT: enable/disable semua input nilai + tombol Simpan ===
  function setEditMode(on) {
    const inputs = document.querySelectorAll('.nilai-input');
    inputs.forEach(inp => inp.disabled = !on);
    if (saveAllBtn) {
      saveAllBtn.disabled = !on;
      saveAllBtn.classList.toggle('btn-success', on);
      saveAllBtn.classList.toggle('btn-secondary', !on);
    }
  }

  if (modeToggle) {
    modeToggle.addEventListener('change', function () {
      setEditMode(this.checked);
    });
  }
  // default: non-edit
  setEditMode(false);

  // === SIMPAN: kirim form dengan mode=save_inline ===
  if (saveAllBtn) {
    saveAllBtn.addEventListener('click', function () {
      if (this.disabled) return;
      const form = bulkForm;
      if (!form) return;
      let modeInput = form.querySelector('input[name="mode"]');
      if (!modeInput) {
        modeInput = document.createElement('input');
        modeInput.type = 'hidden';
        modeInput.name = 'mode';
        form.appendChild(modeInput);
      }
      modeInput.value = 'save_inline';
      form.submit();
    });
  }
</script>

<?php include '../../includes/footer.php'; ?>
</body>
