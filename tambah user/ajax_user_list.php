<?php
require_once '../koneksi.php';
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$like   = "%{$search}%";

$sql = "
  SELECT 
    u.id_user, 
    u.username, 
    u.password_user,
    u.role_user, 
    u.id_guru, 
    g.nama_guru
  FROM user u
  LEFT JOIN guru g ON g.id_guru = u.id_guru
  WHERE u.username LIKE ?
     OR COALESCE(g.nama_guru,'') LIKE ?
  ORDER BY u.id_user DESC
";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// inject style untuk badge & highlight row jika dimuat via ajax
echo '<style>
.role-badge{
  background:#ecf2ff;
  color:#0a2a88;
  border:1px solid #d6e2ff;
  font-weight:700;
}
.highlight-row{
  background-color:#d4edda !important;
}
.password-text{
  font-family:monospace;
}
</style>';

if (mysqli_num_rows($result) === 0): ?>
    <tr>
        <td colspan="7">Tidak ada data yang cocok.</td>
    </tr>
    <?php
else:
    $no = 1;
    $rowClass = ($search !== '') ? 'highlight-row' : '';
    while ($row = mysqli_fetch_assoc($result)): ?>
        <tr class="<?= $rowClass; ?>">
            <td class="text-center" data-label="Pilih">
                <input type="checkbox" class="row-check" value="<?= (int)$row['id_user'] ?>">
            </td>
            <td data-label="No"><?= $no++; ?></td>
            <td data-label="Nama (Guru)"><?= htmlspecialchars($row['nama_guru'] ?? '-') ?></td>
            <td data-label="Username"><?= htmlspecialchars($row['username']) ?></td>
            <td data-label="Password">
                <?php
                $pwd = $row['password_user'] ?? '';
                if ($pwd === '') {
                    echo '-';
                } else {
                ?>
                    <div class="d-inline-flex align-items-center gap-1 password-cell">
                        <span class="password-text" data-visible="0">••••••</span>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary toggle-password"
                            data-password="<?= htmlspecialchars($pwd, ENT_QUOTES, 'UTF-8') ?>"
                            title="Lihat / sembunyikan password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                <?php
                }
                ?>
            </td>
            <td data-label="Role">
                <span class="badge role-badge"><?= htmlspecialchars($row['role_user']) ?></span>
            </td>
            <td data-label="Aksi">
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="edit_user.php?id=<?= (int)$row['id_user'] ?>" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                    <a href="hapus_user.php?id=<?= (int)$row['id_user'] ?>" class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1"
                        onclick="return confirm('Yakin ingin menghapus user ini?');">
                        <i class="bi bi-trash"></i> Hapus
                    </a>
                </div>
            </td>
        </tr>
<?php endwhile;
endif;
