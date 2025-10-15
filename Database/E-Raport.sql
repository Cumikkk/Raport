/*==============================================================*/
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     15/10/2025 13.33.18                          */
/*==============================================================*/


alter table absensi 
   drop foreign key fk_absensi_relations_siswa;

alter table absensi 
   drop foreign key fk_absensi_relations_kelas;

alter table absensi 
   drop foreign key fk_absensi_relations_semester;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_pengatur;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_ekstraku;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_semester;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_kelas;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_siswa;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_absensi;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_guru;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_mata_pel;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_sekolah;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_nilai_ma;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_nilai_ek;

alter table guru 
   drop foreign key fk_guru_relations_kelas;

alter table kelas 
   drop foreign key fk_kelas_relations_guru;

alter table nilai_ekstrakurikuler 
   drop foreign key fk_nilai_ek_relations_semester;

alter table nilai_mata_pelajaran 
   drop foreign key fk_nilai_ma_relations_semester;

alter table nilai_mata_pelajaran 
   drop foreign key fk_nilai_ma_relations_kelas;

alter table nilai_mata_pelajaran 
   drop foreign key fk_nilai_ma_relations_siswa;

alter table nilai_mata_pelajaran 
   drop foreign key fk_nilai_ma_relations_mata_pel;

alter table siswa 
   drop foreign key fk_siswa_relations_kelas;


alter table absensi 
   drop foreign key fk_absensi_relations_siswa;

alter table absensi 
   drop foreign key fk_absensi_relations_kelas;

alter table absensi 
   drop foreign key fk_absensi_relations_semester;

drop table if exists absensi;


alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_pengatur;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_kelas;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_siswa;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_absensi;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_guru;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_mata_pel;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_sekolah;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_nilai_ma;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_nilai_ek;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_ekstraku;

alter table cetak_rapor 
   drop foreign key fk_cetak_ra_relations_semester;

drop table if exists cetak_rapor;

drop table if exists ekstrakurikuler;


alter table guru 
   drop foreign key fk_guru_relations_kelas;

drop table if exists guru;


alter table kelas 
   drop foreign key fk_kelas_relations_guru;

drop table if exists kelas;

drop table if exists mata_pelajaran;


alter table nilai_ekstrakurikuler 
   drop foreign key fk_nilai_ek_relations_semester;

drop table if exists nilai_ekstrakurikuler;


alter table nilai_mata_pelajaran 
   drop foreign key fk_nilai_ma_relations_semester;

alter table nilai_mata_pelajaran 
   drop foreign key fk_nilai_ma_relations_kelas;

alter table nilai_mata_pelajaran 
   drop foreign key fk_nilai_ma_relations_siswa;

alter table nilai_mata_pelajaran 
   drop foreign key fk_nilai_ma_relations_mata_pel;

drop table if exists nilai_mata_pelajaran;

drop table if exists pengaturan_cetak;

drop table if exists sekolah;

drop table if exists semester;


alter table siswa 
   drop foreign key fk_siswa_relations_kelas;

drop table if exists siswa;

drop table if exists user;

/*==============================================================*/
/* Table: absensi                                               */
/*==============================================================*/
create table absensi
(
   id_absensi           int not null  comment '',
   id_kelas             int  comment '',
   id_siswa             int  comment '',
   id_semester          int  comment '',
   sakit                varchar(10)  comment '',
   izin                 varchar(10)  comment '',
   alpha                varchar(10)  comment '',
   primary key (id_absensi)
);

/*==============================================================*/
/* Table: cetak_rapor                                           */
/*==============================================================*/
create table cetak_rapor
(
   id_cetak_raport      int not null  comment '',
   id_pengaturan_cetak  int  comment '',
   id_absensi           int  comment '',
   id_mata_pelajaran    int  comment '',
   id_semester          int  comment '',
   id_siswa             int  comment '',
   id_ekstrakurikuler   int  comment '',
   id_nilai_ekstrakurikuler int  comment '',
   id_kelas             int  comment '',
   id_nilai_mata_pelajaran int  comment '',
   id_guru              int  comment '',
   id_sekolah           int  comment '',
   primary key (id_cetak_raport)
);

/*==============================================================*/
/* Table: ekstrakurikuler                                       */
/*==============================================================*/
create table ekstrakurikuler
(
   id_ekstrakurikuler   int not null  comment '',
   nama_ekstrakurikuler varchar(50)  comment '',
   primary key (id_ekstrakurikuler)
);

