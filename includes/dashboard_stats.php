<?php
// Raport/includes/dashboard_stats.php

require_once __DIR__ . '/../koneksi.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

date_default_timezone_set('Asia/Jakarta');

function getCount(mysqli $koneksi, string $sql, string $types = '', array $params = []): int {
  $stmt = mysqli_prepare($koneksi, $sql);
  if (!$stmt) return 0;

  if ($types !== '' && !empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
  }

  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $row = $res ? mysqli_fetch_assoc($res) : null;
  mysqli_stmt_close($stmt);

  return (int)($row['c'] ?? 0);
}

try {
  $siswa = getCount($koneksi, "SELECT COUNT(*) AS c FROM siswa");
  $guru  = getCount($koneksi, "SELECT COUNT(*) AS c FROM guru");
  $kelas = getCount($koneksi, "SELECT COUNT(*) AS c FROM kelas");

  // Admin berdasarkan role_user = 'Admin'
  $admin = getCount($koneksi, "SELECT COUNT(*) AS c FROM user WHERE role_user = ?", "s", ["Admin"]);

  echo json_encode([
    'ok' => true,
    'siswa' => $siswa,
    'guru'  => $guru,
    'admin' => $admin,
    'kelas' => $kelas,
    'updated_at' => date('d/m/Y H:i:s') . ' WIB'
  ]);
} catch (Throwable $e) {
  echo json_encode([
    'ok' => false,
    'msg' => 'Server error: ' . $e->getMessage()
  ]);
}
