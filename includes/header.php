<?php
// Tentukan base URL agar path ke CSS/JS benar
$baseURL = '/RAPORT'; // ubah sesuai folder project kamu
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Project E-Rapor</title>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- CSS Lokal -->
  <link rel="stylesheet" href="<?= $baseURL ?>/assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= $baseURL ?>/assets/css/dashboard.css">
  <link rel="stylesheet" href="<?= $baseURL ?>/assets/css/datakelas.css">
  <link rel="stylesheet" href="<?= $baseURL ?>/assets/css/navbar.css">
</head>
<body>
  <div class="wrapper">
    <?php include __DIR__ . '/navbar.php'; ?>
    <div class="main-content">
