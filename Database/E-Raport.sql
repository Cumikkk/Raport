/*==============================================================*/
/* MySQL 5.x safe reset + create schema                         */
/*==============================================================*/

SET FOREIGN_KEY_CHECKS = 0;

-- HAPUS TABEL (tak perlu drop FK satu-satu, cukup drop tabelnya)
DROP TABLE IF EXISTS cetak_rapor;
DROP TABLE IF EXISTS nilai_mata_pelajaran;
DROP TABLE IF EXISTS nilai_ekstrakurikuler;
DROP TABLE IF EXISTS absensi;
DROP TABLE IF EXISTS siswa;
DROP TABLE IF EXISTS kelas;
DROP TABLE IF EXISTS guru;
DROP TABLE IF EXISTS mata_pelajaran;
DROP TABLE IF EXISTS pengaturan_cetak;
DROP TABLE IF EXISTS sekolah;
DROP TABLE IF EXISTS semester;
DROP TABLE IF EXISTS user;

SET FOREIGN_KEY_CHECKS = 1;

/*==============================================================*/
/* CREATE TABLES                                                */
/*==============================================================*/

CREATE TABLE user (
  id_user INT NOT NULL AUTO_INCREMENT,
  nama_lengkap_user VARCHAR(150),
  email_user VARCHAR(150),
  no_telepon_user VARCHAR(20),
  username VARCHAR(20),
  password_user VARCHAR(255),
  PRIMARY KEY (id_user)
) ENGINE=InnoDB;

CREATE TABLE sekolah (
  id_sekolah INT NOT NULL AUTO_INCREMENT,
  logo_sekolah VARCHAR(255),
  nama_sekolah VARCHAR(150),
  npsn_sekolah VARCHAR(50),
  nsm_sekolah VARCHAR(50),
  alamat_sekolah TEXT,
  no_telepon_sekolah VARCHAR(20),
  kecamatan_sekolah VARCHAR(50),
  kabupaten_atau_kota_sekolah VARCHAR(50),
  provinsi VARCHAR(50),
  PRIMARY KEY (id_sekolah)
) ENGINE=InnoDB;

CREATE TABLE semester (
  id_semester INT NOT NULL AUTO_INCREMENT,
  nama_semester ENUM('Ganjil','Genap'),
  tahun_ajaran VARCHAR(50),
  PRIMARY KEY (id_semester)
) ENGINE=InnoDB;

CREATE TABLE guru (
  id_guru INT NOT NULL AUTO_INCREMENT,
  id_kelas INT NULL,
  nama_guru VARCHAR(150),
  jabatan_guru ENUM('Kepala Sekolah','Guru'),
  nip_guru VARCHAR(50),
  PRIMARY KEY (id_guru),
  KEY idx_guru_kelas (id_kelas)
) ENGINE=InnoDB;

CREATE TABLE kelas (
  id_kelas INT NOT NULL AUTO_INCREMENT,
  id_guru INT NULL,
  nama_kelas VARCHAR(50),
  PRIMARY KEY (id_kelas),
  KEY idx_kelas_guru (id_guru)
) ENGINE=InnoDB;

CREATE TABLE siswa (
  id_siswa INT NOT NULL AUTO_INCREMENT,
  id_kelas INT,
  no_absen_siswa VARCHAR(10),
  no_induk_siswa VARCHAR(10),
  nama_siswa VARCHAR(150),
  jenis_kelamin_siswa ENUM('L','P'),
  PRIMARY KEY (id_siswa),
  KEY idx_siswa_kelas (id_kelas)
) ENGINE=InnoDB;

CREATE TABLE mata_pelajaran (
  id_mata_pelajaran INT NOT NULL AUTO_INCREMENT,
  nama_mata_pelajaran VARCHAR(50),
  kode_mata_pelajaran VARCHAR(10),
  kelompok_mata_pelajaran VARCHAR(50),
  PRIMARY KEY (id_mata_pelajaran)
) ENGINE=InnoDB;

