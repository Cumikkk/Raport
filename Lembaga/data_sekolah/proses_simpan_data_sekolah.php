<?php
// pages/sekolah/proses_simpan_data_sekolah.php
require_once '../../koneksi.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$koneksi->set_charset('utf8mb4');

function redirectBack(bool $ok, string $msg = ''): void
{
    // ✅ samakan dengan template dk-alert: success / error
    $status = $ok ? 'success' : 'error';
    $loc = 'data_sekolah.php?status=' . $status;

    if ($msg !== '') {
        $loc .= '&msg=' . rawurlencode($msg);
    }

    header('Location: ' . $loc);
    exit;
}

try {
    // ----- Ambil input POST -----
    $id   = isset($_POST['id_sekolah']) ? (int)$_POST['id_sekolah'] : 0;
    $old  = isset($_POST['old_logo']) ? trim($_POST['old_logo']) : '';

    $nama       = isset($_POST['nama_sekolah']) ? trim($_POST['nama_sekolah']) : '';
    $nsm        = isset($_POST['nsm_sekolah']) ? trim($_POST['nsm_sekolah']) : '';
    $npsn       = isset($_POST['npsn_sekolah']) ? trim($_POST['npsn_sekolah']) : '';
    $alamat     = isset($_POST['alamat_sekolah']) ? trim($_POST['alamat_sekolah']) : '';
    $telp       = isset($_POST['no_telepon_sekolah']) ? trim($_POST['no_telepon_sekolah']) : '';
    $kec        = isset($_POST['kecamatan_sekolah']) ? trim($_POST['kecamatan_sekolah']) : '';
    $kabkota    = isset($_POST['kabupaten_atau_kota_sekolah']) ? trim($_POST['kabupaten_atau_kota_sekolah']) : '';
    $prov       = isset($_POST['provinsi_sekolah']) ? trim($_POST['provinsi_sekolah']) : '';

    if ($nama === '') {
        redirectBack(false, 'Nama sekolah wajib diisi.');
    }
    if ($nsm === '' || $npsn === '' || $alamat === '' || $telp === '' || $kec === '' || $kabkota === '' || $prov === '') {
        redirectBack(false, 'Mohon lengkapi semua field yang wajib diisi.');
    }

    // ----- Upload logo (opsional) -----
    $logoFinal = $old;
    if (isset($_FILES['logo_sekolah']) && $_FILES['logo_sekolah']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['logo_sekolah'];

        if ($f['error'] !== UPLOAD_ERR_OK) {
            redirectBack(false, 'Gagal mengunggah file.');
        }

        $max = 10 * 1024 * 1024; // 10MB
        if ($f['size'] > $max) {
            redirectBack(false, 'Ukuran logo melebihi 10MB.');
        }

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
            redirectBack(false, 'Format logo harus jpg/jpeg/png/webp.');
        }

        // Buat nama file aman & unik
        $safeBase = preg_replace('~[^a-zA-Z0-9_-]+~', '-', pathinfo($f['name'], PATHINFO_FILENAME));
        $newName  = 'logo-' . ($safeBase ?: 'sekolah') . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . '.' . $ext;

        $dstDir = __DIR__ . '/uploads';
        if (!is_dir($dstDir)) {
            @mkdir($dstDir, 0755, true);
        }

        $dst = $dstDir . '/' . $newName;
        if (!move_uploaded_file($f['tmp_name'], $dst)) {
            redirectBack(false, 'Gagal memindahkan file upload.');
        }

        $logoFinal = $newName;
    }

    // ----- Insert atau Update -----
    if ($id > 0) {
        $stmt = $koneksi->prepare("UPDATE sekolah SET logo_sekolah=?, nama_sekolah=?, nsm_sekolah=?, npsn_sekolah=?, alamat_sekolah=?, no_telepon_sekolah=?, kecamatan_sekolah=?, kabupaten_atau_kota_sekolah=?, provinsi_sekolah=? WHERE id_sekolah=?");
        $stmt->bind_param(
            'sssssssssi',
            $logoFinal,
            $nama,
            $nsm,
            $npsn,
            $alamat,
            $telp,
            $kec,
            $kabkota,
            $prov,
            $id
        );
        $stmt->execute();
    } else {
        $stmt = $koneksi->prepare("INSERT INTO sekolah (logo_sekolah, nama_sekolah, nsm_sekolah, npsn_sekolah, alamat_sekolah, no_telepon_sekolah, kecamatan_sekolah, kabupaten_atau_kota_sekolah, provinsi_sekolah) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param(
            'sssssssss',
            $logoFinal,
            $nama,
            $nsm,
            $npsn,
            $alamat,
            $telp,
            $kec,
            $kabkota,
            $prov
        );
        $stmt->execute();
    }

    redirectBack(true, 'Data sekolah berhasil disimpan.');
} catch (Throwable $e) {
    redirectBack(false, $e->getMessage());
}
