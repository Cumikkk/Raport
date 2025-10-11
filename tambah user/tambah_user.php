<?php
include '../includes/header.php';
?>

<body>

<?php
include '../includes/navbar.php';
?>

<style>
  body {
    background-color: #f7f8fb;
  }

  /* Bungkus seluruh form di area konten utama */
  .form-wrapper {
    margin-left: 260px; /* menyesuaikan sidebar */
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .form-container {
    background: #ffffff;
    padding: 30px 40px;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    max-width: 400px;
    width: 100%;
  }

  h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #1e3a8a;
  }

  label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #111827;
  }

  input[type="text"],
  input[type="password"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    margin-bottom: 16px;
    font-size: 15px;
  }

  button[type="submit"] {
    width: 100%;
    background-color: green;
    color: white;
    border: none;
    padding: 10px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
  }

  button[type="submit"]:hover {
    background-color: #1d4ed8;
  }

  /* Responsif: form tetap di tengah */
  @media (max-width: 900px) {
    .form-wrapper {
      margin-left: 0;
      height: auto;
      padding: 100px 0;
    }
    .form-container {
      margin: 0 20px;
    }
  }
</style>

<div class="form-wrapper">
  <div class="form-container">
    <h2>Form Tambah User</h2>

    <form action="proses_tambah_user.php" method="POST">
      <label for="nama_lengkap">Nama Lengkap:</label>
      <input type="text" id="nama_lengkap" name="nama_lengkap" required>

      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>

      <button type="submit">Simpan</button>
    </form>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