CREATE TABLE ekstrakurikuler (
  id_ekstrakurikuler INT NOT NULL AUTO_INCREMENT,
  nama_ekstrakurikuler VARCHAR(50),
  PRIMARY KEY (id_ekstrakurikuler)
) ENGINE=InnoDB;

CREATE TABLE absensi (
  id_absensi INT NOT NULL AUTO_INCREMENT,
  id_kelas INT,
  id_siswa INT,
  id_semester INT,
  sakit VARCHAR(10),
  izin VARCHAR(10),
  alpha VARCHAR(10),
  PRIMARY KEY (id_absensi),
  KEY idx_absensi_kelas (id_kelas),
  KEY idx_absensi_siswa (id_siswa),
  KEY idx_absensi_semester (id_semester)
) ENGINE=InnoDB;

CREATE TABLE nilai_ekstrakurikuler (
  id_nilai_ekstrakurikuler INT NOT NULL AUTO_INCREMENT,
  id_semester INT,
  nilai_ekstrakurikuler ENUM('A','B','C','D'),
  PRIMARY KEY (id_nilai_ekstrakurikuler),
  KEY idx_nilek_semester (id_semester)
) ENGINE=InnoDB;

CREATE TABLE nilai_mata_pelajaran (
  id_nilai_mata_pelajaran INT NOT NULL AUTO_INCREMENT,
  id_siswa INT,
  id_kelas INT,
  id_semester INT,
  id_mata_pelajaran INT,
  tp1_lm1 INT,
  tp2_lm1 INT,
  tp3_lm1 INT,
  tp4_lm1 INT,
  sumatif_lm1_ INT,
  tp1_lm2 INT,
  tp2_lm2_ INT,
  tp3_lm2_ INT,
  tp4_lm2_ INT,
  sumatif_lm2_ INT,
  tp1_lm3_ INT,
  tp2_lm3_ INT,
  tp3_lm3_ INT,
  tp4_lm3_ INT,
  sumatif_lm3_ INT,
  tp1_lm4_ INT,
  tp2_lm4_ INT,
  tp3_lm4_ INT,
  tp4_lm4_ INT,
  sumatif_lm4_ INT,
  sumatif_tengah_semester_ INT,
  PRIMARY KEY (id_nilai_mata_pelajaran),
  KEY idx_nilmp_siswa (id_siswa),
  KEY idx_nilmp_kelas (id_kelas),
  KEY idx_nilmp_semester (id_semester),
  KEY idx_nilmp_mapel (id_mata_pelajaran)
) ENGINE=InnoDB;

CREATE TABLE pengaturan_cetak (
  id_pengaturan_cetak INT NOT NULL AUTO_INCREMENT,
  tempat_cetak VARCHAR(50),
  tanggal_cetak DATE,
  PRIMARY KEY (id_pengaturan_cetak)
) ENGINE=InnoDB;

CREATE TABLE cetak_rapor (
  id_cetak_raport INT NOT NULL AUTO_INCREMENT,
  id_pengaturan_cetak INT,
  id_absensi INT,
  id_mata_pelajaran INT,
  id_semester INT,
  id_siswa INT,
  id_ekstrakurikuler INT,
  id_nilai_ekstrakurikuler INT,
  id_kelas INT,
  id_nilai_mata_pelajaran INT,
  id_guru INT,
  id_sekolah INT,
  PRIMARY KEY (id_cetak_raport),
  KEY idx_cr_pengaturan (id_pengaturan_cetak),
  KEY idx_cr_absensi (id_absensi),
  KEY idx_cr_mapel (id_mata_pelajaran),
  KEY idx_cr_semester (id_semester),
  KEY idx_cr_siswa (id_siswa),
  KEY idx_cr_ekstra (id_ekstrakurikuler),
  KEY idx_cr_nilek (id_nilai_ekstrakurikuler),
  KEY idx_cr_kelas (id_kelas),
  KEY idx_cr_nilmp (id_nilai_mata_pelajaran),
  KEY idx_cr_guru (id_guru),
  KEY idx_cr_sekolah (id_sekolah)
) ENGINE=InnoDB;

