# DATABASE SCHEMA UPDATES - Enhanced Version
## Support for Manual Attendance & Violation Tracking

**Versi:** 2.1
**Tanggal:** 13 Desember 2025
**Purpose:** Database updates untuk support absensi manual oleh Wali Kelas & Admin

---

## 1. TABLE UPDATES

### 1.1 UPDATE Table: `attendances`

**Tambahkan kolom baru untuk approval workflow dan tracking:**

```sql
ALTER TABLE attendances ADD COLUMN (
    -- Check-in approval fields
    check_in_approved_by BIGINT UNSIGNED NULL COMMENT 'FK ke users (wali kelas/admin yang approve manual input)',
    check_in_notes TEXT NULL COMMENT 'Catatan untuk manual input (alasan, dll)',
    check_in_evidence VARCHAR(255) NULL COMMENT 'File evidence (foto, surat, screenshot chat)',
    check_in_needs_approval BOOLEAN DEFAULT FALSE COMMENT 'Perlu approval (untuk early/late yang suspicious)',

    -- Check-out approval fields
    check_out_approved_by BIGINT UNSIGNED NULL COMMENT 'FK ke users (wali kelas/admin yang approve)',
    check_out_notes TEXT NULL COMMENT 'Catatan untuk manual checkout/early leave',
    check_out_evidence VARCHAR(255) NULL COMMENT 'File evidence untuk pulang cepat berizin',
    check_out_needs_approval BOOLEAN DEFAULT FALSE COMMENT 'Pulang cepat perlu approval',
    check_out_approved_at TIMESTAMP NULL COMMENT 'Waktu di-approve',

    -- Additional tracking
    is_suspicious BOOLEAN DEFAULT FALSE COMMENT 'Flagged for review (anomaly detected)',
    reviewed_by BIGINT UNSIGNED NULL COMMENT 'Admin yang review anomaly',
    reviewed_at TIMESTAMP NULL COMMENT 'Waktu direview'
);

-- Add indexes
CREATE INDEX idx_check_in_approved_by ON attendances(check_in_approved_by);
CREATE INDEX idx_check_out_approved_by ON attendances(check_out_approved_by);
CREATE INDEX idx_needs_approval ON attendances(check_in_needs_approval, check_out_needs_approval);
CREATE INDEX idx_suspicious ON attendances(is_suspicious);

-- Add foreign keys
ALTER TABLE attendances
    ADD CONSTRAINT fk_check_in_approved_by
        FOREIGN KEY (check_in_approved_by) REFERENCES users(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_check_out_approved_by
        FOREIGN KEY (check_out_approved_by) REFERENCES users(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_reviewed_by
        FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL;
```

**Updated Enum Values untuk check_in_method & check_out_method:**

```sql
ALTER TABLE attendances
    MODIFY check_in_method ENUM('nfc_card', 'nfc_mobile', 'manual_walikelas', 'manual_admin') NULL,
    MODIFY check_out_method ENUM('nfc_card', 'nfc_mobile', 'manual_walikelas', 'manual_admin') NULL;
```

**Updated Enum Values untuk status:**

```sql
ALTER TABLE attendances
    MODIFY check_in_status ENUM(
        'present_ontime',          -- Tepat waktu ≤ 07:30
        'present_late',            -- Terlambat 07:31-08:30
        'very_late'                -- Sangat terlambat > 08:30 (perlu manual approval)
    ) NULL,
    MODIFY check_out_status ENUM(
        'checkout_normal',         -- Pulang normal ≥ 15:30
        'checkout_early_permitted',-- Pulang cepat dengan izin
        'checkout_early_unauthorized' -- Pulang cepat tanpa izin
    ) NULL;
```

---

### 1.2 UPDATE Table: `manual_attendances`

**Tambahkan kolom untuk approval workflow:**

