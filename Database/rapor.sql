-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 02 Jan 2026 pada 21.38
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

--
-- Dumping data untuk tabel `ekstrakurikuler`
--

INSERT INTO `ekstrakurikuler` (`id_ekstrakurikuler`, `nama_ekstrakurikuler`) VALUES
(345, 'as');

-- --------------------------------------------------------

--
-- Struktur dari tabel `guru`
--

CREATE TABLE `guru` (
  `id_guru` int(10) UNSIGNED NOT NULL,
  `npk_guru` varchar(20) NOT NULL,
  `nama_guru` varchar(150) NOT NULL,
  `jabatan_guru` enum('Kepala Sekolah','Guru') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `guru`
--

INSERT INTO `guru` (`id_guru`, `npk_guru`, `nama_guru`, `jabatan_guru`) VALUES
(1853, '23423016', 'M. Fahrul Alfanani', 'Kepala Sekolah'),
(2278, '1', 'Ahmad Fauzi', 'Guru'),
(2279, '2', 'Siti Aminah', 'Guru'),
(2280, '3', 'Budi Santoso', 'Guru'),
(2281, '4', 'Dewi Lestari', 'Guru'),
(2282, '5', 'Agus Pratama', 'Guru'),
(2283, '6', 'Rina Marlina', 'Guru'),
(2284, '7', 'Hendra Wijaya', 'Guru'),
(2285, '8', 'Nur Aisyah', 'Guru'),
(2286, '9', 'Dedi Kurniawan', 'Guru'),
(2287, '10', 'Sri Wahyuni', 'Guru'),
(2288, '11', 'Andi Saputra', 'Guru'),
(2289, '12', 'Lina Handayani', 'Guru'),
(2290, '13', 'Rizky Ramadhan', 'Guru'),
(2291, '14', 'Yuni Astuti', 'Guru'),
(2292, '15', 'Eko Susanto', 'Guru'),
(2293, '16', 'Fitriani', 'Guru'),
(2294, '17', 'Bambang Irawan', 'Guru'),
(2295, '18', 'Nita Permatasari', 'Guru'),
(2296, '19', 'Arif Hidayat', 'Guru'),
(2297, '20', 'Maya Sari', 'Guru'),
(2298, '21', 'Wahyu Setiawan', 'Guru'),
(2299, '22', 'Putri Amalia', 'Guru'),
(2300, '23', 'Heru Purnomo', 'Guru'),
(2301, '24', 'Lilis Suryani', 'Guru'),
(2302, '25', 'Fajar Nugroho', 'Guru'),
(2303, '26', 'Indah Kusuma', 'Guru'),
(2304, '27', 'Rudi Hartono', 'Guru'),
(2305, '28', 'Ani Wulandari', 'Guru'),
(2306, '29', 'Bayu Prakoso', 'Guru'),
(2307, '30', 'Nanik Rahmawati', 'Guru'),
(2308, '31', 'Joko Riyadi', 'Guru'),
(2309, '32', 'Sari Puspita', 'Guru'),
(2310, '33', 'Teguh Santika', 'Guru'),
(2311, '34', 'Dian Pratiwi', 'Guru'),
(2312, '35', 'Ilham Maulana', 'Guru'),
(2313, '36', 'Rika Novitasari', 'Guru'),
(2314, '37', 'Yusuf Abdullah', 'Guru'),
(2315, '38', 'Eka Lestari', 'Guru'),
(2316, '39', 'Anton Saputro', 'Guru'),
(2317, '40', 'Sulastri', 'Guru'),
(2318, '41', 'Asep Saepudin', 'Guru'),
(2319, '42', 'Weni Anggraini', 'Guru'),
(2320, '43', 'Fikri Anshori', 'Guru'),
(2321, '44', 'Ratna Dewi', 'Guru'),
(2322, '45', 'Mulyadi', 'Guru'),
(2323, '46', 'Citra Ayuningtyas', 'Guru'),
(2324, '47', 'Irfan Hakim', 'Guru'),
(2325, '48', 'Melati Putri', 'Guru'),
(2326, '49', 'Slamet Riyanto', 'Guru'),
(2327, '50', 'Erna Susilawati', 'Guru'),
(2328, '51', 'Yoga Prasetyo', 'Guru'),
(2329, '52', 'Bella Kartika', 'Guru'),
(2330, '53', 'Rahmat Hidayah', 'Guru'),
(2331, '54', 'Tuti Alawiyah', 'Guru'),
(2332, '55', 'Ari Kurniadi', 'Guru'),
(2333, '56', 'Silvia Monica', 'Guru'),
(2334, '57', 'Hasan Basri', 'Guru'),
(2335, '58', 'Nurul Hidayati', 'Guru'),
(2336, '59', 'Rian Setia', 'Guru'),
(2337, '60', 'Diah Puspitasari', 'Guru'),
(2338, '61', 'Lukman Hakim', 'Guru'),
(2339, '62', 'Vina Oktaviani', 'Guru'),
(2340, '63', 'Syaiful Anwar', 'Guru'),
(2341, '64', 'Rosi Lestiana', 'Guru'),
(2342, '65', 'Gunawan', 'Guru'),
(2343, '66', 'Farah Nabila', 'Guru'),
(2344, '67', 'Aditya Firmansyah', 'Guru'),
(2345, '68', 'Shinta Maharani', 'Guru'),
(2346, '69', 'Zainal Abidin', 'Guru'),
(2347, '70', 'Wati Susanti', 'Guru'),
(2348, '71', 'Panca Wardana', 'Guru'),
(2349, '72', 'Nuri Fatimah', 'Guru'),
(2350, '73', 'Reza Pramudya', 'Guru'),
(2351, '74', 'Yuliana Sihotang', 'Guru'),
(2352, '75', 'Abdul Karim', 'Guru'),
(2353, '76', 'Ika Setyaningsih', 'Guru'),
(2354, '77', 'Surya Mahendra', 'Guru'),
(2355, '78', 'Poppy Angelia', 'Guru'),
(2356, '79', 'Dani Firmanto', 'Guru'),
(2357, '80', 'Salma Khairunnisa', 'Guru'),
(2358, '81', 'Taufik Hidayat', 'Guru'),
(2359, '82', 'Ayu Laksmi', 'Guru'),
(2360, '83', 'Rendy Saputra', 'Guru'),
(2361, '84', 'Heni Kuswati', 'Guru'),
(2362, '85', 'Nasiruddin', 'Guru'),
(2363, '86', 'Siska Apriani', 'Guru'),
(2364, '87', 'Galih Pratama', 'Guru'),
(2365, '88', 'Murniati', 'Guru'),
(2366, '89', 'Ujang Suryana', 'Guru'),
(2367, '90', 'Novi Handika', 'Guru'),
(2368, '91', 'Kurniawan Putra', 'Guru'),
(2369, '92', 'Elsa Fitriyani', 'Guru'),
(2370, '93', 'Ridho Ilahi', 'Guru'),
(2371, '94', 'Marni Sulastri', 'Guru'),
(2372, '95', 'Aldi Syahputra', 'Guru'),
(2373, '96', 'Rahayu Wibowo', 'Guru'),
(2374, '97', 'M. Iqbal', 'Guru'),
(2375, '98', 'Putu Lestari', 'Guru'),
(2376, '99', 'Yogi Permana', 'Guru'),
(2377, '100', 'Laila Zahra', 'Guru');

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

--
-- Dumping data untuk tabel `kelas`
--

INSERT INTO `kelas` (`id_kelas`, `id_guru`, `tingkat_kelas`, `nama_kelas`) VALUES
(61, 1853, 'X', 'X - 1'),
(62, 1853, 'X', 'X - 2'),
(63, 1853, 'X', 'X - 3'),
(64, 1853, 'XI', 'XI - IPA 1'),
(65, 1853, 'XI', 'XI - IPA 2'),
(66, 1853, 'XI', 'XI - IPA 3'),
(67, 1853, 'XI', 'XI - IPS 1'),
(68, 1853, 'XI', 'XI - IPS 2'),
(69, 1853, 'XI', 'XI - IPS 3'),
(70, 1853, 'XII', 'XII - IPA 1'),
(71, 1853, 'XII', 'XII - IPA 2'),
(72, 1853, 'XII', 'XII - IPA 3'),
(73, 1853, 'XII', 'XII - IPS 1'),
(74, 1853, 'XII', 'XII - IPS 2'),
(75, 1853, 'XII', 'XII - IPS 3');

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

--
-- Dumping data untuk tabel `mata_pelajaran`
--

INSERT INTO `mata_pelajaran` (`id_mata_pelajaran`, `nama_mata_pelajaran`, `kode_mata_pelajaran`, `kelompok_mata_pelajaran`) VALUES
(1, 'Bahasa Inggris', 'BAH', 'Wajib'),
(2, 'oke', 'OKE_001', 'Pilihan'),
(4, 'sdsdd', 'SDS_003', 'Lokal');

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

--
-- Dumping data untuk tabel `pengaturan_cetak_rapor`
--

INSERT INTO `pengaturan_cetak_rapor` (`id_pengaturan_cetak_rapor`, `tempat_cetak`, `tanggal_cetak`) VALUES
(1, 'Sidoarjo', '2025-12-31');

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
(1, 'logo-logo-crop-20251229135238-faebf8.jpg', 'Madrasah Aliyah Nurul Huda', '131235150017', '20584610', 'Jl. Raya Kalanganyar Barat 53, Kec. Sedati, Kabupaten Sidoarjo, Jawa Timur 61253', '(031) 8910 711', 'Sedati', 'Sidoarjo', 'Jawa Timur');

-- --------------------------------------------------------

--
-- Struktur dari tabel `semester`
--

CREATE TABLE `semester` (
  `id_semester` int(10) UNSIGNED NOT NULL,
  `nama_semester` enum('Ganjil','Genap') NOT NULL,
  `tahun_ajaran` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `semester`
--

INSERT INTO `semester` (`id_semester`, `nama_semester`, `tahun_ajaran`) VALUES
(1, 'Ganjil', '2025/2026'),
(2, 'Ganjil', '2025/2026');

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

--
-- Dumping data untuk tabel `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `id_kelas`, `no_induk_siswa`, `no_absen_siswa`, `nama_siswa`) VALUES
(2140, 61, '1000000001', '1', 'Siswa 1'),
(2141, 61, '1000000002', '2', 'Siswa 2'),
(2142, 61, '1000000003', '3', 'Siswa 3'),
(2143, 61, '1000000004', '4', 'Siswa 4'),
(2144, 61, '1000000005', '5', 'Siswa 5'),
(2145, 61, '1000000006', '6', 'Siswa 6'),
(2146, 61, '1000000007', '7', 'Siswa 7'),
(2147, 61, '1000000008', '8', 'Siswa 8'),
(2148, 61, '1000000009', '9', 'Siswa 9'),
(2149, 61, '1000000010', '10', 'Siswa 10'),
(2150, 62, '1000000011', '1', 'Siswa 1'),
(2151, 62, '1000000012', '2', 'Siswa 2'),
(2152, 62, '1000000013', '3', 'Siswa 3'),
(2153, 62, '1000000014', '4', 'Siswa 4'),
(2154, 62, '1000000015', '5', 'Siswa 5'),
(2155, 62, '1000000016', '6', 'Siswa 6'),
(2156, 62, '1000000017', '7', 'Siswa 7'),
(2157, 62, '1000000018', '8', 'Siswa 8'),
(2158, 62, '1000000019', '9', 'Siswa 9'),
(2159, 62, '1000000020', '10', 'Siswa 10'),
(2160, 63, '1000000021', '1', 'Siswa 1'),
(2161, 63, '1000000022', '2', 'Siswa 2'),
(2162, 63, '1000000023', '3', 'Siswa 3'),
(2163, 63, '1000000024', '4', 'Siswa 4'),
(2164, 63, '1000000025', '5', 'Siswa 5'),
(2165, 63, '1000000026', '6', 'Siswa 6'),
(2166, 63, '1000000027', '7', 'Siswa 7'),
(2167, 63, '1000000028', '8', 'Siswa 8'),
(2168, 63, '1000000029', '9', 'Siswa 9'),
(2169, 63, '1000000030', '10', 'Siswa 10'),
(2170, 64, '1000000031', '1', 'Siswa 1'),
(2171, 64, '1000000032', '2', 'Siswa 2'),
(2172, 64, '1000000033', '3', 'Siswa 3'),
(2173, 64, '1000000034', '4', 'Siswa 4'),
(2174, 64, '1000000035', '5', 'Siswa 5'),
(2175, 64, '1000000036', '6', 'Siswa 6'),
(2176, 64, '1000000037', '7', 'Siswa 7'),
(2177, 64, '1000000038', '8', 'Siswa 8'),
(2178, 64, '1000000039', '9', 'Siswa 9'),
(2179, 64, '1000000040', '10', 'Siswa 10'),
(2180, 65, '1000000041', '1', 'Siswa 1'),
(2181, 65, '1000000042', '2', 'Siswa 2'),
(2182, 65, '1000000043', '3', 'Siswa 3'),
(2183, 65, '1000000044', '4', 'Siswa 4'),
(2184, 65, '1000000045', '5', 'Siswa 5'),
(2185, 65, '1000000046', '6', 'Siswa 6'),
(2186, 65, '1000000047', '7', 'Siswa 7'),
(2187, 65, '1000000048', '8', 'Siswa 8'),
(2188, 65, '1000000049', '9', 'Siswa 9'),
(2189, 65, '1000000050', '10', 'Siswa 10'),
(2190, 66, '1000000051', '1', 'Siswa 1'),
(2191, 66, '1000000052', '2', 'Siswa 2'),
(2192, 66, '1000000053', '3', 'Siswa 3'),
(2193, 66, '1000000054', '4', 'Siswa 4'),
(2194, 66, '1000000055', '5', 'Siswa 5'),
(2195, 66, '1000000056', '6', 'Siswa 6'),
(2196, 66, '1000000057', '7', 'Siswa 7'),
(2197, 66, '1000000058', '8', 'Siswa 8'),
(2198, 66, '1000000059', '9', 'Siswa 9'),
(2199, 66, '1000000060', '10', 'Siswa 10'),
(2200, 67, '1000000061', '1', 'Siswa 1'),
(2201, 67, '1000000062', '2', 'Siswa 2'),
(2202, 67, '1000000063', '3', 'Siswa 3'),
(2203, 67, '1000000064', '4', 'Siswa 4'),
(2204, 67, '1000000065', '5', 'Siswa 5'),
(2205, 67, '1000000066', '6', 'Siswa 6'),
(2206, 67, '1000000067', '7', 'Siswa 7'),
(2207, 67, '1000000068', '8', 'Siswa 8'),
(2208, 67, '1000000069', '9', 'Siswa 9'),
(2209, 67, '1000000070', '10', 'Siswa 10'),
(2210, 68, '1000000071', '1', 'Siswa 1'),
(2211, 68, '1000000072', '2', 'Siswa 2'),
(2212, 68, '1000000073', '3', 'Siswa 3'),
(2213, 68, '1000000074', '4', 'Siswa 4'),
(2214, 68, '1000000075', '5', 'Siswa 5'),
(2215, 68, '1000000076', '6', 'Siswa 6'),
(2216, 68, '1000000077', '7', 'Siswa 7'),
(2217, 68, '1000000078', '8', 'Siswa 8'),
(2218, 68, '1000000079', '9', 'Siswa 9'),
(2219, 68, '1000000080', '10', 'Siswa 10'),
(2220, 69, '1000000081', '1', 'Siswa 1'),
(2221, 69, '1000000082', '2', 'Siswa 2'),
(2222, 69, '1000000083', '3', 'Siswa 3'),
(2223, 69, '1000000084', '4', 'Siswa 4'),
(2224, 69, '1000000085', '5', 'Siswa 5'),
(2225, 69, '1000000086', '6', 'Siswa 6'),
(2226, 69, '1000000087', '7', 'Siswa 7'),
(2227, 69, '1000000088', '8', 'Siswa 8'),
(2228, 69, '1000000089', '9', 'Siswa 9'),
(2229, 69, '1000000090', '10', 'Siswa 10'),
(2230, 70, '1000000091', '1', 'Siswa 1'),
(2231, 70, '1000000092', '2', 'Siswa 2'),
(2232, 70, '1000000093', '3', 'Siswa 3'),
(2233, 70, '1000000094', '4', 'Siswa 4'),
(2234, 70, '1000000095', '5', 'Siswa 5'),
(2235, 70, '1000000096', '6', 'Siswa 6'),
(2236, 70, '1000000097', '7', 'Siswa 7'),
(2237, 70, '1000000098', '8', 'Siswa 8'),
(2238, 70, '1000000099', '9', 'Siswa 9'),
(2239, 70, '1000000100', '10', 'Siswa 10'),
(2240, 71, '1000000101', '1', 'Siswa 1'),
(2241, 71, '1000000102', '2', 'Siswa 2'),
(2242, 71, '1000000103', '3', 'Siswa 3'),
(2243, 71, '1000000104', '4', 'Siswa 4'),
(2244, 71, '1000000105', '5', 'Siswa 5'),
(2245, 71, '1000000106', '6', 'Siswa 6'),
(2246, 71, '1000000107', '7', 'Siswa 7'),
(2247, 71, '1000000108', '8', 'Siswa 8'),
(2248, 71, '1000000109', '9', 'Siswa 9'),
(2249, 71, '1000000110', '10', 'Siswa 10'),
(2250, 72, '1000000111', '1', 'Siswa 1'),
(2251, 72, '1000000112', '2', 'Siswa 2'),
(2252, 72, '1000000113', '3', 'Siswa 3'),
(2253, 72, '1000000114', '4', 'Siswa 4'),
(2254, 72, '1000000115', '5', 'Siswa 5'),
(2255, 72, '1000000116', '6', 'Siswa 6'),
(2256, 72, '1000000117', '7', 'Siswa 7'),
(2257, 72, '1000000118', '8', 'Siswa 8'),
(2258, 72, '1000000119', '9', 'Siswa 9'),
(2259, 72, '1000000120', '10', 'Siswa 10'),
(2260, 73, '1000000121', '1', 'Siswa 1'),
(2261, 73, '1000000122', '2', 'Siswa 2'),
(2262, 73, '1000000123', '3', 'Siswa 3'),
(2263, 73, '1000000124', '4', 'Siswa 4'),
(2264, 73, '1000000125', '5', 'Siswa 5'),
(2265, 73, '1000000126', '6', 'Siswa 6'),
(2266, 73, '1000000127', '7', 'Siswa 7'),
(2267, 73, '1000000128', '8', 'Siswa 8'),
(2268, 73, '1000000129', '9', 'Siswa 9'),
(2269, 73, '1000000130', '10', 'Siswa 10'),
(2270, 74, '1000000131', '1', 'Siswa 1'),
(2271, 74, '1000000132', '2', 'Siswa 2'),
(2272, 74, '1000000133', '3', 'Siswa 3'),
(2273, 74, '1000000134', '4', 'Siswa 4'),
(2274, 74, '1000000135', '5', 'Siswa 5'),
(2275, 74, '1000000136', '6', 'Siswa 6'),
(2276, 74, '1000000137', '7', 'Siswa 7'),
(2277, 74, '1000000138', '8', 'Siswa 8'),
(2278, 74, '1000000139', '9', 'Siswa 9'),
(2279, 74, '1000000140', '10', 'Siswa 10'),
(2280, 75, '1000000141', '1', 'Siswa 1'),
(2281, 75, '1000000142', '2', 'Siswa 2'),
(2282, 75, '1000000143', '3', 'Siswa 3'),
(2283, 75, '1000000144', '4', 'Siswa 4'),
(2284, 75, '1000000145', '5', 'Siswa 5'),
(2285, 75, '1000000146', '6', 'Siswa 6'),
(2286, 75, '1000000147', '7', 'Siswa 7'),
(2287, 75, '1000000148', '8', 'Siswa 8'),
(2288, 75, '1000000149', '9', 'Siswa 9'),
(2289, 75, '1000000150', '10', 'Siswa 10');

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
(1, 1853, 'Admin', 'Cumikkk.', '123'),
(2, 1853, 'Guru', 'Fahrul', '456');

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
  ADD UNIQUE KEY `uq_cetak_rapor_siswa` (`id_siswa`),
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
  MODIFY `id_absensi` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `cetak_rapor`
--
ALTER TABLE `cetak_rapor`
  MODIFY `id_cetak_rapor` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `ekstrakurikuler`
--
ALTER TABLE `ekstrakurikuler`
  MODIFY `id_ekstrakurikuler` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=346;

--
-- AUTO_INCREMENT untuk tabel `guru`
--
ALTER TABLE `guru`
  MODIFY `id_guru` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2378;

--
-- AUTO_INCREMENT untuk tabel `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id_kelas` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT untuk tabel `kurikulum`
--
ALTER TABLE `kurikulum`
  MODIFY `id_kurikulum` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  MODIFY `id_mata_pelajaran` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `nilai_ekstrakurikuler`
--
ALTER TABLE `nilai_ekstrakurikuler`
  MODIFY `id_nilai_ekstrakurikuler` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `nilai_mata_pelajaran`
--
ALTER TABLE `nilai_mata_pelajaran`
  MODIFY `id_nilai_mata_pelajaran` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `pengaturan_cetak_rapor`
--
ALTER TABLE `pengaturan_cetak_rapor`
  MODIFY `id_pengaturan_cetak_rapor` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `sekolah`
--
ALTER TABLE `sekolah`
  MODIFY `id_sekolah` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `semester`
--
ALTER TABLE `semester`
  MODIFY `id_semester` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2290;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `fk_absensi_relations_semester` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_absensi_relations_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `cetak_rapor`
--
ALTER TABLE `cetak_rapor`
  ADD CONSTRAINT `fk_cetak_ra_relations_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cetak_ra_relations_pengatur` FOREIGN KEY (`id_pengaturan_cetak_rapor`) REFERENCES `pengaturan_cetak_rapor` (`id_pengaturan_cetak_rapor`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cetak_ra_relations_sekolah` FOREIGN KEY (`id_sekolah`) REFERENCES `sekolah` (`id_sekolah`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cetak_ra_relations_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `fk_kelas_relations_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kurikulum`
--
ALTER TABLE `kurikulum`
  ADD CONSTRAINT `fk_kurikulu_relations_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kurikulu_relations_mata_pel` FOREIGN KEY (`id_mata_pelajaran`) REFERENCES `mata_pelajaran` (`id_mata_pelajaran`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `nilai_ekstrakurikuler`
--
ALTER TABLE `nilai_ekstrakurikuler`
  ADD CONSTRAINT `fk_nilai_ek_relations_ekstraku` FOREIGN KEY (`id_ekstrakurikuler`) REFERENCES `ekstrakurikuler` (`id_ekstrakurikuler`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_nilai_ek_relations_semester` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_nilai_ek_relations_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `nilai_mata_pelajaran`
--
ALTER TABLE `nilai_mata_pelajaran`
  ADD CONSTRAINT `fk_nilai_ma_relations_mata_pel` FOREIGN KEY (`id_mata_pelajaran`) REFERENCES `mata_pelajaran` (`id_mata_pelajaran`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_nilai_ma_relations_semester` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_nilai_ma_relations_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `fk_siswa_relations_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_user_relations_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