/*==============================================================*/
/* ADD FOREIGN KEYS (setelah semua tabel ada)                   */
/*==============================================================*/

ALTER TABLE absensi
  ADD CONSTRAINT fk_absensi_relations_siswa
    FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_absensi_relations_kelas
    FOREIGN KEY (id_kelas) REFERENCES kelas(id_kelas)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_absensi_relations_semester
    FOREIGN KEY (id_semester) REFERENCES semester(id_semester)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE nilai_ekstrakurikuler
  ADD CONSTRAINT fk_nilai_ek_relations_semester
    FOREIGN KEY (id_semester) REFERENCES semester(id_semester)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE nilai_mata_pelajaran
  ADD CONSTRAINT fk_nilai_ma_relations_semester
    FOREIGN KEY (id_semester) REFERENCES semester(id_semester)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_nilai_ma_relations_kelas
    FOREIGN KEY (id_kelas) REFERENCES kelas(id_kelas)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_nilai_ma_relations_siswa
    FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_nilai_ma_relations_mata_pel
    FOREIGN KEY (id_mata_pelajaran) REFERENCES mata_pelajaran(id_mata_pelajaran)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

-- Catatan: hubungan dua arah guru<->kelas bisa bikin siklus.
-- Biar aman, ijinkan kolom FK bernilai NULL dan tambah FK setelah keduanya ada.
ALTER TABLE guru
  ADD CONSTRAINT fk_guru_relations_kelas
    FOREIGN KEY (id_kelas) REFERENCES kelas(id_kelas)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE kelas
  ADD CONSTRAINT fk_kelas_relations_guru
    FOREIGN KEY (id_guru) REFERENCES guru(id_guru)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE siswa
  ADD CONSTRAINT fk_siswa_relations_kelas
    FOREIGN KEY (id_kelas) REFERENCES kelas(id_kelas)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE cetak_rapor
  ADD CONSTRAINT fk_cetak_ra_relations_pengatur
    FOREIGN KEY (id_pengaturan_cetak) REFERENCES pengaturan_cetak(id_pengaturan_cetak)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_cetak_ra_relations_ekstraku
    FOREIGN KEY (id_ekstrakurikuler) REFERENCES ekstrakurikuler(id_ekstrakurikuler)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_cetak_ra_relations_semester
    FOREIGN KEY (id_semester) REFERENCES semester(id_semester)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_cetak_ra_relations_kelas
    FOREIGN KEY (id_kelas) REFERENCES kelas(id_kelas)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_cetak_ra_relations_siswa
    FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_cetak_ra_relations_absensi
    FOREIGN KEY (id_absensi) REFERENCES absensi(id_absensi)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_cetak_ra_relations_guru
    FOREIGN KEY (id_guru) REFERENCES guru(id_guru)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_cetak_ra_relations_mata_pel
    FOREIGN KEY (id_mata_pelajaran) REFERENCES mata_pelajaran(id_mata_pelajaran)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_cetak_ra_relations_sekolah
    FOREIGN KEY (id_sekolah) REFERENCES sekolah(id_sekolah)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_cetak_ra_relations_nilai_ma
    FOREIGN KEY (id_nilai_mata_pelajaran) REFERENCES nilai_mata_pelajaran(id_nilai_mata_pelajaran)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_cetak_ra_relations_nilai_ek
    FOREIGN KEY (id_nilai_ekstrakurikuler) REFERENCES nilai_ekstrakurikuler(id_nilai_ekstrakurikuler)
    ON DELETE RESTRICT ON UPDATE RESTRICT;
