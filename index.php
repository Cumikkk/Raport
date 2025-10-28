<?php
session_start();
include 'koneksi.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = mysqli_real_escape_string($koneksi, $_POST['username']);
  $password = mysqli_real_escape_string($koneksi, $_POST['password']);

  // Cek user berdasarkan username
  $query = "SELECT * FROM user WHERE username = '$username' LIMIT 1";
  $result = mysqli_query($koneksi, $query);

  if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);

    // Karena password di DB belum di-hash, kita bandingkan langsung
    if ($password === $user['password_user']) {

      // Set session
      $_SESSION['id_user'] = $user['id_user'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role_user'] = $user['role_user'];

      // Arahkan berdasarkan role
      if ($user['role_user'] === 'Admin') {
        $success = "✅ Login berhasil sebagai <b>Admin</b>! Mengarahkan ke dashboard...";
        echo "
          <script>
            setTimeout(function() {
              window.location.href = 'includes/dashboard.php';
            }, 2000);
          </script>
        ";
      } elseif ($user['role_user'] === 'Guru') {
        $success = "✅ Login berhasil sebagai <b>Guru</b>! Mengarahkan ke halaman kelas...";
        echo "
          <script>
            setTimeout(function() {
              window.location.href = 'includes/dashboard.php';
            }, 2000);
          </script>
        ";
      } else {
        $error = "❌ Role tidak dikenali!";
      }
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
</head>

<body class="d-flex align-items-center justify-content-center vh-100 position-relative"
  style="background-image: url('assets/img/background/bg-login-spread.jpg'); background-size: cover; background-position: center;">

  <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0, 0, 0, 0.15);"></div>

  <div class="container position-relative" style="z-index: 1;">
    <div class="row justify-content-center">
      <div class="col-lg-8 col-md-10">
        <div class="row bg-white shadow rounded-4 overflow-hidden">

          <!-- Bagian kiri -->
          <div class="col-md-6 d-flex flex-column justify-content-center p-4">
            <img src="assets/img/logo/7.png" alt="Logo" class="img-fluid mx-auto d-block mb-3" style="max-width: 150px;">
            <h4 class="text-center fw-semibold mb-3">Sign In</h4>
            <p class="text-center mb-4 opacity-75" style="font-size: 14px;">Manage your school’s academic data easily and seamlessly.</p>

            <!-- Notifikasi sukses atau error -->
            <?php if (!empty($success)): ?>
              <div class="alert alert-success text-center py-2"><?= $success ?></div>
            <?php elseif (!empty($error)): ?>
              <div class="alert alert-danger text-center py-2"><?= $error ?></div>
            <?php endif; ?>

            <form action="" method="POST">
              <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" name="username" class="form-control border-start-0" placeholder="Username" required>
              </div>

              <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-key"></i></span>
                <input type="password" name="password" class="form-control border-start-0" placeholder="Password" required>
              </div>

              <div class="d-grid mb-4">
                <button type="submit" class="btn btn-primary fw-semibold">Sign In</button>
              </div>
            </form>
          </div>

          <!-- Bagian kanan -->
          <div class="col-md-6 bg-white d-flex align-items-center justify-content-center">
            <img src="assets/img/image/a.png" class="img-fluid" alt="illustration" style="max-width: 400px; object-fit: cover;">
          </div>

        </div>
      </div>
    </div>
  </div>

  <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>