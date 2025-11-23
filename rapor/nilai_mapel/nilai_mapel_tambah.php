<?php
// ==== BACKEND (diproses lebih dulu agar header() tidak error) ====
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
require_once __DIR__ . '/../../koneksi.php';
mysqli_set_charset($koneksi, 'utf8mb4');

/**
 * Generate kode_mata_pelajaran otomatis dari nama mapel
 * Contoh: "Bahasa Inggris" → "BI" (plus angka jika sudah dipakai)
 */
function generateKode(string $nama, mysqli $koneksi): string
{
    // Bersihkan karakter aneh, ambil huruf pertama tiap kata
    $clean = preg_replace('/[^A-Za-z0-9\s]/', '', $nama);
    $parts = preg_split('/\s+/', trim($clean));
    $abbr  = '';

    foreach ($parts as $p) {
        if ($p !== '') {
            $abbr .= strtoupper($p[0]);
        }
    }

    // Kalau terlalu pendek, pakai 3 huruf pertama nama
    if (strlen($abbr) < 3) {
        $abbr = strtoupper(substr(str_replace(' ', '', $clean), 0, 3));
    }

    // Batasi panjang maksimal 10 karakter
    $abbr = substr($abbr, 0, 10);

    // Pastikan unik di kolom kode_mata_pelajaran
    $base = $abbr;
    $i = 1;

    $stmt = $koneksi->prepare("SELECT COUNT(*) AS jml FROM mata_pelajaran WHERE kode_mata_pelajaran = ?");
    while (true) {
        $cek = $abbr;
        $stmt->bind_param('s', $cek);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ((int)$res['jml'] === 0) {
            break; // belum dipakai → aman
        }
        $i++;
        // tambahkan angka di belakang, tetap jaga panjang 10
        $abbr = substr($base, 0, 10 - strlen((string)$i)) . $i;
    }
    $stmt->close();

    return $abbr;
}

// ==== HANYA IZINKAN METHOD POST ====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // akses langsung -> kembalikan ke daftar mapel
    header('Location: mapel.php');
    exit;
}

// Ambil data dari form modal di mapel.php
$nama_mapel  = trim($_POST['nama_mapel'] ?? '');
$jenis_mapel = trim($_POST['jenis_mapel'] ?? ''); // disimpan ke kelompok_mata_pelajaran
$nilai_dummy = trim($_POST['nilai'] ?? '');       // hanya untuk tampilan, tidak disimpan

// Validasi sederhana
if ($nama_mapel === '' || $jenis_mapel === '') {
    echo "<script>alert('Nama Mapel dan Jenis wajib diisi!');history.back();</script>";
    exit;
}

try {
    // Buat kode_mata_pelajaran otomatis
    $kode_mapel = generateKode($nama_mapel, $koneksi);

    // Simpan ke tabel mata_pelajaran (sesuai skema: nama, kode, kelompok)
    $sql = "INSERT INTO mata_pelajaran (nama_mata_pelajaran, kode_mata_pelajaran, kelompok_mata_pelajaran)
            VALUES (?, ?, ?)";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('sss', $nama_mapel, $kode_mapel, $jenis_mapel);
    $stmt->execute();
    $stmt->close();

    // === PENTING: redirect dengan msg=add_success supaya alert di mapel.php muncul ===
    header('Location: mapel.php?msg=add_success');
    exit;

} catch (Throwable $e) {
    $msg = addslashes($e->getMessage());
    echo "<script>alert('Gagal menyimpan data: {$msg}');history.back();</script>";
    exit;
}
