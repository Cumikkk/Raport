<?php
require_once __DIR__ . '/../../koneksi.php';
$baseURL = '/RAPORT';
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "<script>alert('ID siswa tidak valid.'); window.close();</script>";
  exit;
}

$id_siswa = (int) $_GET['id'];

/* =========================
 * DATA SEKOLAH (KOP & LOGO)
 * ========================= */
$sekolah = [
  'logo_sekolah'               => 'default.png',
  'nama_sekolah'               => '',
  'nsm_sekolah'                => '',
  'npsn_sekolah'               => '',
  'alamat_sekolah'             => '',
  'no_telepon_sekolah'         => '',
  'kecamatan_sekolah'          => '',
  'kabupaten_atau_kota_sekolah'=> '',
  'provinsi_sekolah'           => '',
];

$resSek = $koneksi->query("SELECT * FROM sekolah ORDER BY id_sekolah ASC LIMIT 1");
if ($resSek && $rowSek = $resSek->fetch_assoc()) {
  $sekolah = $rowSek;
}

/* =========================
 * DATA SISWA + KELAS + WALAS
 * ========================= */
$sqlSiswa = "
  SELECT s.*, k.nama_kelas, g.nama_guru AS wali_kelas
  FROM siswa s
  LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
  LEFT JOIN guru g  ON k.id_guru  = g.id_guru
  WHERE s.id_siswa = ?
  LIMIT 1
";
$stmtSiswa = $koneksi->prepare($sqlSiswa);
$stmtSiswa->bind_param('i', $id_siswa);
$stmtSiswa->execute();
$dataSiswa = $stmtSiswa->get_result()->fetch_assoc();
$stmtSiswa->close();

if (!$dataSiswa) {
  echo "<script>alert('Data siswa tidak ditemukan.'); window.close();</script>";
  exit;
}

/* =========================
 * SEMESTER AKTIF (PAKAI TERAKHIR)
 * ========================= */
