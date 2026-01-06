-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 26 Des 2025 pada 12.43
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
-- Database: `absensi_mifare`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absence_requests`
--

CREATE TABLE `absence_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `absence_date` date NOT NULL,
  `type` enum('izin','sakit') NOT NULL,
  `reason` text NOT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `academic_calendars`
--

CREATE TABLE `academic_calendars` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `semester_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(200) NOT NULL COMMENT 'Judul event/libur',
  `description` text DEFAULT NULL COMMENT 'Deskripsi detail',
  `start_date` date NOT NULL COMMENT 'Tanggal mulai',
  `end_date` date NOT NULL COMMENT 'Tanggal selesai',
  `type` enum('holiday','event','exam','other') NOT NULL DEFAULT 'event' COMMENT 'Tipe: libur, event, ujian, lainnya',
  `is_holiday` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Apakah hari libur (tidak ada absensi)',
  `color` varchar(7) NOT NULL DEFAULT '#3B82F6' COMMENT 'Warna untuk kalender UI (hex)',
  `custom_check_in_start` time DEFAULT NULL COMMENT 'Jam mulai absen masuk khusus (override default)',
  `custom_check_in_end` time DEFAULT NULL COMMENT 'Jam akhir absen masuk khusus',
  `custom_check_in_normal` time DEFAULT NULL COMMENT 'Jam normal masuk khusus (untuk hitung terlambat)',
  `custom_check_out_start` time DEFAULT NULL COMMENT 'Jam mulai absen pulang khusus',
  `custom_check_out_end` time DEFAULT NULL COMMENT 'Jam akhir absen pulang khusus',
  `custom_check_out_normal` time DEFAULT NULL COMMENT 'Jam normal pulang khusus (untuk hitung pulang cepat)',
  `use_custom_times` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Gunakan jam khusus atau default',
  `affected_departments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Jurusan yang terdampak (null = semua)' CHECK (json_valid(`affected_departments`)),
  `affected_classes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Kelas yang terdampak (null = semua)' CHECK (json_valid(`affected_classes`)),
  `created_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Dibuat oleh user',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `semester_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL COMMENT 'Tanggal absensi',
  `check_in_time` time DEFAULT NULL COMMENT 'Waktu check-in',
  `check_in_method` enum('rfid_physical','nfc_mobile','manual','system') DEFAULT NULL COMMENT 'Metode check-in',
  `check_in_location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `check_in_latitude` decimal(10,8) DEFAULT NULL COMMENT 'Latitude check-in (mobile)',
  `check_in_longitude` decimal(11,8) DEFAULT NULL COMMENT 'Longitude check-in (mobile)',
  `check_in_distance` int(11) DEFAULT NULL COMMENT 'Jarak dari lokasi (meter)',
  `check_in_photo` varchar(255) DEFAULT NULL COMMENT 'Foto saat check-in (opsional)',
  `check_out_time` time DEFAULT NULL COMMENT 'Waktu check-out',
  `check_out_method` enum('rfid_physical','nfc_mobile','manual','system') DEFAULT NULL COMMENT 'Metode check-out',
  `check_out_location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `check_out_latitude` decimal(10,8) DEFAULT NULL COMMENT 'Latitude check-out (mobile)',
  `check_out_longitude` decimal(11,8) DEFAULT NULL COMMENT 'Longitude check-out (mobile)',
  `check_out_distance` int(11) DEFAULT NULL COMMENT 'Jarak dari lokasi (meter)',
  `check_out_photo` varchar(255) DEFAULT NULL COMMENT 'Foto saat check-out (opsional)',
  `status` enum('hadir','alpha','izin','sakit','dispensasi','terlambat','pulang_cepat','bolos','izin_terlambat','izin_pulang_cepat','libur') NOT NULL DEFAULT 'alpha' COMMENT 'Status kehadiran final',
  `late_minutes` int(11) NOT NULL DEFAULT 0 COMMENT 'Menit terlambat',
  `early_leave_minutes` int(11) NOT NULL DEFAULT 0 COMMENT 'Menit pulang lebih awal',
  `percentage` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Persentase kehadiran (weighted)',
  `notes` text DEFAULT NULL COMMENT 'Catatan tambahan',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu approval',
  `approval_status` enum('pending','approved','rejected') DEFAULT NULL COMMENT 'Status approval (untuk manual/izin)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `attendances`
--

