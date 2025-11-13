<?php
// ===== BACKEND: Import nilai dengan PhpSpreadsheet (UPSERT) =====
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
require_once __DIR__ . '/../../koneksi.php';
mysqli_set_charset($koneksi, 'utf8mb4');

// --- Ambil id mapel & semester dari query ---
$id_mapel    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_semester = isset($_GET['id_semester']) ? (int)$_GET['id_semester'] : 0;
if ($id_mapel <= 0) {
  echo "<script>alert('Parameter id mapel tidak valid.');location.href='mapel.php';</script>";
  exit;
}

// --- Nama Mapel untuk header halaman ---
$mapel_nama = 'Tidak Diketahui';
try {
  $stmt = $koneksi->prepare("SELECT nama_mata_pelajaran FROM mata_pelajaran WHERE id_mata_pelajaran=? LIMIT 1");
  $stmt->bind_param('i', $id_mapel);
  $stmt->execute();
  $r = $stmt->get_result()->fetch_assoc();
  if ($r) $mapel_nama = $r['nama_mata_pelajaran'];
  $stmt->close();
} catch (Throwable $e) { /* biarkan */ }

// --- Jika id_semester kosong, pakai yang terakhir (opsional) ---
if ($id_semester <= 0) {
  $qSem = $koneksi->query("SELECT id_semester FROM semester ORDER BY id_semester DESC LIMIT 1");
  if ($qSem && $qSem->num_rows) $id_semester = (int)$qSem->fetch_assoc()['id_semester'];
  if ($qSem) $qSem->close();
}

// === Autoload PhpSpreadsheet (via composer) ===
$autoloadCandidates = [
  __DIR__ . '/../../vendor/autoload.php',
  __DIR__ . '/../../../vendor/autoload.php',
  __DIR__ . '/../../../../vendor/autoload.php',
];
$autoloaded = false;
foreach ($autoloadCandidates as $auto) {
  if (file_exists($auto)) {
    require_once $auto;
    $autoloaded = true;
    break;
  }
}