/*==============================================================*/
/* Table: guru                                                  */
/*==============================================================*/
create table guru
(
   id_guru              int not null  comment '',
   id_kelas             int  comment '',
   nama_guru            varchar(150)  comment '',
   jabatan_guru         enum('Kepala Sekolah', 'Guru')  comment '',
   nip_guru             varchar(50)  comment '',
   primary key (id_guru)
);

/*==============================================================*/
/* Table: kelas                                                 */
/*==============================================================*/
create table kelas
(
   id_kelas             int not null  comment '',
   id_guru              int  comment '',
   nama_kelas           varchar(50)  comment '',
   primary key (id_kelas)
);

/*==============================================================*/
/* Table: mata_pelajaran                                        */
/*==============================================================*/
create table mata_pelajaran
(
   id_mata_pelajaran    int not null  comment '',
   nama_mata_pelajaran  varchar(50)  comment '',
   kode_mata_pelajaran  varchar(10)  comment '',
   kelompok_mata_pelajaran varchar(50)  comment '',
   primary key (id_mata_pelajaran)
);

/*==============================================================*/
/* Table: nilai_ekstrakurikuler                                 */
/*==============================================================*/
create table nilai_ekstrakurikuler
(
   id_nilai_ekstrakurikuler int not null  comment '',
   id_semester          int  comment '',
   nilai_ekstrakurikuler enum('A', 'B', 'C', 'D')  comment '',
   primary key (id_nilai_ekstrakurikuler)
);

/*==============================================================*/
/* Table: nilai_mata_pelajaran                                  */
/*==============================================================*/
create table nilai_mata_pelajaran
(
   id_nilai_mata_pelajaran int not null  comment '',
   id_siswa             int  comment '',
   id_kelas             int  comment '',
   id_semester          int  comment '',
   id_mata_pelajaran    int  comment '',
   tp1_lm1              int  comment '',
   tp2_lm1              int  comment '',
   tp3_lm1              int  comment '',
   tp4_lm1              int  comment '',
   sumatif_lm1_         int  comment '',
   tp1_lm2              int  comment '',
   tp2_lm2_             int  comment '',
   tp3_lm2_             int  comment '',
   tp4_lm2_             int  comment '',
   sumatif_lm2_         int  comment '',
   tp1_lm3_             int  comment '',
   tp2_lm3_             int  comment '',
   tp3_lm3_             int  comment '',
   tp4_lm3_             int  comment '',
   sumatif_lm3_         int  comment '',
   tp1_lm4_             int  comment '',
   tp2_lm4_             int  comment '',
   tp3_lm4_             int  comment '',
   tp4_lm4_             int  comment '',
   sumatif_lm4_         int  comment '',
   sumatif_tengah_semester_ int  comment '',
   primary key (id_nilai_mata_pelajaran)
);

/*==============================================================*/
/* Table: pengaturan_cetak                                      */
/*==============================================================*/
create table pengaturan_cetak
(
   id_pengaturan_cetak  int not null  comment '',
   tempat_cetak         varchar(50)  comment '',
   tanggal_cetak        date  comment '',
   primary key (id_pengaturan_cetak)
);

/*==============================================================*/
/* Table: sekolah                                               */
/*==============================================================*/
create table sekolah
(
   id_sekolah           int not null  comment '',
   logo_sekolah         varchar(255)  comment '',
   nama_sekolah         varchar(150)  comment '',
   npsn_sekolah         varchar(50)  comment '',
   nsm_sekolah          varchar(50)  comment '',
   alamat_sekolah       text  comment '',
   no_telepon_sekolah   varchar(20)  comment '',
   kecamatan_sekolah    varchar(50)  comment '',
   kabupaten_atau_kota_sekolah varchar(50)  comment '',
   provinsi             varchar(50)  comment '',
   primary key (id_sekolah)
);

/*==============================================================*/
/* Table: semester                                              */
/*==============================================================*/
create table semester
(
   id_semester          int not null  comment '',
   nama_semester        enum('Ganjil', 'Genap')  comment '',
   tahun_ajaran         varchar(50)  comment '',
   primary key (id_semester)
);

/*==============================================================*/
/* Table: siswa                                                 */
/*==============================================================*/
create table siswa
(
   id_siswa             int not null  comment '',
   id_kelas             int  comment '',
   no_absen_siswa       varchar(10)  comment '',
   no_induk_siswa       varchar(10)  comment '',
   nama_siswa           varchar(150)  comment '',
   jenis_kelamin_siswa  enum('L', 'P')  comment '',
   primary key (id_siswa)
);

