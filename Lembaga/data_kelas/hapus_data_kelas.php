<?php
// pages/kelas/hapus_data_kelas.php
require_once '../../koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_set_charset($koneksi, 'utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_ajax(): bool
{
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}
function json_out(bool $ok, string $msg, string $type = 'success', int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => $ok, 'msg' => $msg, 'type' => $type], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: data_kelas.php');
    exit;
}

if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    if (is_ajax()) json_out(false, 'Sesi tidak valid (CSRF). Silakan refresh halaman.', 'danger', 403);
    header('Location: data_kelas.php?err=' . urlencode('Sesi tidak valid (CSRF).'));
    exit;
}

// ambil id tunggal / bulk
$ids = [];
if (!empty($_POST['id'])) {
    $ids[] = (int)$_POST['id'];
}
if (!empty($_POST['ids']) && is_array($_POST['ids'])) {
    foreach ($_POST['ids'] as $raw) $ids[] = (int)$raw;
}

$ids = array_values(array_unique(array_filter($ids, fn($v) => $v > 0)));

if (count($ids) === 0) {
    if (is_ajax()) json_out(true, 'Tidak ada data yang dipilih.', 'success');
    header('Location: data_kelas.php?msg=' . urlencode('Tidak ada data yang dipilih.'));
    exit;
}

try {
    // cek FK: masih ada siswa di salah satu kelas
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $sqlCheck = "SELECT COUNT(*) AS cnt FROM siswa WHERE id_kelas IN ($placeholders)";
    $stmtC = mysqli_prepare($koneksi, $sqlCheck);
    mysqli_stmt_bind_param($stmtC, $types, ...$ids);
    mysqli_stmt_execute($stmtC);
    $resC = mysqli_stmt_get_result($stmtC);
    $rowC = mysqli_fetch_assoc($resC);
    $cnt = (int)($rowC['cnt'] ?? 0);
    mysqli_stmt_close($stmtC);

    if ($cnt > 0) {
        $m = 'Tidak bisa menghapus karena kelas masih dipakai (ada siswa terkait). Kosongkan dulu siswa pada kelas tersebut.';
        if (is_ajax()) json_out(false, $m, 'warning', 409);
        header('Location: data_kelas.php?err=' . urlencode($m));
        exit;
    }

    $sqlDel = "DELETE FROM kelas WHERE id_kelas IN ($placeholders)";
    $stmt = mysqli_prepare($koneksi, $sqlDel);
    mysqli_stmt_bind_param($stmt, $types, ...$ids);
    mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    $msg = (count($ids) > 1)
        ? "Berhasil menghapus {$affected} data kelas."
        : "Data kelas berhasil dihapus.";

    if (is_ajax()) json_out(true, $msg, 'success');
    header('Location: data_kelas.php?msg=' . urlencode($msg));
    exit;
} catch (Throwable $e) {
    if (is_ajax()) json_out(false, 'Terjadi kesalahan saat menghapus data.', 'danger', 500);
    header('Location: data_kelas.php?err=' . urlencode('Terjadi kesalahan saat menghapus data.'));
    exit;
}
