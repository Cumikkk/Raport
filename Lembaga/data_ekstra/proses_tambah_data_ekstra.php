<?php
// pages/ekstra/proses_tambah_data_ekstra.php
require_once '../../koneksi.php';

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function respond_json(array $data)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        respond_json([
            'success' => false,
            'message' => 'Metode tidak diizinkan.'
        ]);
    } else {
        header('Location: data_ekstra.php?err=' . urlencode('Metode tidak diizinkan.'));
        exit;
    }
}

$nama_ekstra = isset($_POST['nama_ekstra']) ? trim($_POST['nama_ekstra']) : '';
$errors = [];

if ($nama_ekstra === '') {
    $errors[] = 'Nama ekstrakurikuler wajib diisi.';
}

// CEK DUPLIKAT
if ($nama_ekstra !== '') {
    $sqlCheck = "SELECT COUNT(*) AS cnt FROM ekstrakurikuler WHERE nama_ekstrakurikuler = ?";
    $stmtCheck = $koneksi->prepare($sqlCheck);
    $stmtCheck->bind_param('s', $nama_ekstra);
    $stmtCheck->execute();
    $rowCheck = $stmtCheck->get_result()->fetch_assoc();
    $cnt = (int)($rowCheck['cnt'] ?? 0);

    if ($cnt > 0) {
        $errors[] = 'Nama ekstrakurikuler sudah ada.';
    }
}

if (!empty($errors)) {
    if ($isAjax) {
        respond_json([
            'success' => false,
            'errors'  => $errors
        ]);
    } else {
        header('Location: data_ekstra.php?add_err=' . urlencode(implode(' ', $errors)));
        exit;
    }
}

// INSERT
$sql = "INSERT INTO ekstrakurikuler (nama_ekstrakurikuler) VALUES (?)";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param('s', $nama_ekstra);

if ($stmt->execute()) {
    if ($isAjax) {
        respond_json([
            'success' => true,
            'message' => 'Data ekstrakurikuler berhasil ditambahkan.'
        ]);
    } else {
        header('Location: data_ekstra.php?msg=' . urlencode('Data ekstrakurikuler berhasil ditambahkan.'));
        exit;
    }
}

// gagal eksekusi
if ($isAjax) {
    respond_json([
        'success' => false,
        'message' => 'Gagal menyimpan data.'
    ]);
} else {
    header('Location: data_ekstra.php?add_err=' . urlencode('Gagal menyimpan data.'));
    exit;
}
