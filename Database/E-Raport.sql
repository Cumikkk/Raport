-- === Pilih database yang benar (ganti sesuai milikmu) ===
-- USE project_raport;

-- === Bersihkan skema dengan aman ===
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS cetak_rapor;
DROP TABLE IF EXISTS absensi;
DROP TABLE IF EXISTS nilai_ekstrakurikuler;
DROP TABLE IF EXISTS nilai_mata_pelajaran;
DROP TABLE IF EXISTS kurikulum;
DROP TABLE IF EXISTS kelas;
DROP TABLE IF EXISTS siswa;
DROP TABLE IF EXISTS pengaturan_cetak_rapor;
DROP TABLE IF EXISTS sekolah;
DROP TABLE IF EXISTS semester;
DROP TABLE IF EXISTS mata_pelajaran;
DROP TABLE IF EXISTS ekstrakurikuler;
DROP TABLE IF EXISTS guru;
DROP TABLE IF EXISTS `user`;

SET FOREIGN_KEY_CHECKS = 1;

-- === Buat ulang tabel ===
-- Catatan: semua PK AUTO_INCREMENT, kolom FK di-index, engine/charset konsisten

/*==============================================================*/
/* Table: guru                                                  */
/*==============================================================*/
CREATE TABLE guru (
  id_guru INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  nama_guru VARCHAR(150) NOT NULL COMMENT '',
  jabatan_guru ENUM('Kepala Sekolah','Guru') NOT NULL COMMENT '',
  PRIMARY KEY (id_guru)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*==============================================================*/
/* Table: sekolah                                               */
/*==============================================================*/
CREATE TABLE sekolah (
  id_sekolah INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  logo_sekolah VARCHAR(255) COMMENT '',
  nama_sekolah VARCHAR(150) NOT NULL COMMENT '',
  nsm_sekolah VARCHAR(50) COMMENT '',
  npsn_sekolah VARCHAR(50) COMMENT '',
  alamat_sekolah TEXT COMMENT '',
  no_telepon_sekolah VARCHAR(20) COMMENT '',
  kecamatan_sekolah VARCHAR(50) COMMENT '',
  kabupaten_atau_kota_sekolah VARCHAR(50) COMMENT '',
  provinsi_sekolah VARCHAR(50) COMMENT '',
  PRIMARY KEY (id_sekolah)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*==============================================================*/
/* Table: pengaturan_cetak_rapor                                */
/*==============================================================*/
CREATE TABLE pengaturan_cetak_rapor (
  id_pengaturan_cetak_rapor INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  tempat_cetak VARCHAR(50) COMMENT '',
  tanggal_cetak DATE COMMENT '',
  PRIMARY KEY (id_pengaturan_cetak_rapor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*==============================================================*/
/* Table: semester                                              */
/*==============================================================*/
CREATE TABLE semester (
  id_semester INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  nama_semester ENUM('Ganjil','Genap') NOT NULL COMMENT '',
  tahun_ajaran VARCHAR(50) NOT NULL COMMENT '',
  PRIMARY KEY (id_semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*==============================================================*/
/* Table: mata_pelajaran                                        */
/*==============================================================*/
CREATE TABLE mata_pelajaran (
  id_mata_pelajaran INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  nama_mata_pelajaran VARCHAR(150) NOT NULL COMMENT '',
  kode_mata_pelajaran VARCHAR(50) COMMENT '',
  kelompok_mata_pelajaran VARCHAR(50) COMMENT '',
  PRIMARY KEY (id_mata_pelajaran)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*==============================================================*/
/* Table: ekstrakurikuler                                       */
/*==============================================================*/
CREATE TABLE ekstrakurikuler (
  id_ekstrakurikuler INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  nama_ekstrakurikuler VARCHAR(150) NOT NULL COMMENT '',
  PRIMARY KEY (id_ekstrakurikuler)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*==============================================================*/
/* Table: kelas                                                 */
/*==============================================================*/
CREATE TABLE kelas (
  id_kelas INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  id_guru INT UNSIGNED COMMENT '',
  tingkat_kelas ENUM('X','XI','XII') NOT NULL COMMENT '',
  nama_kelas VARCHAR(50) NOT NULL COMMENT '',
  PRIMARY KEY (id_kelas),
  KEY idx_kelas_id_guru (id_guru),
  CONSTRAINT fk_kelas_relations_guru
    FOREIGN KEY (id_guru) REFERENCES guru (id_guru)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*==============================================================*/
/* Table: siswa                                                 */
/*==============================================================*/
CREATE TABLE siswa (
  id_siswa INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  id_kelas INT UNSIGNED COMMENT '',
  no_induk_siswa VARCHAR(50) COMMENT '',
  no_absen_siswa VARCHAR(50) COMMENT '',
  nama_siswa VARCHAR(150) NOT NULL COMMENT '',
  PRIMARY KEY (id_siswa),
  KEY idx_siswa_id_kelas (id_kelas),
  CONSTRAINT fk_siswa_relations_kelas
    FOREIGN KEY (id_kelas) REFERENCES kelas (id_kelas)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*==============================================================*/
/* Table: user                                                  */
/*==============================================================*/
CREATE TABLE `user` (
  id_user INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  id_guru INT UNSIGNED COMMENT '',
  role_user ENUM('Admin','Guru') NOT NULL COMMENT '',
  username VARCHAR(20) NOT NULL COMMENT '',
  password_user VARCHAR(255) NOT NULL COMMENT '',
  PRIMARY KEY (id_user),
  UNIQUE KEY uk_user_username (username),
  KEY idx_user_id_guru (id_guru),
  CONSTRAINT fk_user_relations_guru
    FOREIGN KEY (id_guru) REFERENCES guru (id_guru)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*==============================================================*/
/* Table: absensi                                               */
/*==============================================================*/
CREATE TABLE absensi (
  id_absensi INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  id_semester INT UNSIGNED NOT NULL COMMENT '',
  id_siswa INT UNSIGNED NOT NULL COMMENT '',
  sakit INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '',
  izin INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '',
  alpha INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '',
  PRIMARY KEY (id_absensi),
  KEY idx_absensi_id_semester (id_semester),
  KEY idx_absensi_id_siswa (id_siswa),
  CONSTRAINT fk_absensi_relations_semester
    FOREIGN KEY (id_semester) REFERENCES semester (id_semester)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT fk_absensi_relations_siswa
    FOREIGN KEY (id_siswa) REFERENCES siswa (id_siswa)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*==============================================================*/
/* Table: kurikulum                                             */
/*==============================================================*/
CREATE TABLE kurikulum (
  id_kurikulum INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  id_mata_pelajaran INT UNSIGNED NOT NULL COMMENT '',
  id_kelas INT UNSIGNED NOT NULL COMMENT '',
  nilai_kurikulum ENUM('0','1') NOT NULL COMMENT '',
  PRIMARY KEY (id_kurikulum),
  KEY idx_kurikulum_id_mapel (id_mata_pelajaran),
  KEY idx_kurikulum_id_kelas (id_kelas),
  CONSTRAINT fk_kurikulu_relations_mata_pel
    FOREIGN KEY (id_mata_pelajaran) REFERENCES mata_pelajaran (id_mata_pelajaran)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT fk_kurikulu_relations_kelas
    FOREIGN KEY (id_kelas) REFERENCES kelas (id_kelas)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*==============================================================*/
/* Table: nilai_ekstrakurikuler                                 */
/*==============================================================*/
CREATE TABLE nilai_ekstrakurikuler (
  id_nilai_ekstrakurikuler INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  id_semester INT UNSIGNED NOT NULL COMMENT '',
  id_siswa INT UNSIGNED NOT NULL COMMENT '',
  id_ekstrakurikuler INT UNSIGNED NOT NULL COMMENT '',
  nilai_ekstrakurikuler ENUM('A','B','C','D') NOT NULL COMMENT '',
  PRIMARY KEY (id_nilai_ekstrakurikuler),
  KEY idx_nek_id_semester (id_semester),
  KEY idx_nek_id_siswa (id_siswa),
  KEY idx_nek_id_ekstra (id_ekstrakurikuler),
  CONSTRAINT fk_nilai_ek_relations_semester
    FOREIGN KEY (id_semester) REFERENCES semester (id_semester)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT fk_nilai_ek_relations_siswa
    FOREIGN KEY (id_siswa) REFERENCES siswa (id_siswa)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT fk_nilai_ek_relations_ekstraku
    FOREIGN KEY (id_ekstrakurikuler) REFERENCES ekstrakurikuler (id_ekstrakurikuler)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*==============================================================*/
/* Table: nilai_mata_pelajaran                                  */
/*==============================================================*/
CREATE TABLE nilai_mata_pelajaran (
  id_nilai_mata_pelajaran INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  id_semester INT UNSIGNED NOT NULL COMMENT '',
  id_siswa INT UNSIGNED NOT NULL COMMENT '',
  id_mata_pelajaran INT UNSIGNED NOT NULL COMMENT '',
  tp1_lm1 INT DEFAULT NULL COMMENT '',
  tp2_lm1 INT DEFAULT NULL COMMENT '',
  tp3_lm1 INT DEFAULT NULL COMMENT '',
  tp4_lm1 INT DEFAULT NULL COMMENT '',
  sumatif_lm1 INT DEFAULT NULL COMMENT '',
  tp1_lm2 INT DEFAULT NULL COMMENT '',
  tp2_lm2 INT DEFAULT NULL COMMENT '',
  tp3_lm2 INT DEFAULT NULL COMMENT '',
  tp4_lm2 INT DEFAULT NULL COMMENT '',
  sumatif_lm2 INT DEFAULT NULL COMMENT '',
  tp1_lm3 INT DEFAULT NULL COMMENT '',
  tp2_lm3 INT DEFAULT NULL COMMENT '',
  tp3_lm3 INT DEFAULT NULL COMMENT '',
  tp4_lm3 INT DEFAULT NULL COMMENT '',
  sumatif_lm3 INT DEFAULT NULL COMMENT '',
  tp1_lm4 INT DEFAULT NULL COMMENT '',
  tp2_lm4 INT DEFAULT NULL COMMENT '',
  tp3_lm4 INT DEFAULT NULL COMMENT '',
  tp4_lm4 INT DEFAULT NULL COMMENT '',
  sumatif_lm4 INT DEFAULT NULL COMMENT '',
  sumatif_tengah_semester INT DEFAULT NULL COMMENT '',
  PRIMARY KEY (id_nilai_mata_pelajaran),
  KEY idx_nmp_id_semester (id_semester),
  KEY idx_nmp_id_siswa (id_siswa),
  KEY idx_nmp_id_mapel (id_mata_pelajaran),
  CONSTRAINT fk_nilai_ma_relations_semester
    FOREIGN KEY (id_semester) REFERENCES semester (id_semester)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT fk_nilai_ma_relations_siswa
    FOREIGN KEY (id_siswa) REFERENCES siswa (id_siswa)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT fk_nilai_ma_relations_mata_pel
    FOREIGN KEY (id_mata_pelajaran) REFERENCES mata_pelajaran (id_mata_pelajaran)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*==============================================================*/
/* Table: cetak_rapor                                           */
/*==============================================================*/
CREATE TABLE cetak_rapor (
  id_cetak_rapor INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '',
  id_guru INT UNSIGNED COMMENT '',
  id_siswa INT UNSIGNED COMMENT '',
  id_pengaturan_cetak_rapor INT UNSIGNED COMMENT '',
  id_sekolah INT UNSIGNED COMMENT '',
  catatan_wali_kelas TEXT COMMENT '',
  PRIMARY KEY (id_cetak_rapor),
  KEY idx_cr_id_guru (id_guru),
  KEY idx_cr_id_siswa (id_siswa),
  KEY idx_cr_id_pengaturan (id_pengaturan_cetak_rapor),
  KEY idx_cr_id_sekolah (id_sekolah),
  CONSTRAINT fk_cetak_ra_relations_guru
    FOREIGN KEY (id_guru) REFERENCES guru (id_guru)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT fk_cetak_ra_relations_siswa
    FOREIGN KEY (id_siswa) REFERENCES siswa (id_siswa)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT fk_cetak_ra_relations_pengatur
    FOREIGN KEY (id_pengaturan_cetak_rapor) REFERENCES pengaturan_cetak_rapor (id_pengaturan_cetak_rapor)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT fk_cetak_ra_relations_sekolah
    FOREIGN KEY (id_sekolah) REFERENCES sekolah (id_sekolah)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