```sql
ALTER TABLE manual_attendances ADD COLUMN (
    status ENUM('pending', 'approved', 'rejected', 'auto_generated') DEFAULT 'approved'
        COMMENT 'Status approval (untuk workflow)',
    approved_by BIGINT UNSIGNED NULL COMMENT 'User yang approve (jika perlu multi-level approval)',
    approved_at TIMESTAMP NULL COMMENT 'Waktu di-approve',
    rejection_reason TEXT NULL COMMENT 'Alasan jika rejected',
    needs_verification BOOLEAN DEFAULT FALSE COMMENT 'Perlu verifikasi (misal: sakit > 7 hari)',
    verification_deadline DATE NULL COMMENT 'Deadline upload surat dokter/bukti',
    parent_phone VARCHAR(20) NULL COMMENT 'Nomor orang tua yang konfirmasi',
    contact_log TEXT NULL COMMENT 'Log komunikasi dengan orang tua'
);

-- Add index
CREATE INDEX idx_status ON manual_attendances(status);
CREATE INDEX idx_needs_verification ON manual_attendances(needs_verification);
CREATE INDEX idx_verification_deadline ON manual_attendances(verification_deadline);

-- Add FK
ALTER TABLE manual_attendances
    ADD CONSTRAINT fk_manual_approved_by
        FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL;
```

**Update type enum untuk lebih detail:**

```sql
ALTER TABLE manual_attendances
    MODIFY type ENUM(
        'excused',                  -- Izin
        'sick',                     -- Sakit
        'sick_verified',            -- Sakit dengan surat dokter
        'dispensation',             -- Dispensasi (kegiatan sekolah)
        'absent',                   -- Alpha (auto-generated)
        'truant',                   -- Bolos (confirmed by walas)
        'late_manual'               -- Terlambat > 08:30, manual input by walas
    ) NOT NULL;
```

---

### 1.3 NEW Table: `attendance_violations`

**Tracking pelanggaran kehadiran untuk sanksi:**

