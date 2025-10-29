data_sekolah.php (old)
<?php
include '../../includes/header.php';
?>

<body>

  <?php
  include '../../includes/navbar.php';
  ?>
  <!-- Tambahan CSS untuk tampilan form -->
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background-color: #f5f6fa;
    }

    .content {
      padding: 30px;
    }

    h4 {
      font-weight: 700;
      color: #004080;
      margin-bottom: 25px;
    }

    .card {
      border-radius: 14px !important;
      padding: 25px !important;
      background: #fff;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
    }

    .form-label {
      font-weight: 600;
      display: block;
      margin-bottom: 6px;
    }

    .form-control {
      width: 100%;
      padding: 12px 14px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 8px;
      transition: all 0.2s ease-in-out;
    }

    .form-control:focus {
      border-color: #007bff;
      box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
      outline: none;
    }

    textarea.form-control {
      resize: vertical;
    }

    .form-check-input {
      width: 18px;
      height: 18px;
      margin-right: 6px;
    }

    .btn {
      font-size: 17px;
      padding: 12px 18px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.2s;
      border: none;
      border-radius: 8px;
      width: 100%;
    }



    .btn-success {
      background-color: #28a745;
      color: white;
    }

    .btn:hover {
      opacity: 0.9;
      transform: translateY(-1px);
    }

    .logo-preview {
      width: 150px !important;
      height: 150px !important;
      object-fit: cover;
      border-radius: 10px;
      border: 2px solid #ccc;
    }

    .row {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }

    .col-md-8 {
      flex: 2;
      min-width: 300px;
    }

    .col-md-4 {
      flex: 1;
      min-width: 250px;
    }
  </style>
  </head>

  <body>

    <!-- Checkbox hack -->
    <input type="checkbox" id="menu-toggle" />


    <!-- MAIN CONTENT -->
    <div class="content">
      <h4>Data Sekolah</h4>

      <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'success'): ?>
          <div class="alert alert-success">✅ Data sekolah berhasil diperbarui.</div>
        <?php else: ?>
          <div class="alert alert-danger">❌ Gagal memperbarui data sekolah.</div>
        <?php endif; ?>
      <?php endif; ?>

      <div class="row">
        <!-- Kolom kiri -->
        <div class="col-md-8">
          <div class="card">
            <h5 class="mb-3">Edit Data Sekolah</h5>

            <form action="update_data_sekolah.php" method="POST">
              <div class="mb-3">
                <label class="form-label">Nama Sekolah</label>
                <input type="text" name="nama_sekolah" class="form-control"
                  value="<?= htmlspecialchars($data['nama_sekolah'] ?? '') ?>" required>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <label class="form-label">NSM</label>
                  <input type="text" name="npsn" class="form-control"
                    value="<?= htmlspecialchars($data['npsn'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">NPSN</label>
                  <input type="text" name="nss" class="form-control"
                    value="<?= htmlspecialchars($data['nss'] ?? '') ?>">
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <label class="form-label">Alamat</label>
                  <input type="text" name="kode_pos" class="form-control"
                    value="<?= htmlspecialchars($data['kode_pos'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Telepon</label>
                  <input type="text" name="telepon" class="form-control"
                    value="<?= htmlspecialchars($data['telepon'] ?? '') ?>">
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Kecamatan</label>
                <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($data['alamat'] ?? '') ?></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">Kabupaten atau Kota</label>
                <input type="email" name="email" class="form-control"
                  value="<?= htmlspecialchars($data['email'] ?? '') ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Provinsi</label>
                <input type="text" name="website" class="form-control"
                  value="<?= htmlspecialchars($data['website'] ?? '') ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Kepala Sekolah</label>
                <input type="text" name="kepala_sekolah" class="form-control"
                  value="<?= htmlspecialchars($data['kepala_sekolah'] ?? '') ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">NIP Kepala Sekolah</label>
                <input type="text" name="nip_kepala_sekolah" class="form-control"
                  value="<?= htmlspecialchars($data['nip_kepala_sekolah'] ?? '') ?>">
              </div>

              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="confirm" required>
                <label class="form-check-label" for="confirm">
                  Saya yakin akan mengubah data tersebut
                </label>
              </div>

              <button type="submit" class="btn btn-success">Simpan</button>
            </form>
          </div>
        </div>

        <!-- Kolom kanan -->
        <div class="col-md-4">
          <div class="card">
            <h5 class="mb-3">Edit Logo Sekolah</h5>

            <div class="text-center mb-3">
              <img src="uploads/<?= htmlspecialchars($data['logo'] ?? 'default.png') ?>"
                class="logo-preview mb-2"
                alt="Logo Sekolah">
            </div>

            <form action="update_logo_sekolah.php" method="POST" enctype="multipart/form-data">
              <div class="mb-3">
                <label class="form-label">Ganti Logo Sekolah</label>
                <input type="file" name="logo" class="form-control" accept="image/*" required>
              </div>
              <button type="submit" class="btn btn-warning" style="color:white">Update</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <?php include '../../includes/footer.php'; ?>