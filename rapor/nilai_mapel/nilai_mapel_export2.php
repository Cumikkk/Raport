<?php
// ===== Export XLSX TANPA COMPOSER =====
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
require_once __DIR__ . '/../../koneksi.php';
mysqli_set_charset($koneksi, 'utf8mb4');

// Pastikan ZipArchive tersedia
if (!class_exists('ZipArchive')) {
  header('Content-Type: text/plain; charset=utf-8');
  http_response_code(500);
  echo "ZipArchive tidak tersedia. Aktifkan ekstensi zip di php.ini (extension=zip) lalu restart server.";
  exit;
}

// Params
$id_mapel     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_semester  = (isset($_GET['id_semester']) && is_numeric($_GET['id_semester'])) ? (int)$_GET['id_semester'] : 0;
$id_kelas_opt = (isset($_GET['id_kelas']) && is_numeric($_GET['id_kelas'])) ? (int)$_GET['id_kelas'] : 0;

if ($id_mapel <= 0 || $id_semester <= 0) {
  header('Content-Type: text/plain; charset=utf-8');
  http_response_code(400);
  echo "Parameter id atau id_semester tidak valid.";
  exit;
}

// Nama mapel → untuk nama file
$mapel_nama = 'Tidak_Diketahui';
$stmt = $koneksi->prepare("SELECT nama_mata_pelajaran FROM mata_pelajaran WHERE id_mata_pelajaran=? LIMIT 1");
$stmt->bind_param('i', $id_mapel);
$stmt->execute();
$mm = $stmt->get_result()->fetch_assoc();
$stmt->close();
if ($mm && !empty($mm['nama_mata_pelajaran'])) {
  $mapel_nama = preg_replace('/[^A-Za-z0-9_\-]/', '_', $mm['nama_mata_pelajaran']);
}

// Ambil data sesuai tampilan tabel
$sql = "
  SELECT 
    s.no_absen_siswa   AS No_Absen,
    s.nama_siswa       AS Nama_Siswa,
    n.tp1_lm1, n.tp2_lm1, n.tp3_lm1, n.tp4_lm1, n.sumatif_lm1,
    n.tp1_lm2, n.tp2_lm2, n.tp3_lm2, n.tp4_lm2, n.sumatif_lm2,
    n.tp1_lm3, n.tp2_lm3, n.tp3_lm3, n.tp4_lm3, n.sumatif_lm3,
    n.tp1_lm4, n.tp2_lm4, n.tp3_lm4, n.tp4_lm4, n.sumatif_lm4,
    n.sumatif_tengah_semester
  FROM nilai_mata_pelajaran n
  INNER JOIN siswa s ON s.id_siswa = n.id_siswa
  WHERE n.id_mata_pelajaran = ?
    AND n.id_semester = ?
";
$params = [$id_mapel, $id_semester];
$types  = 'ii';

if ($id_kelas_opt > 0) {
  $sql .= " AND s.id_kelas = ? ";
  $params[] = $id_kelas_opt;
  $types   .= 'i';
}
$sql .= " ORDER BY (s.no_absen_siswa + 0), s.nama_siswa";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res  = $stmt->get_result();
$data = [];
while ($r = $res->fetch_assoc()) $data[] = $r;
$stmt->close();

