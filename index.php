<?php
session_start();
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query cek user
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['username'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        echo "<script>alert('Username atau password salah!'); window.location.href='index.php';</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>

  <!-- Bootstrap CSS Lokal -->
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<body class="d-flex align-items-center justify-content-center vh-100 position-relative" style="background-image: url('assets/img/background/bg-login-spread.jpg'); background-size: cover; background-position: center;">
<div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0, 0, 0, 0.15);"></div>
  <div class="container position-relative" style="z-index: 1;">
    <div class="row justify-content-center">
      <div class="col-lg-8 col-md-10">
        <div class="row bg-white shadow rounded-4 overflow-hidden">
          <!-- Logo dan Judul  -->
            
          <!-- Bagian kiri -->
          <div class="col-md-6 d-flex flex-column justify-content-center p-4">

            <div class="text-center mb-4">
              
            </div>
            <img src="assets/img/logo/7.png" alt="Logo" class="img-fluid" style="max-width: 150px;">
            <h4 class="text-center fw-semibold mb-3">Sign In</h4>
            <p class="text-center mb-4 opacity-75" style="font-size: 14px;">Manage your school’s academic data easily and seamlessly.</p>
            <form action="" method="POST">
              <div class="input-group mb-3">
                <span class="input-group-text">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="opacity-75 bi bi-envelope-at" viewBox="0 0 16 16">
                    <path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2zm3.708 6.208L1 11.105V5.383zM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2z" />
                    <path d="M14.247 14.269c1.01 0 1.587-.857 1.587-2.025v-.21C15.834 10.43 14.64 9 12.52 9h-.035C10.42 9 9 10.36 9 12.432v.214C9 14.82 10.438 16 12.358 16h.044c.594 0 1.018-.074 1.237-.175v-.73c-.245.11-.673.18-1.18.18h-.044c-1.334 0-2.571-.788-2.571-2.655v-.157c0-1.657 1.058-2.724 2.64-2.724h.04c1.535 0 2.484 1.05 2.484 2.326v.118c0 .975-.324 1.39-.639 1.39-.232 0-.41-.148-.41-.42v-2.19h-.906v.569h-.03c-.084-.298-.368-.63-.954-.63-.778 0-1.259.555-1.259 1.4v.528c0 .892.49 1.434 1.26 1.434.471 0 .896-.227 1.014-.643h.043c.118.42.617.648 1.12.648m-2.453-1.588v-.227c0-.546.227-.791.573-.791.297 0 .572.192.572.708v.367c0 .573-.253.744-.564.744-.354 0-.581-.215-.581-.8Z" />
                  </svg>
                </span>
                <input type="email" name="username" class="form-control border-start-0" placeholder="Email Or Username" aria-label="Email">
              </div>
              <div class="input-group mb-3">
                <span class="input-group-text">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="opacity-75 bi bi-key" viewBox="0 0 16 16">
                    <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8m4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5" />
                    <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0" />
                  </svg>
                </span>
                <input type="password" name="password" class="form-control border-start-0" placeholder="Password" aria-label="Password">
              </div>

              <div class="d-flex justify-content-end mb-3">
                <a href="#" class="text-decoration-none small">Forgot password?</a>
              </div>

              <div class="d-grid mb-4">
                <button type="submit" class="btn btn-primary border-start-0 fw-semibold">Sign In</button>
              </div>
            </form>

          </div>

          <!-- Bagian kanan -->
          <div class="col-md-6 bg-white d-flex align-items-center justify-content-center">
            <img src="assets/img/image/a.png" class="img-fluid" alt="illustration" style="max-width: 400px; object-fit: cover; border-top-right-radius: 0.5rem; border-bottom-right-radius: 0.5rem;">
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS Lokal -->
  <script src="assets/js/bootstrap.bundle.min.js"></script>

  <!-- Bootstrap Icons Lokal (opsional, bisa dihapus kalau nggak dipakai) -->
  <link rel="stylesheet" href="assets/css/bootstrap-icons.css">
</body>

</html>