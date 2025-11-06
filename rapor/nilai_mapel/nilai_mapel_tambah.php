<?php
// ==== BACKEND (diproses lebih dulu agar header() tidak error) ====
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
require_once __DIR__ . '/../../koneksi.php';
mysqli_set_charset($koneksi, 'utf8mb4');

function generateKode($nama, $koneksi) {
    // Ambil huruf pertama tiap kata, fallback 3 huruf awal
    $clean = preg_replace('/[^A-Za-z0-9\s]/', '', $nama);
    $parts = preg_split('/\s+/', trim($clean));
    $abbr  = '';
    foreach ($parts as $p) {
        if ($p !== '') $abbr .= strtoupper($p[0]);
    }
    if (strlen($abbr) < 3) {
        $abbr = strtoupper(substr(str_replace(' ', '', $clean), 0, 3));
    }
    $abbr = substr($abbr, 0, 10); // batasi

    // Pastikan unik di kode_mata_pelajaran
    $base = $abbr;
    $i = 1;
    $stmt = $koneksi->prepare("SELECT COUNT(*) AS jml FROM mata_pelajaran WHERE kode_mata_pelajaran = ?");
    while (true) {
        $cek = $abbr;
        $stmt->bind_param('s', $cek);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ((int)$res['jml'] === 0) break;
        $i++;
        $abbr = substr($base, 0, 10 - strlen((string)$i)) . $i; // jaga panjang
    }
    $stmt->close();
    return $abbr;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_mapel  = trim($_POST['nama_mapel'] ?? '');
    $jenis_mapel = trim($_POST['jenis_mapel'] ?? ''); // disimpan ke kelompok_mata_pelajaran
    $nilai_dummy = trim($_POST['nilai'] ?? '');       // ikut UI, tidak disimpan

    if ($nama_mapel === '' || $jenis_mapel === '') {
        echo "<script>alert('Nama Mapel dan Jenis wajib diisi!');history.back();</script>";
        exit;
    }

    try {
        // Buat kode_mata_pelajaran otomatis dari nama
        $kode_mapel = generateKode($nama_mapel, $koneksi);

        // Simpan ke skema sesuai screenshot
        $sql = "INSERT INTO mata_pelajaran (nama_mata_pelajaran, kode_mata_pelajaran, kelompok_mata_pelajaran)
                VALUES (?, ?, ?)";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param('sss', $nama_mapel, $kode_mapel, $jenis_mapel);
        $stmt->execute();
        $stmt->close();

        // Redirect ke halaman daftar mapel
        header('Location: mapel.php');
        exit;
    } catch (Throwable $e) {
        $msg = addslashes($e->getMessage());
        echo "<script>alert('Gagal menyimpan data: {$msg}');history.back();</script>";
        exit;
    }
}

// ==== VIEW (tampilan formmu tetap) ====
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="dk-page" style="margin-top: 50px;">
    <div class="dk-main">
        <div class="dk-content-box">
            <div class="container py-4">
                <h4 class="fw-bold mb-4">Tambah Data Nilai Mapel</h4>

                <!-- Form tetap sama; hanya ditambah method="post" dan name="" -->
                <form id="formEkstra" method="post" action="">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Mapel</label>
                        <select class="form-select" id="namaSiswa" name="nama_mapel" required>
                            <option value="">-- Pilih Mapel --</option>
                            <option value="Bahasa Inggris">Bahasa Inggris</option>
                            <option value="Bahasa dan Sastra Indonesia">Bahasa dan Sastra Indonesia</option>
                            <option value="Matematika">Matematika</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Jenis</label>
                        <select class="form-select" id="namaEkstra" name="jenis_mapel" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Wajib">Wajib</option>
                            <option value="Paket">Paket</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nilai</label>
                        <select class="form-select" id="nilai" name="nilai" required>
                            <option value="">-- Pilih Nilai --</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Simpan
                        </button>
                        <a href="mapel.php" class="btn btn-danger">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php
include '../../includes/footer.php';
?>
