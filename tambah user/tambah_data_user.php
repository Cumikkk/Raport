<?php
include '../includes/header.php';
include '../includes/navbar.php';
require_once '../koneksi.php';

// Ambil daftar guru
$guruRes = mysqli_query($koneksi, "SELECT id_guru, nama_guru FROM guru ORDER BY nama_guru ASC");
?>
<style>
  :root {
    --brand: #0a4db3;
    --brand-600: #083f93;
    --ink: #0f172a;
    --text: #111827;
    --muted: #475569;
    --ring: #cbd5e1;
    --card: #ffffff;
    --card-radius: 14px;
  }

  body {
    background: #f7f8fb;
    color: var(--text);
  }

  .form-wrapper {
    margin-left: 260px;
    min-height: 100vh;
    display: grid;
    place-items: center;
    padding: clamp(24px, 4vw, 48px) 12px;
  }

  .form-container {
    background: var(--card);
    width: min(720px, 100%);
    border: 1px solid #e8eef6;
    border-radius: var(--card-radius);
    box-shadow: 0 6px 20px rgba(0, 0, 0, .06);
    padding: clamp(18px, 2.6vw, 28px);
  }

  .form-title {
    color: var(--ink);
  }

  label {
    font-weight: 600;
    margin: 10px 0 6px;
    color: var(--ink);
  }

  .form-control,
  .form-select {
    border: 1px solid var(--ring);
    border-radius: 10px;
    padding: 10px 12px;
    color: var(--ink);
  }

  .form-control:focus,
  .form-select:focus {
    box-shadow: 0 0 0 3px rgba(10, 77, 179, .15);
    border-color: var(--brand);
  }

  /* Tombol & hover */
  .btn-brand {
    background: #0a4db3 !important;
    border-color: #0a4db3 !important;
    color: #fff !important;
    font-weight: 700;
  }

  .btn-brand:hover {
    background: #083f93 !important;
    border-color: #083f93 !important;
  }

  .btn-outline-secondary {
    border-color: #6c757d !important;
    color: #6c757d !important;
  }

  .btn-outline-secondary:hover {
    background: #e9ecef !important;
    color: #333 !important;
  }

  @media (max-width:900px) {
    .form-wrapper {
      margin-left: 0;
    }
  }
</style>

<div class="form-wrapper">
  <div class="form-container">
    <h2 class="form-title h4 mb-0">Tambah User</h2>
    <hr class="mt-3 mb-3">

    <form action="proses_tambah_user.php" method="POST" autocomplete="off">
      <div class="row g-3">
        <div class="col-12">
          <label for="role">Role</label>
          <select id="role" name="role" class="form-select" required>
            <option value="" disabled selected>-- Pilih Role --</option>
            <option value="Admin">Admin</option>
            <option value="Guru">Guru</option>
          </select>
        </div>

        <div class="col-12">
          <label for="id_guru">Pilih Guru</label>
          <select id="id_guru" name="id_guru" class="form-select" required>
            <option value="" disabled selected>-- Pilih Guru --</option>
            <?php while ($g = mysqli_fetch_assoc($guruRes)): ?>
              <option value="<?= (int)$g['id_guru'] ?>">
                <?= htmlspecialchars($g['nama_guru']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="col-12">
          <label for="username">Username</label>
          <input
            type="text"
            id="username"
            name="username"
            maxlength="50"
            class="form-control"
            required>
        </div>

        <div class="col-12">
          <label for="password_user">Password</label>
          <input
            type="password"
            id="password_user"
            name="password_user"
            class="form-control"
            required>
        </div>
      </div>

      <div class="d-grid d-sm-flex gap-2 mt-4">
        <button type="submit"
          class="btn btn-brand px-4 d-inline-flex align-items-center gap-2">
          <i class="bi bi-check2-circle"></i> Simpan
        </button>
        <a href="data_user.php"
          class="btn btn-outline-secondary px-4 d-inline-flex align-items-center gap-2">
          <i class="bi bi-x-lg"></i> Batal
        </a>
      </div>
    </form>
  </div>
</div>

<?php include '../includes/footer.php'; ?>