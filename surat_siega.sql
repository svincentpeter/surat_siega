-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 15, 2025 at 05:35 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `surat_siega`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jenis_tugas`
--

CREATE TABLE `jenis_tugas` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jenis_tugas`
--

INSERT INTO `jenis_tugas` (`id`, `nama`, `created_at`, `updated_at`) VALUES
(1, 'Bimbingan', '2025-07-31 15:48:26', '2025-07-31 15:48:26'),
(2, 'Penelitian', '2025-07-31 15:48:26', '2025-07-31 15:48:26'),
(3, 'Pengabdian', '2025-07-31 15:48:26', '2025-07-31 15:48:26'),
(4, 'Penunjang Almamater', '2025-07-31 15:48:26', '2025-07-31 15:48:26'),
(5, 'Penunjang Administrasi & Manajemen', '2025-07-31 15:48:26', '2025-07-31 15:48:26'),
(6, 'Publikasi', '2025-07-31 15:48:26', '2025-07-31 15:48:26'),
(7, 'TA di Luar Mengajar', '2025-07-31 15:48:26', '2025-07-31 15:48:26'),
(8, 'Lainnya', '2025-08-25 08:59:05', '2025-08-25 08:59:05');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `keputusan_header`
--

CREATE TABLE `keputusan_header` (
  `id` bigint UNSIGNED NOT NULL,
  `nomor` varchar(255) NOT NULL,
  `tanggal_surat` date DEFAULT NULL,
  `signed_at` timestamp NULL DEFAULT NULL,
  `tanggal_asli` date NOT NULL,
  `tentang` varchar(255) NOT NULL,
  `menimbang` json NOT NULL,
  `mengingat` json NOT NULL,
  `memutuskan` longtext NOT NULL,
  `signed_pdf_path` varchar(255) DEFAULT NULL,
  `tembusan` varchar(255) DEFAULT NULL,
  `status_surat` enum('draft','pending','disetujui') NOT NULL,
  `dibuat_oleh` bigint UNSIGNED NOT NULL,
  `penandatangan` bigint UNSIGNED DEFAULT NULL,
  `ttd_config` json DEFAULT NULL,
  `cap_config` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `keputusan_header`
--

INSERT INTO `keputusan_header` (`id`, `nomor`, `tanggal_surat`, `signed_at`, `tanggal_asli`, `tentang`, `menimbang`, `mengingat`, `memutuskan`, `signed_pdf_path`, `tembusan`, `status_surat`, `dibuat_oleh`, `penandatangan`, `ttd_config`, `cap_config`, `created_at`, `updated_at`) VALUES
(1, 'SK-001/FIKOM/2025', NULL, NULL, '2025-06-05', 'Penetapan Visi, Misi, dan Tujuan FIKOM', '\"[\\\"Menimbang bahwa Fakultas Ilmu Komputer perlu menyelaraskan visi dengan standar akreditasi terbaru.\\\",\\\"Menimbang bahwa penyusunan misi harus melibatkan seluruh program studi untuk konsistensi.\\\",\\\"Menimbang bahwa dokumen lama belum mencantumkan tujuan strategis fakultas.\\\"]\"', '\"[\\\"Undang-Undang No. 12 Tahun 2012 tentang Pendidikan Tinggi.\\\",\\\"Standar Nasional Pendidikan Tinggi yang berlaku.\\\",\\\"Pedoman Perencanaan Strategis Internal Universitas.\\\"]\"', 'Menetapkan:\n\n1. Visi Fakultas Ilmu Komputer: Menjadi fakultas unggulan di bidang Informatika dan Sistem Informasi yang inovatif dan berdaya saing internasional.\n\n2. Misi Fakultas Ilmu Komputer:\n   a. Menyelenggarakan pendidikan berkualitas yang merujuk pada kebutuhan pasar kerja dan perkembangan teknologi.\n   b. Menjalin kemitraan strategis dengan industri dan lembaga riset di dalam dan luar negeri.\n   c. Menghasilkan penelitian inovatif di bidang Informatika dan Sistem Informasi.\n   d. Menumbuhkan budaya kewirausahaan di kalangan sivitas akademika.\n\n3. Tujuan Fakultas Ilmu Komputer:\n   a. Meningkatkan mutu lulusan bagi kebutuhan industri dalam 5 tahun ke depan.\n   b. Meningkatkan publikasi ilmiah bereputasi internasional.\n   c. Meningkatkan kerja sama penelitian dengan minimal 10 mitra industri setiap tahun.', NULL, NULL, 'draft', 1, NULL, NULL, NULL, '2025-06-05 08:51:00', '2025-06-19 09:58:37'),
(2, 'SK-002/FIKOM/2025', NULL, NULL, '2025-06-05', 'Penetapan Kurikulum Baru Program Studi TI', '[\"Menimbang bahwa kurikulum lama sudah tidak sesuai perkembangan teknologi terbaru.\", \"Menimbang perlunya revitalisasi mata kuliah agar lulusan siap kerja.\", \"Menimbang rekomendasi akreditasi prodi Tahun 2024.\"]', '[\"Peraturan Menteri Pendidikan dan Kebudayaan RI No. 3 Tahun 2020 tentang Standar Nasional Pendidikan Tinggi.\", \"Hasil Evaluasi LAM-TEKNO Tahun 2024.\", \"Panduan Kurikulum Merdeka Belajar Kampus Merdeka.\"]', 'Menetapkan:\n\n1. Kurikulum Program Studi Teknik Informatika Tahun 2025 mulai berlaku sejak semester ganjil 2025.\n\n2. Kurikulum memuat 10 mata kuliah inti, 5 mata kuliah pilihan keahlian, dan 2 mata kuliah karakter.\n\n3. Dosen pengampu kurikulum bertanggung jawab menyusun silabus, SAP, dan penilaian sesuai kurikulum baru.\n\n4. Keputusan ini berlaku sejak tanggal ditetapkan dan setiap perubahan terbitkan SK lanjutan.', NULL, NULL, 'disetujui', 2, 4, NULL, NULL, '2025-06-05 08:52:00', '2025-06-05 09:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `keputusan_penerima`
--

CREATE TABLE `keputusan_penerima` (
  `id` bigint UNSIGNED NOT NULL,
  `keputusan_id` bigint UNSIGNED NOT NULL,
  `pengguna_id` bigint UNSIGNED NOT NULL,
  `dibaca` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `keputusan_penerima`
--

INSERT INTO `keputusan_penerima` (`id`, `keputusan_id`, `pengguna_id`, `dibaca`) VALUES
(3, 2, 5, 1),
(4, 2, 6, 1),
(5, 1, 4, 0),
(6, 1, 3, 0),
(7, 1, 5, 0),
(8, 1, 6, 0);

-- --------------------------------------------------------

--
-- Table structure for table `keputusan_versi`
--

CREATE TABLE `keputusan_versi` (
  `id` bigint UNSIGNED NOT NULL,
  `header_id` bigint UNSIGNED NOT NULL,
  `versi` int NOT NULL,
  `is_final` tinyint(1) NOT NULL DEFAULT '0',
  `konten_json` json NOT NULL,
  `versi_induk` bigint UNSIGNED DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `keputusan_versi`
--

INSERT INTO `keputusan_versi` (`id`, `header_id`, `versi`, `is_final`, `konten_json`, `versi_induk`, `dibuat_pada`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 0, '{\"misi\": [\"Menyelenggarakan pendidikan berkualitas yang merujuk pada kebutuhan pasar kerja dan perkembangan teknologi.\", \"Menjalin kemitraan strategis dengan industri dan lembaga riset di dalam dan luar negeri.\", \"Menghasilkan penelitian inovatif di bidang Informatika dan Sistem Informasi.\", \"Menumbuhkan budaya kewirausahaan di kalangan sivitas akademika.\"], \"visi\": \"Menjadi fakultas unggulan di bidang Informatika dan Sistem Informasi yang inovatif dan berdaya saing internasional.\", \"tujuan\": [\"Meningkatkan mutu lulusan bagi kebutuhan industri dalam 5 tahun ke depan.\", \"Meningkatkan publikasi ilmiah bereputasi internasional.\", \"Meningkatkan kerja sama penelitian dengan minimal 10 mitra industri setiap tahun.\"]}', NULL, '2025-06-05 08:51:00', NULL, NULL),
(2, 2, 1, 1, '{\"misi\": [\"Menyelenggarakan pendidikan berkualitas yang merujuk pada kebutuhan pasar kerja dan perkembangan teknologi.\", \"Menjalin kemitraan strategis dengan industri dan lembaga riset di dalam dan luar negeri.\", \"Menghasilkan penelitian inovatif di bidang Informatika dan Sistem Informasi.\", \"Menumbuhkan budaya kewirausahaan di kalangan sivitas akademika.\"], \"visi\": \"Menjadi fakultas unggulan di bidang Informatika dan Sistem Informasi yang inovatif dan berdaya saing internasional.\", \"tujuan\": [\"Meningkatkan mutu lulusan bagi kebutuhan industri dalam 5 tahun ke depan.\", \"Meningkatkan publikasi ilmiah bereputasi internasional.\", \"Meningkatkan kerja sama penelitian dengan minimal 10 mitra industri setiap tahun.\"]}', NULL, '2025-06-05 08:52:00', NULL, NULL),
(3, 1, 2, 0, '\"{\\\"menimbang\\\":[\\\"Menimbang bahwa Fakultas Ilmu Komputer perlu menyelaraskan visi dengan standar akreditasi terbaru.\\\",\\\"Menimbang bahwa penyusunan misi harus melibatkan seluruh program studi untuk konsistensi.\\\",\\\"Menimbang bahwa dokumen lama belum mencantumkan tujuan strategis fakultas.\\\"],\\\"mengingat\\\":[\\\"Undang-Undang No. 12 Tahun 2012 tentang Pendidikan Tinggi.\\\",\\\"Standar Nasional Pendidikan Tinggi yang berlaku.\\\",\\\"Pedoman Perencanaan Strategis Internal Universitas.\\\"],\\\"menetapkan\\\":[{\\\"judul\\\":\\\"KESATU\\\",\\\"isi\\\":null}]}\"', 1, '2025-06-19 09:58:37', '2025-06-19 09:58:37', '2025-06-19 09:58:37');

-- --------------------------------------------------------

--
-- Table structure for table `klasifikasi_surat`
--

CREATE TABLE `klasifikasi_surat` (
  `id` bigint UNSIGNED NOT NULL,
  `kode` varchar(255) NOT NULL,
  `deskripsi` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `klasifikasi_surat`
--

INSERT INTO `klasifikasi_surat` (`id`, `kode`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1, 'A.4', 'Program Terpadu Mahasiswa Baru (PTMB)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(2, 'A.4.--', 'Program Terpadu Mahasiswa Baru (PTMB)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(3, 'B.1.1', 'Penawaran Matakuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(4, 'B.1.2', 'Jadwal Kuliah (revisi/pengganti/tambahan)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(5, 'B.1.3', 'Pembatalan Matakuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(6, 'B.1.4', 'Pengisian KRS', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(7, 'B.1.5', 'Kuliah Umum', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(8, 'B.1.6', 'Awal Kuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(9, 'B.1.7', 'Penugasan Perkuliahan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(10, 'B.1.8', 'Praktikum/Laboratorium', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(11, 'B.1.9', 'Kuliah Sisipan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(12, 'B.1.10', 'Akhir Kuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(13, 'B.1.11', 'Pekan Teduh', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(14, 'B.1.12', 'Libur Kuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(15, 'B.1.13', 'Angket evaluasi perkuliahan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(16, 'B.2.1', 'Ujian Tengah Semester (UTS)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(17, 'B.2.2', 'Ujian Akhir Semester (UAS)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(18, 'B.2.3', 'Ujian Sisipan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(19, 'B.2.4', 'Ujian Susulan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(20, 'B.2.5', 'Ujian Pembekalan KKN/KKU/KAPKI', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(21, 'B.2.6', 'Ujian Kertas Karya', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(22, 'B.2.7', 'Ujian Kerja Praktek/Seminar/Proposal/Draf', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(23, 'B.2.8', 'Ujian Skripsi/Pendadaran/Ujian Tahap Akhir/Proyek', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(24, 'B.2.9', 'Ujian Tesis', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(25, 'B.2.10', 'Ujian Disertasi', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(26, 'B.3.1', 'Pendaftaran KKN/KKU/KAPKI/KKUKerja Praktek/Kertas Karya/Skripsi/Tesis/Disertasi', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(27, 'B.3.2', 'Peninjauan/Survey/Data', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(28, 'B.3.3', 'Perijinan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(29, 'B.3.4', 'Pembekalan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(30, 'B.3.5', 'Bimbingan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(31, 'B.3.6', 'Pembatalan/Gugur', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(32, 'B.3.7', 'Perpanjangan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(33, 'B.3.8', 'Perintah Kerja', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(34, 'B.4.1', 'Evaluasi semesteran', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(35, 'B.4.2', 'Evaluasi tahunan/Jumlah SKS yang telah ditempuh', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(36, 'B.4.3', 'Peringatan masa studi', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(37, 'B.4.4', 'Perpanjangan masa studi', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(38, 'B.4.5', 'Sanksi akademik', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(39, 'B.4.6', 'Pemberhentian Status Mahasiswa (DO)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(40, 'B.5.1', 'Pindah Fakultas/Program Studi', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(41, 'B.5.2', 'Pindah dari Perguruan Tinggi lain', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(42, 'B.5.3', 'Pindah ke Perguruan Tinggi lain', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(43, 'B.5.4', 'Mengundurkan diri', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(44, 'B.6.1', 'Mohon Nilai', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(45, 'B.6.2', 'Kirim Nilai', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(46, 'B.6.3', 'Revisi Nilai', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(47, 'B.6.4', 'Hapus Nilai', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(48, 'B.6.5', 'Konversi Nilai', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(49, 'B.6.6', 'Yudisium (Penentuan Nilai Lulus Ujian Sarjana)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(50, 'B.6.7', 'Hasil Studi (KHS)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(51, 'B.6.8', 'Daftar Kumpulan Nilai (Transkrip)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(52, 'B.6.9', 'Pedoman Penilaian', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(53, 'B.7.1', 'Informasi/Penawaran Penelitian', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(54, 'B.7.2', 'Tim Peneliti/Reviewer/Konsultan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(55, 'B.7.3', 'Ijin Penelitian/Survey/Data', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(56, 'B.7.4', 'Usulan Proyek Penelitian', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(57, 'B.7.5', 'Review/Revisi', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(58, 'B.7.6', 'Laporan Hasil Penelitian', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(59, 'B.7.7', 'Publikasi (Seminar/Diskusi/Lokakarya) Hasil Penelitian', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(60, 'B.7.8', 'Pelatihan Pembuatan Proposal', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(61, 'B.7.9', 'Penulisan Ilmiah/Jurnal', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(62, 'B.8.1', 'Informasi/Penawaran Kegiatan Pengabdian', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(63, 'B.8.2', 'Tim Pengabdian/Reviewer/Konsultan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(64, 'B.8.3', 'Ijin Kegiatan Pengabdian/Survey/Data', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(65, 'B.8.4', 'Usulan Kegiatan Pengabdian', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(66, 'B.8.5', 'Review/Revisi/Presentasi Hasil Kegiatan Pengabdian', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(67, 'B.8.6', 'Laporan Hasil Kegiatan Pengabdian', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(68, 'B.8.7', 'Publikasi (Seminar/Diskusi/Lokakarya) Hasil Kegiatan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(69, 'B.8.8', 'Ceramah/Bimbingan/Penyuluhan/Pelatihan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(70, 'B.8.9', 'Pelatihan Pembuatan Proposal', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(71, 'B.8.10', 'Penulisan Ilmiah/Jumat', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(72, 'B.9.1', 'Penetapan Keputusan (SK Kelulusan)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(73, 'B.9.2', 'Lulusan Terbaik/Tercepat', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(74, 'B.9.3', 'Keterangan Lulus', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(75, 'B.9.4', 'Wisuda/Pelepasan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(76, 'B.9.5', 'Ijazah/Bukti Kelulusan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(77, 'B.9.6', 'Legalisasi', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(78, 'B.9.7', 'Keterangan Pengganti Ijazah', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(79, 'B.9.8', 'Penggunaan Gelar', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(80, 'B.9.9', 'Kartu Alumni', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(81, 'B.10.1', 'Pengadaan Buku/Jumat', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(82, 'B.10.2', 'Pengotahan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(83, 'B.10.3', 'Peminjaman', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(84, 'B.10.4', 'Tagihan Buku', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(85, 'B.10.5', 'Bedah/Resensi Buku', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(86, 'B.10.6', 'Pelatihan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(87, 'B.10.7', 'Pameran Buku/Bursa Buku', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(88, 'B.10.8', 'Koleksi', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(89, 'B.10.9', 'Sumbangan Koleksi', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(90, 'B.10.10', 'Stock Opname', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(91, 'B.10.11', 'Statistik', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(92, 'B.10.12', 'Tata Tertib', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(93, 'B.10.13', 'Keanggotaan', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(94, 'B.11.1', 'Kalender Akademik', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(95, 'B.12.1', 'Dispensasi (Perkuliahan/Tugas/Praktikum)', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(96, 'B.13.1', 'Heregistrasi', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(97, 'B.13.2', 'Aktif Kuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(98, 'B.13.3', 'Mahasiswa Asing/Pendengar', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(99, 'B.13.4', 'Cuti Kuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(100, 'B.13.5', 'Sedang Skripsi', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(101, 'B.13.6', 'Pernah Kuliah', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(102, 'B.13.7', 'Double Degree', '2025-08-02 15:41:34', '2025-08-02 15:41:34'),
(103, 'B.13.8', 'Penyerahan ijazah sma/smk/paket c', '2025-08-02 15:41:34', '2025-08-02 15:41:34');

-- --------------------------------------------------------

--
-- Table structure for table `master_kop_surat`
--

CREATE TABLE `master_kop_surat` (
  `id` bigint UNSIGNED NOT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `header_path` varchar(255) DEFAULT NULL,
  `footer_path` varchar(255) DEFAULT NULL,
  `cap_path` varchar(255) DEFAULT NULL,
  `cap_default_width_mm` smallint UNSIGNED NOT NULL DEFAULT '30',
  `cap_opacity` tinyint UNSIGNED NOT NULL DEFAULT '85',
  `cap_offset_x_mm` int NOT NULL DEFAULT '0',
  `cap_offset_y_mm` int NOT NULL DEFAULT '0',
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `mode` varchar(255) NOT NULL DEFAULT 'image',
  `judul_atas` varchar(255) DEFAULT NULL,
  `subjudul` varchar(255) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `telepon` varchar(255) DEFAULT NULL,
  `fax` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo_kiri_path` varchar(255) DEFAULT NULL,
  `logo_kanan_path` varchar(255) DEFAULT NULL,
  `tampilkan_logo_kiri` tinyint(1) NOT NULL DEFAULT '0',
  `tampilkan_logo_kanan` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `master_kop_surat`
--

INSERT INTO `master_kop_surat` (`id`, `unit`, `header_path`, `footer_path`, `cap_path`, `cap_default_width_mm`, `cap_opacity`, `cap_offset_x_mm`, `cap_offset_y_mm`, `updated_by`, `created_at`, `updated_at`, `mode`, `judul_atas`, `subjudul`, `alamat`, `telepon`, `fax`, `email`, `website`, `logo_kiri_path`, `logo_kanan_path`, `tampilkan_logo_kiri`, `tampilkan_logo_kanan`) VALUES
(1, NULL, NULL, NULL, 'kop/mOKxKWXWoH3XMn44zcgyiUpfCBWnoSnxmOa1rcij.png', 30, 85, 0, 0, 1, '2025-08-26 10:50:41', '2025-09-14 01:24:17', 'composed', 'SOEGIJAPRANATA', 'CATHOLIC UNIVERSITY', 'Jl. Pawiyatan Luhur IV/1 Bendan Duwur Semarang 50234', '(024) 8441555, 85050003', '(024) 8415429 – 8454265', 'unika@unika.ac.id', 'https://www.unika.ac.id', 'kop/phOkZpewAN19m2gs4MAxcsUHj15eqqRJijNv9Q3y.jpg', 'kop/yIn0CbH3EdJGKjw7Kk23em9jZr1OFApo6EfQ3WXt.png', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_06_02_053923_create_peran_table', 1),
(2, '2025_06_02_053924_create_pengguna_table', 1),
(3, '2025_06_02_053924_create_tugas_header_table', 1),
(4, '2025_06_02_053924_create_tugas_versi_table', 1),
(5, '2025_06_02_053925_create_tugas_log_table', 1),
(6, '2025_06_02_053925_create_tugas_penerima_table', 1),
(7, '2025_06_02_053926_create_agenda_surat_keluar_table', 1),
(8, '2025_06_02_053926_create_notifikasi_table', 1),
(9, '2025_06_02_054733_create_sessions_table', 1),
(10, '2025_06_02_054840_create_cache_table', 1),
(14, '2025_06_05_132456_create_keputusan_header_table', 2),
(15, '2025_06_05_132500_create_keputusan_versi_table', 2),
(16, '2025_06_05_132504_create_keputusan_penerima_table', 2),
(17, '2025_07_31_093533_create_jenis_tugas_table', 3),
(18, '2025_08_01_060824_create_sub_tugas_table', 4),
(19, '2025_08_01_061002_create_tugas_detail_table', 4),
(20, '2025_08_01_061108_update_tugas_header_and_penerima', 5),
(21, '2025_08_01_072249_add_kode_surat_and_bulan_to_tugas_header', 6),
(22, '2025_08_02_043447_add_detail_tugas_to_tugas_header_table', 7),
(23, '2025_08_02_125424_ubah_struktur_tugas_penerima', 8),
(24, '2025_08_02_152651_drop_tugas_versi_table', 9),
(25, '2025_08_02_153206_remove_ijin_tidak_presensi_from_tugas_header_table', 10),
(26, '2025_08_02_153501_create_klasifikasi_surats_table', 11),
(27, '2025_08_02_153851_add_klasifikasi_surat_id_to_tugas_header_table', 12),
(28, '2025_08_25_085953_fix_detail_tugas_notnull', 13),
(29, '2025_08_25_091128_create_nomor_surat_counters_table', 14),
(30, '2025_08_25_100353_add_indexes_surat_tugas', 15),
(31, '2025_08_25_131625_add_tanggal_surat_to_keputusan_header_table', 16),
(32, '2025_08_26_050351_create_jobs_table', 17),
(33, '2025_08_26_060301_create_master_kop_surat_table', 18),
(34, '2025_08_26_155428_extend_master_kop_surat_for_structured_header', 19),
(35, '2025_09_03_191500_create_user_signatures_table', 20),
(36, '2025_09_03_191502_add_signature_fields_to_tugas_and_keputusan', 20),
(37, '2025_09_03_191502_extend_master_kop_surat_add_stamp_layout', 20),
(38, '2025_09_15_025626_add_sign_dimensions_to_tugas_header_table', 21);

-- --------------------------------------------------------

--
-- Table structure for table `nomor_surat_counters`
--

CREATE TABLE `nomor_surat_counters` (
  `id` bigint UNSIGNED NOT NULL,
  `kode_surat` varchar(255) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `bulan_romawi` varchar(255) NOT NULL,
  `tahun` int NOT NULL,
  `last_number` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` bigint UNSIGNED NOT NULL,
  `pengguna_id` bigint UNSIGNED NOT NULL,
  `tipe` varchar(255) NOT NULL,
  `referensi_id` int NOT NULL,
  `pesan` varchar(255) NOT NULL,
  `dibaca` tinyint(1) NOT NULL DEFAULT '0',
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifikasi`
--

INSERT INTO `notifikasi` (`id`, `pengguna_id`, `tipe`, `referensi_id`, `pesan`, `dibaca`, `dibuat_pada`) VALUES
(1, 3, 'surat_tugas', 9, 'Surat Tugas 001/B.10.6/TG/UNIKA/IX/2025 menunggu persetujuan Anda.', 0, '2025-09-13 23:47:54'),
(2, 10, 'surat_tugas', 7, 'Surat Tugas 006/B.3.5/TG/UNIKA/VIII/2025 menunggu persetujuan Anda.', 0, '2025-09-14 06:55:01'),
(3, 1, 'surat_tugas', 11, 'Surat Tugas 003/B.8.2/TG/UNIKA/IX/2025 telah disetujui.', 0, '2025-09-14 22:34:13'),
(4, 16, 'surat_tugas', 11, 'Anda terdaftar sebagai penerima pada Surat Tugas 003/B.8.2/TG/UNIKA/IX/2025.', 0, '2025-09-14 22:34:13'),
(5, 17, 'surat_tugas', 11, 'Anda terdaftar sebagai penerima pada Surat Tugas 003/B.8.2/TG/UNIKA/IX/2025.', 0, '2025-09-14 22:34:13'),
(6, 18, 'surat_tugas', 11, 'Anda terdaftar sebagai penerima pada Surat Tugas 003/B.8.2/TG/UNIKA/IX/2025.', 0, '2025-09-14 22:34:13');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id` bigint UNSIGNED NOT NULL,
  `email` varchar(100) NOT NULL,
  `sandi_hash` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `peran_id` bigint UNSIGNED NOT NULL,
  `status` enum('aktif','tidak_aktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `last_activity` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id`, `email`, `sandi_hash`, `nama_lengkap`, `jabatan`, `peran_id`, `status`, `created_at`, `updated_at`, `last_activity`, `deleted_at`, `remember_token`) VALUES
(1, 'agustina.anggitasari@unika.ac.id', '$2y$12$0rYDf0RqcBpaABHw3vaOxe3LV6UxLazy9R85vBmmwA8juagm6Xadq', 'AGUSTINA ALAM ANGGITASARI, SE., MM', 'Ka. TU Fakultas Ilmu Komputer', 1, 'aktif', '2025-04-22 03:15:27', '2025-09-14 13:55:06', '2025-09-14 13:55:06', NULL, '4XHwMMVvLJRGLrfe5oDw6DrsmiNxOklZ4oMrpBXWrbaTOLRFKNIEIN2splnC'),
(2, 'kariyani.spd@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'KARIYANI, S.Pd', 'Ka. TU Fakultas Ilmu Komputer', 1, 'aktif', '2025-04-22 03:15:27', '2025-08-01 23:28:10', NULL, NULL, NULL),
(3, 'bernhardinus.harnadi@unika.ac.id', '$2y$12$rr.ntE7OagwdG25kLxSLwOnZwIaq72oImrbM8jXOkn6AEM62QRIY2', 'Prof. BERNARDINUS HARNADI, ST., MT., Ph.D.', NULL, 3, 'aktif', '2025-04-22 03:15:27', '2025-09-15 05:34:47', '2025-09-15 05:34:47', NULL, NULL),
(4, 'muh.khudori@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'MUH KHUDORI', NULL, 6, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:39:31', NULL, NULL, NULL),
(5, 'paulus.sapto@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'PAULUS SAPTO NUGROHO', NULL, 6, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:39:36', NULL, NULL, NULL),
(6, 'bambang.setiawan@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'BAMBANG SETIAWAN, ST', NULL, 6, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:39:39', NULL, NULL, NULL),
(7, 'erdhi.nugroho@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'ERDHI WIDYARTO NUGROHO, ST., MT', NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:39:43', NULL, NULL, NULL),
(8, 'fx.hendra@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'FX. HENDRA PRASETYA, ST, MT', NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:39:48', NULL, NULL, NULL),
(9, 'tecla.chandrawati@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'Dr. TECLA BRENDA CHANDRAWATI, S.T., MT', NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:39:51', NULL, NULL, NULL),
(10, 'ridwan.sanjaya@unika.ac.id', '$2y$12$VRlxXvgiT0gdC3mVx0vp6Oct3Q/VPnmvACYjDz3n.DKotAIkG1QrS', 'Prof. Dr. F. RIDWAN SANJAYA, SE.,S.KOM., MS.IEC', NULL, 2, 'aktif', '2025-04-22 03:15:27', '2025-09-15 04:42:28', '2025-09-15 04:42:28', NULL, NULL),
(11, 'alb.dwiw@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'ALBERTUS DWIYOGA WIDIANTORO, S.Kom., M.Kom', NULL, 4, 'aktif', '2025-04-22 03:15:27', NULL, NULL, NULL, NULL),
(12, 'agus.cahyo@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'AGUS CAHYO NUGROHO, S.Kom., M.T', NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:02', NULL, NULL, NULL),
(13, 'andre.pamudji@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'ANDRE KURNIAWAN PAMUDJI, S.Kom., M.Ling', NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:10', NULL, NULL, NULL),
(14, 'stephan.swastini@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'STEPHAN INGRITT SWASTINI DEWI, S.Kom., MBA', NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:13', NULL, NULL, NULL),
(15, 'hironimus.leong@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'HIRONIMUS LEONG, S.Kom., M.Com', NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:15', NULL, NULL, NULL),
(16, 'rosita.herawati@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'ROSITA HERAWATI, ST., MT', NULL, 4, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:41:03', NULL, NULL, NULL),
(17, 'yulianto.putranto@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'Dr. YULIANTO TEDJO PUTRANTO, ST., MT', NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:21', NULL, NULL, NULL),
(18, 'shinta.wahyuningrum@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'SHINTA ESTRI WAHYUNINGRUM, S.Si., M.Cs', NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:25', NULL, NULL, NULL),
(19, 'setiawan.aji@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'R. SETIAWAN AJI NUGROHO, ST. M.CompIT, Ph.D', NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:28', NULL, NULL, NULL),
(20, 'dwi.setianto@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'Y.B. DWI SETIANTO, ST., M.Cs(CCNA)', NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:31', NULL, NULL, NULL),
(21, 'yonathan.santosa@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'YONATHAN PURBO SANTOSA, S.Kom., M.Sc', NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:47', NULL, NULL, NULL),
(22, 'henoch.christanto@unika.ac.id', '$2b$12$1Ps4Q4F7MLPQgfa86NQIGOuHy7pjiFiLZA.4Bp3qUhPYTfOvwpUfS', 'HENOCH JULI CHRISTANTO, S.Kom., M.Kom', NULL, 5, 'aktif', '2025-04-22 03:15:27', '2025-08-02 16:40:52', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `peran`
--

CREATE TABLE `peran` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `peran`
--

INSERT INTO `peran` (`id`, `nama`, `deskripsi`, `dibuat_pada`) VALUES
(1, 'admin_tu', 'Administrator Tata Usaha Fakultas', '2025-06-01 22:53:10'),
(2, 'dekan', 'Dekan Fakultas', '2025-06-01 22:53:10'),
(3, 'wakil_dekan', 'Wakil Dekan Fakultas', '2025-06-01 22:53:10'),
(4, 'kaprodi', 'Kepala Program Studi', '2025-06-01 22:53:10'),
(5, 'dosen', 'Dosen Pengajar', '2025-08-02 16:38:34'),
(6, 'tendik', 'Tenaga Kependidikan', '2025-08-02 16:38:34');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('pTt23h8wag1idzrybL8u7yjBpEC9A8ZoXDLvAsd9', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 OPR/121.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSkdIM3lsakdWVFljZUJaWU9TUHNiTDNRdGY4aE1TVjFuQmVjcWhOZCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zdXJhdF90dWdhcy83L3ByZXZpZXc/dj0xNzU3ODU4OTM2Ijt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mzt9', 1757914487);

-- --------------------------------------------------------

--
-- Table structure for table `sub_tugas`
--

CREATE TABLE `sub_tugas` (
  `id` bigint UNSIGNED NOT NULL,
  `jenis_tugas_id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sub_tugas`
--

INSERT INTO `sub_tugas` (`id`, `jenis_tugas_id`, `nama`, `created_at`, `updated_at`) VALUES
(1, 1, 'Koordinator kelompok MK/Rumpun/Konsorsium', NULL, NULL),
(2, 1, 'Koordinator MK', NULL, NULL),
(3, 1, 'Koordinator Tugas MK', NULL, NULL),
(4, 1, 'Bimbingan Mahasiswa/Akademik', NULL, NULL),
(5, 7, 'Pendampingan dosen dalam KKL', NULL, NULL),
(6, 7, 'DPL untuk KKN, KKU, KAPKI, atau lainnya yang setara', NULL, NULL),
(7, 5, 'Reviewer Kenaikan Jabatan Fungsional Lektor Kepala', NULL, NULL),
(8, 5, 'Reviewer Kenaikan Jabatan Fungsional Guru Besar', NULL, NULL),
(9, 5, 'Reviewer Kenaikan Jabatan Fungsional Asisten Ahli', NULL, NULL),
(10, 5, 'Reviewer Kenaikan Jabatan Fungsional Lektor', NULL, NULL),
(11, 5, 'Asesor BKD', NULL, NULL),
(12, 5, 'Validator BKD', NULL, NULL),
(13, 3, 'Reviewer Penelitian dan Pengabdian di lingkungan Unika', NULL, NULL),
(14, 6, 'Reviewer Jurnal Nasional', NULL, NULL),
(15, 6, 'Reviewer Jurnal Internasional', NULL, NULL),
(16, 8, 'Lainnya', '2025-08-25 08:59:05', '2025-08-25 08:59:05');

-- --------------------------------------------------------

--
-- Table structure for table `tugas_detail`
--

CREATE TABLE `tugas_detail` (
  `id` bigint UNSIGNED NOT NULL,
  `sub_tugas_id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tugas_detail`
--

INSERT INTO `tugas_detail` (`id`, `sub_tugas_id`, `nama`, `created_at`, `updated_at`) VALUES
(1, 1, 'Jumlah kelompok dikoordinasi', NULL, NULL),
(2, 2, 'Jumlah MK dikoordinasi', NULL, NULL),
(3, 3, 'Jumlah Tugas MK dikoordinasi', NULL, NULL),
(4, 4, 'Jumlah mahasiswa dibimbing', NULL, NULL),
(5, 5, 'Jumlah pendampingan KKL', NULL, NULL),
(6, 6, 'Jumlah DPL/KKN/KAPKI', NULL, NULL),
(7, 7, 'Jumlah usulan penelitian/pengabdian direview', NULL, NULL),
(8, 8, 'Jumlah artikel jurnal nasional direview', NULL, NULL),
(9, 9, 'Jumlah artikel jurnal internasional direview', NULL, NULL),
(10, 10, 'Jumlah usulan kenaikan jabatan Lektor Kepala', NULL, NULL),
(11, 11, 'Jumlah usulan kenaikan jabatan Guru Besar', NULL, NULL),
(12, 12, 'Jumlah usulan kenaikan jabatan Asisten Ahli', NULL, NULL),
(13, 13, 'Jumlah usulan kenaikan jabatan Lektor', NULL, NULL),
(14, 14, 'Jumlah asesor BKD', NULL, NULL),
(15, 15, 'Jumlah validator BKD', NULL, NULL),
(16, 16, 'Lainnya', '2025-08-25 08:59:05', '2025-08-25 08:59:05');

-- --------------------------------------------------------

--
-- Table structure for table `tugas_header`
--

CREATE TABLE `tugas_header` (
  `id` bigint UNSIGNED NOT NULL,
  `nomor` varchar(255) NOT NULL,
  `tanggal_asli` datetime DEFAULT NULL,
  `status_surat` enum('draft','pending','disetujui') NOT NULL,
  `nomor_surat` varchar(255) DEFAULT NULL,
  `tanggal_surat` date DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `signed_at` timestamp NULL DEFAULT NULL,
  `dibuat_oleh` bigint UNSIGNED NOT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dikunci_pada` timestamp NULL DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `signed_pdf_path` varchar(255) DEFAULT NULL,
  `nomor_status` enum('reserved','locked') NOT NULL,
  `nama_pembuat` bigint UNSIGNED NOT NULL,
  `no_bin` varchar(255) DEFAULT NULL,
  `tahun` int DEFAULT NULL,
  `semester` varchar(255) DEFAULT NULL,
  `no_surat_manual` varchar(255) DEFAULT NULL,
  `nama_umum` varchar(255) DEFAULT NULL,
  `asal_surat` bigint UNSIGNED NOT NULL,
  `status_penerima` enum('dosen','tendik','mahasiswa') DEFAULT NULL,
  `jenis_tugas` varchar(255) DEFAULT NULL,
  `tugas` varchar(255) NOT NULL,
  `detail_tugas` text,
  `detail_tugas_id` bigint UNSIGNED NOT NULL,
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `tempat` varchar(255) DEFAULT NULL,
  `redaksi_pembuka` text,
  `penutup` varchar(255) DEFAULT NULL,
  `tembusan` text,
  `penandatangan` bigint UNSIGNED DEFAULT NULL,
  `ttd_config` json DEFAULT NULL,
  `cap_config` json DEFAULT NULL,
  `ttd_w_mm` smallint UNSIGNED DEFAULT NULL COMMENT 'Lebar TTD dalam mm',
  `cap_w_mm` smallint UNSIGNED DEFAULT NULL COMMENT 'Lebar Cap dalam mm',
  `cap_opacity` decimal(3,2) DEFAULT NULL COMMENT 'Opacity Cap (0.00 - 1.00)',
  `next_approver` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `kode_surat` varchar(255) DEFAULT NULL,
  `bulan` varchar(255) DEFAULT NULL,
  `klasifikasi_surat_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tugas_header`
--

INSERT INTO `tugas_header` (`id`, `nomor`, `tanggal_asli`, `status_surat`, `nomor_surat`, `tanggal_surat`, `submitted_at`, `signed_at`, `dibuat_oleh`, `dibuat_pada`, `dikunci_pada`, `file_path`, `signed_pdf_path`, `nomor_status`, `nama_pembuat`, `no_bin`, `tahun`, `semester`, `no_surat_manual`, `nama_umum`, `asal_surat`, `status_penerima`, `jenis_tugas`, `tugas`, `detail_tugas`, `detail_tugas_id`, `waktu_mulai`, `waktu_selesai`, `tempat`, `redaksi_pembuka`, `penutup`, `tembusan`, `penandatangan`, `ttd_config`, `cap_config`, `ttd_w_mm`, `cap_w_mm`, `cap_opacity`, `next_approver`, `created_at`, `updated_at`, `kode_surat`, `bulan`, `klasifikasi_surat_id`) VALUES
(1, 'ST-001/UNIKA/2025', '2025-05-01 00:00:00', 'draft', NULL, NULL, NULL, NULL, 1, '2025-06-01 22:53:10', NULL, NULL, NULL, 'reserved', 1, NULL, 2025, 'Genap', NULL, 'Surat Tugas Kegiatan 1', 1, 'dosen', 'Seminar', '', NULL, 16, '2025-05-10 00:00:00', '2025-05-12 00:00:00', 'Aula UNIKA', NULL, 'Demikian, terima kasih.', NULL, 4, NULL, NULL, NULL, NULL, NULL, 3, '2025-06-01 22:53:10', '2025-06-01 22:53:10', NULL, NULL, NULL),
(2, 'ST-002/UNIKA/2025', '2025-06-01 00:00:00', 'disetujui', '002/UNIKA/2025', '2025-06-01', '2025-06-02 05:53:10', NULL, 2, '2025-06-01 22:53:10', '2025-06-01 22:53:10', NULL, NULL, 'locked', 2, NULL, 2025, 'Genap', NULL, 'Surat Tugas Kegiatan 2', 2, 'tendik', 'Pelatihan', '', NULL, 16, '2025-06-10 00:00:00', '2025-06-12 00:00:00', 'Ruang Rapat', NULL, 'Harap dilaksanakan sebaik-baiknya.', NULL, 3, NULL, NULL, NULL, NULL, NULL, 4, '2025-06-01 22:53:10', '2025-06-01 22:53:10', NULL, NULL, NULL),
(4, '002/TG/UNIKA/II/2025', '2025-07-31 00:00:00', 'draft', NULL, NULL, NULL, NULL, 1, '2025-07-31 11:08:30', NULL, NULL, NULL, 'reserved', 1, NULL, 2025, 'Genap', NULL, NULL, 10, 'dosen', 'Bimbingan', 'Koordinator kelompok MK/Rumpun/Konsorsium', NULL, 16, '2025-07-31 00:00:00', '2025-07-31 00:00:00', 'Jogja', 'Test', NULL, NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-31 04:08:30', '2025-08-01 21:08:10', NULL, NULL, NULL),
(5, '003/TG/UNIKA/II/2025', '2025-07-31 00:00:00', 'pending', NULL, NULL, '2025-07-31 14:49:20', NULL, 1, '2025-07-31 11:44:02', NULL, NULL, NULL, 'reserved', 1, 'FIKOM/006', 2025, 'Genap', NULL, 'Surat Tugas Kegiatan 3', 4, 'dosen', 'Seminar', '', NULL, 16, '2025-07-31 00:00:00', '2025-07-31 00:00:00', 'Aula UNIKA', NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, NULL, 4, '2025-07-31 04:44:02', '2025-07-31 07:49:20', NULL, NULL, NULL),
(6, '001/TG/UNIKA/I/2025', '2025-08-01 09:30:10', 'disetujui', NULL, '2025-09-04', '2025-08-01 09:30:10', '2025-09-04 00:20:32', 1, '2025-08-01 09:30:10', NULL, NULL, 'private/surat_tugas/signed/6.pdf', 'reserved', 1, NULL, 2025, 'Ganjil', NULL, NULL, 10, 'dosen', 'Bimbingan', 'Koordinator kelompok MK/Rumpun/Konsorsium', NULL, 16, '2025-08-01 09:29:00', '2025-08-01 11:29:00', NULL, NULL, NULL, NULL, 10, '{\"path\": \"private/ttd/10.png\", \"show\": true, \"offset_x\": -38, \"offset_y\": 72, \"width_mm\": 51, \"height_mm\": 22}', '{\"path\": \"kop/mOKxKWXWoH3XMn44zcgyiUpfCBWnoSnxmOa1rcij.png\", \"show\": true, \"opacity\": 85, \"offset_x\": 2, \"offset_y\": 70, \"width_mm\": 30}', NULL, NULL, NULL, 10, '2025-08-01 02:30:10', '2025-09-04 00:20:35', NULL, NULL, NULL),
(7, '006/B.3.5/TG/UNIKA/VIII/2025', '2025-08-02 17:30:55', 'disetujui', NULL, '2025-08-01', '2025-09-14 13:55:01', '2025-09-14 07:08:54', 1, '2025-08-02 17:30:55', NULL, NULL, 'private/surat_tugas/signed/7.pdf', 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Bimbingan', 10, 'dosen', 'Bimbingan', 'Bimbingan Mahasiswa/Akademik', '<p><strong>Keren</strong></p>', 16, '2025-08-02 17:29:00', '2025-08-02 19:29:00', 'HC Lt 8', 'Test', NULL, NULL, 10, '{\"path\": \"private/ttd/10.png\", \"show\": true, \"offset_x\": 118, \"offset_y\": 17, \"width_mm\": 35, \"height_mm\": 15, \"base_top_mm\": 20, \"base_left_mm\": 15}', '{\"path\": \"kop/mOKxKWXWoH3XMn44zcgyiUpfCBWnoSnxmOa1rcij.png\", \"show\": true, \"opacity\": 85, \"offset_x\": 102, \"offset_y\": 14, \"width_mm\": 30, \"base_top_mm\": 15, \"base_left_mm\": 35}', NULL, NULL, NULL, 10, '2025-08-02 10:30:55', '2025-09-14 07:08:56', NULL, 'VIII', 30),
(8, '010/B.10.1/TG/UNIKA/VIII/2025', '2025-08-03 07:57:06', 'disetujui', NULL, '2025-09-03', '2025-08-03 07:57:06', '2025-09-03 13:39:16', 1, '2025-08-03 07:57:06', NULL, NULL, 'private/surat_tugas/signed/8.pdf', 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Bimbingan', 10, 'tendik', 'Bimbingan', 'Koordinator Tugas MK', '<p>WOW</p>', 16, '2025-08-03 07:56:00', '2025-08-03 09:56:00', NULL, 'Coba', 'Coba', NULL, 10, NULL, NULL, NULL, NULL, NULL, 10, '2025-08-03 00:57:06', '2025-09-03 13:39:18', NULL, 'VIII', 81),
(9, '001/B.10.6/TG/UNIKA/IX/2025', NULL, 'disetujui', NULL, '2025-09-14', NULL, '2025-09-14 01:27:19', 1, '2025-09-14 06:47:54', NULL, NULL, 'private/surat_tugas/signed/9.pdf', 'reserved', 1, NULL, 2025, NULL, NULL, 'Surat Dekan', 3, 'dosen', 'Pengabdian', 'Reviewer Penelitian dan Pengabdian di lingkungan Unika', NULL, 13, '2025-09-14 06:46:00', '2025-09-14 08:46:00', 'Ruang Teater TA', 'Sehubung', 'Demikian', NULL, 3, '{\"path\": \"private/ttd/3.png\", \"show\": true, \"offset_x\": -33, \"offset_y\": 77, \"width_mm\": 35, \"height_mm\": 15, \"base_top_mm\": 205, \"base_left_mm\": 108}', '{\"path\": \"kop/mOKxKWXWoH3XMn44zcgyiUpfCBWnoSnxmOa1rcij.png\", \"show\": true, \"opacity\": 85, \"offset_x\": 0, \"offset_y\": 68, \"width_mm\": 30, \"base_top_mm\": 185, \"base_left_mm\": 125}', NULL, NULL, NULL, 3, '2025-09-13 23:47:54', '2025-09-14 01:27:22', NULL, 'IX', 86),
(10, '002/B.7.2/TG/UNIKA/IX/2025', NULL, 'pending', NULL, NULL, '2025-09-15 12:04:37', NULL, 1, '2025-09-15 05:04:37', NULL, NULL, NULL, 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Penugasan Tim Reviewer Jurnal Internal', 1, NULL, 'Penelitian', 'Reviewer Kenaikan Jabatan Fungsional Lektor', NULL, 13, '2025-09-20 08:00:00', '2025-10-20 17:00:00', 'Fakultas Ilmu Komputer', NULL, NULL, NULL, 10, NULL, NULL, NULL, NULL, NULL, 10, '2025-09-15 05:04:37', '2025-09-15 05:04:37', NULL, 'IX', 54),
(11, '003/B.8.2/TG/UNIKA/IX/2025', NULL, 'disetujui', NULL, '2025-09-15', '2025-09-15 12:04:37', '2025-09-14 22:34:11', 1, '2025-09-15 05:04:37', NULL, NULL, 'private/surat_tugas/signed/11_.pdf', 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Penugasan Panitia Pengabdian Masyarakat', 1, NULL, 'Pengabdian', 'Validator BKD', NULL, 15, '2025-09-22 08:00:00', '2025-09-22 17:00:00', 'Desa Binaan ABC', NULL, NULL, NULL, 3, NULL, NULL, 45, 38, 0.70, 3, '2025-09-15 05:04:37', '2025-09-14 22:34:13', NULL, 'IX', 63),
(12, '004/B.9.4/TG/UNIKA/IX/2025', NULL, 'disetujui', NULL, '2025-09-12', '2025-09-15 12:04:37', '2025-09-12 03:00:00', 1, '2025-09-15 05:04:37', NULL, NULL, NULL, 'reserved', 1, NULL, 2025, 'Ganjil', NULL, 'Penugasan Panitia Wisuda', 1, NULL, 'Lainnya', 'Lainnya', NULL, 16, '2025-09-15 08:00:00', '2025-09-20 17:00:00', 'Auditorium Albertus', NULL, NULL, NULL, 3, NULL, NULL, 45, 38, 0.90, NULL, '2025-09-15 05:04:37', '2025-09-15 05:04:37', NULL, 'IX', 75);

-- --------------------------------------------------------

--
-- Table structure for table `tugas_log`
--

CREATE TABLE `tugas_log` (
  `id` bigint UNSIGNED NOT NULL,
  `tugas_id` bigint UNSIGNED NOT NULL,
  `status_lama` varchar(255) DEFAULT NULL,
  `status_baru` varchar(255) DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tugas_log`
--

INSERT INTO `tugas_log` (`id`, `tugas_id`, `status_lama`, `status_baru`, `user_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(2, 4, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-31 04:08:30'),
(3, 5, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-31 04:44:02'),
(17, 5, 'draft', 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-31 07:49:20'),
(18, 6, NULL, 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-01 02:30:10'),
(19, 7, NULL, 'draft', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 10:30:55'),
(20, 8, NULL, 'pending', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-03 00:57:06');

-- --------------------------------------------------------

--
-- Table structure for table `tugas_penerima`
--

CREATE TABLE `tugas_penerima` (
  `id` bigint UNSIGNED NOT NULL,
  `tugas_id` bigint UNSIGNED NOT NULL,
  `pengguna_id` bigint UNSIGNED DEFAULT NULL,
  `nama_penerima` varchar(255) NOT NULL,
  `jabatan_penerima` varchar(255) DEFAULT NULL,
  `penerima_key` varchar(300) DEFAULT NULL,
  `dibaca` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tugas_penerima`
--

INSERT INTO `tugas_penerima` (`id`, `tugas_id`, `pengguna_id`, `nama_penerima`, `jabatan_penerima`, `penerima_key`, `dibaca`) VALUES
(1, 1, 5, '', NULL, 'I#5', 0),
(2, 1, 6, '', NULL, 'I#6', 0),
(3, 2, 5, '', NULL, 'I#5', 1),
(4, 2, 4, '', NULL, 'I#4', 1),
(9, 5, 6, '', NULL, 'I#6', 0),
(12, 6, 4, '', NULL, 'I#4', 0),
(13, 4, 6, '', NULL, 'I#6', 0),
(14, 4, 19, '', NULL, 'I#19', 0),
(17, 8, 4, '', 'Tenaga Kependidikan', 'I#4', 0),
(21, 9, 10, '', NULL, 'I#10', 0),
(24, 7, 3, '', 'Wakil Dekan Fakultas', 'I#3', 0),
(25, 10, 8, '', NULL, 'I#8', 0),
(26, 10, 9, '', NULL, 'I#9', 0),
(27, 11, 16, '', NULL, 'I#16', 0),
(28, 11, 17, '', NULL, 'I#17', 0),
(29, 11, 18, '', NULL, 'I#18', 0),
(30, 12, 4, '', NULL, 'I#4', 0),
(31, 12, 5, '', NULL, 'I#5', 0);

--
-- Triggers `tugas_penerima`
--
DELIMITER $$
CREATE TRIGGER `trg_tugas_penerima_bi` BEFORE INSERT ON `tugas_penerima` FOR EACH ROW BEGIN
  -- Normalisasi internal/eksternal
  IF NEW.pengguna_id IS NOT NULL THEN
    SET NEW.nama_penerima = '';  -- internal tak perlu nama manual
  ELSE
    IF NEW.nama_penerima IS NULL OR TRIM(NEW.nama_penerima) = '' THEN
      SET NEW.nama_penerima = 'TANPA NAMA';
    END IF;
  END IF;

  -- Bangun penerima_key
  IF NEW.pengguna_id IS NOT NULL THEN
    SET NEW.penerima_key = CONCAT('I#', NEW.pengguna_id);
  ELSE
    SET NEW.penerima_key = CONCAT('E#', LOWER(TRIM(NEW.nama_penerima)));
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_tugas_penerima_bu` BEFORE UPDATE ON `tugas_penerima` FOR EACH ROW BEGIN
  -- Normalisasi internal/eksternal
  IF NEW.pengguna_id IS NOT NULL THEN
    SET NEW.nama_penerima = '';
  ELSE
    IF NEW.nama_penerima IS NULL OR TRIM(NEW.nama_penerima) = '' THEN
      SET NEW.nama_penerima = 'TANPA NAMA';
    END IF;
  END IF;

  -- Bangun penerima_key
  IF NEW.pengguna_id IS NOT NULL THEN
    SET NEW.penerima_key = CONCAT('I#', NEW.pengguna_id);
  ELSE
    SET NEW.penerima_key = CONCAT('E#', LOWER(TRIM(NEW.nama_penerima)));
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_signatures`
--

CREATE TABLE `user_signatures` (
  `id` bigint UNSIGNED NOT NULL,
  `pengguna_id` bigint UNSIGNED NOT NULL,
  `ttd_path` varchar(255) NOT NULL,
  `default_width_mm` smallint UNSIGNED NOT NULL DEFAULT '35',
  `default_height_mm` smallint UNSIGNED NOT NULL DEFAULT '15',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_signatures`
--

INSERT INTO `user_signatures` (`id`, `pengguna_id`, `ttd_path`, `default_width_mm`, `default_height_mm`, `created_at`, `updated_at`) VALUES
(1, 10, 'private/ttd/10.png', 35, 15, '2025-09-03 13:32:08', '2025-09-03 13:32:08'),
(2, 3, 'private/ttd/3.png', 35, 15, '2025-09-14 01:26:47', '2025-09-14 01:26:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `jenis_tugas`
--
ALTER TABLE `jenis_tugas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama` (`nama`),
  ADD KEY `idx_nama` (`nama`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `keputusan_header`
--
ALTER TABLE `keputusan_header`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `keputusan_header_nomor_unique` (`nomor`),
  ADD KEY `keputusan_header_dibuat_oleh_foreign` (`dibuat_oleh`),
  ADD KEY `keputusan_header_penandatangan_foreign` (`penandatangan`);

--
-- Indexes for table `keputusan_penerima`
--
ALTER TABLE `keputusan_penerima`
  ADD PRIMARY KEY (`id`),
  ADD KEY `keputusan_penerima_keputusan_id_foreign` (`keputusan_id`),
  ADD KEY `keputusan_penerima_pengguna_id_foreign` (`pengguna_id`);

--
-- Indexes for table `keputusan_versi`
--
ALTER TABLE `keputusan_versi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `keputusan_versi_header_id_foreign` (`header_id`),
  ADD KEY `keputusan_versi_versi_induk_foreign` (`versi_induk`);

--
-- Indexes for table `klasifikasi_surat`
--
ALTER TABLE `klasifikasi_surat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `klasifikasi_surat_kode_unique` (`kode`);

--
-- Indexes for table `master_kop_surat`
--
ALTER TABLE `master_kop_surat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nomor_surat_counters`
--
ALTER TABLE `nomor_surat_counters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_counter_scope` (`kode_surat`,`unit`,`bulan_romawi`,`tahun`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifikasi_pengguna_id_foreign` (`pengguna_id`),
  ADD KEY `idx_notif_dibaca` (`dibaca`),
  ADD KEY `idx_notif_tipe_ref` (`tipe`,`referensi_id`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pengguna_email_unique` (`email`),
  ADD KEY `pengguna_peran_id_foreign` (`peran_id`);

--
-- Indexes for table `peran`
--
ALTER TABLE `peran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `peran_nama_unique` (`nama`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `sub_tugas`
--
ALTER TABLE `sub_tugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sub_tugas_jenis_tugas_id_foreign` (`jenis_tugas_id`);

--
-- Indexes for table `tugas_detail`
--
ALTER TABLE `tugas_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tugas_detail_sub_tugas_id_foreign` (`sub_tugas_id`);

--
-- Indexes for table `tugas_header`
--
ALTER TABLE `tugas_header`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tugas_header_nomor_unique` (`nomor`),
  ADD KEY `tugas_header_nama_pembuat_foreign` (`nama_pembuat`),
  ADD KEY `tugas_header_asal_surat_foreign` (`asal_surat`),
  ADD KEY `tugas_header_klasifikasi_surat_id_foreign` (`klasifikasi_surat_id`),
  ADD KEY `tugas_header_detail_tugas_id_foreign` (`detail_tugas_id`),
  ADD KEY `idx_tugas_status` (`status_surat`),
  ADD KEY `idx_tugas_dibuat_oleh` (`dibuat_oleh`),
  ADD KEY `idx_tugas_next_approver` (`next_approver`),
  ADD KEY `idx_tugas_penandatangan` (`penandatangan`),
  ADD KEY `idx_tugas_created_status` (`created_at`,`status_surat`);

--
-- Indexes for table `tugas_log`
--
ALTER TABLE `tugas_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tugas_log_tugas_id_foreign` (`tugas_id`),
  ADD KEY `tugas_log_user_id_foreign` (`user_id`);

--
-- Indexes for table `tugas_penerima`
--
ALTER TABLE `tugas_penerima`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_tugas_penerima_unique_per_surat` (`tugas_id`,`penerima_key`),
  ADD KEY `idx_penerima_tugas` (`tugas_id`),
  ADD KEY `idx_penerima_pengguna` (`pengguna_id`);

--
-- Indexes for table `user_signatures`
--
ALTER TABLE `user_signatures`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_signatures_pengguna_id_unique` (`pengguna_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jenis_tugas`
--
ALTER TABLE `jenis_tugas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `keputusan_header`
--
ALTER TABLE `keputusan_header`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `keputusan_penerima`
--
ALTER TABLE `keputusan_penerima`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `keputusan_versi`
--
ALTER TABLE `keputusan_versi`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `klasifikasi_surat`
--
ALTER TABLE `klasifikasi_surat`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `master_kop_surat`
--
ALTER TABLE `master_kop_surat`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `nomor_surat_counters`
--
ALTER TABLE `nomor_surat_counters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `peran`
--
ALTER TABLE `peran`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sub_tugas`
--
ALTER TABLE `sub_tugas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tugas_detail`
--
ALTER TABLE `tugas_detail`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tugas_header`
--
ALTER TABLE `tugas_header`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tugas_log`
--
ALTER TABLE `tugas_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tugas_penerima`
--
ALTER TABLE `tugas_penerima`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `user_signatures`
--
ALTER TABLE `user_signatures`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `keputusan_header`
--
ALTER TABLE `keputusan_header`
  ADD CONSTRAINT `keputusan_header_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `keputusan_header_penandatangan_foreign` FOREIGN KEY (`penandatangan`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `keputusan_penerima`
--
ALTER TABLE `keputusan_penerima`
  ADD CONSTRAINT `keputusan_penerima_keputusan_id_foreign` FOREIGN KEY (`keputusan_id`) REFERENCES `keputusan_header` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `keputusan_penerima_pengguna_id_foreign` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `keputusan_versi`
--
ALTER TABLE `keputusan_versi`
  ADD CONSTRAINT `keputusan_versi_header_id_foreign` FOREIGN KEY (`header_id`) REFERENCES `keputusan_header` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `keputusan_versi_versi_induk_foreign` FOREIGN KEY (`versi_induk`) REFERENCES `keputusan_versi` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_pengguna_id_foreign` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD CONSTRAINT `pengguna_peran_id_foreign` FOREIGN KEY (`peran_id`) REFERENCES `peran` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sub_tugas`
--
ALTER TABLE `sub_tugas`
  ADD CONSTRAINT `sub_tugas_jenis_tugas_id_foreign` FOREIGN KEY (`jenis_tugas_id`) REFERENCES `jenis_tugas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tugas_detail`
--
ALTER TABLE `tugas_detail`
  ADD CONSTRAINT `tugas_detail_sub_tugas_id_foreign` FOREIGN KEY (`sub_tugas_id`) REFERENCES `sub_tugas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tugas_header`
--
ALTER TABLE `tugas_header`
  ADD CONSTRAINT `tugas_header_asal_surat_foreign` FOREIGN KEY (`asal_surat`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tugas_header_detail_tugas_id_foreign` FOREIGN KEY (`detail_tugas_id`) REFERENCES `tugas_detail` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `tugas_header_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `tugas_header_klasifikasi_surat_id_foreign` FOREIGN KEY (`klasifikasi_surat_id`) REFERENCES `klasifikasi_surat` (`id`),
  ADD CONSTRAINT `tugas_header_nama_pembuat_foreign` FOREIGN KEY (`nama_pembuat`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `tugas_header_next_approver_foreign` FOREIGN KEY (`next_approver`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `tugas_header_penandatangan_foreign` FOREIGN KEY (`penandatangan`) REFERENCES `pengguna` (`id`);

--
-- Constraints for table `tugas_log`
--
ALTER TABLE `tugas_log`
  ADD CONSTRAINT `tugas_log_tugas_id_foreign` FOREIGN KEY (`tugas_id`) REFERENCES `tugas_header` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tugas_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`);

--
-- Constraints for table `tugas_penerima`
--
ALTER TABLE `tugas_penerima`
  ADD CONSTRAINT `tugas_penerima_pengguna_id_foreign` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tugas_penerima_tugas_id_foreign` FOREIGN KEY (`tugas_id`) REFERENCES `tugas_header` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_signatures`
--
ALTER TABLE `user_signatures`
  ADD CONSTRAINT `user_signatures_pengguna_id_foreign` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
