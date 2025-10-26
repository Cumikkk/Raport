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

  .form-wrapper {
    margin-left: 260px;
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
    color: rgb(6, 6, 6);
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
    <h2>Tambah User</h2>

    <!-- arahkan ke file proses -->
    <form action="proses_tambah_user.php" method="POST">
      <label for="role">Role:</label>
<select id="role" name="role" required>
  <option value="admin">Admin</option>
  <option value="guru" selected>Guru</option>
</select>

      <label for="nama_lengkap">Nama Lengkap:</label>
      <input type="text" id="nama_lengkap" name="nama_lengkap" required>

      <label for="email_user">Email:</label>
      <input type="text" id="email_user" name="email_user">

      <label for="no_telepon_user">No Telepon:</label>
      <input type="text" id="no_telepon_user" name="no_telepon_user">

      <label for="username">Username:</label>
      <input type="text" id="username" name="username" required>

      <label for="password_user">Password:</label>
      <input type="password" id="password_user" name="password_user" required>

      <button type="submit">Simpan</button>
    </form>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