// variabel untuk menampilkan error di tampilan
$err_msg = '';
$import_summary = ['ok' => 0, 'update' => 0, 'skip' => 0, 'err' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!$autoloaded) {
    $err_msg = 'Paket PhpSpreadsheet belum ditemukan. Jalankan: composer require phpoffice/phpspreadsheet.';
  } elseif (!isset($_FILES['excel']) || $_FILES['excel']['error'] !== UPLOAD_ERR_OK) {
    $err_msg = 'Upload gagal. Pastikan file Excel sudah dipilih.';
  } else {
    // ====== Mulai proses baca Excel ======
    $file   = $_FILES['excel'];
    $tmpPath = $file['tmp_name'];

    try {
      // suppress warning dari ZipArchive saat load, tapi tetap tangkap Exception
      set_error_handler(function() { /* abaikan warning dari IOFactory::load */ });
      $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpPath);
      restore_error_handler();

      $sheet = $spreadsheet->getActiveSheet();
      $rows  = $sheet->toArray(null, true, true, true); // A,B,C,...

      if (!$rows || count($rows) < 2) {
        throw new RuntimeException('File kosong atau tidak ada data baris (minimal 1 baris header + 1 baris data).');
      }

      // --- Baca header baris pertama ---
      $headerRow = $rows[1]; // ['A'=>'No_Absen','B'=>'Nama_Siswa',...]
      $headerMap = [];       // nama_kolom_lower => 'A'
      foreach ($headerRow as $col => $name) {
        $key = strtolower(trim((string)$name));
        if ($key !== '') $headerMap[$key] = $col;
      }

      // Pastikan ada identitas siswa
      $idKeys = ['id_siswa', 'nis', 'nama_siswa', 'no_absen'];
      $hasIdKey = false;
      foreach ($idKeys as $k) {
        if (isset($headerMap[$k])) { $hasIdKey = true; break; }
      }
      if (!$hasIdKey) {
        throw new RuntimeException('Header harus punya minimal: Nama_Siswa dan/atau No_Absen (boleh juga id_siswa / nis).');
      }

      // Kolom-kolom nilai yang kita dukung (sesuai ekspor)
      $nilaiCols = [
        'tp1_lm1','tp2_lm1','tp3_lm1','tp4_lm1','sumatif_lm1',
        'tp1_lm2','tp2_lm2','tp3_lm2','tp4_lm2','sumatif_lm2',
        'tp1_lm3','tp2_lm3','tp3_lm3','tp4_lm3','sumatif_lm3',
        'tp1_lm4','tp2_lm4','tp3_lm4','tp4_lm4','sumatif_lm4',
        'sumatif_tengah_semester'
      ];

      // Helper ambil cell berdasarkan nama header
      $getCell = function(array $row, string $name) use ($headerMap) {
        $k = strtolower($name);
        if (!isset($headerMap[$k])) return null;
        $col = $headerMap[$k];
        return array_key_exists($col, $row) ? $row[$col] : null;
      };

      // Siapkan statement cek-ada / insert / update
      $stmtCheck = $koneksi->prepare("
        SELECT 1 FROM nilai_mata_pelajaran
        WHERE id_mata_pelajaran = ? AND id_semester = ? AND id_siswa = ?
        LIMIT 1
      ");

      $setParts = [];
      foreach ($nilaiCols as $c) $setParts[] = "$c = ?";
      $setClause = implode(', ', $setParts);

      $stmtUpdate = $koneksi->prepare("
        UPDATE nilai_mata_pelajaran
        SET $setClause
        WHERE id_mata_pelajaran = ? AND id_semester = ? AND id_siswa = ?
      ");

      $insertCols = implode(', ', array_merge(['id_mata_pelajaran','id_semester','id_siswa'], $nilaiCols));
      $insertQ    = rtrim(str_repeat('?,', 3 + count($nilaiCols)), ',');
      $stmtInsert = $koneksi->prepare("
        INSERT INTO nilai_mata_pelajaran ($insertCols)
        VALUES ($insertQ)
      ");

      $koneksi->begin_transaction();

      // Loop data mulai baris ke-2
      $rowCount = count($rows);
      for ($i = 2; $i <= $rowCount; $i++) {
        $row = $rows[$i];

        // 1) Ambil identitas dari Excel
        $rawId      = $getCell($row, 'id_siswa');
        $rawNis     = $getCell($row, 'nis');
        $rawNama    = $getCell($row, 'nama_siswa');   // dari header "Nama_Siswa"
        $rawNoAbsen = $getCell($row, 'no_absen');     // dari header "No_Absen"

        // Normalisasi
        $nama_key   = $rawNama !== null ? trim((string)$rawNama) : '';
        $no_absen   = $rawNoAbsen !== null ? trim((string)$rawNoAbsen) : '';

        if ($nama_key === '' && $no_absen === '' && ($rawId === null || $rawId === '')) {
          // baris kosong → lewati
          $import_summary['skip']++;
          continue;
        }

        // 2) Resolve id_siswa
        $id_siswa = 0;

        // 2a. id_siswa langsung
        if ($rawId !== null && trim((string)$rawId) !== '' && is_numeric($rawId)) {
          $id_siswa = (int)$rawId;
        }

        // 2b. cari dari NIS
        if ($id_siswa <= 0 && $rawNis !== null && trim((string)$rawNis) !== '') {
          $nis = trim((string)$rawNis);
          $stmt = $koneksi->prepare("SELECT id_siswa FROM siswa WHERE no_induk_siswa = ? LIMIT 1");
          $stmt->bind_param('s', $nis);
          $stmt->execute();
          $rs = $stmt->get_result()->fetch_assoc();
          $stmt->close();
          if ($rs) $id_siswa = (int)$rs['id_siswa'];
        }

        // 2c. kombinasi nama + no_absen
        if ($id_siswa <= 0 && $nama_key !== '' && $no_absen !== '') {
          $stmt = $koneksi->prepare("
            SELECT id_siswa
            FROM siswa
            WHERE LOWER(TRIM(REPLACE(nama_siswa, '  ', ' '))) =
                  LOWER(TRIM(REPLACE(?, '  ', ' ')))
              AND no_absen_siswa = ?
            LIMIT 1
          ");
          $stmt->bind_param('ss', $nama_key, $no_absen);
          $stmt->execute();
          $rs = $stmt->get_result()->fetch_assoc();
          $stmt->close();
          if ($rs) $id_siswa = (int)$rs['id_siswa'];
        }

        // 2d. nama saja
        if ($id_siswa <= 0 && $nama_key !== '') {
          $stmt = $koneksi->prepare("
            SELECT id_siswa
            FROM siswa
            WHERE LOWER(TRIM(REPLACE(nama_siswa, '  ', ' '))) =
                  LOWER(TRIM(REPLACE(?, '  ', ' ')))
            LIMIT 1
          ");
          $stmt->bind_param('s', $nama_key);
          $stmt->execute();
          $rs = $stmt->get_result()->fetch_assoc();
          $stmt->close();
          if ($rs) $id_siswa = (int)$rs['id_siswa'];
        }

        // 2e. kalau tetap belum ketemu → buat siswa baru
        if ($id_siswa <= 0) {
          if ($nama_key === '') {
            // tidak ada nama sama sekali, baris di-skip
            $import_summary['skip']++;
            continue;
          }
          $id_kelas = null;
          $nis      = null;
          $stmt = $koneksi->prepare("
            INSERT INTO siswa (id_kelas, no_induk_siswa, no_absen_siswa, nama_siswa)
            VALUES (?, ?, ?, ?)
          ");
          $stmt->bind_param('isss', $id_kelas, $nis, $no_absen, $nama_key);
          $stmt->execute();
          $id_siswa = $stmt->insert_id;
          $stmt->close();
        }

        // 3) Ambil nilai
        $values = [];
        foreach ($nilaiCols as $c) {
          $val = $getCell($row, $c);
          if ($val === '' || $val === null) {
            $values[] = null;
          } elseif (is_numeric($val)) {
            $values[] = $val + 0;
          } else {
            $v = str_replace(',', '.', trim((string)$val));
            $values[] = is_numeric($v) ? ($v + 0) : null;
          }
        }

        // 4) cek sudah ada atau belum
        $stmtCheck->bind_param('iii', $id_mapel, $id_semester, $id_siswa);
        $stmtCheck->execute();
        $exists = (bool)$stmtCheck->get_result()->fetch_row();

        if ($exists) {
          $types  = str_repeat('d', count($nilaiCols)) . 'iii';
          $params = array_merge($values, [$id_mapel, $id_semester, $id_siswa]);
          $stmtUpdate->bind_param($types, ...$params);
          $stmtUpdate->execute();
          $import_summary['update']++;
        } else {
          $types  = 'iii' . str_repeat('d', count($nilaiCols));
          $params = array_merge([$id_mapel, $id_semester, $id_siswa], $values);
          $stmtInsert->bind_param($types, ...$params);
          $stmtInsert->execute();
          $import_summary['ok']++;
        }
      }

      $koneksi->commit();

      // Sukses → langsung redirect sebelum ada output HTML
      $qs = http_build_query([
        'id'          => $id_mapel,
        'id_semester' => $id_semester,
        'import_ok'   => 1,
        'ok'          => $import_summary['ok'],
        'update'      => $import_summary['update'],
        'skip'        => $import_summary['skip'],
        'err'         => $import_summary['err'],
      ]);
      header("Location: nilai_mapel.php?$qs");
      exit;

    } catch (Throwable $e) {
      // Gagal proses file → rollback & tampilkan pesan
      try { $koneksi->rollback(); } catch (Throwable $e2) {}
      restore_error_handler();
      $err_msg = 'Gagal memproses file: ' . $e->getMessage();
    }
  }
}
?>

<?php include '../../includes/header.php'; ?>

<body>
<?php include '../../includes/navbar.php'; ?>

<div class="dk-page" style="margin-top: 50px;">
  <div class="dk-main">
    <div class="dk-content-box">
      <div class="container py-4">
        <h4 class="fw-bold mb-1">Import Data Nilai Mata Pelajaran</h4>
        <div class="text-muted mb-4">
          Mapel: <b><?= htmlspecialchars($mapel_nama, ENT_QUOTES) ?></b>
          <?php if ($id_semester > 0): ?>
            <span class="ms-2">(Semester ID: <?= (int)$id_semester ?>)</span>
          <?php endif; ?>
        </div>

        <?php if ($err_msg): ?>
          <div class="alert alert-danger">
            <?= htmlspecialchars($err_msg, ENT_QUOTES) ?>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="excelFile" class="form-label">Pilih File Excel (.xlsx / .xls)</label>

            <div class="position-relative" style="display: flex; align-items: center;">
              <input
                type="file"
                class="form-control"
                id="excelFile"
                name="excel"
                accept=".xlsx, .xls"
                onchange="toggleClearButton()"
                style="padding-right: 35px;"
                required
              >
              <button
                type="button"
                id="clearFileBtn"
                onclick="clearFile()"
                title="Hapus file"
                style="
                  position: absolute;
                  right: 10px;
                  background: none;
                  border: none;
                  color: #6c757d;
                  font-size: 20px;
                  line-height: 1;
                  display: none;
                  cursor: pointer;
                ">
                &times;
              </button>
            </div>

            <div class="form-text mt-2">
              Contoh header yang didukung (bebas urutan): 
              <code>No_Absen</code>, <code>Nama_Siswa</code>,
              dan kolom nilai seperti:
              <code>tp1_lm1, tp2_lm1, tp3_lm1, tp4_lm1, sumatif_lm1</code>,
              <code>tp1_lm2 ... sumatif_lm2</code>,
              <code>tp1_lm3 ... sumatif_lm3</code>,
              <code>tp1_lm4 ... sumatif_lm4</code>,
              <code>sumatif_tengah_semester</code>.
            </div>
          </div>

          <div class="d-flex justify-content-between mt-4">
            <a href="nilai_mapel.php?id=<?= urlencode($id_mapel) ?>&id_semester=<?= urlencode($id_semester) ?>" class="btn btn-danger">
              <i class="fa fa-times"></i> Batal
            </a>
            <button type="submit" class="btn btn-warning">
              <i class="fas fa-upload"></i> Upload
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<script>
  const fileInput = document.getElementById("excelFile");
  const clearBtn  = document.getElementById("clearFileBtn");

  function toggleClearButton() {
    clearBtn.style.display = fileInput.files.length > 0 ? "block" : "none";
  }

  function clearFile() {
    fileInput.value = "";
    clearBtn.style.display = "none";
  }
</script>

<?php include '../../includes/footer.php'; ?>
