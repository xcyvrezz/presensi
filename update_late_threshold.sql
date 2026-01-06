-- Update late_threshold from 07:00:00 to 07:15:00
-- Run this SQL script to update your existing database

UPDATE attendance_settings
SET value = '07:15:00',
    default_value = '07:15:00',
    updated_at = NOW()
WHERE `key` = 'late_threshold';

-- Verify the update
SELECT `key`, `value`, `default_value`, `updated_at`
FROM attendance_settings
WHERE `key` = 'late_threshold';
