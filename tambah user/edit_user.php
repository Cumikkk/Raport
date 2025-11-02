<?php
include '../includes/header.php';
include '../includes/navbar.php';
require_once '../koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    exit("<script>alert('ID tidak valid'); window.location='data_user.php';</script>");
}

$stmt = mysqli_prepare($koneksi, "SELECT id_user, id_guru, role_user, username FROM user WHERE id_user=?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$user = mysqli_stmt_get_result($stmt)->fetch_assoc();
if (!$user) {
    exit("<script>alert('User tidak ditemukan'); window.location='data_user.php';</script>");
}

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
        width: min(760px, 100%);
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

    .hint {
        font-size: 12px;
        color: var(--muted);
        margin-top: -2px;
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

    .btn-brand {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
        font-weight: 700;
    }

    .btn-brand:hover {
        background: var(--brand-600);
        border-color: var(--brand-600);
    }

    @media (max-width:900px) {
        .form-wrapper {
            margin-left: 0;
        }
    }
</style>

<div class="form-wrapper">
    <div class="form-container">
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="form-title h4 mb-0">Edit User</h2>
            <a href="data_user.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>
        <hr class="mt-3 mb-3">

        <form action="proses_edit_user.php" method="POST" autocomplete="off" novalidate>
            <input type="hidden" name="id_user" value="<?= (int)$user['id_user'] ?>">

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="role">Role</label>
                    <select id="role" name="role" class="form-select" required>
                        <option value="Admin" <?= $user['role_user'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="Guru" <?= $user['role_user'] === 'Guru'  ? 'selected' : ''; ?>>Guru</option>
                    </select>
                </div>

                <div class="col-12 col-md-8">
                    <label for="id_guru">Pilih Guru</label>
                    <select id="id_guru" name="id_guru" class="form-select">
                        <option value="">-- Tanpa Guru --</option>
                        <?php while ($g = mysqli_fetch_assoc($guruRes)): ?>
                            <option value="<?= (int)$g['id_guru'] ?>" <?= ((int)$user['id_guru'] === (int)$g['id_guru']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($g['nama_guru']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" maxlength="50" class="form-control" required
                        value="<?= htmlspecialchars($user['username']) ?>">
                </div>

                <div class="col-12 col-md-6">
                    <label for="password_user">Password (opsional)</label>
                    <input type="password" id="password_user" name="password_user" class="form-control">
                </div>
            </div>

            <div class="d-grid d-sm-flex gap-2 mt-4">
                <button type="submit" class="btn btn-brand px-4"><i class="bi bi-save"></i> Simpan Perubahan</button>
                <a href="data_user.php" class="btn btn-outline-secondary px-4">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>