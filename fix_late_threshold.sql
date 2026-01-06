-- ==========================================
-- SQL SCRIPT: Verify dan Fix Late Threshold
-- ==========================================

-- 1. CEK NILAI SAAT INI
SELECT
    `key`,
    `value`,
    `default_value`,
    `updated_at`
FROM attendance_settings
WHERE `key` = 'late_threshold';

-- Hasil yang BENAR:
-- key: late_threshold
-- value: 07:15:00
-- default_value: 07:15:00

-- ==========================================

-- 2. UPDATE KE 07:15:00 (Jika belum benar)
UPDATE attendance_settings
SET
    value = '07:15:00',
    default_value = '07:15:00',
    updated_at = NOW()
WHERE `key` = 'late_threshold';

-- ==========================================

-- 3. VERIFY UPDATE
SELECT
    `key`,
    `value`,
    `default_value`,
    `updated_at`
FROM attendance_settings
WHERE `key` = 'late_threshold';

-- ==========================================

-- 4. CEK ATTENDANCE HARI INI YANG SALAH
-- Cari attendance yang check-in setelah 07:15 tapi masih status 'hadir'
SELECT
    a.id,
    s.full_name,
    s.nis,
    a.date,
    a.check_in_time,
    a.status,
    a.late_minutes,
    CASE
        WHEN TIME(a.check_in_time) > '07:15:00' THEN 'HARUSNYA TERLAMBAT'
        ELSE 'SUDAH BENAR'
    END as keterangan,
    TIMESTAMPDIFF(MINUTE,
        CONCAT(a.date, ' 07:15:00'),
        CONCAT(a.date, ' ', a.check_in_time)
    ) as seharusnya_late_minutes
FROM attendances a
JOIN students s ON a.student_id = s.id
WHERE a.date = CURDATE()
    AND TIME(a.check_in_time) > '07:15:00'
    AND a.status = 'hadir'
ORDER BY a.check_in_time DESC;

-- ==========================================

-- 5. FIX ATTENDANCE YANG SALAH (Opsional - jika ada)
-- HATI-HATI: Backup dulu sebelum menjalankan!

-- Contoh: Fix untuk Febrian Putra yang tap jam 09:12:21
-- UPDATE attendances
-- SET
--     status = 'terlambat',
--     late_minutes = 117  -- 09:12 - 07:15 = 117 menit
-- WHERE id = [ID_ATTENDANCE_FEBRIAN];

-- ==========================================

-- 6. UPDATE SEMUA ATTENDANCE HARI INI YANG SALAH (BULK FIX)
-- HATI-HATI: Review dulu query SELECT di poin 4 sebelum run ini!

-- UPDATE attendances a
-- SET
--     a.status = 'terlambat',
--     a.late_minutes = TIMESTAMPDIFF(MINUTE,
--         CONCAT(a.date, ' 07:15:00'),
--         CONCAT(a.date, ' ', a.check_in_time)
--     )
-- WHERE a.date = CURDATE()
--     AND TIME(a.check_in_time) > '07:15:00'
--     AND a.status = 'hadir';

-- ==========================================

-- 7. VERIFY FIX
SELECT
    s.full_name,
    s.nis,
    a.check_in_time,
    a.status,
    a.late_minutes
FROM attendances a
JOIN students s ON a.student_id = s.id
WHERE a.date = CURDATE()
ORDER BY a.check_in_time DESC
LIMIT 20;

-- ==========================================
