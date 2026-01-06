-- SQL untuk update tabel academic_calendars
-- Jalankan di MySQL Command Line atau phpMyAdmin

USE absensi_mifare; -- Ganti dengan nama database Anda jika berbeda

-- Tambah kolom custom attendance times
ALTER TABLE `academic_calendars`
ADD COLUMN `custom_check_in_start` TIME NULL COMMENT 'Jam mulai absen masuk khusus (override default)' AFTER `color`,
ADD COLUMN `custom_check_in_end` TIME NULL COMMENT 'Jam akhir absen masuk khusus' AFTER `custom_check_in_start`,
ADD COLUMN `custom_check_in_normal` TIME NULL COMMENT 'Jam normal masuk khusus (untuk hitung terlambat)' AFTER `custom_check_in_end`,
ADD COLUMN `custom_check_out_start` TIME NULL COMMENT 'Jam mulai absen pulang khusus' AFTER `custom_check_in_normal`,
ADD COLUMN `custom_check_out_end` TIME NULL COMMENT 'Jam akhir absen pulang khusus' AFTER `custom_check_out_start`,
ADD COLUMN `custom_check_out_normal` TIME NULL COMMENT 'Jam normal pulang khusus (untuk hitung pulang cepat)' AFTER `custom_check_out_end`,
ADD COLUMN `use_custom_times` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Gunakan jam khusus atau default' AFTER `custom_check_out_normal`,
ADD COLUMN `affected_departments` JSON NULL COMMENT 'Jurusan yang terdampak (null = semua)' AFTER `use_custom_times`,
ADD COLUMN `affected_classes` JSON NULL COMMENT 'Kelas yang terdampak (null = semua)' AFTER `affected_departments`,
ADD COLUMN `created_by` BIGINT UNSIGNED NULL COMMENT 'Dibuat oleh user' AFTER `affected_classes`;

-- Tambah foreign key untuk created_by
ALTER TABLE `academic_calendars`
ADD CONSTRAINT `academic_calendars_created_by_foreign`
FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- Verifikasi perubahan
DESCRIBE `academic_calendars`;