// ==== Helper kecil untuk XLSX ====
function xl_col_letter($i) { // 1->A, 2->B, ...
  $s = '';
  while ($i > 0) {
    $m = ($i - 1) % 26;
    $s = chr(65 + $m) . $s;
    $i = (int)(($i - $m) / 26);
  }
  return $s;
}
function xml_t($s) {
  // Escape text untuk XML
  return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Header kolom (urut & nama sama seperti tabel export)
$headers = [
  'No_Absen','Nama_Siswa',
  'tp1_lm1','tp2_lm1','tp3_lm1','tp4_lm1','sumatif_lm1',
  'tp1_lm2','tp2_lm2','tp3_lm2','tp4_lm2','sumatif_lm2',
  'tp1_lm3','tp2_lm3','tp3_lm3','tp4_lm3','sumatif_lm3',
  'tp1_lm4','tp2_lm4','tp3_lm4','tp4_lm4','sumatif_lm4',
  'sumatif_tengah_semester'
];

// Bangun sheet XML (pakai inlineStr untuk teks; angka pakai <v>)
$sheetRows = [];
$rowNum = 1;

// Row header
$cells = [];
for ($c = 1; $c <= count($headers); $c++) {
  $ref = xl_col_letter($c) . $rowNum;
  $cells[] = '<c r="'.$ref.'" t="inlineStr"><is><t>'.xml_t($headers[$c-1]).'</t></is></c>';
}
$sheetRows[] = '<row r="'.$rowNum.'">'.implode('', $cells).'</row>';
$rowNum++;

// Data rows
foreach ($data as $row) {
  $cells = [];
  $colIdx = 1;
  foreach ($headers as $key) {
    $val = $row[$key] ?? '';
    $ref = xl_col_letter($colIdx) . $rowNum;

    if ($val === '' || $val === null) {
      $cells[] = '<c r="'.$ref.'"/>';
    } elseif (is_numeric($val)) {
      $cells[] = '<c r="'.$ref.'"><v>'.((0 + $val)).'</v></c>';
    } else {
      $cells[] = '<c r="'.$ref.'" t="inlineStr"><is><t>'.xml_t($val).'</t></is></c>';
    }
    $colIdx++;
  }
  $sheetRows[] = '<row r="'.$rowNum.'">'.implode('', $cells).'</row>';
  $rowNum++;
}

$lastCol = xl_col_letter(count($headers));
$lastRow = max(1, $rowNum - 1); // kalau tidak ada data → tetap A1:lastCol1
$dimension = 'A1:'.$lastCol.$lastRow;

// ====== Semua part XLSX (tanpa sharedStrings untuk menyederhanakan) ======
$content_types = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>
XML;

$rels = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;

$workbook = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Nilai" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML;

$workbook_rels = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;

// Styles minimal: bold untuk header (xfId=0, applyFont=1)
$styles = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><sz val="11"/><name val="Calibri"/></font>
    <font><b/><sz val="11"/><name val="Calibri"/></font>
  </fonts>
  <fills count="1"><fill><patternFill patternType="none"/></fill></fills>
  <borders count="1"><border/></borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="2">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>
  </cellXfs>
</styleSheet>
XML;

// Sheet1 (beri style header di row 1 via s="1" di setiap cell header)
$sheetRowsStyled = [];
if (!empty($sheetRows)) {
  // row 1: inject s="1"
  $sheetRowsStyled[] = preg_replace('/<c /', '<c s="1" ', $sheetRows[0], -1);
  for ($i = 1; $i < count($sheetRows); $i++) {
    $sheetRowsStyled[] = $sheetRows[$i];
  }
}

$sheet1 = '<?xml version="1.0" encoding="UTF-8"?>' .
  '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" ' .
  'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
  '<dimension ref="'.xml_t($dimension).'"/>' .
  '<sheetData>' . implode('', $sheetRowsStyled) . '</sheetData>' .
  '</worksheet>';

// ====== Build ZIP ke output ======
$filename = 'Nilai_' . $mapel_nama . '_Semester_' . $id_semester . ($id_kelas_opt>0 ? '_Kelas_'.$id_kelas_opt : '') . '.xlsx';

$zip = new ZipArchive();
$tmp = tempnam(sys_get_temp_dir(), 'xlsx_');
@unlink($tmp); // ZipArchive butuh nama file yang belum ada

if ($zip->open($tmp, ZipArchive::CREATE) !== TRUE) {
  header('Content-Type: text/plain; charset=utf-8');
  http_response_code(500);
  echo "Gagal membuat file ZIP.";
  exit;
}

// Tambahkan part-part wajib XLSX
$zip->addFromString('[Content_Types].xml', $content_types);
$zip->addFromString('_rels/.rels', $rels);
$zip->addFromString('xl/workbook.xml', $workbook);
$zip->addFromString('xl/_rels/workbook.xml.rels', $workbook_rels);
$zip->addFromString('xl/styles.xml', $styles);
$zip->addFromString('xl/worksheets/sheet1.xml', $sheet1);

$zip->close();

// Output unduhan
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Content-Length: ' . filesize($tmp));
header('Cache-Control: max-age=0');

readfile($tmp);
@unlink($tmp);
exit;
