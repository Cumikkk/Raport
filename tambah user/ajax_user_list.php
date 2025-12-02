<?php
require_once '../koneksi.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$like   = "%{$search}%";

// Pagination params
$allowedPer = [10, 20, 50, 100];
$perPage    = isset($_GET['per']) ? (int)$_GET['per'] : 10;
if (!in_array($perPage, $allowedPer, true)) {
    $perPage = 10;
}
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Hitung total data untuk query ini
$countSql = "
  SELECT COUNT(*) AS total
  FROM user u
  LEFT JOIN guru g ON g.id_guru = u.id_guru
  WHERE u.username LIKE ?
     OR COALESCE(g.nama_guru,'') LIKE ?
";
$stmtCount = mysqli_prepare($koneksi, $countSql);
mysqli_stmt_bind_param($stmtCount, 'ss', $like, $like);
mysqli_stmt_execute($stmtCount);
$resCount  = mysqli_stmt_get_result($stmtCount);
$rowCount  = mysqli_fetch_assoc($resCount);
$totalRows = (int)($rowCount['total'] ?? 0);

$totalPages = max(1, (int)ceil($totalRows / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

// Ambil data per halaman
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
  LIMIT ? OFFSET ?
";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'ssii', $like, $like, $perPage, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!-- baris meta untuk JS pagination (disembunyikan) -->
<tr class="meta-row" style="display:none"
    data-total="<?= htmlspecialchars((string)$totalRows, ENT_QUOTES, 'UTF-8') ?>"
    data-page="<?= htmlspecialchars((string)$page, ENT_QUOTES, 'UTF-8') ?>"
    data-per="<?= htmlspecialchars((string)$perPage, ENT_QUOTES, 'UTF-8') ?>">
</tr>

<?php if (mysqli_num_rows($result) === 0): ?>
    <tr>
        <td colspan="7">Tidak ada data yang cocok.</td>
    </tr>
    <?php
else:
    $no = $offset + 1;
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
                    <!-- Sama seperti di data_user.php: pakai modal edit -->
                    <button type="button"
                        class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-edit-user"
                        data-id="<?= (int)$row['id_user'] ?>"
                        data-role="<?= htmlspecialchars($row['role_user'], ENT_QUOTES, 'UTF-8') ?>"
                        data-id-guru="<?= (int)($row['id_guru'] ?? 0) ?>"
                        data-username="<?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-pencil-square"></i> Edit
                    </button>

                    <!-- Hapus pakai modal konfirmasi custom (bukan confirm bawaan browser) -->
                    <button type="button"
                        class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1 px-2 py-1 btn-delete-single"
                        data-href="hapus_data_user.php?id=<?= (int)$row['id_user'] ?>"
                        data-label="<?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>
            </td>
        </tr>
<?php
    endwhile;
endif;