```sql
CREATE TABLE attendance_violations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    semester_id BIGINT UNSIGNED NOT NULL,
    attendance_id BIGINT UNSIGNED NULL COMMENT 'FK ke attendances (if related to specific attendance)',

    violation_type ENUM(
        'unauthorized_early_checkout',  -- Pulang cepat tanpa izin
        'truant',                        -- Bolos
        'excessive_lateness',            -- Terlambat berlebihan (>3x/week)
        'no_checkout',                   -- Tidak absen pulang
        'fake_excuse',                   -- Keterangan palsu
        'excessive_absence'              -- Tidak masuk berlebihan
    ) NOT NULL,

    severity ENUM('ringan', 'sedang', 'berat') NOT NULL,

    violation_date DATE NOT NULL,
    description TEXT NOT NULL COMMENT 'Deskripsi pelanggaran',
    evidence TEXT NULL COMMENT 'Bukti pelanggaran (CCTV, saksi, dll)',

    -- Handling & Sanctions
    reported_by BIGINT UNSIGNED NULL COMMENT 'User yang report (walas/admin)',
    handled_by BIGINT UNSIGNED NULL COMMENT 'User yang handle (BK/admin)',
    handling_notes TEXT NULL COMMENT 'Catatan penanganan',
    sanction TEXT NULL COMMENT 'Sanksi yang diberikan',
    sanction_date DATE NULL COMMENT 'Tanggal sanksi',

    -- Status
    status ENUM('pending', 'under_investigation', 'resolved', 'escalated') DEFAULT 'pending',
    resolved BOOLEAN DEFAULT FALSE,
    resolved_at TIMESTAMP NULL,

    -- Point system (optional)
    violation_points INT DEFAULT 0 COMMENT 'Poin pelanggaran (untuk akumulasi)',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_student_semester (student_id, semester_id),
    INDEX idx_violation_type (violation_type),
    INDEX idx_severity (severity),
    INDEX idx_status (status),
    INDEX idx_violation_date (violation_date),
    INDEX idx_resolved (resolved),

    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE,
    FOREIGN KEY (attendance_id) REFERENCES attendances(id) ON DELETE SET NULL,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (handled_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 1.4 NEW Table: `attendance_approvals`

**Workflow approval untuk kasus khusus:**

```sql
CREATE TABLE attendance_approvals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    attendance_id BIGINT UNSIGNED NULL,
    manual_attendance_id BIGINT UNSIGNED NULL,

    approval_type ENUM(
        'early_checkout',           -- Approve pulang cepat
        'manual_checkin',           -- Approve manual check-in
        'manual_checkout',          -- Approve manual check-out
        'late_arrival',             -- Approve datang terlambat > 08:30
        'sick_extension',           -- Approve sakit > 7 hari
        'data_correction'           -- Approve koreksi data
    ) NOT NULL,

    requested_by BIGINT UNSIGNED NOT NULL COMMENT 'User yang request approval (siswa/walas)',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    request_reason TEXT NOT NULL,
    request_evidence VARCHAR(255) NULL,

    -- Approval flow
    approver_level_1 BIGINT UNSIGNED NULL COMMENT 'Wali kelas',
    approved_level_1_at TIMESTAMP NULL,
    approval_notes_level_1 TEXT NULL,

    approver_level_2 BIGINT UNSIGNED NULL COMMENT 'Admin/Kepala sekolah (jika perlu)',
    approved_level_2_at TIMESTAMP NULL,
    approval_notes_level_2 TEXT NULL,

    -- Final status
    final_status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    final_decision_by BIGINT UNSIGNED NULL,
    final_decision_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_student (student_id),
    INDEX idx_approval_type (approval_type),
    INDEX idx_final_status (final_status),
    INDEX idx_requested_by (requested_by),

    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (attendance_id) REFERENCES attendances(id) ON DELETE CASCADE,
    FOREIGN KEY (manual_attendance_id) REFERENCES manual_attendances(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approver_level_1) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approver_level_2) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (final_decision_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 1.5 NEW Table: `attendance_reports`

**Pre-generated reports untuk performance:**

```sql
CREATE TABLE attendance_reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    semester_id BIGINT UNSIGNED NOT NULL,
    month INT NULL COMMENT 'Bulan (1-12) atau NULL untuk semester penuh',

    -- Counters
    total_effective_days INT DEFAULT 0,
    total_present INT DEFAULT 0,
    total_late INT DEFAULT 0,
    total_very_late INT DEFAULT 0,
    total_excused INT DEFAULT 0,
    total_sick INT DEFAULT 0,
    total_dispensation INT DEFAULT 0,
    total_absent INT DEFAULT 0,
    total_truant INT DEFAULT 0,

    total_early_checkout_permitted INT DEFAULT 0,
    total_early_checkout_unauthorized INT DEFAULT 0,

    -- Time tracking (minutes)
    total_late_minutes INT DEFAULT 0,
    total_early_checkout_minutes INT DEFAULT 0,

    -- Percentages & grades
    attendance_percentage DECIMAL(5,2) DEFAULT 0.00,
    attendance_grade CHAR(2) NULL COMMENT 'A, B+, B, C+, C, D, E',

    -- Violations
    total_violations INT DEFAULT 0,
    violation_points INT DEFAULT 0,

    -- Method analysis
    nfc_card_count INT DEFAULT 0,
    nfc_mobile_count INT DEFAULT 0,
    manual_input_count INT DEFAULT 0,

    -- Metadata
    last_calculated_at TIMESTAMP NULL,
    is_final BOOLEAN DEFAULT FALSE COMMENT 'TRUE jika semester sudah ditutup',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_student_semester_month (student_id, semester_id, month),
    INDEX idx_semester (semester_id),
    INDEX idx_month (month),
    INDEX idx_percentage (attendance_percentage),
    INDEX idx_grade (attendance_grade),

    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose:** Pre-calculated reports untuk faster dashboard loading. Diupdate via cron job setiap malam.

---

### 1.6 NEW Table: `attendance_anomalies`

**Detect & log anomali untuk review:**

```sql
CREATE TABLE attendance_anomalies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    attendance_id BIGINT UNSIGNED NULL,

    anomaly_type ENUM(
        'duplicate_location',       -- Tap dari 2 lokasi berbeda dalam waktu dekat
        'conflicting_method',       -- NFC tap & manual input bentrok
        'impossible_time',          -- Waktu tidak masuk akal
        'gps_suspicious',           -- GPS accuracy buruk atau spoofing
        'excessive_manual',         -- Terlalu sering manual input
        'pattern_change',           -- Perubahan pola drastis
        'no_checkout_pattern'       -- Sering tidak tap pulang
    ) NOT NULL,

    detected_date DATE NOT NULL,
    severity ENUM('low', 'medium', 'high') DEFAULT 'medium',
    description TEXT NOT NULL,
    evidence JSON NULL COMMENT 'Data pendukung dalam format JSON',

    auto_detected BOOLEAN DEFAULT TRUE COMMENT 'Detected by system vs manual flag',

    -- Review
    reviewed BOOLEAN DEFAULT FALSE,
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at TIMESTAMP NULL,
    review_notes TEXT NULL,
    action_taken VARCHAR(255) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_student (student_id),
    INDEX idx_anomaly_type (anomaly_type),
    INDEX idx_severity (severity),
    INDEX idx_reviewed (reviewed),
    INDEX idx_detected_date (detected_date),

    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (attendance_id) REFERENCES attendances(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 2. UPDATED MIGRATION ORDER

```
1. create_roles_table
2. create_users_table
3. create_permissions_table
4. create_role_permission_table
5. create_departments_table
6. create_classes_table
7. create_students_table
8. create_semesters_table
9. create_academic_calendars_table
10. create_attendance_locations_table
11. create_attendances_table (with all enhanced fields)
12. create_manual_attendances_table (with approval workflow)
13. create_attendance_violations_table          ← NEW
14. create_attendance_approvals_table           ← NEW
15. create_attendance_reports_table             ← NEW
16. create_attendance_anomalies_table           ← NEW
17. create_attendance_settings_table
18. create_notifications_table
19. create_audit_logs_table
```

**Total: 19 tables**

---

## 3. SAMPLE DATA & BUSINESS RULES

### 3.1 Example: Manual Check-In by Wali Kelas

```sql
-- Siswa lupa tap, wali kelas input manual
INSERT INTO attendances (
    student_id, semester_id, date,
    check_in_time, check_in_status, check_in_late_minutes,
    check_in_method, check_in_approved_by, check_in_notes,
    check_in_evidence
) VALUES (
    123,  -- student_id
    1,    -- semester_id
    '2025-12-13',
    '07:25:00',
    'present_ontime',
    0,
    'manual_walikelas',
    45,   -- wali_kelas user_id
    'Siswa lupa membawa kartu, datang tepat waktu. Dikonfirmasi oleh siswa langsung ke ruang guru.',
    'uploads/evidence/2025/12/siswa123_chat_walas.jpg'
);

-- Log audit
INSERT INTO audit_logs (user_id, action, auditable_type, auditable_id, new_values, ip_address)
VALUES (
    45,
    'manual_checkin',
    'Attendance',
    LAST_INSERT_ID(),
    JSON_OBJECT('reason', 'Lupa tap kartu', 'method', 'manual_walikelas'),
    '192.168.1.100'
);
```

### 3.2 Example: Pulang Cepat Berizin

```sql
-- Siswa tap pulang jam 14:30 (sebelum 15:30)
-- System auto-mark sebagai "checkout_early_unauthorized"
INSERT INTO attendances (..., check_out_status, check_out_needs_approval)
VALUES (..., 'checkout_early_unauthorized', TRUE);

-- Send notification to wali kelas
INSERT INTO notifications (student_id, type, channel, message, status)
VALUES (
    123,
    'early_checkout_alert',
    'in_app',
    'Siswa Ahmad Rizki (X PPLG 1) pulang cepat jam 14:30. Perlu approval Anda.',
    'sent'
);

-- Wali kelas approve via web
UPDATE attendances
SET
    check_out_status = 'checkout_early_permitted',
    check_out_approved_by = 45,
    check_out_notes = 'Siswa sakit, izin pulang cepat',
    check_out_evidence = 'uploads/evidence/surat_izin_ortu.jpg',
    check_out_approved_at = NOW(),
    check_out_needs_approval = FALSE
WHERE id = 456;
```

### 3.3 Example: Auto-detect Violation

```sql
-- Cron job deteksi: siswa terlambat 4x dalam 1 minggu
INSERT INTO attendance_violations (
    student_id, semester_id, violation_type, severity,
    violation_date, description, status, violation_points
) VALUES (
    123, 1, 'excessive_lateness', 'sedang',
    '2025-12-13',
    'Siswa terlambat 4x dalam minggu ini (Senin 10 menit, Selasa 15 menit, Rabu 5 menit, Jumat 20 menit). Total keterlambatan: 50 menit.',
    'pending',
    10
);

-- Send alert to wali kelas & BK
```

---

## 4. CALCULATED FIELDS & VIEWS

### 4.1 View: Daily Attendance Summary

```sql
CREATE VIEW v_daily_attendance_summary AS
SELECT
    a.date,
    c.name as class_name,
    d.name as department_name,
    COUNT(DISTINCT s.id) as total_students,

    -- Presence counts
    SUM(CASE WHEN a.check_in_time IS NOT NULL THEN 1 ELSE 0 END) as total_present,
    SUM(CASE WHEN a.check_in_status = 'present_late' THEN 1 ELSE 0 END) as total_late,
    SUM(CASE WHEN ma.type = 'excused' THEN 1 ELSE 0 END) as total_excused,
    SUM(CASE WHEN ma.type = 'sick' THEN 1 ELSE 0 END) as total_sick,
    SUM(CASE WHEN ma.type = 'absent' THEN 1 ELSE 0 END) as total_absent,

    -- Method counts
    SUM(CASE WHEN a.check_in_method = 'nfc_card' THEN 1 ELSE 0 END) as via_nfc_card,
    SUM(CASE WHEN a.check_in_method = 'nfc_mobile' THEN 1 ELSE 0 END) as via_nfc_mobile,
    SUM(CASE WHEN a.check_in_method IN ('manual_walikelas', 'manual_admin') THEN 1 ELSE 0 END) as via_manual,

    -- Percentage
    ROUND((SUM(CASE WHEN a.check_in_time IS NOT NULL THEN 1 ELSE 0 END) / COUNT(DISTINCT s.id)) * 100, 2) as attendance_percentage

FROM students s
INNER JOIN classes c ON s.class_id = c.id
INNER JOIN departments d ON c.department_id = d.id
LEFT JOIN attendances a ON s.id = a.student_id
LEFT JOIN manual_attendances ma ON s.id = ma.student_id AND a.date = ma.date
WHERE s.is_active = TRUE
GROUP BY a.date, c.id, d.id;
```

### 4.2 Stored Procedure: Calculate Monthly Report

```sql
DELIMITER //

CREATE PROCEDURE calculate_monthly_report(
    IN p_student_id BIGINT,
    IN p_semester_id BIGINT,
    IN p_month INT
)
BEGIN
    DECLARE v_total_days INT;
    DECLARE v_attendance_percentage DECIMAL(5,2);
    DECLARE v_grade CHAR(2);

    -- Count effective days
    SELECT COUNT(*) INTO v_total_days
    FROM attendances
    WHERE student_id = p_student_id
      AND semester_id = p_semester_id
      AND MONTH(date) = p_month;

    -- Calculate percentage (complex weighted formula)
    -- ... (implementation dari Business Logic doc)

    -- Determine grade
    SET v_grade = CASE
        WHEN v_attendance_percentage >= 95 THEN 'A'
        WHEN v_attendance_percentage >= 90 THEN 'B+'
        WHEN v_attendance_percentage >= 85 THEN 'B'
        WHEN v_attendance_percentage >= 80 THEN 'C+'
        WHEN v_attendance_percentage >= 75 THEN 'C'
        WHEN v_attendance_percentage >= 70 THEN 'D'
        ELSE 'E'
    END;

    -- Insert or update report
    INSERT INTO attendance_reports (
        student_id, semester_id, month,
        total_effective_days, attendance_percentage, attendance_grade,
        -- ... (all counters)
        last_calculated_at
    ) VALUES (
        p_student_id, p_semester_id, p_month,
        v_total_days, v_attendance_percentage, v_grade,
        NOW()
    ) ON DUPLICATE KEY UPDATE
        total_effective_days = v_total_days,
        attendance_percentage = v_attendance_percentage,
        attendance_grade = v_grade,
        last_calculated_at = NOW();

END //

DELIMITER ;
```

---

## 5. INDEXES OPTIMIZATION

### 5.1 Composite Indexes untuk Query Performance

```sql
-- Untuk query: "Cari semua siswa yang belum checkout hari ini"
CREATE INDEX idx_incomplete_checkout
ON attendances(date, is_complete, check_in_time)
WHERE check_in_time IS NOT NULL AND is_complete = FALSE;

-- Untuk query: "Cari semua absensi yang perlu approval"
CREATE INDEX idx_needs_approval
ON attendances(check_in_needs_approval, check_out_needs_approval, date)
WHERE check_in_needs_approval = TRUE OR check_out_needs_approval = TRUE;

-- Untuk query: "Analisis metode absensi per bulan"
CREATE INDEX idx_method_analysis
ON attendances(date, check_in_method, check_out_method);

-- Untuk query violation tracking
CREATE INDEX idx_violation_student_date
ON attendance_violations(student_id, violation_date, resolved);
```

---

## 6. DATA RETENTION & ARCHIVING STRATEGY

```sql
-- Archive old semesters (> 5 tahun)
CREATE TABLE attendances_archive LIKE attendances;
CREATE TABLE manual_attendances_archive LIKE manual_attendances;

-- Procedure untuk archiving
CREATE PROCEDURE archive_old_semesters()
BEGIN
    DECLARE v_cutoff_date DATE;
    SET v_cutoff_date = DATE_SUB(CURDATE(), INTERVAL 5 YEAR);

    -- Move to archive
    INSERT INTO attendances_archive
    SELECT * FROM attendances
    WHERE date < v_cutoff_date;

    -- Delete from main table
    DELETE FROM attendances WHERE date < v_cutoff_date;

    -- Similar untuk tables lain
END;

-- Run yearly via cron
```

---

## 7. STORAGE ESTIMATES (UPDATED)

```
Per tahun (1500 siswa, 200 hari efektif):

attendances:
- 1500 × 200 × 500 bytes (with all new fields) = ~150 MB

manual_attendances:
- ~50k records × 400 bytes = ~20 MB

attendance_violations:
- ~5k violations/year × 300 bytes = ~1.5 MB

attendance_approvals:
- ~10k approvals/year × 250 bytes = ~2.5 MB

attendance_reports:
- 1500 students × 12 months × 200 bytes = ~3.6 MB

attendance_anomalies:
- ~2k anomalies/year × 300 bytes = ~600 KB

TOTAL per year: ~180 MB
5 years: ~900 MB

Masih sangat manageable!
```

---

**Document Control:**
- **Author:** Database Architect
- **Version:** 2.1 Enhanced
- **Status:** Ready for Implementation

