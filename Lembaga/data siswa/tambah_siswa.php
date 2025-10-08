<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Siswa</title>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/../dashboard.css">

    <style>
        .content {
            margin-left: 260px;
            padding: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            width: 50%;
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            margin-top: 15px;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-simpan {
            background-color: #28a745;
            color: white;
        }

        .btn-simpan:hover {
            background-color: #218838;
        }

        .btn-batal {
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            margin-left: 10px;
        }

        .btn-batal:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-center">
            <h2>Tambah Data Siswa</h2>
        </div>
    </header>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <nav class="menu">
            <a href="data_siswa.php" class="home-link">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Data Siswa</span>
            </a>
        </nav>
    </aside>

    <main class="content">
        <form method="post">
            <label>Absen</label>
            <input type="number" name="absen" required>

            <label>NIS</label>
            <input type="text" name="nis" required>

            <label>Nama</label>
            <input type="text" name="nama" required>

            <label>Wali Kelas</label>
            <input type="text" name="wali_kelas" required>

            <button type="submit" name="simpan" class="btn-simpan">
                <i class="fas fa-save"></i> Simpan
            </button>
            <a href="data_siswa.php" class="btn-batal">
                <i class="fas fa-times"></i> Batal
            </a>
        </form>
    </main>
</body>
</html>
