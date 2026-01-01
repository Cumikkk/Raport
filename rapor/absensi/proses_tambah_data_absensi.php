<?php
// pages/absensi/proses_tambah_data_absensi.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);

require_once __DIR__ . '/../../koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Deteksi request AJAX (biar bisa alert di modal seperti data_guru)
$isAjax = (
  !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
);

function json_out(bool $ok, string $msg, string $type = 'success')
{
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    'ok'   => $ok,
    'msg'  => $msg,
    'type' => $type,
  ]);
  exit;
}

function back_with(string $key, string $val)
{
  header('Location: data_absensi.php?' . $key . '=' . urlencode($val));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  if ($isAjax) json_out(false, 'Metode tidak diizinkan.', 'danger');
  back_with('err', 'Metode tidak diizinkan.');
}

// CSRF (wajib karena sekarang submit via AJAX)
$csrf = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
  if ($isAjax) json_out(false, 'Token tidak valid. Silakan coba lagi.', 'danger');
  back_with('err', 'Token tidak valid. Silakan coba lagi.');
}

// Ambil input & validasi
$id_siswa = isset($_POST['id_siswa']) ? (int)$_POST['id_siswa'] : 0;
$sakit    = isset($_POST['sakit']) ? (int)$_POST['sakit'] : 0;
$izin     = isset($_POST['izin']) ? (int)$_POST['izin'] : 0;
$alpha    = isset($_POST['alpha']) ? (int)$_POST['alpha'] : 0;

if ($id_siswa <= 0) {
  if ($isAjax) json_out(false, 'Silakan pilih Nama Siswa.', 'warning');
  back_with('err', 'Silakan pilih Nama Siswa.');
}

if ($sakit < 0 || $izin < 0 || $alpha < 0) {
  if ($isAjax) json_out(false, 'Input Sakit/Izin/Alpha tidak boleh bernilai negatif.', 'warning');
  back_with('err', 'Input Sakit/Izin/Alpha tidak boleh bernilai negatif.');
}

// Ambil data siswa (sekalian ambil NIS buat pesan)
$stmtS = $koneksi->prepare("SELECT nama_siswa, no_induk_siswa FROM siswa WHERE id_siswa = ? LIMIT 1");
$stmtS->bind_param('i', $id_siswa);
$stmtS->execute();
$siswaRow = $stmtS->get_result()->fetch_assoc();
$stmtS->close();

if (!$siswaRow) {
  if ($isAjax) json_out(false, 'Data siswa tidak ditemukan.', 'danger');
  back_with('err', 'Data siswa tidak ditemukan.');
}

$namaSiswa = (string)($siswaRow['nama_siswa'] ?? '');
$nisSiswa  = (string)($siswaRow['no_induk_siswa'] ?? '');

// Ambil id_semester (pakai yang pertama/aktif sesuai versi kamu)
$res = $koneksi->query("SELECT id_semester FROM semester ORDER BY id_semester ASC LIMIT 1");
if (!$res || !$res->num_rows) {
  if ($isAjax) json_out(false, 'Data semester belum ada. Silakan isi data semester terlebih dahulu.', 'danger');
  back_with('err', 'Data semester belum ada. Silakan isi data semester terlebih dahulu.');
}
$id_semester = (int)$res->fetch_assoc()['id_semester'];

// ==========================
// CEK DUPLIKAT (berdasar semester + siswa/NIS)
// ==========================
$stmtDup = $koneksi->prepare("
  SELECT 1
  FROM absensi
  WHERE id_semester = ? AND id_siswa = ?
  LIMIT 1
");
$stmtDup->bind_param('ii', $id_semester, $id_siswa);
$stmtDup->execute();
$dup = $stmtDup->get_result()->fetch_row();
$stmtDup->close();

if ($dup) {
  $label = $nisSiswa !== '' ? "NIS {$nisSiswa}" : "siswa ini";
  $msg = "Data absensi untuk {$label} sudah ada. Data duplikat ditolak.";
  if ($isAjax) json_out(false, $msg, 'warning');
  back_with('err', $msg);
}

// Insert ke absensi
$stmtIns = $koneksi->prepare("
  INSERT INTO absensi (id_semester, id_siswa, sakit, izin, alpha)
  VALUES (?, ?, ?, ?, ?)
");
$stmtIns->bind_param('iiiii', $id_semester, $id_siswa, $sakit, $izin, $alpha);
$stmtIns->execute();
$stmtIns->close();

$okMsg = 'Data absensi berhasil ditambahkan.';
if ($isAjax) json_out(true, $okMsg, 'success');

header('Location: data_absensi.php?msg=add_success');
exit;
