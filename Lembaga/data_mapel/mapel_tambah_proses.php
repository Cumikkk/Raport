<?php
include '../../koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nama_mapel = trim($_POST['nama_mapel']);
  $jenis_mapel = $_POST['jenis_mapel'];

  if (empty($nama_mapel) || empty($jenis_mapel)) {
    echo "<script>alert('Harap isi semua field!'); window.history.back();</script>";
    exit;
  }

  // ====== 1️⃣ Bikin singkatan dari nama mapel ======
  // Ambil kata pertama, kedua, dst → ambil 4 huruf pertama dari tiap kata
  $kata = explode(" ", strtoupper($nama_mapel));
  $singkatan = "";

  foreach ($kata as $k) {
    // ambil maksimal 4 huruf per kata
    $singkatan .= substr($k, 0, 3);
  }

  // Biar lebih rapi, hapus karakter non-huruf
  $singkatan = preg_replace("/[^A-Z]/", "", $singkatan);

  // Biar gak terlalu panjang, ambil maksimal 6 huruf aja
  $singkatan = substr($singkatan, 0, 6);

  // ====== 2️⃣ Ambil nomor urut terakhir dari tabel ======
  $query_last = "SELECT kode_mata_pelajaran FROM mata_pelajaran ORDER BY id_mata_pelajaran DESC LIMIT 1";
  $result_last = mysqli_query($koneksi, $query_last);

  $last_number = 0;
  if ($result_last && mysqli_num_rows($result_last) > 0) {
    $row = mysqli_fetch_assoc($result_last);
    // Ambil angka terakhir dari kode, misal BING_005 → 5
    $parts = explode("_", $row['kode_mata_pelajaran']);
    if (isset($parts[1])) {
      $last_number = intval($parts[1]);
    }
  }

  // Tambah 1 untuk kode baru
  $new_number = str_pad($last_number + 1, 3, "0", STR_PAD_LEFT);

  // ====== 3️⃣ Bentuk kode mapel ======
  $kode_mapel = $singkatan . "_" . $new_number;

  // ====== 4️⃣ Simpan ke database ======
  $query = "INSERT INTO mata_pelajaran (nama_mata_pelajaran, kode_mata_pelajaran, kelompok_mata_pelajaran) 
            VALUES ('$nama_mapel', '$kode_mapel', '$jenis_mapel')";

  $result = mysqli_query($koneksi, $query);

  if ($result) {
    echo "<script>alert('Data berhasil disimpan dengan kode $kode_mapel'); window.location.href='data_mapel.php';</script>";
  } else {
    echo "<script>alert('Gagal menyimpan data: " . mysqli_error($koneksi) . "'); window.history.back();</script>";
  }
}
?>