$id_semester   = 0;
$nama_semester = '';
$resSem = $koneksi->query("
  SELECT id_semester,
         COALESCE(nama_semester, CONCAT('Semester ', id_semester)) AS nama_semester
  FROM semester
  ORDER BY id_semester DESC
  LIMIT 1
");
if ($resSem && $rowSem = $resSem->fetch_assoc()) {
  $id_semester   = (int) $rowSem['id_semester'];
  $nama_semester = $rowSem['nama_semester'];
}

/* =========================
 * NILAI MATA PELAJARAN
 * (hanya mapel yang aktif di kurikulum kelas siswa)
 * ========================= */
$nilaiMapel = [];
$id_kelas_siswa = isset($dataSiswa['id_kelas']) ? (int)$dataSiswa['id_kelas'] : 0;
if ($id_semester > 0 && $id_kelas_siswa > 0) {
  $sqlNilai = "
    SELECT
      m.kelompok_mata_pelajaran,
      m.nama_mata_pelajaran,
      n.*
    FROM nilai_mata_pelajaran n
    INNER JOIN mata_pelajaran m
        ON m.id_mata_pelajaran = n.id_mata_pelajaran
    INNER JOIN kurikulum k
        ON k.id_mata_pelajaran = m.id_mata_pelajaran
       AND k.id_kelas = ?
    WHERE n.id_siswa = ?
      AND n.id_semester = ?
      AND k.nilai_kurikulum <> 0
    ORDER BY m.kelompok_mata_pelajaran, m.nama_mata_pelajaran
  ";
  $stmtN = $koneksi->prepare($sqlNilai);
  $stmtN->bind_param('iii', $id_kelas_siswa, $id_siswa, $id_semester);
  $stmtN->execute();
  $resN = $stmtN->get_result();
  while ($row = $resN->fetch_assoc()) {
    $nilaiMapel[] = $row;
  }
  $stmtN->close();
}

/* =========================
 * NILAI EKSTRAKURIKULER
 * ========================= */
$nilaiEkstra = [];
$sqlEk = "
  SELECT e.nama_ekstrakurikuler, ne.nilai_ekstrakurikuler
  FROM nilai_ekstrakurikuler ne
  INNER JOIN ekstrakurikuler e
      ON e.id_ekstrakurikuler = ne.id_ekstrakurikuler
  WHERE ne.id_siswa = ?
  ORDER BY e.nama_ekstrakurikuler
";
$stmtEk = $koneksi->prepare($sqlEk);
$stmtEk->bind_param('i', $id_siswa);
$stmtEk->execute();
$resEk = $stmtEk->get_result();
while ($row = $resEk->fetch_assoc()) {
  $nilaiEkstra[] = $row;
}
$stmtEk->close();

/* =========================
 * ABSENSI
 * ========================= */
$absensi = [
  'sakit' => 0,
  'izin'  => 0,
  'alpha' => 0,
];

$stmtAbs = $koneksi->prepare("
  SELECT sakit, izin, alpha
  FROM absensi
  WHERE id_siswa = ?
  LIMIT 1
");
$stmtAbs->bind_param('i', $id_siswa);
$stmtAbs->execute();
$resAbs = $stmtAbs->get_result()->fetch_assoc();
$stmtAbs->close();
if ($resAbs) {
  $absensi = $resAbs;
}

/* =========================
 * CATATAN WALAS (OPSIONAL)
 * ========================= */
// Ambil pengaturan_cetak_rapor (satu baris terakhir)
$pengaturanCetak = [
  'id_pengaturan_cetak_rapor' => null,
  'tempat_cetak'              => '',
  'tanggal_cetak'             => null,
];
$resPeng = $koneksi->query("SELECT * FROM pengaturan_cetak_rapor ORDER BY id_pengaturan_cetak_rapor DESC LIMIT 1");
if ($resPeng && $rowPeng = $resPeng->fetch_assoc()) {
  $pengaturanCetak = $rowPeng;
}

$id_pengaturan_cetak_rapor = isset($pengaturanCetak['id_pengaturan_cetak_rapor'])
  ? (int)$pengaturanCetak['id_pengaturan_cetak_rapor']
  : null;

// Tempat dan tanggal cetak
$tempat_cetak = $pengaturanCetak['tempat_cetak'] ?? '';
if ($tempat_cetak === '' && isset($sekolah['kabupaten_atau_kota_sekolah'])) {
  $tempat_cetak = $sekolah['kabupaten_atau_kota_sekolah'];
}

$tanggal_cetak_indo = '';
if (!empty($pengaturanCetak['tanggal_cetak'])) {
  $ts = strtotime($pengaturanCetak['tanggal_cetak']);
  if ($ts) {
    $bulanIndo = [
      1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
           'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $tgl = (int)date('j', $ts);
    $bln = $bulanIndo[(int)date('n', $ts)] ?? date('F', $ts);
    $thn = date('Y', $ts);
    $tanggal_cetak_indo = $tgl . ' ' . $bln . ' ' . $thn;
  }
}
if ($tanggal_cetak_indo === '') {
  $tanggal_cetak_indo = date('j F Y');
}

$catatan_walas = '';
$stmtCR = $koneksi->prepare("SELECT catatan_wali_kelas FROM cetak_rapor WHERE id_siswa = ? ORDER BY id_cetak_rapor DESC LIMIT 1");
if ($stmtCR) {
  $stmtCR->bind_param('i', $id_siswa);
  $stmtCR->execute();
  $resCR = $stmtCR->get_result();
  if ($rowCR = $resCR->fetch_assoc()) {
    $catatan_walas = (string)($rowCR['catatan_wali_kelas'] ?? '');
  }
  $stmtCR->close();
}

/* =========================
 * KEPALA MADRASAH (GURU)
 * ========================= */
$kepala_madrasah = '';
$resKep = $koneksi->query("SELECT nama_guru FROM guru WHERE jabatan_guru = 'Kepala Sekolah' LIMIT 1");
if ($resKep && $rowKep = $resKep->fetch_assoc()) {
  $kepala_madrasah = $rowKep['nama_guru'] ?? '';
}

/* =========================
 * HELPER & PENGELOMPOKAN MAPEL
 * ========================= */
function safeInt($v) {
  if ($v === null || $v === '') return '';
  return (int) $v;
}

function mapLabelKelompok($label) {
  $l = strtolower($label);

  if (strpos($l, 'kelompok a') !== false || strpos($l, 'wajib') !== false) {
    return 'Mata Pelajaran Wajib';
  }

  if (strpos($l, 'kelompok b') !== false || strpos($l, 'pilihan') !== false) {
    return 'Mata Pelajaran Pilihan';
  }

  if (strpos($l, 'kelompok c') !== false || strpos($l, 'lokal') !== false) {
    return 'Muatan Lokal';
  }

  if (strpos($l, 'kelompok d') !== false || strpos($l, 'peminatan') !== false) {
    return 'Mata Pelajaran Peminatan';
  }

  // fallback
  return $label;
}


// Mapping rumpun mata pelajaran berbasis nama mapel
function mapRumpunMapel($namaMapel) {
  $key = strtolower(trim($namaMapel));

  // Contoh rumpun IPA
  $ipa = [
    'fisika',
    'kimia',
    'biologi',
    'astronomi',
  ];

  if (in_array($key, $ipa, true)) {
    return 'Ilmu Pengetahuan Alam ';
  }
  
  $pai = [
    'Al-Quran Hadist',
    'akidah akhlak',
    'fiqih',
    'sejarah kebudayaan islam',
  ];

  if (in_array($key, $pai, true)) {
    return 'Pendidikan Agama Islam ';
  }
  $ips = [
    'sosiologi',
    'ekonomi',
    'geografi',
    'sejarah',
  ];

  if (in_array($key, $ips, true)) {
    return 'Ilmu Pengetahuan Sosial ';
  }
  // Rumpun lain bisa ditambahkan di sini jika dibutuhkan

  return 'Mandiri';
}

// Kelompok mapel per-rumpun (presentasi saja, tidak disimpan DB)
$rumpunMapel = [];
foreach ($nilaiMapel as $n) {
  $rumpun = mapRumpunMapel($n['nama_mata_pelajaran'] ?? '');
  if (!isset($rumpunMapel[$rumpun])) {
    $rumpunMapel[$rumpun] = [];
  }
  $rumpunMapel[$rumpun][] = $n;
}

// Kelompok mapel dinamis sesuai kolom kelompok_mata_pelajaran di DB
$kelompokMapel = []; // contoh: ['Kelompok A (Wajib)' => [...], 'Kelompok B (Pilihan)' => [...]]
foreach ($nilaiMapel as $n) {
  $label = trim($n['kelompok_mata_pelajaran'] ?? ''); // <-- pakai nama kolom yang benar
  if ($label === '') {
    $label = 'Lain-lain';
  }
  if (!isset($kelompokMapel[$label])) {
    $kelompokMapel[$label] = [];
  }
  $kelompokMapel[$label][] = $n;
}

/* =========================
 * LOKASI LOGO
 * ========================= */
$logoFile = $sekolah['logo_sekolah'] ?: 'default.png';
$logoPath = '../../Lembaga/data_sekolah/uploads/' . $logoFile;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Cetak Rapor - <?= htmlspecialchars($dataSiswa['nama_siswa']); ?></title>
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: "Times New Roman", serif;
      font-size: 11px;
      margin: 0;
      padding: 0;
    }
    .page {
        width: 210mm;
        height: 297mm;
        padding: 10mm;
        position: relative;
        padding-bottom: 45mm;
    }
    .kop {
      text-align: center;
      border-bottom: 2px solid #000;
      padding-bottom: 6px;
      margin-bottom: 4px;
      position: relative;
      font-weight: bold;
    }
    .kop-logo {
      position: absolute;
      left: 0;
      top: 0;
    }
    .kop-logo img {
      width: 80px;
      height: 80px;
      object-fit: contain;
    }
    .kop-text { line-height: 1.3; }
    .kop-text .yayasan {
      font-size: 13px;
      font-weight: bold;
      text-transform: uppercase;
    }
    .kop-text .nama-sekolah {
      font-size: 16px;
      font-weight: bold;
      text-transform: uppercase;
    }
    .kop-text .akreditasi { font-size: 11px; }
    .kop-text .alamat     { font-size: 10px; }

    .judul-rapor {
      text-align: center;
      margin: 6px 0 8px;
      font-weight: bold;
      text-transform: uppercase;
    }

    .identitas {
      margin-bottom: 8px;
      font-size: 11px;
      font-weight: bold;
    }
    .identitas-table { width: 100%; border:none; }
    .identitas-table td { padding: 1px 0; border:none;}

    table {
      border-collapse: collapse;
      width: 100%;
    }
    th, td {
      border: 1px solid #000;
      padding: 2px 3px;
    }
    th { text-align: center; }

    .nilai-table th,
    .nilai-table td { font-size: 10px; }
    .nilai-table .mapel { text-align: left; }

    .bottom {
      margin-top: 8px;
      display: flex;
      gap: 8px;
    }
    .bottom .left  { flex: 0 0 60%; max-width: 60%; }
    .bottom .right { flex: 1; margin-left: 10px; }
    .small { font-size: 10px; }

    .catatan {
      border: 1px solid #000;
      min-height: 80px;
      padding: 4px;
      font-size: 10px;
      white-space: pre-wrap;
      word-break: break-all;
    }

    .ttd {
        position: absolute;
        bottom: 10mm;
        left: 10mm;
        right: 10mm;
    }
    .ttd-table {
      width: 100%;
      border: none;
    }
    .ttd-table td {
      border: none;
      text-align: center;
      padding: 2px 4px;
    }

    @page {
      size: A4;
      margin: 10mm 10mm 10mm 15mm;
    }
    @media print {
      body  { margin: 0; }
      .page { box-shadow: none; padding: 0; }
    }
  </style>
</head>
<!-- <body onload="window.print()"> -->
<body>
  <div class="page">

    <!-- KOP -->
    <header class="kop">
      <div class="kop-logo">
        <img src="<?= htmlspecialchars($logoPath); ?>" alt="Logo">
      </div>
      <div class="kop-text fw-bold">
        <div class="yayasan">YAYASAN PENDIDIKAN ISLAM "NURUL HUDA SEDATI"</div>
        <div class="nama-sekolah">MADRASAH ALIYAH NURUL HUDA SEDATI</div>
        <div class="akreditasi">TERAKREDITASI A</div>
        <div class="akreditasi">
          NSM: <?= htmlspecialchars($sekolah['nsm_sekolah'] ?? ''); ?>
          &nbsp; NPSN: <?= htmlspecialchars($sekolah['npsn_sekolah'] ?? ''); ?>
        </div>
        <div class="alamat">
          <?= htmlspecialchars($sekolah['alamat_sekolah'] ?? ''); ?>
          Telp. <?= htmlspecialchars($sekolah['no_telepon_sekolah'] ?? ''); ?>
        </div>
      </div>
    </header>

    <!-- JUDUL -->
    <div class="judul-rapor">
      DAFTAR NILAI HASIL BELAJAR TENGAH SEMESTER <?= htmlspecialchars($nama_semester); ?>
    </div>

    <!-- IDENTITAS -->
    <section class="identitas">
      <table class="identitas-table">
        <tr>
          <td style="width:20%;">Nama Peserta Didik</td>
          <td style="width:50%;">: <?= htmlspecialchars($dataSiswa['nama_siswa']); ?></td>
          <td style="width:80px;">Kelas</td>
          <td>: <?= htmlspecialchars($dataSiswa['nama_kelas'] ?? ''); ?></td>
        </tr>
        <tr>
          <td>No. Absen</td>
          <td>: <?= htmlspecialchars($dataSiswa['no_absen_siswa'] ?? ''); ?></td>
          <td>No. Induk</td>
          <td>: <?= htmlspecialchars($dataSiswa['no_induk_siswa'] ?? ''); ?></td>
        </tr>
      </table>
    </section>

<!-- NILAI AKADEMIK -->
<section class="nilai">
  <table class="nilai-table">
    <thead>
      <tr>
        <th rowspan="3" style="width:25px;">No</th>
        <th rowspan="3" style="width:220px;">Mata Pelajaran</th>
        <th colspan="16">FORMATIF</th>
        <th colspan="4">SUMATIF</th>
        <th rowspan="3" style="width:40px;">
          SUMATIF TENGAH<br>SEMESTER
        </th>
      </tr>
      <tr>
        <th colspan="4" style="font-size: 9px;">LINGKUP MATERI 1</th>
        <th colspan="4" style="font-size: 9px;">LINGKUP MATERI 2</th>
        <th colspan="4" style="font-size: 9px;">LINGKUP MATERI 3</th>
        <th colspan="4" style="font-size: 9px;">LINGKUP MATERI 4</th>
        <th colspan="4" style="font-size: 9px;">LINGKUP MATERI</th>
      </tr>
      <tr>
        <?php for ($i = 0; $i < 4; $i++): ?>
          <th>TP1</th>
          <th>TP2</th>
          <th>TP3</th>
          <th>TP4</th>
        <?php endfor; ?>
        <th>LM1</th>
        <th>LM2</th>
        <th>LM3</th>
        <th>LM4</th>
      </tr>
    </thead>
        <tbody>
          <?php
$no = 1;

$renderKelompok = function ($label, $rows) use (&$no) {
  if (!$rows) return;

  $labelTampil = mapLabelKelompok($label);

echo '<tr><td colspan="23" style="font-weight:bold;text-align:left;">'
   . htmlspecialchars($labelTampil)
   . '</td></tr>';


  // Pisahkan: tanpa rumpun (Mandiri) vs punya rumpun (IPA, dst)
  $tanpaRumpun = [];
  $byRumpun    = [];

  foreach ($rows as $r) {
    $rumpun = mapRumpunMapel($r['nama_mata_pelajaran'] ?? '');
    if ($rumpun === 'Mandiri') {
      $tanpaRumpun[] = $r;               // dianggap mapel biasa
    } else {
      if (!isset($byRumpun[$rumpun])) {
        $byRumpun[$rumpun] = [];
      }
      $byRumpun[$rumpun][] = $r;
    }
  }

  // Fungsi kecil untuk cetak 1 baris mapel
  $printRow = function($r) use (&$no) {
    echo '<tr>';
    echo '<td style="text-align:center;">' . $no++ . '</td>';
    echo '<td class="mapel">' . htmlspecialchars($r['nama_mata_pelajaran']) . '</td>';

    $cols = [
      // FORMATIF LM1–4 (TP1–TP4)
      'tp1_lm1','tp2_lm1','tp3_lm1','tp4_lm1',
      'tp1_lm2','tp2_lm2','tp3_lm2','tp4_lm2',
      'tp1_lm3','tp2_lm3','tp3_lm3','tp4_lm3',
      'tp1_lm4','tp2_lm4','tp3_lm4','tp4_lm4',
      // SUMATIF LM1–4
      'sumatif_lm1','sumatif_lm2','sumatif_lm3','sumatif_lm4',
    ];

    foreach ($cols as $c) {
      $v = safeInt($r[$c] ?? '');
      echo '<td style="text-align:center;">' . ($v === '' ? '' : $v) . '</td>';
    }

    $sts = safeInt($r['sumatif_tengah_semester'] ?? '');
    echo '<td style="text-align:center;">' . ($sts === '' ? '' : $sts) . '</td>';
    echo '</tr>';
  };

  // 1) Cetak dulu mapel tanpa rumpun (Mandiri) -> tercampur dengan mapel lain biasa
  foreach ($tanpaRumpun as $r) {
    $printRow($r);
  }

  // 2) Baru di bawahnya: rumpun khusus (IPA, dst)
  //    Atur prioritas kalau perlu
  $prioritas = [
    'Ilmu Pengetahuan Alam (IPA)',
    // nanti bisa tambah rumpun lain di sini
  ];

  $orderedRumpun = [];
  foreach ($prioritas as $rp) {
    if (isset($byRumpun[$rp])) {
      $orderedRumpun[$rp] = $byRumpun[$rp];
    }
  }
  foreach ($byRumpun as $rp => $list) {
    if (!isset($orderedRumpun[$rp])) {
      $orderedRumpun[$rp] = $list;
    }
  }

  foreach ($orderedRumpun as $rumpunLabel => $list) {
    if (!$list) continue;

    // Sub‑header rumpun (tanpa kata Mandiri karena Mandiri sudah di atas)
    echo '<tr><td colspan="23" style="text-align:left; padding-left:12px;">'
       . htmlspecialchars($rumpunLabel)
       . '</td></tr>';

    foreach ($list as $r) {
      $printRow($r);
    }
  }
};
$ordered = [];

foreach ($kelompokMapel as $label => $rows) {
  $prio = 99;
  $lower = strtolower($label);

  if (strpos($lower, 'kelompok a') !== false || strpos($lower, 'wajib') !== false) {
    $prio = 1;
  } elseif (strpos($lower, 'kelompok b') !== false || strpos($lower, 'pilihan') !== false) {
    $prio = 2;
  } elseif (strpos($lower, 'kelompok c') !== false || strpos($lower, 'lokal') !== false) {
    $prio = 3;
  } elseif (strpos($lower, 'kelompok d') !== false || strpos($lower, 'peminatan') !== false) {
    $prio = 4;
  }

  $ordered[$prio . '_' . $label] = [$label, $rows];
}


ksort($ordered);
foreach ($ordered as $item) {
  [$label, $rows] = $item;
  $renderKelompok($label, $rows);
}
?>
        </tbody>
      </table>
    </section>

    <!-- BAGIAN BAWAH: EKSKUL, ABSENSI, CATATAN -->
    <section class="bottom">
      <div class="left">
        <h4 style="margin:8px 0 4px; font-size:11px;">Absensi</h4>
        <table class="small">
          <thead>
            <tr>
              <th style="width:25px;">No</th>
              <th>Faktor Tidak Hadir</th>
              <th style="width:60px;">Jumlah</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="text-align:center;">1</td>
              <td>Sakit</td>
              <td style="text-align:center;"><?= (int)($absensi['sakit'] ?? 0); ?></td>
            </tr>
            <tr>
              <td style="text-align:center;">2</td>
              <td>Izin</td>
              <td style="text-align:center;"><?= (int)($absensi['izin'] ?? 0); ?></td>
            </tr>
            <tr>
              <td style="text-align:center;">3</td>
              <td>Tanpa Keterangan</td>
              <td style="text-align:center;"><?= (int)($absensi['alpha'] ?? 0); ?></td>
            </tr>
          </tbody>
        </table>

        <h4 style="margin:8px 0; font-size:11px;">Catatan Wali Kelas</h4>
        <div class="catatan">
          <?= nl2br(htmlspecialchars($catatan_walas)); ?>
        </div>
      </div>

      <div class="right">
        <h4 style="margin:8px 0 4px; font-size:11px;">Ekstrakurikuler</h4>
        <table class="small">
          <thead>
            <tr>
              <th style="width:25px;">No</th>
              <th>Ekstrakulikuler</th>
              <th style="width:60px;">Nilai</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($nilaiEkstra)): ?>
              <tr><td colspan="3" style="text-align:center;">-</td></tr>
            <?php else: $i = 1; foreach ($nilaiEkstra as $e): ?>
              <tr>
                <td style="text-align:center;"><?= $i++; ?></td>
                <td><?= htmlspecialchars($e['nama_ekstrakurikuler']); ?></td>
                <td style="text-align:center;"><?= htmlspecialchars($e['nilai_ekstrakurikuler']); ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- TANDA TANGAN -->
    <footer class="ttd">
      <table class="ttd-table" >
        <tr>            
          <td colspan="3" style="text-align:left;">Mengetahui,</td>
        </tr>
        <tr>
          <td style="text-align:left; vertical-align:top;">
            Orang Tua / Wali Peserta Didik
          </td>
          <td style="text-align:center; vertical-align:top; width:55%;">
            Kepala Madrasah
          </td>
          <td style="text-align:left; vertical-align:top;">
            <?= htmlspecialchars($tempat_cetak); ?>,
            <?= htmlspecialchars($tanggal_cetak_indo); ?><br>
            Wali Kelas <?= htmlspecialchars($dataSiswa['nama_kelas'] ?? ''); ?>
          </td>
        </tr>
        <tr>
          <td style="height:45px;"></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td style="text-align:left;">(........................................)</td>
          <td>
            <u><?= htmlspecialchars($kepala_madrasah !== '' ? $kepala_madrasah : '........................................'); ?></u>
          </td>
          <td style="text-align:left;">
            <u><?= htmlspecialchars($dataSiswa['wali_kelas'] ?? ''); ?></u>
          </td>
        </tr>
      </table>
    </footer>

  </div>
</body>
</html>