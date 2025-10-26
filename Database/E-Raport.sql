-- === Reset & safety ===
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Drop all tables (aman tanpa urutan)
DROP TABLE IF EXISTS `cetak_rapor`;
DROP TABLE IF EXISTS `absensi`;
DROP TABLE IF EXISTS `relationship_10`;
DROP TABLE IF EXISTS `relationship_7`;
DROP TABLE IF EXISTS `nilai_mata_pelajaran`;
DROP TABLE IF EXISTS `nilai_ekstrakurikuler`;
DROP TABLE IF EXISTS `kurikulum`;
DROP TABLE IF EXISTS `kelas`;
DROP TABLE IF EXISTS `guru`;
DROP TABLE IF EXISTS `mata_pelajaran`;
DROP TABLE IF EXISTS `pengaturan_cetak_rapor`;
DROP TABLE IF EXISTS `sekolah`;
DROP TABLE IF EXISTS `semester`;
DROP TABLE IF EXISTS `siswa`;
DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `ekstrakurikuler`;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================
-- Create tables (InnoDB)
-- =========================

/*==============================================================*/
/* Table: absensi                                               */
/*==============================================================*/
CREATE TABLE `absensi` (
  `id_absensi` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `id_siswa` INT DEFAULT NULL COMMENT '',
  `id_semester` INT DEFAULT NULL COMMENT '',
  `sakit` INT DEFAULT NULL COMMENT '',
  `izin` INT DEFAULT NULL COMMENT '',
  `alpha` INT DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id_absensi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*==============================================================*/
/* Table: cetak_rapor                                           */
/*==============================================================*/
CREATE TABLE `cetak_rapor` (
  `id_cetak_rapor` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `id_nilai_mata_pelajaran` INT DEFAULT NULL COMMENT '',
  `id_pengaturan_cetak_rapor` INT DEFAULT NULL COMMENT '',
  `id_sekolah` INT DEFAULT NULL COMMENT '',
  `id_guru` INT DEFAULT NULL COMMENT '',
  `id_absensi` INT DEFAULT NULL COMMENT '',
  `id_mata_pelajaran` INT DEFAULT NULL COMMENT '',
  `id_semester` INT DEFAULT NULL COMMENT '',
  `id_siswa` INT DEFAULT NULL COMMENT '',
  `id_nilai_ekstrakurikuler` INT DEFAULT NULL COMMENT '',
  `id_kelas` INT DEFAULT NULL COMMENT '',
  `id_kurikulum` INT DEFAULT NULL COMMENT '',
  `id_ekstrakurikuler` INT DEFAULT NULL COMMENT '',
  `catatan_wali_kelas` TEXT COMMENT '',
  PRIMARY KEY (`id_cetak_rapor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*==============================================================*/
/* Table: ekstrakurikuler                                       */
/*==============================================================*/
CREATE TABLE `ekstrakurikuler` (
  `id_ekstrakurikuler` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `nama_ekstrakurikuler` VARCHAR(150) DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id_ekstrakurikuler`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*==============================================================*/
/* Table: guru                                                  */
/*==============================================================*/
CREATE TABLE `guru` (
  `id_guru` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `nama_guru` VARCHAR(150) DEFAULT NULL COMMENT '',
  `jabatan_guru` ENUM('Kepala Sekolah', 'Guru') DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id_guru`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*==============================================================*/
/* Table: kelas                                                 */
/*==============================================================*/
CREATE TABLE `kelas` (
  `id_kelas` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `id_guru` INT DEFAULT NULL COMMENT '',
  `tingkat_kelas` ENUM('X', 'XI', 'XII') DEFAULT NULL COMMENT '',
  `nama_kelas` VARCHAR(50) DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id_kelas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*==============================================================*/
/* Table: kurikulum                                             */
/*==============================================================*/
CREATE TABLE `kurikulum` (
  `id_kurikulum` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `id_mata_pelajaran` INT DEFAULT NULL COMMENT '',
  `nilai_kurikulum` ENUM('0', '1') DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id_kurikulum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*==============================================================*/
/* Table: mata_pelajaran                                        */
/*==============================================================*/
CREATE TABLE `mata_pelajaran` (
  `id_mata_pelajaran` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `nama_mata_pelajaran` VARCHAR(150) DEFAULT NULL COMMENT '',
  `kode_mata_pelajaran` VARCHAR(50) DEFAULT NULL COMMENT '',
  `kelompok_mata_pelajaran` ENUM('Wajib', 'Pilihan', 'Muatan Lokal') DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id_mata_pelajaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*==============================================================*/
/* Table: nilai_ekstrakurikuler                                 */
/*==============================================================*/
CREATE TABLE `nilai_ekstrakurikuler` (
  `id_nilai_ekstrakurikuler` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `id_siswa` INT DEFAULT NULL COMMENT '',
  `id_ekstrakurikuler` INT DEFAULT NULL COMMENT '',
  `id_semester` INT DEFAULT NULL COMMENT '',
  `nilai_ekstrakurikuler` ENUM('A', 'B', 'C', 'D') DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id_nilai_ekstrakurikuler`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*==============================================================*/
/* Table: nilai_mata_pelajaran                                  */
/*==============================================================*/
CREATE TABLE `nilai_mata_pelajaran` (
  `id_nilai_mata_pelajaran` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `id_mata_pelajaran` INT DEFAULT NULL COMMENT '',
  `id_siswa` INT DEFAULT NULL COMMENT '',
  `id_semester` INT DEFAULT NULL COMMENT '',
  `tp1_lm1` INT DEFAULT NULL COMMENT '',
  `tp2_lm1` INT DEFAULT NULL COMMENT '',
  `tp3_lm1` INT DEFAULT NULL COMMENT '',
  `tp4_lm1` INT DEFAULT NULL COMMENT '',
  `sumatif_lm1` INT DEFAULT NULL COMMENT '',
  `tp1_lm2` INT DEFAULT NULL COMMENT '',
  `tp2_lm2` INT DEFAULT NULL COMMENT '',
  `tp3_lm2` INT DEFAULT NULL COMMENT '',
  `tp4_lm2` INT DEFAULT NULL COMMENT '',
  `sumatif_lm2` INT DEFAULT NULL COMMENT '',
  `tp1_lm3` INT DEFAULT NULL COMMENT '',
  `tp2_lm3` INT DEFAULT NULL COMMENT '',
  `tp3_lm3` INT DEFAULT NULL COMMENT '',
  `tp4_lm3` INT DEFAULT NULL COMMENT '',
  `sumatif_lm3` INT DEFAULT NULL COMMENT '',
  `tp1_lm4` INT DEFAULT NULL COMMENT '',
  `tp2_lm4` INT DEFAULT NULL COMMENT '',
  `tp3_lm4` INT DEFAULT NULL COMMENT '',
  `tp4_lm4` INT DEFAULT NULL COMMENT '',
  `sumatif_lm4` INT DEFAULT NULL COMMENT '',
  `sumatif_tengah_semester` INT DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id_nilai_mata_pelajaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*==============================================================*/
/* Table: pengaturan_cetak_rapor                                */
/*==============================================================*/
CREATE TABLE `pengaturan_cetak_rapor` (
  `id_pengaturan_cetak_rapor` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `tempat_cetak` VARCHAR(50) DEFAULT NULL COMMENT '',
  `tanggal_cetak` DATE DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id_pengaturan_cetak_rapor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*==============================================================*/
/* Table: relationship_10 (PK komposit - tidak AI)              */
/*==============================================================*/
CREATE TABLE `relationship_10` (
  `id_siswa` INT NOT NULL COMMENT '',
  `id_ekstrakurikuler` INT NOT NULL COMMENT '',
  PRIMARY KEY (`id_siswa`, `id_ekstrakurikuler`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*==============================================================*/
/* Table: relationship_7 (PK komposit - tidak AI)               */
/*==============================================================*/
CREATE TABLE `relationship_7` (
  `id_kelas` INT NOT NULL COMMENT '',
  `id_mata_pelajaran` INT NOT NULL COMMENT '',
  PRIMARY KEY (`id_kelas`, `id_mata_pelajaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*==============================================================*/
/* Table: sekolah                                               */
/*==============================================================*/
CREATE TABLE `sekolah` (
  `id_sekolah` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `logo_sekolah` VARCHAR(255) DEFAULT NULL COMMENT '',
  `nama_sekolah` VARCHAR(150) DEFAULT NULL COMMENT '',
  `nsm_sekolah` VARCHAR(50) DEFAULT NULL COMMENT '',
  `npsn_sekolah` VARCHAR(50) DEFAULT NULL COMMENT '',
  `alamat_sekolah` TEXT COMMENT '',
  `no_telepon_sekolah` VARCHAR(20) DEFAULT NULL COMMENT '',
  `kecamatan_sekolah` VARCHAR(50) DEFAULT NULL COMMENT '',
  `kabupaten_atau_kota_sekolah` VARCHAR(50) DEFAULT NULL COMMENT '',
  `provinsi_sekolah` VARCHAR(50) DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id_sekolah`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*==============================================================*/
/* Table: semester                                              */
/*==============================================================*/
CREATE TABLE `semester` (
  `id_semester` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `nama_semester` ENUM('Ganjil', 'Genap') DEFAULT NULL COMMENT '',
  `tahun_ajaran` VARCHAR(50) DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id_semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*==============================================================*/
/* Table: siswa                                                 */
/*==============================================================*/
CREATE TABLE `siswa` (
  `id_siswa` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `id_kelas` INT DEFAULT NULL COMMENT '',
  `id_semester` INT DEFAULT NULL COMMENT '',
  `no_induk_siswa` VARCHAR(50) DEFAULT NULL COMMENT '',
  `no_absen_siswa` VARCHAR(50) DEFAULT NULL COMMENT '',
  `nama_siswa` VARCHAR(150) DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id_siswa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*==============================================================*/
/* Table: user                                                  */
/*==============================================================*/
CREATE TABLE `user` (
  `id_user` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `nama_lengkap_user` VARCHAR(150) DEFAULT NULL COMMENT '',
  `role_user` ENUM('Admin','Wali Kelas') DEFAULT NULL COMMENT '',
  `username` VARCHAR(20) DEFAULT NULL COMMENT '',
  `password_user` VARCHAR(20) DEFAULT NULL COMMENT '',
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===========================================
-- Foreign Keys (setelah semua tabel ada)
-- ===========================================

ALTER TABLE `absensi`
  ADD CONSTRAINT `fk_absensi_relations_semester`
    FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_absensi_relations_siswa`
    FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `cetak_rapor`
  ADD CONSTRAINT `fk_cetak_ra_relations_pengatur`
    FOREIGN KEY (`id_pengaturan_cetak_rapor`) REFERENCES `pengaturan_cetak_rapor` (`id_pengaturan_cetak_rapor`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_cetak_ra_relations_sekolah`
    FOREIGN KEY (`id_sekolah`) REFERENCES `sekolah` (`id_sekolah`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_cetak_ra_relations_absensi`
    FOREIGN KEY (`id_absensi`) REFERENCES `absensi` (`id_absensi`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_cetak_ra_relations_ekstraku`
    FOREIGN KEY (`id_ekstrakurikuler`) REFERENCES `ekstrakurikuler` (`id_ekstrakurikuler`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_cetak_ra_relations_nilai_ek`
    FOREIGN KEY (`id_nilai_ekstrakurikuler`) REFERENCES `nilai_ekstrakurikuler` (`id_nilai_ekstrakurikuler`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_cetak_ra_relations_kurikulu`
    FOREIGN KEY (`id_kurikulum`) REFERENCES `kurikulum` (`id_kurikulum`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_cetak_ra_relations_mata_pel`
    FOREIGN KEY (`id_mata_pelajaran`) REFERENCES `mata_pelajaran` (`id_mata_pelajaran`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_cetak_ra_relations_kelas`
    FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_cetak_ra_relations_semester`
    FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_cetak_ra_relations_nilai_ma`
    FOREIGN KEY (`id_nilai_mata_pelajaran`) REFERENCES `nilai_mata_pelajaran` (`id_nilai_mata_pelajaran`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_cetak_ra_relations_siswa`
    FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_cetak_ra_relations_guru`
    FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `kelas`
  ADD CONSTRAINT `fk_kelas_relions_guru` -- (ejaan nama constraint bisa bebas)
    FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `kurikulum`
  ADD CONSTRAINT `fk_kurikulu_relations_mata_pel`
    FOREIGN KEY (`id_mata_pelajaran`) REFERENCES `mata_pelajaran` (`id_mata_pelajaran`)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `nilai_ekstrakurikuler`
  ADD CONSTRAINT `fk_nilai_ek_relations_semester`
    FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_nilai_ek_relations_ekstraku`
    FOREIGN KEY (`id_ekstrakurikuler`) REFERENCES `ekstrakurikuler` (`id_ekstrakurikuler`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_nilai_ek_relations_siswa`
    FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `nilai_mata_pelajaran`
  ADD CONSTRAINT `fk_nilai_ma_relations_siswa`
    FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_nilai_ma_relations_semester`
    FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_nilai_ma_relations_mata_pel`
    FOREIGN KEY (`id_mata_pelajaran`) REFERENCES `mata_pelajaran` (`id_mata_pelajaran`)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `relationship_10`
  ADD CONSTRAINT `fk_relation_relations_siswa`
    FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_relation_relations_ekstraku`
    FOREIGN KEY (`id_ekstrakurikuler`) REFERENCES `ekstrakurikuler` (`id_ekstrakurikuler`)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `relationship_7`
  ADD CONSTRAINT `fk_relation_relations_kelas`
    FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_relation_relations_mata_pel`
    FOREIGN KEY (`id_mata_pelajaran`) REFERENCES `mata_pelajaran` (`id_mata_pelajaran`)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `siswa`
  ADD CONSTRAINT `fk_siswa_relations_kelas`
    FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_siswa_relations_semester`
    FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`)
    ON DELETE RESTRICT ON UPDATE RESTRICT;
