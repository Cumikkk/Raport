-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 31 Okt 2025 pada 08.18
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rapor`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi`
--

CREATE TABLE `absensi` (
  `id_absensi` int(10) UNSIGNED NOT NULL,
  `id_semester` int(10) UNSIGNED NOT NULL,
  `id_siswa` int(10) UNSIGNED NOT NULL,
  `sakit` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `izin` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `alpha` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cetak_rapor`
--

CREATE TABLE `cetak_rapor` (
  `id_cetak_rapor` int(10) UNSIGNED NOT NULL,
  `id_guru` int(10) UNSIGNED DEFAULT NULL,
  `id_siswa` int(10) UNSIGNED DEFAULT NULL,
  `id_pengaturan_cetak_rapor` int(10) UNSIGNED DEFAULT NULL,
  `id_sekolah` int(10) UNSIGNED DEFAULT NULL,
  `catatan_wali_kelas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ekstrakurikuler`
--

CREATE TABLE `ekstrakurikuler` (
  `id_ekstrakurikuler` int(10) UNSIGNED NOT NULL,
  `nama_ekstrakurikuler` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `guru`
--

CREATE TABLE `guru` (
  `id_guru` int(10) UNSIGNED NOT NULL,
  `nama_guru` varchar(150) NOT NULL,
  `jabatan_guru` enum('Kepala Sekolah','Guru') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `guru`
--

INSERT INTO `guru` (`id_guru`, `nama_guru`, `jabatan_guru`) VALUES
(1, 'Tegar', 'Guru'),
(2, 'Fahrul', 'Guru');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelas`
--

CREATE TABLE `kelas` (
  `id_kelas` int(10) UNSIGNED NOT NULL,
  `id_guru` int(10) UNSIGNED DEFAULT NULL,
  `tingkat_kelas` enum('X','XI','XII') NOT NULL,
  `nama_kelas` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kurikulum`
--

CREATE TABLE `kurikulum` (
  `id_kurikulum` int(10) UNSIGNED NOT NULL,
  `id_mata_pelajaran` int(10) UNSIGNED NOT NULL,
  `id_kelas` int(10) UNSIGNED NOT NULL,
  `nilai_kurikulum` enum('0','1') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `mata_pelajaran`
--

CREATE TABLE `mata_pelajaran` (
  `id_mata_pelajaran` int(10) UNSIGNED NOT NULL,
  `nama_mata_pelajaran` varchar(150) NOT NULL,
  `kode_mata_pelajaran` varchar(50) DEFAULT NULL,
  `kelompok_mata_pelajaran` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `nilai_ekstrakurikuler`
--

CREATE TABLE `nilai_ekstrakurikuler` (
  `id_nilai_ekstrakurikuler` int(10) UNSIGNED NOT NULL,
  `id_semester` int(10) UNSIGNED NOT NULL,
  `id_siswa` int(10) UNSIGNED NOT NULL,
  `id_ekstrakurikuler` int(10) UNSIGNED NOT NULL,
  `nilai_ekstrakurikuler` enum('A','B','C','D') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `nilai_mata_pelajaran`
--

CREATE TABLE `nilai_mata_pelajaran` (
  `id_nilai_mata_pelajaran` int(10) UNSIGNED NOT NULL,
  `id_semester` int(10) UNSIGNED NOT NULL,
  `id_siswa` int(10) UNSIGNED NOT NULL,
  `id_mata_pelajaran` int(10) UNSIGNED NOT NULL,
  `tp1_lm1` int(11) DEFAULT NULL,
  `tp2_lm1` int(11) DEFAULT NULL,
  `tp3_lm1` int(11) DEFAULT NULL,
  `tp4_lm1` int(11) DEFAULT NULL,
  `sumatif_lm1` int(11) DEFAULT NULL,
  `tp1_lm2` int(11) DEFAULT NULL,
  `tp2_lm2` int(11) DEFAULT NULL,
  `tp3_lm2` int(11) DEFAULT NULL,
  `tp4_lm2` int(11) DEFAULT NULL,
  `sumatif_lm2` int(11) DEFAULT NULL,
  `tp1_lm3` int(11) DEFAULT NULL,
  `tp2_lm3` int(11) DEFAULT NULL,
  `tp3_lm3` int(11) DEFAULT NULL,
  `tp4_lm3` int(11) DEFAULT NULL,
  `sumatif_lm3` int(11) DEFAULT NULL,
  `tp1_lm4` int(11) DEFAULT NULL,
  `tp2_lm4` int(11) DEFAULT NULL,
  `tp3_lm4` int(11) DEFAULT NULL,
  `tp4_lm4` int(11) DEFAULT NULL,
  `sumatif_lm4` int(11) DEFAULT NULL,
  `sumatif_tengah_semester` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan_cetak_rapor`
--

CREATE TABLE `pengaturan_cetak_rapor` (
  `id_pengaturan_cetak_rapor` int(10) UNSIGNED NOT NULL,
  `tempat_cetak` varchar(50) DEFAULT NULL,
  `tanggal_cetak` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sekolah`
--

CREATE TABLE `sekolah` (
  `id_sekolah` int(10) UNSIGNED NOT NULL,
  `logo_sekolah` varchar(255) DEFAULT NULL,
  `nama_sekolah` varchar(150) NOT NULL,
  `nsm_sekolah` varchar(50) DEFAULT NULL,
  `npsn_sekolah` varchar(50) DEFAULT NULL,
  `alamat_sekolah` text DEFAULT NULL,
  `no_telepon_sekolah` varchar(20) DEFAULT NULL,
  `kecamatan_sekolah` varchar(50) DEFAULT NULL,
  `kabupaten_atau_kota_sekolah` varchar(50) DEFAULT NULL,
  `provinsi_sekolah` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sekolah`
--

INSERT INTO `sekolah` (`id_sekolah`, `logo_sekolah`, `nama_sekolah`, `nsm_sekolah`, `npsn_sekolah`, `alamat_sekolah`, `no_telepon_sekolah`, `kecamatan_sekolah`, `kabupaten_atau_kota_sekolah`, `provinsi_sekolah`) VALUES
(4, 'logo-channels4_profile-20251031081804-5b06bb.jpg', 'SD Negeri Kedondong 02', '123', '456', 'Kedondong RT 02 RW 01', '789', 'Tulangan', 'Sidoarjo', 'Jawa Timur');

-- --------------------------------------------------------

--
-- Struktur dari tabel `semester`
--

CREATE TABLE `semester` (
  `id_semester` int(10) UNSIGNED NOT NULL,
  `nama_semester` enum('Ganjil','Genap') NOT NULL,
  `tahun_ajaran` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int(10) UNSIGNED NOT NULL,
  `id_kelas` int(10) UNSIGNED DEFAULT NULL,
  `no_induk_siswa` varchar(50) DEFAULT NULL,
  `no_absen_siswa` varchar(50) DEFAULT NULL,
  `nama_siswa` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id_user` int(10) UNSIGNED NOT NULL,
  `id_guru` int(10) UNSIGNED DEFAULT NULL,
  `role_user` enum('Admin','Guru') NOT NULL,
  `username` varchar(20) NOT NULL,
  `password_user` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id_user`, `id_guru`, `role_user`, `username`, `password_user`) VALUES
(1, 1, 'Guru', 'Tegar', '123'),
(2, 2, 'Admin', 'Cumikkk.', '456');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id_absensi`),
  ADD KEY `idx_absensi_id_semester` (`id_semester`),
  ADD KEY `idx_absensi_id_siswa` (`id_siswa`);

--
-- Indeks untuk tabel `cetak_rapor`
--
ALTER TABLE `cetak_rapor`
  ADD PRIMARY KEY (`id_cetak_rapor`),
  ADD KEY `idx_cr_id_guru` (`id_guru`),
  ADD KEY `idx_cr_id_siswa` (`id_siswa`),
  ADD KEY `idx_cr_id_pengaturan` (`id_pengaturan_cetak_rapor`),
  ADD KEY `idx_cr_id_sekolah` (`id_sekolah`);

--
-- Indeks untuk tabel `ekstrakurikuler`
--
ALTER TABLE `ekstrakurikuler`
  ADD PRIMARY KEY (`id_ekstrakurikuler`);

--
-- Indeks untuk tabel `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id_guru`);

--
-- Indeks untuk tabel `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id_kelas`),
  ADD KEY `idx_kelas_id_guru` (`id_guru`);

--
-- Indeks untuk tabel `kurikulum`
--
ALTER TABLE `kurikulum`
  ADD PRIMARY KEY (`id_kurikulum`),
  ADD KEY `idx_kurikulum_id_mapel` (`id_mata_pelajaran`),
  ADD KEY `idx_kurikulum_id_kelas` (`id_kelas`);

--
-- Indeks untuk tabel `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  ADD PRIMARY KEY (`id_mata_pelajaran`);

--
-- Indeks untuk tabel `nilai_ekstrakurikuler`
--
ALTER TABLE `nilai_ekstrakurikuler`
  ADD PRIMARY KEY (`id_nilai_ekstrakurikuler`),
  ADD KEY `idx_nek_id_semester` (`id_semester`),
  ADD KEY `idx_nek_id_siswa` (`id_siswa`),
  ADD KEY `idx_nek_id_ekstra` (`id_ekstrakurikuler`);

--
-- Indeks untuk tabel `nilai_mata_pelajaran`
--
ALTER TABLE `nilai_mata_pelajaran`
  ADD PRIMARY KEY (`id_nilai_mata_pelajaran`),
  ADD KEY `idx_nmp_id_semester` (`id_semester`),
  ADD KEY `idx_nmp_id_siswa` (`id_siswa`),
  ADD KEY `idx_nmp_id_mapel` (`id_mata_pelajaran`);

--
-- Indeks untuk tabel `pengaturan_cetak_rapor`
--
ALTER TABLE `pengaturan_cetak_rapor`
  ADD PRIMARY KEY (`id_pengaturan_cetak_rapor`);

--
-- Indeks untuk tabel `sekolah`
--
ALTER TABLE `sekolah`
  ADD PRIMARY KEY (`id_sekolah`);

--
-- Indeks untuk tabel `semester`
--
ALTER TABLE `semester`
  ADD PRIMARY KEY (`id_semester`);

--
-- Indeks untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD KEY `idx_siswa_id_kelas` (`id_kelas`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `uk_user_username` (`username`),
  ADD KEY `idx_user_id_guru` (`id_guru`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id_absensi` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `cetak_rapor`
--
ALTER TABLE `cetak_rapor`
  MODIFY `id_cetak_rapor` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ekstrakurikuler`
--
ALTER TABLE `ekstrakurikuler`
  MODIFY `id_ekstrakurikuler` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `guru`
--
ALTER TABLE `guru`
  MODIFY `id_guru` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id_kelas` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kurikulum`
--
ALTER TABLE `kurikulum`
  MODIFY `id_kurikulum` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  MODIFY `id_mata_pelajaran` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `nilai_ekstrakurikuler`
--
ALTER TABLE `nilai_ekstrakurikuler`
  MODIFY `id_nilai_ekstrakurikuler` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `nilai_mata_pelajaran`
--
ALTER TABLE `nilai_mata_pelajaran`
  MODIFY `id_nilai_mata_pelajaran` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengaturan_cetak_rapor`
--
ALTER TABLE `pengaturan_cetak_rapor`
  MODIFY `id_pengaturan_cetak_rapor` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `sekolah`
--
ALTER TABLE `sekolah`
  MODIFY `id_sekolah` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `semester`
--
ALTER TABLE `semester`
  MODIFY `id_semester` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `fk_absensi_relations_semester` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`),
  ADD CONSTRAINT `fk_absensi_relations_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`);

--
-- Ketidakleluasaan untuk tabel `cetak_rapor`
--
ALTER TABLE `cetak_rapor`
  ADD CONSTRAINT `fk_cetak_ra_relations_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`),
  ADD CONSTRAINT `fk_cetak_ra_relations_pengatur` FOREIGN KEY (`id_pengaturan_cetak_rapor`) REFERENCES `pengaturan_cetak_rapor` (`id_pengaturan_cetak_rapor`),
  ADD CONSTRAINT `fk_cetak_ra_relations_sekolah` FOREIGN KEY (`id_sekolah`) REFERENCES `sekolah` (`id_sekolah`),
  ADD CONSTRAINT `fk_cetak_ra_relations_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`);

--
-- Ketidakleluasaan untuk tabel `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `fk_kelas_relations_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`);

--
-- Ketidakleluasaan untuk tabel `kurikulum`
--
ALTER TABLE `kurikulum`
  ADD CONSTRAINT `fk_kurikulu_relations_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`),
  ADD CONSTRAINT `fk_kurikulu_relations_mata_pel` FOREIGN KEY (`id_mata_pelajaran`) REFERENCES `mata_pelajaran` (`id_mata_pelajaran`);

--
-- Ketidakleluasaan untuk tabel `nilai_ekstrakurikuler`
--
ALTER TABLE `nilai_ekstrakurikuler`
  ADD CONSTRAINT `fk_nilai_ek_relations_ekstraku` FOREIGN KEY (`id_ekstrakurikuler`) REFERENCES `ekstrakurikuler` (`id_ekstrakurikuler`),
  ADD CONSTRAINT `fk_nilai_ek_relations_semester` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`),
  ADD CONSTRAINT `fk_nilai_ek_relations_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`);

--
-- Ketidakleluasaan untuk tabel `nilai_mata_pelajaran`
--
ALTER TABLE `nilai_mata_pelajaran`
  ADD CONSTRAINT `fk_nilai_ma_relations_mata_pel` FOREIGN KEY (`id_mata_pelajaran`) REFERENCES `mata_pelajaran` (`id_mata_pelajaran`),
  ADD CONSTRAINT `fk_nilai_ma_relations_semester` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`),
  ADD CONSTRAINT `fk_nilai_ma_relations_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`);

--
-- Ketidakleluasaan untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `fk_siswa_relations_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`);

--
-- Ketidakleluasaan untuk tabel `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_user_relations_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
