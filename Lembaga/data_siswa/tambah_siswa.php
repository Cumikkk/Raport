<?php
include '../../includes/header.php';
?>

<body>

  <!-- Style tambahan halaman -->
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background-color: #f5f6fa;
    }

    .content {
      padding: 40px;
    }

    h2 {
      color: #004080;
      font-weight: 700;
      margin-bottom: 25px;
    }

    /* === CARD FORM === */
    .form-card {
      background: #fff;
      padding: 30px 35px;
      border-radius: 16px;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
      max-width: 700px;
      margin: 0 auto;
    }

    .form-card label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #333;
    }

    .form-card input {
      width: 100%;
      padding: 12px 14px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
      transition: 0.2s;
    }

    .form-card input:focus {
      border-color: #004080;
      box-shadow: 0 0 4px rgba(0, 64, 128, 0.3);
      outline: none;
    }

    .form-buttons {
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      margin-top: 10px;
    }

    .btn-simpan, .btn-batal {
      padding: 10px 18px;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      font-size: 15px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      text-decoration: none;
      transition: 0.2s;
    }

    .btn-simpan {
      background-color: green;
      color: white;
    }

    .btn-batal {
      background-color: #dc3545;
      color: white;
    }

    .btn-simpan:hover {
      background-color: #003366;
      transform: translateY(-1px);
    }

    .btn-batal:hover {
      background-color: #c82333;
      transform: translateY(-1px);
    }

    @media (max-width: 768px) {
      .content {
        padding: 20px;
      }

      .form-card {
        padding: 20px;
      }

      .form-buttons {
        flex-direction: column;
      }

      .btn-simpan, .btn-batal {
        width: 100%;
        justify-content: center;
      }
    }
  </style>
</head>
<body>

<?php
include '../../includes/navbar.php';
?>
  

  <!-- MAIN CONTENT -->
  <main class="content">
    <h2 style="text-align: center;">Tambah Data Siswa</h2>

    <div class="form-card">
      <form method="post">
        <label>Absen</label>
        <input type="number" name="absen" required>

        <label>NIS</label>
        <input type="text" name="nis" required>

        <label>Nama</label>
        <input type="text" name="nama" required>

        <label>Wali Kelas</label>
        <input type="text" name="wali_kelas" required>

        <div class="form-buttons">
          <button type="submit" name="simpan" class="btn-simpan">
            <i class="fas fa-save"></i> Simpan
          </button>
          <a href="data_siswa.php" class="btn-batal">
            <i class="fas fa-times"></i> Batal
          </a>
        </div>
      </form>
    </div>
  </main>

<?php
include '../../includes/footer.php';
?>