INSERT INTO `attendances` (`id`, `student_id`, `class_id`, `semester_id`, `date`, `check_in_time`, `check_in_method`, `check_in_location_id`, `check_in_latitude`, `check_in_longitude`, `check_in_distance`, `check_in_photo`, `check_out_time`, `check_out_method`, `check_out_location_id`, `check_out_latitude`, `check_out_longitude`, `check_out_distance`, `check_out_photo`, `status`, `late_minutes`, `early_leave_minutes`, `percentage`, `notes`, `approved_by`, `approved_at`, `approval_status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 2, 1, 1, '2025-12-14', '07:00:00', 'manual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'hadir', 0, 0, 100.00, 'Manual Entry by Ahmad Fauzi, S.T.', NULL, NULL, NULL, '2025-12-13 11:14:12', '2025-12-13 11:14:12', NULL),
(5, 2, 1, 1, '2025-12-25', '07:00:00', 'manual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'hadir', 0, 0, 100.00, 'Manual Entry by Ahmad Fauzi, S.T.', NULL, NULL, NULL, '2025-12-25 11:53:45', '2025-12-25 11:53:45', NULL),
(6, 2, 1, 1, '2025-12-26', '07:00:00', 'manual', NULL, NULL, NULL, NULL, NULL, '18:21:35', 'rfid_physical', NULL, NULL, NULL, NULL, NULL, 'hadir', 0, 0, 100.00, 'Manual Entry by Ahmad Fauzi, S.T.', NULL, NULL, NULL, '2025-12-26 10:27:03', '2025-12-26 11:21:35', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `attendance_anomalies`
--

CREATE TABLE `attendance_anomalies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `attendance_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL COMMENT 'Tanggal anomali',
  `anomaly_type` enum('duplicate_checkin','duplicate_checkout','missing_checkout','missing_checkin','unusual_time','unusual_location','gps_jump','rapid_sequence','device_mismatch','other') NOT NULL COMMENT 'Tipe anomali',
  `severity` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium' COMMENT 'Tingkat severity',
  `description` text NOT NULL COMMENT 'Deskripsi anomali yang terdeteksi',
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Data pendukung (JSON: GPS, waktu, device, dll)' CHECK (json_valid(`data`)),
  `detection_method` varchar(50) DEFAULT NULL COMMENT 'Metode deteksi: cron_job, real_time, manual',
  `detected_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Waktu deteksi',
  `is_reviewed` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Sudah direview admin',
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu review',
  `review_notes` text DEFAULT NULL COMMENT 'Catatan review',
  `resolution` enum('false_positive','confirmed_anomaly','corrected','ignored') DEFAULT NULL COMMENT 'Hasil review',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `attendance_approvals`
--

CREATE TABLE `attendance_approvals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `manual_attendance_id` bigint(20) UNSIGNED DEFAULT NULL,
  `attendance_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approval_type` enum('manual_attendance','edit_attendance','delete_attendance','bulk_import') NOT NULL COMMENT 'Tipe approval',
  `request_data` text DEFAULT NULL COMMENT 'Data yang diminta (JSON)',
  `request_reason` text DEFAULT NULL COMMENT 'Alasan request',
  `requested_by` bigint(20) UNSIGNED NOT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Waktu pengajuan',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu approval/rejection',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending' COMMENT 'Status approval',
  `approval_notes` text DEFAULT NULL COMMENT 'Catatan approval/rejection',
  `approval_level` int(11) NOT NULL DEFAULT 1 COMMENT 'Level approval saat ini (1=Wali Kelas, 2=Kepala Sekolah)',
  `requires_multi_approval` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Butuh approval bertingkat',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `attendance_locations`
--

CREATE TABLE `attendance_locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Nama lokasi: Gerbang Utama, Gedung A, Lab PPLG',
  `description` text DEFAULT NULL COMMENT 'Deskripsi lokasi',
  `latitude` decimal(10,8) NOT NULL COMMENT 'Latitude pusat geofencing',
  `longitude` decimal(11,8) NOT NULL COMMENT 'Longitude pusat geofencing',
  `radius` int(11) NOT NULL DEFAULT 15 COMMENT 'Radius geofencing dalam meter',
  `type` enum('gate','building','classroom','lab','other') NOT NULL DEFAULT 'gate' COMMENT 'Tipe lokasi',
  `is_check_in_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Boleh check-in di lokasi ini',
  `is_check_out_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Boleh check-out di lokasi ini',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Status aktif/nonaktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `attendance_locations`
--

INSERT INTO `attendance_locations` (`id`, `name`, `description`, `latitude`, `longitude`, `radius`, `type`, `is_check_in_enabled`, `is_check_out_enabled`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Gerbang Utama SMK Negeri 10 Pandeglang', 'Lokasi check-in/out utama di gerbang sekolah', -6.30198160, 105.96327440, 15, 'gate', 1, 1, 1, '2025-12-12 23:33:07', '2025-12-12 23:33:07', NULL),
(2, 'Gedung A (Lab PPLG)', 'Laboratorium Pengembangan Perangkat Lunak dan Gim', -6.30200000, 105.96330000, 15, 'lab', 1, 1, 1, '2025-12-12 23:33:07', '2025-12-12 23:33:07', NULL),
(3, 'Gedung B (Lab AKL)', 'Laboratorium Akuntansi dan Keuangan Lembaga', -6.30195000, 105.96335000, 15, 'lab', 1, 1, 1, '2025-12-12 23:33:07', '2025-12-12 23:33:07', NULL),
(4, 'Workshop TO', 'Workshop Teknik Otomotif', -6.30205000, 105.96320000, 15, 'lab', 1, 1, 1, '2025-12-12 23:33:07', '2025-12-12 23:33:07', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `attendance_reports`
--

CREATE TABLE `attendance_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `generated_by` bigint(20) UNSIGNED NOT NULL,
  `report_name` varchar(200) NOT NULL COMMENT 'Nama laporan',
  `report_type` enum('daily','weekly','monthly','semester','student','class','department','violation','custom') NOT NULL COMMENT 'Tipe laporan',
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Filter yang digunakan (JSON)' CHECK (json_valid(`filters`)),
  `start_date` date DEFAULT NULL COMMENT 'Tanggal mulai periode',
  `end_date` date DEFAULT NULL COMMENT 'Tanggal akhir periode',
  `student_id` bigint(20) UNSIGNED DEFAULT NULL,
  `class_id` bigint(20) UNSIGNED DEFAULT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `semester_id` bigint(20) UNSIGNED DEFAULT NULL,
  `format` enum('pdf','excel','csv') NOT NULL DEFAULT 'pdf' COMMENT 'Format output',
  `file_path` varchar(255) DEFAULT NULL COMMENT 'Path file hasil generate',
  `file_size` int(11) DEFAULT NULL COMMENT 'Ukuran file (bytes)',
  `statistics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Statistik hasil (JSON: total_hadir, total_alpha, dll)' CHECK (json_valid(`statistics`)),
  `status` enum('pending','processing','completed','failed') NOT NULL DEFAULT 'pending' COMMENT 'Status generate',
  `error_message` text DEFAULT NULL COMMENT 'Error message jika gagal',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu selesai generate',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `attendance_settings`
--

CREATE TABLE `attendance_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL COMMENT 'Setting key: check_in_start, check_in_end, dll',
  `group` varchar(50) NOT NULL COMMENT 'Group: time_windows, geofencing, violations, notifications',
  `label` varchar(150) NOT NULL COMMENT 'Label untuk UI',
  `description` text DEFAULT NULL COMMENT 'Deskripsi setting',
  `value_type` varchar(20) NOT NULL DEFAULT 'string' COMMENT 'Tipe nilai: string, integer, time, boolean, json',
  `value` text NOT NULL COMMENT 'Nilai setting',
  `default_value` text DEFAULT NULL COMMENT 'Nilai default',
  `validation_rules` text DEFAULT NULL COMMENT 'Aturan validasi (JSON)',
  `is_editable` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Bisa diedit melalui UI',
  `display_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Urutan tampil di UI',
  `last_modified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `attendance_settings`
--

INSERT INTO `attendance_settings` (`id`, `key`, `group`, `label`, `description`, `value_type`, `value`, `default_value`, `validation_rules`, `is_editable`, `display_order`, `last_modified_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'check_in_start', 'time_windows', 'Waktu Mulai Check-in', 'Waktu paling awal siswa dapat melakukan check-in', 'time', '06:00', '05:00:00', '\"[\\\"required\\\",\\\"date_format:H:i:s\\\"]\"', 1, 1, 1, '2025-12-12 23:33:07', '2025-12-13 05:26:09', NULL),
(2, 'check_in_end', 'time_windows', 'Waktu Akhir Check-in', 'Batas waktu check-in tanpa dianggap terlambat', 'time', '08:30', '07:00:00', '\"[\\\"required\\\",\\\"date_format:H:i:s\\\"]\"', 1, 2, 1, '2025-12-12 23:33:07', '2025-12-13 05:26:09', NULL),
(3, 'late_threshold', 'time_windows', 'Batas Waktu Terlambat', 'Check-in setelah waktu ini dianggap terlambat', 'time', '07:30', '07:00:00', '\"[\\\"required\\\",\\\"date_format:H:i:s\\\"]\"', 1, 3, 1, '2025-12-12 23:33:07', '2025-12-13 05:26:09', NULL),
(4, 'check_out_start', 'time_windows', 'Waktu Mulai Check-out', 'Waktu paling awal untuk check-out normal', 'time', '15:30', '14:00:00', '\"[\\\"required\\\",\\\"date_format:H:i:s\\\"]\"', 1, 4, 1, '2025-12-12 23:33:07', '2025-12-13 05:26:09', NULL),
(5, 'check_out_end', 'time_windows', 'Waktu Akhir Check-out', 'Batas waktu normal check-out', 'time', '22:00', '15:00:00', '\"[\\\"required\\\",\\\"date_format:H:i:s\\\"]\"', 1, 5, 1, '2025-12-12 23:33:07', '2025-12-25 11:47:23', NULL),
(6, 'geofencing_enabled', 'geofencing', 'Aktifkan Geofencing', 'Validasi lokasi GPS saat check-in/out mobile', 'boolean', '1', 'true', '\"[\\\"required\\\",\\\"boolean\\\"]\"', 1, 10, 1, '2025-12-12 23:33:07', '2025-12-13 05:26:22', NULL),
(7, 'geofencing_radius', 'geofencing', 'Radius Geofencing (meter)', 'Jarak maksimal dari lokasi sekolah', 'integer', '15', '15', '\"[\\\"required\\\",\\\"integer\\\",\\\"min:5\\\",\\\"max:100\\\"]\"', 1, 11, 1, '2025-12-12 23:33:07', '2025-12-13 05:26:09', NULL),
(8, 'gps_accuracy_threshold', 'geofencing', 'Akurasi GPS Minimum (meter)', 'GPS accuracy harus lebih baik dari nilai ini', 'integer', '50', '50', '\"[\\\"required\\\",\\\"integer\\\",\\\"min:10\\\",\\\"max:200\\\"]\"', 1, 12, 1, '2025-12-12 23:33:07', '2025-12-13 05:26:09', NULL),
(9, 'alpha_detection_time', 'violations', 'Waktu Deteksi Alpha', 'Waktu sistem mendeteksi siswa alpha (tidak hadir)', 'time', '23:55:00', '23:55:00', '\"[\\\"required\\\",\\\"date_format:H:i:s\\\"]\"', 1, 20, 1, '2025-12-12 23:33:07', '2025-12-13 05:26:09', NULL),
(10, 'bolos_detection_times', 'violations', 'Waktu Deteksi Bolos', 'Waktu-waktu sistem cek bolos (JSON array)', 'json', '[\"18:30:00\",\"20:00:00\"]', '[\"18:30:00\",\"20:00:00\"]', '\"[\\\"required\\\",\\\"json\\\"]\"', 1, 21, 1, '2025-12-12 23:33:07', '2025-12-13 05:26:09', NULL),
(11, 'late_tolerance_minutes', 'violations', 'Toleransi Keterlambatan (menit)', 'Toleransi menit untuk keterlambatan tanpa sanksi', 'integer', '10', '5', '\"[\\\"required\\\",\\\"integer\\\",\\\"min:0\\\",\\\"max:30\\\"]\"', 1, 22, 1, '2025-12-12 23:33:07', '2025-12-13 05:27:02', NULL),
(12, 'violation_points_alpha', 'violations', 'Poin Pelanggaran Alpha', 'Poin untuk pelanggaran alpha', 'integer', '5', '10', '\"[\\\"required\\\",\\\"integer\\\",\\\"min:0\\\"]\"', 1, 23, 1, '2025-12-12 23:33:07', '2025-12-13 05:27:02', NULL),
(13, 'violation_points_late', 'violations', 'Poin Pelanggaran Terlambat', 'Poin untuk keterlambatan', 'integer', '3', '5', '\"[\\\"required\\\",\\\"integer\\\",\\\"min:0\\\"]\"', 1, 24, 1, '2025-12-12 23:33:07', '2025-12-13 05:27:02', NULL),
(14, 'violation_points_bolos', 'violations', 'Poin Pelanggaran Bolos', 'Poin untuk bolos/pulang cepat tanpa izin', 'integer', '15', '15', '\"[\\\"required\\\",\\\"integer\\\",\\\"min:0\\\"]\"', 1, 25, 1, '2025-12-12 23:33:07', '2025-12-13 05:26:09', NULL),
(15, 'manual_grace_period_hours', 'manual_attendance', 'Grace Period Input Manual (jam)', 'Wali kelas bisa input manual H-1 sampai jam berapa hari ini (contoh: 12 = sampai jam 12 siang)', 'integer', '12', '12', '\"[\\\"required\\\",\\\"integer\\\",\\\"min:0\\\",\\\"max:23\\\"]\"', 1, 30, 1, '2025-12-12 23:33:07', '2025-12-13 05:26:09', NULL),
(16, 'manual_requires_evidence', 'manual_attendance', 'Wajib Upload Bukti', 'Izin/sakit/dispensasi wajib upload file bukti', 'boolean', 'true', 'true', '\"[\\\"required\\\",\\\"boolean\\\"]\"', 1, 31, 1, '2025-12-12 23:33:07', '2025-12-13 05:26:09', NULL),
(17, 'manual_auto_approve', 'manual_attendance', 'Auto-approve Manual Attendance', 'Input manual langsung approved tanpa perlu approval', 'boolean', '1', 'false', '\"[\\\"required\\\",\\\"boolean\\\"]\"', 1, 32, 1, '2025-12-12 23:33:07', '2025-12-13 05:27:29', NULL),
(18, 'notification_late_enabled', 'notifications', 'Notifikasi Terlambat', 'Kirim notifikasi saat siswa terlambat', 'boolean', '1', 'true', '\"[\\\"required\\\",\\\"boolean\\\"]\"', 1, 40, 1, '2025-12-12 23:33:07', '2025-12-13 05:27:38', NULL),
(19, 'notification_alpha_enabled', 'notifications', 'Notifikasi Alpha', 'Kirim notifikasi saat siswa alpha', 'boolean', '1', 'true', '\"[\\\"required\\\",\\\"boolean\\\"]\"', 1, 41, 1, '2025-12-12 23:33:07', '2025-12-13 05:27:38', NULL),
(20, 'notification_approval_enabled', 'notifications', 'Notifikasi Approval', 'Kirim notifikasi untuk approval request', 'boolean', '1', 'true', '\"[\\\"required\\\",\\\"boolean\\\"]\"', 1, 42, 1, '2025-12-12 23:33:07', '2025-12-13 05:27:38', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `attendance_violations`
--

CREATE TABLE `attendance_violations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `attendance_id` bigint(20) UNSIGNED DEFAULT NULL,
  `semester_id` bigint(20) UNSIGNED NOT NULL,
  `violation_date` date NOT NULL COMMENT 'Tanggal pelanggaran',
  `type` enum('alpha','terlambat','pulang_cepat','bolos','tidak_checkin','tidak_checkout','gps_invalid','other') NOT NULL COMMENT 'Tipe pelanggaran',
  `points` int(11) NOT NULL DEFAULT 0 COMMENT 'Poin pelanggaran (untuk sistem sanksi)',
  `description` text DEFAULT NULL COMMENT 'Deskripsi pelanggaran',
  `evidence` text DEFAULT NULL COMMENT 'Bukti/data pendukung (JSON)',
  `sanction_level` enum('warning','mild','medium','severe') DEFAULT NULL COMMENT 'Level sanksi',
  `sanction_notes` text DEFAULT NULL COMMENT 'Catatan sanksi yang diberikan',
  `sanctioned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `sanctioned_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu pemberian sanksi',
  `is_resolved` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Apakah sudah diselesaikan',
  `resolved_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu penyelesaian',
  `resolution_notes` text DEFAULT NULL COMMENT 'Catatan penyelesaian',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_type` varchar(50) DEFAULT NULL COMMENT 'Tipe user: admin, wali_kelas, dll',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'User agent browser',
  `auditable_type` varchar(100) NOT NULL COMMENT 'Model yang diaudit: Student, Attendance, dll',
  `auditable_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID record yang diaudit',
  `event` varchar(50) NOT NULL COMMENT 'Event: created, updated, deleted, restored',
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Nilai lama (untuk update/delete)' CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Nilai baru (untuk create/update)' CHECK (json_valid(`new_values`)),
  `url` varchar(255) DEFAULT NULL COMMENT 'URL request',
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Tags untuk filtering (JSON array)' CHECK (json_valid(`tags`)),
  `notes` text DEFAULT NULL COMMENT 'Catatan tambahan',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `classes`
--

CREATE TABLE `classes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `wali_kelas_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(50) NOT NULL COMMENT 'Nama kelas: X PPLG 1, XI AKL 2, etc',
  `grade` int(11) NOT NULL COMMENT 'Tingkat kelas: 10, 11, 12',
  `academic_year` varchar(9) NOT NULL COMMENT 'Tahun ajaran: 2024/2025',
  `capacity` int(11) NOT NULL DEFAULT 36 COMMENT 'Kapasitas maksimal siswa',
  `current_students` int(11) NOT NULL DEFAULT 0 COMMENT 'Jumlah siswa saat ini',
  `description` text DEFAULT NULL COMMENT 'Deskripsi atau catatan kelas',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Status aktif/nonaktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `classes`
--

INSERT INTO `classes` (`id`, `department_id`, `wali_kelas_id`, `name`, `grade`, `academic_year`, `capacity`, `current_students`, `description`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 5, 'X PPLG 1', 10, '2025/2026', 36, 0, 'ewr', 1, '2025-12-13 02:06:58', '2025-12-13 02:06:58', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(10) NOT NULL COMMENT 'Kode jurusan: PPLG, AKL, TO',
  `name` varchar(100) NOT NULL COMMENT 'Nama lengkap jurusan',
  `description` text DEFAULT NULL COMMENT 'Deskripsi jurusan',
  `head_teacher` varchar(255) DEFAULT NULL COMMENT 'Nama kepala jurusan',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Nomor telepon jurusan',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Status aktif/nonaktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `departments`
--

INSERT INTO `departments` (`id`, `code`, `name`, `description`, `head_teacher`, `phone`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'PPLG', 'Pengembangan Perangkat Lunak dan Gim', 'Jurusan yang mempelajari pemrograman, pengembangan aplikasi, web development, mobile development, dan game development.', NULL, NULL, 1, '2025-12-12 23:33:07', '2025-12-12 23:33:07', NULL),
(2, 'AKL', 'Akuntansi dan Keuangan Lembaga', 'Jurusan yang mempelajari akuntansi, pembukuan, perpajakan, dan manajemen keuangan.', NULL, NULL, 1, '2025-12-12 23:33:07', '2025-12-12 23:33:07', NULL),
(3, 'TO', 'Teknik Otomotif', 'Jurusan yang mempelajari teknologi kendaraan bermotor, mesin otomotif, dan sistem kelistrikan kendaraan.', NULL, NULL, 1, '2025-12-12 23:33:07', '2025-12-12 23:33:07', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `manual_attendances`
--

CREATE TABLE `manual_attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `attendance_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL COMMENT 'Tanggal absensi manual',
  `type` enum('check_in','check_out','full_day') NOT NULL COMMENT 'Tipe input manual',
  `status` enum('izin','sakit','dispensasi','hadir','alpha') NOT NULL COMMENT 'Status yang diinput',
  `reason` text DEFAULT NULL COMMENT 'Alasan izin/sakit/dispensasi',
  `evidence_file` varchar(255) DEFAULT NULL COMMENT 'File bukti (surat izin, surat sakit, dll)',
  `time` time DEFAULT NULL COMMENT 'Waktu check-in/out (jika type bukan full_day)',
  `requested_by` bigint(20) UNSIGNED NOT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Waktu pengajuan',
  `request_notes` text DEFAULT NULL COMMENT 'Catatan dari pengaju',
  `approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending' COMMENT 'Status approval',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu approval',
  `approval_notes` text DEFAULT NULL COMMENT 'Catatan approval/rejection',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2025_12_13_000001_create_roles_table', 1),
(4, '2025_12_13_000002_create_permissions_table', 1),
(5, '2025_12_13_000003_create_role_permission_table', 1),
(6, '2025_12_13_000004_create_departments_table', 1),
(7, '2025_12_13_000007_create_semesters_table', 1),
(8, '2025_12_13_100001_create_users_table', 1),
(9, '2025_12_13_100002_create_classes_table', 1),
(10, '2025_12_13_100003_create_students_table', 1),
(11, '2025_12_13_100004_create_academic_calendars_table', 1),
(12, '2025_12_13_100005_create_attendance_locations_table', 1),
(13, '2025_12_13_100006_create_attendances_table', 1),
(14, '2025_12_13_100007_create_manual_attendances_table', 1),
(15, '2025_12_13_100008_create_attendance_violations_table', 1),
(16, '2025_12_13_100009_create_attendance_approvals_table', 1),
(17, '2025_12_13_100010_create_attendance_reports_table', 1),
(18, '2025_12_13_100011_create_attendance_anomalies_table', 1),
(19, '2025_12_13_100012_create_attendance_settings_table', 1),
(20, '2025_12_13_100013_create_notifications_table', 1),
(21, '2025_12_13_100014_create_audit_logs_table', 1),
(22, '2025_12_13_065206_create_personal_access_tokens_table', 2),
(23, '2025_12_13_150000_create_absence_requests_table', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL COMMENT 'Judul notifikasi',
  `message` text NOT NULL COMMENT 'Isi pesan notifikasi',
  `type` enum('attendance_reminder','violation_warning','approval_request','approval_result','report_ready','system_alert','other') NOT NULL COMMENT 'Tipe notifikasi',
  `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal' COMMENT 'Prioritas notifikasi',
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Data tambahan (JSON: link, action, dll)' CHECK (json_valid(`data`)),
  `related_type` varchar(50) DEFAULT NULL COMMENT 'Model terkait: Attendance, ManualAttendance, dll',
  `related_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ID record terkait',
  `is_read` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Sudah dibaca',
  `read_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu dibaca',
  `sent_push` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Sudah dikirim via push notification',
  `sent_email` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Sudah dikirim via email',
  `sent_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu pengiriman',
  `action_url` varchar(255) DEFAULT NULL COMMENT 'URL action button',
  `action_taken` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Action sudah diambil',
  `action_taken_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu action diambil',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `priority`, `data`, `related_type`, `related_id`, `is_read`, `read_at`, `sent_push`, `sent_email`, `sent_at`, `action_url`, `action_taken`, `action_taken_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 5, 'Pengajuan Izin Baru', 'jumri mengajukan Izin untuk tanggal 14/12/2025', 'approval_request', 'normal', NULL, 'AbsenceRequest', 5, 1, '2025-12-13 10:44:55', 0, 0, '2025-12-13 10:44:41', 'http://localhost:8000/wali-kelas/absence-requests', 0, NULL, '2025-12-13 10:44:41', '2025-12-13 10:44:55', NULL),
(2, 5, 'Pengajuan Izin Baru', 'jumri mengajukan Izin untuk tanggal 14/12/2025', 'approval_request', 'normal', NULL, 'AbsenceRequest', 6, 1, '2025-12-13 10:48:38', 0, 0, '2025-12-13 10:47:53', 'http://localhost:8000/wali-kelas/absence-requests', 0, NULL, '2025-12-13 10:47:53', '2025-12-13 10:48:38', NULL),
(3, 12, 'Izin Disetujui', 'Pengajuan Izin Anda untuk tanggal 14/12/2025 telah disetujui oleh Ahmad Fauzi, S.T.', 'approval_result', 'normal', NULL, 'AbsenceRequest', 6, 0, NULL, 0, 0, '2025-12-13 10:48:11', 'http://localhost:8000/siswa/absence/request', 0, NULL, '2025-12-13 10:48:11', '2025-12-13 18:39:10', '2025-12-13 18:39:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Permission name: users.view, students.create, etc',
  `display_name` varchar(150) NOT NULL COMMENT 'Display name untuk UI',
  `group` varchar(50) NOT NULL COMMENT 'Group: users, students, attendance, reports, settings',
  `description` text DEFAULT NULL COMMENT 'Deskripsi permission',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `display_name`, `group`, `description`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'users.view', 'Lihat Users', 'users', 'Melihat daftar users', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(2, 'users.create', 'Tambah User', 'users', 'Membuat user baru', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(3, 'users.edit', 'Edit User', 'users', 'Mengubah data user', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(4, 'users.delete', 'Hapus User', 'users', 'Menghapus user', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(5, 'users.activate', 'Aktifkan/Nonaktifkan User', 'users', 'Mengaktifkan atau menonaktifkan user', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(6, 'users.reset_password', 'Reset Password User', 'users', 'Reset password user', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(7, 'users.assign_role', 'Assign Role', 'users', 'Mengubah role user', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(8, 'users.view_logs', 'Lihat User Logs', 'users', 'Melihat activity log user', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(9, 'users.import', 'Import Users', 'users', 'Import users dari Excel', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(10, 'users.export', 'Export Users', 'users', 'Export users ke Excel', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(11, 'students.view', 'Lihat Siswa', 'students', 'Melihat daftar siswa', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(12, 'students.view_detail', 'Lihat Detail Siswa', 'students', 'Melihat detail lengkap siswa', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(13, 'students.create', 'Tambah Siswa', 'students', 'Membuat data siswa baru', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(14, 'students.edit', 'Edit Siswa', 'students', 'Mengubah data siswa', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(15, 'students.delete', 'Hapus Siswa', 'students', 'Menghapus data siswa', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(16, 'students.activate', 'Aktifkan/Nonaktifkan Siswa', 'students', 'Status aktif siswa', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(17, 'students.assign_card', 'Assign Kartu RFID', 'students', 'Mendaftarkan UID kartu RFID', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(18, 'students.enable_nfc', 'Enable NFC Mobile', 'students', 'Mengaktifkan NFC mobile', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(19, 'students.move_class', 'Pindah Kelas', 'students', 'Memindahkan siswa ke kelas lain', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(20, 'students.import', 'Import Siswa', 'students', 'Import siswa dari Excel', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(21, 'students.export', 'Export Siswa', 'students', 'Export siswa ke Excel', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(22, 'students.bulk_operations', 'Operasi Bulk', 'students', 'Operasi bulk pada siswa', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(23, 'classes.view', 'Lihat Kelas', 'classes', 'Melihat daftar kelas', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(24, 'classes.create', 'Tambah Kelas', 'classes', 'Membuat kelas baru', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(25, 'classes.edit', 'Edit Kelas', 'classes', 'Mengubah data kelas', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(26, 'classes.delete', 'Hapus Kelas', 'classes', 'Menghapus kelas', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(27, 'classes.assign_wali', 'Assign Wali Kelas', 'classes', 'Menentukan wali kelas', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(28, 'classes.view_students', 'Lihat Siswa di Kelas', 'classes', 'Melihat siswa dalam kelas', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(29, 'classes.manage_students', 'Kelola Siswa di Kelas', 'classes', 'Menambah/menghapus siswa', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(30, 'classes.view_statistics', 'Lihat Statistik Kelas', 'classes', 'Melihat statistik kelas', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(31, 'attendance.view_all', 'Lihat Semua Absensi', 'attendance', 'Melihat absensi semua siswa', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(32, 'attendance.view_class', 'Lihat Absensi Kelas', 'attendance', 'Melihat absensi siswa di kelas tertentu', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(33, 'attendance.view_own', 'Lihat Absensi Sendiri', 'attendance', 'Siswa lihat absensi sendiri', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(34, 'attendance.checkin', 'Check-in', 'attendance', 'Melakukan check-in', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(35, 'attendance.checkout', 'Check-out', 'attendance', 'Melakukan check-out', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(36, 'attendance.manual_input', 'Input Manual', 'attendance', 'Input absensi manual', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(37, 'attendance.manual_approve', 'Approve Manual Attendance', 'attendance', 'Menyetujui absensi manual', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(38, 'attendance.manual_reject', 'Reject Manual Attendance', 'attendance', 'Menolak absensi manual', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(39, 'attendance.edit', 'Edit Absensi', 'attendance', 'Mengubah data absensi', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(40, 'attendance.delete', 'Hapus Absensi', 'attendance', 'Menghapus data absensi', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(41, 'attendance.export', 'Export Absensi', 'attendance', 'Export absensi ke Excel', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(42, 'attendance.view_violations', 'Lihat Pelanggaran', 'attendance', 'Melihat pelanggaran absensi', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(43, 'attendance.manage_violations', 'Kelola Pelanggaran', 'attendance', 'Mengelola pelanggaran absensi', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(44, 'attendance.view_anomalies', 'Lihat Anomali', 'attendance', 'Melihat anomali absensi', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(45, 'attendance.resolve_anomalies', 'Review Anomali', 'attendance', 'Mereview dan resolve anomali', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(46, 'reports.view_all', 'Lihat Semua Laporan', 'reports', 'Melihat semua laporan', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(47, 'reports.view_class', 'Lihat Laporan Kelas', 'reports', 'Melihat laporan kelas tertentu', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(48, 'reports.view_own', 'Lihat Laporan Pribadi', 'reports', 'Siswa lihat laporan sendiri', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(49, 'reports.generate_daily', 'Generate Laporan Harian', 'reports', 'Membuat laporan harian', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(50, 'reports.generate_monthly', 'Generate Laporan Bulanan', 'reports', 'Membuat laporan bulanan', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(51, 'reports.generate_semester', 'Generate Laporan Semester', 'reports', 'Membuat laporan semester', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(52, 'reports.export_pdf', 'Export PDF', 'reports', 'Export laporan ke PDF', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(53, 'reports.export_excel', 'Export Excel', 'reports', 'Export laporan ke Excel', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(54, 'reports.schedule', 'Jadwal Laporan Otomatis', 'reports', 'Membuat jadwal laporan otomatis', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(55, 'reports.delete', 'Hapus Laporan', 'reports', 'Menghapus laporan', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(56, 'dashboard.view_admin', 'Dashboard Admin', 'dashboard', 'Akses dashboard admin', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(57, 'dashboard.view_principal', 'Dashboard Kepala Sekolah', 'dashboard', 'Dashboard kepala sekolah', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(58, 'dashboard.view_teacher', 'Dashboard Wali Kelas', 'dashboard', 'Dashboard wali kelas', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(59, 'dashboard.view_student', 'Dashboard Siswa', 'dashboard', 'Dashboard siswa', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(60, 'dashboard.analytics', 'Analytics', 'dashboard', 'Akses fitur analytics', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(61, 'dashboard.realtime', 'Real-time Monitoring', 'dashboard', 'Monitoring real-time', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(62, 'settings.view', 'Lihat Pengaturan', 'settings', 'Melihat pengaturan sistem', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(63, 'settings.edit_attendance', 'Edit Pengaturan Absensi', 'settings', 'Mengubah pengaturan absensi', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(64, 'settings.edit_time', 'Edit Jam Operasional', 'settings', 'Mengubah jam operasional', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(65, 'settings.edit_geofencing', 'Edit Geofencing', 'settings', 'Mengubah setting geofencing', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(66, 'settings.manage_locations', 'Kelola Lokasi', 'settings', 'Mengelola lokasi absensi', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(67, 'settings.manage_semesters', 'Kelola Semester', 'settings', 'Mengelola data semester', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(68, 'settings.manage_calendar', 'Kelola Kalender Akademik', 'settings', 'Mengelola kalender akademik', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(69, 'settings.manage_departments', 'Kelola Jurusan', 'settings', 'Mengelola data jurusan', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(70, 'settings.backup', 'Backup Database', 'settings', 'Melakukan backup database', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(71, 'settings.restore', 'Restore Database', 'settings', 'Restore database dari backup', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(72, 'settings.system', 'Pengaturan Sistem', 'settings', 'Pengaturan sistem lanjutan', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'Role name: admin, kepala_sekolah, wali_kelas, siswa',
  `display_name` varchar(100) NOT NULL COMMENT 'Display name untuk UI',
  `description` text DEFAULT NULL COMMENT 'Deskripsi role',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Status active/inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'admin', 'Administrator', 'Akses penuh ke seluruh sistem. Dapat mengelola users, master data, absensi, laporan, dan pengaturan.', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(2, 'kepala_sekolah', 'Kepala Sekolah', 'Dapat melihat dashboard analitik, laporan komprehensif, approval absensi manual, dan monitoring seluruh sistem.', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(3, 'wali_kelas', 'Wali Kelas', 'Dapat mengelola absensi siswa di kelasnya, input manual attendance, melihat laporan kelas, dan approval absensi.', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL),
(4, 'siswa', 'Siswa', 'Dapat melakukan check-in/check-out via NFC, melihat riwayat absensi pribadi, dan mengajukan izin/dispensasi.', 1, '2025-12-12 23:33:06', '2025-12-12 23:33:06', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `role_permission`
--

CREATE TABLE `role_permission` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `role_permission`
--

INSERT INTO `role_permission` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(2, 1, 2, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(3, 1, 3, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(4, 1, 4, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(5, 1, 5, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(6, 1, 6, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(7, 1, 7, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(8, 1, 8, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(9, 1, 9, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(10, 1, 10, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(11, 1, 11, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(12, 1, 12, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(13, 1, 13, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(14, 1, 14, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(15, 1, 15, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(16, 1, 16, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(17, 1, 17, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(18, 1, 18, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(19, 1, 19, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(20, 1, 20, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(21, 1, 21, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(22, 1, 22, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(23, 1, 23, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(24, 1, 24, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(25, 1, 25, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(26, 1, 26, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(27, 1, 27, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(28, 1, 28, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(29, 1, 29, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(30, 1, 30, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(31, 1, 31, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(32, 1, 32, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(33, 1, 33, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(34, 1, 34, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(35, 1, 35, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(36, 1, 36, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(37, 1, 37, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(38, 1, 38, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(39, 1, 39, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(40, 1, 40, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(41, 1, 41, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(42, 1, 42, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(43, 1, 43, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(44, 1, 44, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(45, 1, 45, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(46, 1, 46, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(47, 1, 47, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(48, 1, 48, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(49, 1, 49, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(50, 1, 50, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(51, 1, 51, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(52, 1, 52, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(53, 1, 53, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(54, 1, 54, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(55, 1, 55, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(56, 1, 56, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(57, 1, 57, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(58, 1, 58, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(59, 1, 59, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(60, 1, 60, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(61, 1, 61, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(62, 1, 62, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(63, 1, 63, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(64, 1, 64, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(65, 1, 65, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(66, 1, 66, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(67, 1, 67, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(68, 1, 68, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(69, 1, 69, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(70, 1, 70, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(71, 1, 71, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(72, 1, 72, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(73, 2, 1, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(74, 2, 11, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(75, 2, 12, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(76, 2, 23, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(77, 2, 28, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(78, 2, 30, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(79, 2, 31, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(80, 2, 37, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(81, 2, 38, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(82, 2, 42, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(83, 2, 44, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(84, 2, 46, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(85, 2, 49, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(86, 2, 50, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(87, 2, 51, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(88, 2, 52, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(89, 2, 53, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(90, 2, 57, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(91, 2, 60, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(92, 2, 61, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(93, 3, 11, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(94, 3, 12, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(95, 3, 14, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(96, 3, 17, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(97, 3, 18, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(98, 3, 23, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(99, 3, 28, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(100, 3, 30, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(101, 3, 32, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(102, 3, 36, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(103, 3, 37, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(104, 3, 38, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(105, 3, 39, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(106, 3, 41, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(107, 3, 42, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(108, 3, 43, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(109, 3, 44, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(110, 3, 47, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(111, 3, 49, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(112, 3, 50, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(113, 3, 52, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(114, 3, 53, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(115, 3, 58, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(116, 4, 34, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(117, 4, 35, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(118, 4, 33, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(119, 4, 59, '2025-12-12 23:33:07', '2025-12-12 23:33:07'),
(120, 4, 48, '2025-12-12 23:33:07', '2025-12-12 23:33:07');

-- --------------------------------------------------------

--
-- Struktur dari tabel `semesters`
--

CREATE TABLE `semesters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'Nama semester: Semester 1 2024/2025',
  `academic_year` varchar(9) NOT NULL COMMENT 'Tahun ajaran: 2024/2025',
  `semester` int(11) NOT NULL COMMENT 'Semester ke: 1 atau 2',
  `start_date` date NOT NULL COMMENT 'Tanggal mulai semester',
  `end_date` date NOT NULL COMMENT 'Tanggal akhir semester',
  `is_active` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Apakah semester aktif saat ini',
  `description` text DEFAULT NULL COMMENT 'Deskripsi atau catatan',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `semesters`
--

INSERT INTO `semesters` (`id`, `name`, `academic_year`, `semester`, `start_date`, `end_date`, `is_active`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Semester 1 2025/2026', '2025/2026', 1, '2025-07-01', '2025-12-31', 1, NULL, '2025-12-13 09:00:37', '2025-12-13 09:00:37', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('rTrXyHtlXc467NypxKensMHOvOQ8aNxWsFWAo9Ou', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNnA3bk5QUFdzb3VmSkM3Nk8wc3pMaXhwSlVVR0g5dWZlN0F3TU9XQyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjI6Imh0dHA6Ly9sb2NhbGhvc3QvYWJzZW5zaS1taWZhcmUvcHVibGljL2FkbWluL2F0dGVuZGFuY2UvbWFudWFsIjtzOjU6InJvdXRlIjtzOjIzOiJhZG1pbi5hdHRlbmRhbmNlLm1hbnVhbCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1766749390);

-- --------------------------------------------------------

--
-- Struktur dari tabel `students`
--

CREATE TABLE `students` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `nis` varchar(20) NOT NULL COMMENT 'Nomor Induk Siswa',
  `nisn` varchar(20) DEFAULT NULL COMMENT 'Nomor Induk Siswa Nasional',
  `card_uid` varchar(50) DEFAULT NULL COMMENT 'UID kartu MIFARE (Physical)',
  `full_name` varchar(150) NOT NULL COMMENT 'Nama lengkap siswa',
  `nickname` varchar(50) DEFAULT NULL COMMENT 'Nama panggilan',
  `gender` enum('L','P') NOT NULL COMMENT 'Jenis kelamin: L=Laki-laki, P=Perempuan',
  `birth_date` date DEFAULT NULL COMMENT 'Tanggal lahir',
  `birth_place` varchar(100) DEFAULT NULL COMMENT 'Tempat lahir',
  `address` text DEFAULT NULL COMMENT 'Alamat lengkap',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Nomor HP siswa',
  `parent_phone` varchar(20) DEFAULT NULL COMMENT 'Nomor HP orang tua/wali',
  `parent_name` varchar(150) DEFAULT NULL COMMENT 'Nama orang tua/wali',
  `photo` varchar(255) DEFAULT NULL COMMENT 'Path foto siswa',
  `nfc_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Apakah NFC smartphone aktif',
  `home_latitude` decimal(10,8) DEFAULT NULL COMMENT 'Latitude rumah (untuk referensi)',
  `home_longitude` decimal(11,8) DEFAULT NULL COMMENT 'Longitude rumah (untuk referensi)',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Status aktif/nonaktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `students`
--

INSERT INTO `students` (`id`, `user_id`, `class_id`, `nis`, `nisn`, `card_uid`, `full_name`, `nickname`, `gender`, `birth_date`, `birth_place`, `address`, `phone`, `parent_phone`, `parent_name`, `photo`, `nfc_enabled`, `home_latitude`, `home_longitude`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 12, 1, '11223344', '11223344', '3151484868', 'jumri', 'jms', 'L', '2001-02-12', 'pandeglang', 'rwerwre', '0555555', '08877', 'sakinah', 'students/a1eUTkjmVr1OC6xfo1feHvT7efCO603Yhb1oAO2S.jpg', 1, NULL, NULL, 1, '2025-12-13 02:14:16', '2025-12-25 11:25:01', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Nama lengkap user',
  `email` varchar(255) NOT NULL COMMENT 'Email untuk login',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Hashed password',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Nomor telepon/WhatsApp',
  `photo` varchar(255) DEFAULT NULL COMMENT 'Path foto profil',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Status aktif/nonaktif',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT 'Terakhir login',
  `last_login_ip` varchar(45) DEFAULT NULL COMMENT 'IP terakhir login',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `email_verified_at`, `password`, `phone`, `photo`, `is_active`, `last_login_at`, `last_login_ip`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Admin SMK Negeri 10', 'admin@smkn10pdg.sch.id', '2025-12-12 23:33:07', '$2y$12$XmLWM3WeR0//8g47E853bO.4pW7Yi5NrGykn7AG2GHOjd/fqaHg3.', '081234567890', NULL, 1, '2025-12-26 10:27:13', '::1', NULL, '2025-12-12 23:33:09', '2025-12-26 10:27:13', NULL),
(2, 2, 'Dr. Suherman, M.Pd.', 'kepala.sekolah@smkn10pdg.sch.id', '2025-12-12 23:33:07', '$2y$12$nP2wdR61nDJGN1G9QBh1SelFqGhEgtLdgPYkdF0PNnPMq2m0wX1rW', '081234567891', NULL, 1, '2025-12-13 18:38:42', '127.0.0.1', NULL, '2025-12-12 23:33:09', '2025-12-13 18:38:42', NULL),
(3, 3, 'Budi Santoso, S.Kom.', 'budi.santoso@smkn10pdg.sch.id', '2025-12-12 23:33:08', '$2y$12$spJvnWoow7fm5Wc2l7W5F.iVuizLL3bmhaMsCmS3kf7Q9DbvBjoie', '081234567892', NULL, 1, NULL, NULL, NULL, '2025-12-12 23:33:09', '2025-12-12 23:33:09', NULL),
(4, 3, 'Siti Aminah, S.Pd.', 'siti.aminah@smkn10pdg.sch.id', '2025-12-12 23:33:08', '$2y$12$03PyURx.AxsY0.vnaXYYE.dJcoKPCx7JrBpTlb.tEdLAa3OOExEyC', '081234567893', NULL, 1, NULL, NULL, NULL, '2025-12-12 23:33:09', '2025-12-12 23:33:09', NULL),
(5, 3, 'Ahmad Fauzi, S.T.', 'ahmad.fauzi@smkn10pdg.sch.id', '2025-12-12 23:33:08', '$2y$12$RYN02nPOvOivZ7xO02Kv1efy5qATzsqYY4ST21IND9pXhl5OODHdG', '081234567894', NULL, 1, '2025-12-26 10:26:57', '::1', NULL, '2025-12-12 23:33:09', '2025-12-26 10:26:57', NULL),
(6, 4, 'Andi Wijaya', 'andi.wijaya@student.smkn10pdg.sch.id', '2025-12-12 23:33:08', '$2y$12$6EVTOHPlW.nbX11hS.GIvOJll.qK9HlrHZPbpoYveJ8iiMfMCjRRa', '081234567895', NULL, 1, NULL, NULL, NULL, '2025-12-12 23:33:09', '2025-12-12 23:33:09', NULL),
(7, 4, 'Dewi Lestari', 'dewi.lestari@student.smkn10pdg.sch.id', '2025-12-12 23:33:09', '$2y$12$hjQczSuGRrHUw1ARAn6zpuZEZs2cKsE7Pv4GdrKzfjP0x.pmU2QNi', '081234567896', NULL, 1, NULL, NULL, NULL, '2025-12-12 23:33:09', '2025-12-12 23:33:09', NULL),
(8, 4, 'Reza Pratama', 'reza.pratama@student.smkn10pdg.sch.id', '2025-12-12 23:33:09', '$2y$12$fhhD.ug7SbHoEm6HND6czeRFPAk8DVSWNg3BG/OP1jhwq99A4sV3u', '081234567897', NULL, 1, NULL, NULL, NULL, '2025-12-12 23:33:09', '2025-12-12 23:33:09', NULL),
(9, 4, 'Fitria Rahmawati', 'fitria.rahmawati@student.smkn10pdg.sch.id', '2025-12-12 23:33:09', '$2y$12$yN0Jq7NlVBBJhai3yqsqLeIgLzr/.9eSNLtCtbnkxX5C3GlZslK0G', '081234567898', NULL, 1, NULL, NULL, NULL, '2025-12-12 23:33:09', '2025-12-12 23:33:09', NULL),
(10, 4, 'Doni Saputra', 'doni.saputra@student.smkn10pdg.sch.id', '2025-12-12 23:33:09', '$2y$12$VEOGAQZFrh16UhEAs4QvdOhzJCwKfdM6P7H.CJekgY9SiLxmjLla.', '081234567899', NULL, 1, NULL, NULL, NULL, '2025-12-12 23:33:09', '2025-12-12 23:33:09', NULL),
(12, 4, 'jumri', 'jumri@mail.com', NULL, '$2y$12$8XJvPrz0ESYK92KGl5M4Legxn0EF3lQfPZU7cZwZMLrDK32II1dgu', NULL, NULL, 1, '2025-12-26 09:57:45', '::1', NULL, '2025-12-13 02:14:16', '2025-12-26 09:57:45', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absence_requests`
--
ALTER TABLE `absence_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `absence_requests_approved_by_foreign` (`approved_by`),
  ADD KEY `absence_requests_student_id_index` (`student_id`),
  ADD KEY `absence_requests_absence_date_index` (`absence_date`),
  ADD KEY `absence_requests_status_index` (`status`);

--
-- Indeks untuk tabel `academic_calendars`
--
ALTER TABLE `academic_calendars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `academic_calendars_semester_id_index` (`semester_id`),
  ADD KEY `academic_calendars_start_date_end_date_index` (`start_date`,`end_date`),
  ADD KEY `academic_calendars_type_index` (`type`),
  ADD KEY `academic_calendars_is_holiday_index` (`is_holiday`),
  ADD KEY `academic_calendars_created_by_foreign` (`created_by`);

--
-- Indeks untuk tabel `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendances_student_id_date_unique` (`student_id`,`date`),
  ADD KEY `attendances_check_in_location_id_foreign` (`check_in_location_id`),
  ADD KEY `attendances_check_out_location_id_foreign` (`check_out_location_id`),
  ADD KEY `attendances_approved_by_foreign` (`approved_by`),
  ADD KEY `attendances_student_id_index` (`student_id`),
  ADD KEY `attendances_class_id_index` (`class_id`),
  ADD KEY `attendances_semester_id_index` (`semester_id`),
  ADD KEY `attendances_date_index` (`date`),
  ADD KEY `attendances_status_index` (`status`),
  ADD KEY `attendances_student_id_date_index` (`student_id`,`date`),
  ADD KEY `attendances_approval_status_index` (`approval_status`);

--
-- Indeks untuk tabel `attendance_anomalies`
--
ALTER TABLE `attendance_anomalies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_anomalies_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `attendance_anomalies_attendance_id_index` (`attendance_id`),
  ADD KEY `attendance_anomalies_student_id_index` (`student_id`),
  ADD KEY `attendance_anomalies_date_index` (`date`),
  ADD KEY `attendance_anomalies_anomaly_type_index` (`anomaly_type`),
  ADD KEY `attendance_anomalies_severity_index` (`severity`),
  ADD KEY `attendance_anomalies_is_reviewed_index` (`is_reviewed`),
  ADD KEY `attendance_anomalies_detected_at_index` (`detected_at`);

--
-- Indeks untuk tabel `attendance_approvals`
--
ALTER TABLE `attendance_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_approvals_manual_attendance_id_index` (`manual_attendance_id`),
  ADD KEY `attendance_approvals_attendance_id_index` (`attendance_id`),
  ADD KEY `attendance_approvals_requested_by_index` (`requested_by`),
  ADD KEY `attendance_approvals_approved_by_index` (`approved_by`),
  ADD KEY `attendance_approvals_status_index` (`status`),
  ADD KEY `attendance_approvals_approval_type_index` (`approval_type`);

--
-- Indeks untuk tabel `attendance_locations`
--
ALTER TABLE `attendance_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_locations_type_index` (`type`),
  ADD KEY `attendance_locations_is_active_index` (`is_active`);

--
-- Indeks untuk tabel `attendance_reports`
--
ALTER TABLE `attendance_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_reports_generated_by_index` (`generated_by`),
  ADD KEY `attendance_reports_report_type_index` (`report_type`),
  ADD KEY `attendance_reports_start_date_end_date_index` (`start_date`,`end_date`),
  ADD KEY `attendance_reports_student_id_index` (`student_id`),
  ADD KEY `attendance_reports_class_id_index` (`class_id`),
  ADD KEY `attendance_reports_department_id_index` (`department_id`),
  ADD KEY `attendance_reports_semester_id_index` (`semester_id`),
  ADD KEY `attendance_reports_status_index` (`status`);

--
-- Indeks untuk tabel `attendance_settings`
--
ALTER TABLE `attendance_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendance_settings_key_unique` (`key`),
  ADD KEY `attendance_settings_last_modified_by_foreign` (`last_modified_by`),
  ADD KEY `attendance_settings_key_index` (`key`),
  ADD KEY `attendance_settings_group_index` (`group`),
  ADD KEY `attendance_settings_is_editable_index` (`is_editable`);

--
-- Indeks untuk tabel `attendance_violations`
--
ALTER TABLE `attendance_violations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_violations_sanctioned_by_foreign` (`sanctioned_by`),
  ADD KEY `attendance_violations_student_id_index` (`student_id`),
  ADD KEY `attendance_violations_attendance_id_index` (`attendance_id`),
  ADD KEY `attendance_violations_semester_id_index` (`semester_id`),
  ADD KEY `attendance_violations_violation_date_index` (`violation_date`),
  ADD KEY `attendance_violations_type_index` (`type`),
  ADD KEY `attendance_violations_is_resolved_index` (`is_resolved`);

--
-- Indeks untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_user_id_index` (`user_id`),
  ADD KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  ADD KEY `audit_logs_event_index` (`event`),
  ADD KEY `audit_logs_created_at_index` (`created_at`),
  ADD KEY `audit_logs_ip_address_index` (`ip_address`);

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `classes_department_id_index` (`department_id`),
  ADD KEY `classes_wali_kelas_id_index` (`wali_kelas_id`),
  ADD KEY `classes_grade_academic_year_index` (`grade`,`academic_year`),
  ADD KEY `classes_is_active_index` (`is_active`);

--
-- Indeks untuk tabel `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_code_unique` (`code`),
  ADD KEY `departments_code_index` (`code`),
  ADD KEY `departments_is_active_index` (`is_active`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `manual_attendances`
--
ALTER TABLE `manual_attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `manual_attendances_attendance_id_index` (`attendance_id`),
  ADD KEY `manual_attendances_student_id_index` (`student_id`),
  ADD KEY `manual_attendances_date_index` (`date`),
  ADD KEY `manual_attendances_approval_status_index` (`approval_status`),
  ADD KEY `manual_attendances_requested_by_index` (`requested_by`),
  ADD KEY `manual_attendances_approved_by_index` (`approved_by`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_index` (`user_id`),
  ADD KEY `notifications_type_index` (`type`),
  ADD KEY `notifications_priority_index` (`priority`),
  ADD KEY `notifications_is_read_index` (`is_read`),
  ADD KEY `notifications_related_type_related_id_index` (`related_type`,`related_id`),
  ADD KEY `notifications_created_at_index` (`created_at`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`),
  ADD KEY `permissions_name_index` (`name`),
  ADD KEY `permissions_group_index` (`group`),
  ADD KEY `permissions_is_active_index` (`is_active`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`),
  ADD KEY `roles_name_index` (`name`),
  ADD KEY `roles_is_active_index` (`is_active`);

--
-- Indeks untuk tabel `role_permission`
--
ALTER TABLE `role_permission`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permission_role_id_permission_id_unique` (`role_id`,`permission_id`),
  ADD KEY `role_permission_role_id_index` (`role_id`),
  ADD KEY `role_permission_permission_id_index` (`permission_id`);

--
-- Indeks untuk tabel `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `semesters_academic_year_index` (`academic_year`),
  ADD KEY `semesters_is_active_index` (`is_active`),
  ADD KEY `semesters_start_date_end_date_index` (`start_date`,`end_date`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `students_nis_unique` (`nis`),
  ADD UNIQUE KEY `students_nisn_unique` (`nisn`),
  ADD UNIQUE KEY `students_card_uid_unique` (`card_uid`),
  ADD KEY `students_user_id_index` (`user_id`),
  ADD KEY `students_class_id_index` (`class_id`),
  ADD KEY `students_nis_index` (`nis`),
  ADD KEY `students_nisn_index` (`nisn`),
  ADD KEY `students_card_uid_index` (`card_uid`),
  ADD KEY `students_is_active_index` (`is_active`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_id_index` (`role_id`),
  ADD KEY `users_email_index` (`email`),
  ADD KEY `users_is_active_index` (`is_active`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absence_requests`
--
ALTER TABLE `absence_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `academic_calendars`
--
ALTER TABLE `academic_calendars`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `attendance_anomalies`
--
ALTER TABLE `attendance_anomalies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `attendance_approvals`
--
ALTER TABLE `attendance_approvals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `attendance_locations`
--
ALTER TABLE `attendance_locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `attendance_reports`
--
ALTER TABLE `attendance_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `attendance_settings`
--
ALTER TABLE `attendance_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `attendance_violations`
--
ALTER TABLE `attendance_violations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `classes`
--
ALTER TABLE `classes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `manual_attendances`
--
ALTER TABLE `manual_attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `role_permission`
--
ALTER TABLE `role_permission`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT untuk tabel `semesters`
--
ALTER TABLE `semesters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absence_requests`
--
ALTER TABLE `absence_requests`
  ADD CONSTRAINT `absence_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `absence_requests_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `academic_calendars`
--
ALTER TABLE `academic_calendars`
  ADD CONSTRAINT `academic_calendars_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `academic_calendars_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`);

--
-- Ketidakleluasaan untuk tabel `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `attendances_check_in_location_id_foreign` FOREIGN KEY (`check_in_location_id`) REFERENCES `attendance_locations` (`id`),
  ADD CONSTRAINT `attendances_check_out_location_id_foreign` FOREIGN KEY (`check_out_location_id`) REFERENCES `attendance_locations` (`id`),
  ADD CONSTRAINT `attendances_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `attendances_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`),
  ADD CONSTRAINT `attendances_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Ketidakleluasaan untuk tabel `attendance_anomalies`
--
ALTER TABLE `attendance_anomalies`
  ADD CONSTRAINT `attendance_anomalies_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_anomalies_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `attendance_anomalies_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Ketidakleluasaan untuk tabel `attendance_approvals`
--
ALTER TABLE `attendance_approvals`
  ADD CONSTRAINT `attendance_approvals_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `attendance_approvals_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_approvals_manual_attendance_id_foreign` FOREIGN KEY (`manual_attendance_id`) REFERENCES `manual_attendances` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_approvals_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `attendance_reports`
--
ALTER TABLE `attendance_reports`
  ADD CONSTRAINT `attendance_reports_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `attendance_reports_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `attendance_reports_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `attendance_reports_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`),
  ADD CONSTRAINT `attendance_reports_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Ketidakleluasaan untuk tabel `attendance_settings`
--
ALTER TABLE `attendance_settings`
  ADD CONSTRAINT `attendance_settings_last_modified_by_foreign` FOREIGN KEY (`last_modified_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `attendance_violations`
--
ALTER TABLE `attendance_violations`
  ADD CONSTRAINT `attendance_violations_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`),
  ADD CONSTRAINT `attendance_violations_sanctioned_by_foreign` FOREIGN KEY (`sanctioned_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `attendance_violations_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`),
  ADD CONSTRAINT `attendance_violations_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Ketidakleluasaan untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `classes_wali_kelas_id_foreign` FOREIGN KEY (`wali_kelas_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `manual_attendances`
--
ALTER TABLE `manual_attendances`
  ADD CONSTRAINT `manual_attendances_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `manual_attendances_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `manual_attendances_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `manual_attendances_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `role_permission`
--
ALTER TABLE `role_permission`
  ADD CONSTRAINT `role_permission_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permission_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
