<?php
session_start();
include 'koneksi.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = mysqli_real_escape_string($koneksi, $_POST['username']);
  $password = mysqli_real_escape_string($koneksi, $_POST['password']);

  $query = "SELECT * FROM user WHERE username = '$username' LIMIT 1";
  $result = mysqli_query($koneksi, $query);

  if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    if ($password === $user['password_user']) {
      $_SESSION['id_user']   = $user['id_user'];
      $_SESSION['username']  = $user['username'];
      $_SESSION['role_user'] = $user['role_user'];
      // simpan relasi ke guru (jika ada) untuk filter data per-guru
      $_SESSION['id_guru']   = isset($user['id_guru']) ? (int)$user['id_guru'] : 0;

      $role = $user['role_user'];
      $success = "✅ Login berhasil sebagai <b>$role</b>! Mengarahkan ke dashboard...";
      echo "
        <script>
          setTimeout(function() {
            window.location.href = 'includes/dashboard.php';
          }, 2000);
        </script>
      ";
    } else {
      $error = "❌ Password salah!";
    }
  } else {
    $error = "❌ Username tidak ditemukan!";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>

  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/bootstrap-icons.css">

  <style>
    body {
      background-image: url('assets/img/background/bg-login-spread.jpg');
      background-size: cover;
      background-position: center;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      padding: 15px;
    }

    .overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.15);
      z-index: 0;
    }

    .login-container {
      position: relative;
      z-index: 1;
      background: #fff;
      border-radius: 1rem;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
      width: 100%;
      max-width: 900px;
      display: flex;
      flex-wrap: wrap;
    }

    .login-left,
    .login-right {
      flex: 1 1 50%;
      padding: 2rem;
    }

    .login-left img.logo {
      display: block;
      margin: 0 auto 1rem;
      max-width: 130px;
    }

    .login-right img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-top: 1px solid #eee;
    }

    .input-group-text {
      background-color: #f8f9fa;
      border-right: 0;
    }

    .form-control {
      border-left: 0;
    }

    .btn-primary {
      background-color: #0d6efd;
      border: none;
      transition: background-color 0.2s;
    }

    .btn-primary:hover {
      background-color: #0b5ed7;
    }

    /* ===================== RESPONSIVE ===================== */
    @media (max-width: 992px) {
      .login-container {
        max-width: 700px;
      }
    }

    @media (max-width: 768px) {
      .login-container {
        flex-direction: column;
        max-width: 420px;
        text-align: center;
      }

      .login-left, .login-right {
        flex: 1 1 100%;
        padding: 1.5rem;
      }

      .login-right img {
        max-height: 250px;
        border-top: 1px solid #ccc;
      }

      .login-left form {
        max-width: 100%;
      }

      .login-left img.logo {
        max-width: 100px;
      }
    }

    @media (max-width: 480px) {
      .login-container {
        max-width: 100%;
        border-radius: 10px;
      }

      .login-left {
        padding: 1.2rem;
      }

      h4 {
        font-size: 1.2rem;
      }

      .btn-primary {
        font-size: 0.95rem;
      }
    }
  </style>
</head>

<body>
  <div class="overlay"></div>

  <div class="login-container">
    <!-- BAGIAN KIRI -->
    <div class="login-left d-flex flex-column justify-content-center">
      <img src="assets/img/logo/7.png" alt="Logo" class="logo">
      <h4 class="fw-semibold text-center mb-3">Sign In</h4>
      <p class="text-center text-muted mb-4" style="font-size: 14px;">
        Manage your school’s academic data easily and seamlessly.
      </p>

      <?php if (!empty($success)): ?>
        <div class="alert alert-success text-center py-2"><?= $success ?></div>
      <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger text-center py-2"><?= $error ?></div>
      <?php endif; ?>

      <form action="" method="POST" class="mx-auto" style="max-width: 340px; width: 100%;">
        <div class="input-group mb-3">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>

        <div class="input-group mb-3">
          <span class="input-group-text"><i class="bi bi-key"></i></span>
          <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>

        <div class="d-grid mb-4">
          <button type="submit" class="btn btn-primary fw-semibold py-2">Sign In</button>
        </div>
      </form>
    </div>

    <!-- BAGIAN KANAN -->
    <div class="login-right d-flex align-items-center justify-content-center">
      <img src="assets/img/image/a.png" alt="Illustration">
    </div>
  </div>

  <script src="assets/js/bootstrap.bundle.min.js"></script>