/*==============================================================*/
/* Table: user                                                  */
/*==============================================================*/
create table user
(
   id_user              int not null  comment '',
   nama_lengkap_user    varchar(150)  comment '',
   email_user           varchar(150)  comment '',
   no_telepon_user      varchar(20)  comment '',
   username             varchar(20)  comment '',
   password_user        varchar(255)  comment '',
   primary key (id_user)
);

alter table absensi add constraint fk_absensi_relations_siswa foreign key (id_siswa)
      references siswa (id_siswa) on delete restrict on update restrict;

alter table absensi add constraint fk_absensi_relations_kelas foreign key (id_kelas)
      references kelas (id_kelas) on delete restrict on update restrict;

alter table absensi add constraint fk_absensi_relations_semester foreign key (id_semester)
      references semester (id_semester) on delete restrict on update restrict;

alter table cetak_rapor add constraint fk_cetak_ra_relations_pengatur foreign key (id_pengaturan_cetak)
      references pengaturan_cetak (id_pengaturan_cetak) on delete restrict on update restrict;

alter table cetak_rapor add constraint fk_cetak_ra_relations_ekstraku foreign key (id_ekstrakurikuler)
      references ekstrakurikuler (id_ekstrakurikuler) on delete restrict on update restrict;

alter table cetak_rapor add constraint fk_cetak_ra_relations_semester foreign key (id_semester)
      references semester (id_semester) on delete restrict on update restrict;

alter table cetak_rapor add constraint fk_cetak_ra_relations_kelas foreign key (id_kelas)
      references kelas (id_kelas) on delete restrict on update restrict;

alter table cetak_rapor add constraint fk_cetak_ra_relations_siswa foreign key (id_siswa)
      references siswa (id_siswa) on delete restrict on update restrict;

alter table cetak_rapor add constraint fk_cetak_ra_relations_absensi foreign key (id_absensi)
      references absensi (id_absensi) on delete restrict on update restrict;

alter table cetak_rapor add constraint fk_cetak_ra_relations_guru foreign key (id_guru)
      references guru (id_guru) on delete restrict on update restrict;

alter table cetak_rapor add constraint fk_cetak_ra_relations_mata_pel foreign key (id_mata_pelajaran)
      references mata_pelajaran (id_mata_pelajaran) on delete restrict on update restrict;

alter table cetak_rapor add constraint fk_cetak_ra_relations_sekolah foreign key (id_sekolah)
      references sekolah (id_sekolah) on delete restrict on update restrict;

alter table cetak_rapor add constraint fk_cetak_ra_relations_nilai_ma foreign key (id_nilai_mata_pelajaran)
      references nilai_mata_pelajaran (id_nilai_mata_pelajaran) on delete restrict on update restrict;

alter table cetak_rapor add constraint fk_cetak_ra_relations_nilai_ek foreign key (id_nilai_ekstrakurikuler)
      references nilai_ekstrakurikuler (id_nilai_ekstrakurikuler) on delete restrict on update restrict;

alter table guru add constraint fk_guru_relations_kelas foreign key (id_kelas)
      references kelas (id_kelas) on delete restrict on update restrict;

alter table kelas add constraint fk_kelas_relations_guru foreign key (id_guru)
      references guru (id_guru) on delete restrict on update restrict;

alter table nilai_ekstrakurikuler add constraint fk_nilai_ek_relations_semester foreign key (id_semester)
      references semester (id_semester) on delete restrict on update restrict;

alter table nilai_mata_pelajaran add constraint fk_nilai_ma_relations_semester foreign key (id_semester)
      references semester (id_semester) on delete restrict on update restrict;

alter table nilai_mata_pelajaran add constraint fk_nilai_ma_relations_kelas foreign key (id_kelas)
      references kelas (id_kelas) on delete restrict on update restrict;

alter table nilai_mata_pelajaran add constraint fk_nilai_ma_relations_siswa foreign key (id_siswa)
      references siswa (id_siswa) on delete restrict on update restrict;

alter table nilai_mata_pelajaran add constraint fk_nilai_ma_relations_mata_pel foreign key (id_mata_pelajaran)
      references mata_pelajaran (id_mata_pelajaran) on delete restrict on update restrict;

alter table siswa add constraint fk_siswa_relations_kelas foreign key (id_kelas)
      references kelas (id_kelas) on delete restrict on update restrict;

