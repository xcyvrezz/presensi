# ENHANCED BUSINESS LOGIC & ATTENDANCE RULES
## Sistem Absensi MIFARE - SMK Negeri 10 Pandeglang

**Versi:** 2.0 - Enhanced Edition
**Tanggal:** 13 Desember 2025
**Status:** Optimized for Real-World School Scenarios

---

## 1. ATTENDANCE METHOD TYPES

### 1.1 Metode Pencatatan Absensi

Sistem mendukung 3 metode pencatatan absensi:

| Method | Code | Description | Who Can Do | Validation |
|--------|------|-------------|------------|------------|
| **NFC Card** | `nfc_card` | Tap kartu MIFARE di reader | Siswa | Card UID + Time + Holiday check |
| **NFC Mobile** | `nfc_mobile` | Tap via smartphone NFC | Siswa | Card UID + Time + GPS + Holiday |
| **Manual by Wali Kelas** | `manual_walikelas` | Input manual oleh wali kelas | Wali Kelas | Time + Reason + Evidence |
| **Manual by Admin** | `manual_admin` | Input manual oleh admin (koreksi) | Admin | Full override capability |

### 1.2 Attendance Status Types

Sistem memiliki status kehadiran yang lebih detail:

| Status | Code | Category | Description | Affects Percentage |
|--------|------|----------|-------------|-------------------|
| **Hadir Tepat Waktu** | `present_ontime` | HADIR | Datang ≤ 07:30 | ✅ Yes (100%) |
| **Hadir Terlambat** | `present_late` | HADIR | Datang 07:31 - 08:30 | ✅ Yes (100%) |
| **Pulang Normal** | `checkout_normal` | PULANG | Pulang ≥ 15:30 | ✅ Yes |
| **Pulang Cepat (Izin)** | `checkout_early_permitted` | PULANG | Pulang < 15:30 dengan izin wali kelas | ✅ Yes |
| **Pulang Cepat (Tidak Izin)** | `checkout_early_unauthorized` | PELANGGARAN | Pulang < 15:30 tanpa izin | ⚠️ Partial (50%) |
| **Izin** | `excused` | ABSEN BERIZIN | Izin dengan surat/konfirmasi | ⚠️ Partial (75%) |
| **Sakit** | `sick` | ABSEN BERIZIN | Sakit dengan surat dokter | ⚠️ Partial (75%) |
| **Dispensasi** | `dispensation` | HADIR KHUSUS | Hadir tapi ada kegiatan khusus | ✅ Yes (100%) |
| **Alpha** | `absent` | ABSEN TANPA IZIN | Tidak hadir tanpa keterangan | ❌ No (0%) |
| **Bolos** | `truant` | PELANGGARAN BERAT | Tidak tap pulang & tidak lapor | ❌ No (0%) + Sanksi |
| **Terlambat Berat** | `very_late` | PELANGGARAN | Datang > 08:30 tanpa izin | ⚠️ Partial (50%) |

---

## 2. DETAILED ATTENDANCE FLOW

### 2.1 Flow: Absen Datang (Check-In)

#### Scenario A: Siswa Tap NFC (Card/Mobile) - Normal Flow

```
TIME: 06:00 - 08:30
1. Siswa tap kartu MIFARE / smartphone NFC
2. System validate:
   ├─ Card UID registered? → No: REJECT "Kartu tidak terdaftar"
   ├─ Student active? → No: REJECT "Siswa tidak aktif"
   ├─ Holiday today? → Yes: REJECT "Hari ini libur"
   ├─ Already checked in? → Yes: REJECT "Sudah absen datang"
   ├─ Time valid (06:00-08:30)? → No: REJECT "Di luar jam absensi"
   └─ [If Mobile] GPS valid? → No: REJECT "Di luar area sekolah"

3. System determine status:
   ├─ Time ≤ 07:30 → Status: "present_ontime" (Tepat Waktu)
   ├─ Time 07:31 - 08:30 → Status: "present_late" (Terlambat)
   │   └─ Calculate: late_minutes = (current_time - 07:30)
   └─ Save to attendances table:
       ├─ check_in_time = current_time
       ├─ check_in_status = status
       ├─ check_in_method = 'nfc_card' or 'nfc_mobile'
       ├─ check_in_late_minutes = late_minutes (if late)
       └─ [If mobile] GPS data (latitude, longitude, location_id, distance)

4. Return success response
5. [OPTIONAL] Send notification to wali kelas if terlambat
```

#### Scenario B: Siswa Lupa Tap - Lapor ke Wali Kelas

```
TIME: Siswa sadar lupa tap (misal jam 10:00)
1. Siswa contact wali kelas (WhatsApp/langsung)
2. Siswa jelaskan alasan (lupa, kartu hilang, HP mati, dll)
3. Wali Kelas login ke sistem web
4. Wali Kelas navigate: "Kelola Absensi" → "Input Absensi Manual"
5. Wali Kelas pilih:
   ├─ Siswa: [Pilih dari dropdown kelas]
   ├─ Tanggal: [Hari ini atau kemarin]
   ├─ Tipe: "Hadir - Datang"
   ├─ Waktu: [Input jam siswa sebenarnya datang, misal: 07:20]
   ├─ Metode: "Manual Input"
   ├─ Alasan: "Siswa lupa tap kartu, kartu tertinggal di rumah"
   └─ Evidence (optional): Upload foto/screenshot chat

6. System validate:
   ├─ Wali kelas owns this class? → No: REJECT "Bukan siswa kelas Anda"
   ├─ Date valid (today or yesterday)? → No: REJECT "Hanya bisa input H-0 atau H-1"
   ├─ Already has record? → Yes: Show warning + option to override
   └─ Time reasonable? → No: Warning if time > 08:30

7. System save:
   ├─ check_in_time = [waktu yang diinput wali kelas]
   ├─ check_in_status = calculate based on time
   ├─ check_in_method = 'manual_walikelas'
   ├─ check_in_approved_by = [wali_kelas_id]
   ├─ check_in_notes = "Manual input: [alasan]"
   └─ check_in_evidence = [file path jika ada]

8. System log to audit_logs:
   ├─ Action: "Manual check-in input"
   ├─ User: Wali Kelas X
   ├─ Student: Siswa Y
   └─ Reason: [alasan]

9. Display success notification
10. [OPTIONAL] Send confirmation to siswa via WA
```

#### Scenario C: Admin Koreksi Data (Override)

```
USE CASE: Ada kesalahan data, perlu koreksi
1. Admin login ke sistem
2. Navigate: "Admin" → "Kelola Absensi" → "Koreksi Data"
3. Search siswa by NIS/Nama/Kelas
4. Pilih tanggal yang perlu dikoreksi
5. Admin can:
   ├─ Edit existing record (change time, status, method)
   ├─ Delete wrong record
   └─ Add new record (full manual input)

6. System save with:
   ├─ check_in_method = 'manual_admin'
   ├─ check_in_approved_by = [admin_id]
   └─ check_in_notes = "Admin correction: [reason]"

7. Log to audit trail (critical)
```

---

### 2.2 Flow: Absen Pulang (Check-Out)

#### Scenario A: Siswa Tap NFC (Card/Mobile) - Normal Flow

```
TIME: 15:00 - 18:00
1. Siswa tap kartu MIFARE / smartphone NFC
2. System validate:
   ├─ Already checked in today? → No: REJECT "Belum absen datang"
   ├─ Already checked out? → Yes: REJECT "Sudah absen pulang"
   ├─ Holiday? → Yes: REJECT "Hari ini libur"
   ├─ Time valid (15:00-18:00)? → No: REJECT "Di luar jam absensi pulang"
   └─ [If Mobile] GPS valid? → No: REJECT "Di luar area sekolah"

3. System determine status:
   ├─ Time ≥ 15:30 → Status: "checkout_normal"
   ├─ Time < 15:30 → Status: "checkout_early_unauthorized" (default)
   │   ├─ Calculate: early_minutes = (15:30 - current_time)
   │   └─ Trigger alert to wali kelas: "Siswa X pulang cepat tanpa izin"
   └─ Save to attendances:
       ├─ check_out_time = current_time
       ├─ check_out_status = status
       ├─ check_out_method = 'nfc_card' or 'nfc_mobile'
       ├─ check_out_early_minutes = early_minutes (if early)
       ├─ is_complete = TRUE
       └─ [If mobile] GPS data

4. Return response
5. If early checkout → Require manual approval by wali kelas later
```

#### Scenario B: Siswa Izin Pulang Cepat (Ada Keperluan)

```
PROPER FLOW:
1. Siswa minta izin ke wali kelas SEBELUM pulang
   - Via WhatsApp atau langsung ke ruang guru
   - Jelaskan alasan (sakit, keperluan keluarga, dll)

2. Wali Kelas approve:
   - Option 1: Input manual pulang cepat di sistem
   - Option 2: Tandai "pre-approved early checkout" untuk siswa X hari ini

3. Siswa tap pulang (sebelum 15:30)
4. System cek: Ada pre-approval?
   ├─ Yes → Status: "checkout_early_permitted" (Pulang cepat berizin)
   └─ No → Status: "checkout_early_unauthorized" + Alert wali kelas

5. Wali kelas dapat approve/reject later via web:
   - Approve: Change status to "checkout_early_permitted"
   - Reject: Status tetap "checkout_early_unauthorized" → Sanksi
```

#### Scenario C: Siswa Lupa Tap Pulang - Lapor ke Wali Kelas

```
TIME: Siswa sadar lupa tap pulang (misal malam hari)
1. Siswa chat wali kelas: "Pak/Bu saya lupa tap pulang, saya pulang jam 15:45"
2. Wali Kelas verify:
   - Cek CCTV (if available)
   - Cek dengan teman sekelas
   - Cek kewajaran waktu

3. Wali Kelas input manual via web:
   ├─ Siswa: [Select]
   ├─ Tipe: "Hadir - Pulang"
   ├─ Waktu: 15:45
   ├─ Metode: "Manual Input"
   ├─ Alasan: "Lupa tap kartu"
   └─ Evidence: Chat screenshot

4. System save:
   ├─ check_out_time = 15:45
   ├─ check_out_status = "checkout_normal"
   ├─ check_out_method = 'manual_walikelas'
   ├─ check_out_approved_by = wali_kelas_id
   └─ is_complete = TRUE
```

#### Scenario D: Siswa Bolos / Tidak Tap Pulang & Tidak Lapor

```
AUTOMATED DETECTION SYSTEM:

Cron Job 1: Jam 18:30 (setelah jam pulang berakhir)
For each student where:
  - check_in_time IS NOT NULL (sudah tap datang)
  - check_out_time IS NULL (belum tap pulang)
  - No manual attendance record for checkout

Actions:
1. Send alert to wali kelas:
   "PERHATIAN: Siswa [Nama] belum absen pulang.
    Datang: [jam], Pulang: -
    Mohon segera konfirmasi apakah siswa:
    a) Lupa tap pulang
    b) Mengikuti kegiatan ekstrakurikuler
    c) Bolos/pulang tanpa izin"

2. Mark temporary status: "pending_checkout_confirmation"
3. Create pending task for wali kelas

Wali Kelas Action (Before 20:00 same day):
Option A: "Siswa lupa tap, sebenarnya pulang jam X"
   → Input manual checkout

Option B: "Siswa mengikuti ekstrakurikuler/OSIS sampai jam X"
   → Input manual checkout + note

Option C: "Siswa bolos / pulang tanpa izin"
   → Confirm as "truant"

Option D: "Belum konfirmasi / no response"
   → Auto-escalate

Cron Job 2: Jam 20:00 (deadline konfirmasi)
For each pending_checkout_confirmation:
If no action from wali kelas:
  1. Auto-mark as: "checkout_early_unauthorized" (asumsi pulang diam-diam)
  2. Send alert to admin & kepala sekolah
  3. Add to violation report
  4. Create task for investigation next day

Next Day (H+1):
Wali kelas must investigate & update:
  - Interview siswa
  - Check with orang tua
  - Final decision: Bolos / Lupa tap / Izin
  - Update attendance status accordingly
```

---

### 2.3 Flow: Izin / Sakit / Dispensasi

#### Scenario A: Siswa Izin Tidak Masuk (Planned Absence)

```
BEST PRACTICE FLOW:
1. Orang tua/siswa inform wali kelas (H-1 atau pagi hari):
   - Via WhatsApp / Telepon / Surat
   - Jelaskan alasan: keperluan keluarga, acara, dll
   - Attach evidence: Surat/foto undangan/dll

2. Wali Kelas input ke sistem:
   Route: "Kelola Absensi" → "Input Izin/Sakit/Dispensasi"
   ├─ Siswa: [Select]
   ├─ Tanggal: [Hari ini atau tanggal yang dimaksud]
   ├─ Tipe: "Izin"
   ├─ Alasan: "Acara keluarga / Keperluan keluarga"
   ├─ Durasi: 1 hari / Multi hari (if >1 hari)
   ├─ Evidence: Upload surat/foto
   └─ Contact: Nomor orang tua yang confirm

3. System save to manual_attendances:
   ├─ type = 'excused' (izin)
   ├─ reason = [alasan]
   ├─ attachment = [file]
   ├─ created_by = wali_kelas_id
   ├─ status = 'approved'
   └─ approved_at = NOW()

4. System behavior:
   - Tidak mengirim notifikasi "lupa absen" ke siswa
   - Tidak auto-mark sebagai alpha
   - Count sebagai "Izin" di statistik (75% credit)
```

#### Scenario B: Siswa Sakit

```
Similar dengan Izin, tapi:
1. Evidence preferred: Surat dokter (for >3 days)
2. For 1-2 days: Konfirmasi orang tua via WA acceptable
3. Type: 'sick'
4. If no surat dokter after 3 days → convert to Izin
5. Count sebagai "Sakit" di statistik (75% credit)

ENHANCEMENT: Sick Leave Tracking
- Track total sick days per semester
- Alert if > 7 days (might need medical check)
- Report to orang tua & BK
```

#### Scenario C: Dispensasi (Hadir tapi Izin Kegiatan)

```
USE CASE: Siswa mengikuti lomba, OSIS, pramuka, dll
1. Pembina/Guru pengampu inform ke wali kelas
2. Wali kelas input dispensasi:
   ├─ Tipe: "Dispensasi"
   ├─ Kegiatan: "Lomba LKS Tingkat Provinsi"
   ├─ Waktu: 08:00 - 12:00 (partial) atau Full day
   ├─ List siswa: [Multiple select]
   └─ Evidence: Surat tugas

3. System:
   - Mark as present but excused from class
   - Count as 100% attendance (not penalized)
   - Show in report: "Dispensasi - [kegiatan]"
```

---

### 2.4 Flow: Alpha (Tidak Hadir Tanpa Keterangan)

#### Automated Alpha Detection

```
Cron Job: Every day at 23:55
For each active student:
  Check today's attendance:

  IF (
    No check_in record AND
    No manual_attendance record (izin/sakit/dispensasi) AND
    Not holiday AND
    Not weekend
  ) THEN:

    1. Create manual_attendance:
       ├─ student_id = [student]
       ├─ date = TODAY
       ├─ type = 'absent' (alpha)
       ├─ reason = 'Tidak hadir tanpa keterangan'
       ├─ created_by = NULL (system)
       ├─ status = 'auto_generated'
       └─ needs_confirmation = TRUE

    2. Send alert to wali kelas (untuk investigasi besok):
       "Siswa [Nama] tidak hadir dan tidak ada keterangan.
        Mohon konfirmasi ke siswa/orang tua besok."

    3. Add to pending investigation list

Next Day (H+1):
Wali kelas must follow up:
  - Contact siswa/orang tua
  - Cek alasan sebenarnya
  - Update record:
    - If ada alasan valid → Change to Izin/Sakit (with evidence)
    - If memang bolos → Confirm as Alpha + Sanksi
    - If no response → Escalate to BK
```

---

## 3. GRACE PERIOD & FLEXIBILITY RULES

### 3.1 Time Windows

```
Check-In (Datang):
├─ Early Bird: 06:00 - 07:00 (Sangat tepat waktu)
├─ On Time: 07:01 - 07:30 (Tepat waktu)
├─ Late: 07:31 - 08:00 (Terlambat ringan)
├─ Very Late: 08:01 - 08:30 (Terlambat berat - warning)
└─ Closed: > 08:30 (Tidak bisa tap - harus manual by walas)

Check-Out (Pulang):
├─ Early: 15:00 - 15:29 (Perlu approval walas)
├─ On Time: 15:30 - 16:00 (Normal)
└─ Extended: 16:01 - 18:00 (Ekstrakurikuler/tugas tambahan)
```

### 3.2 Grace Period for Manual Input

```
Wali Kelas can input manual for:
├─ H-0 (hari ini): Unlimited until 23:59
├─ H-1 (kemarin): Until 12:00 (next day noon)
└─ H-2 and beyond: Need admin approval

Admin can input manual for:
└─ Any date within current semester (full access)

Rationale:
- H-1 grace period allows siswa to report next morning
- After H-1, data considered frozen (untuk integritas data)
- Exception cases handled by admin
```

### 3.3 Conflict Resolution

```
Scenario: Student has both NFC tap AND manual input for same time

Rule 1: NFC tap takes precedence (if timestamps close)
├─ If manual input time ≈ NFC tap time (±15 min)
└─ Keep NFC tap, mark manual as "duplicate/cancelled"

Rule 2: If times significantly different
├─ Flag for wali kelas review
├─ Wali kelas choose which to keep
└─ Log the decision

Rule 3: If NFC shows "late" but manual shows "on-time"
├─ Require evidence from wali kelas
├─ Admin approval needed
└─ Strict audit logging
```

---

## 4. ATTENDANCE PERCENTAGE CALCULATION

### 4.1 Weighted Attendance Formula

```php
// Complex calculation considering all factors

$totalDaysEffective = getTotalEffectiveDays($semester);
// Effective days = school days - holidays

$weights = [
    'present_ontime' => 1.0,      // 100%
    'present_late' => 1.0,         // 100% (late but present)
    'checkout_normal' => 1.0,
    'checkout_early_permitted' => 1.0,
    'checkout_early_unauthorized' => 0.5,  // 50% (violation)
    'dispensation' => 1.0,         // 100% (official excuse)
    'excused' => 0.75,             // 75% (izin)
    'sick' => 0.75,                // 75% (sakit)
    'absent' => 0.0,               // 0% (alpha)
    'truant' => 0.0,               // 0% (bolos)
    'very_late' => 0.5,            // 50% (terlambat berat)
];

function calculateAttendancePercentage($studentId, $semesterId) {
    $attendances = getAttendances($studentId, $semesterId);

    $totalScore = 0;
    foreach ($attendances as $att) {
        // Check-in score
        if ($att->check_in_status) {
            $totalScore += $weights[$att->check_in_status];
        }

        // Deductions for violations
        if ($att->check_in_late_minutes > 30) {
            $totalScore -= 0.1; // -10% for being very late
        }

        if ($att->check_out_status == 'checkout_early_unauthorized') {
            $totalScore -= 0.3; // -30% for unauthorized early leave
        }
    }

    // Handle manual attendances
    $manualAttendances = getManualAttendances($studentId, $semesterId);
    foreach ($manualAttendances as $manual) {
        $totalScore += $weights[$manual->type];
    }

    $totalDaysEffective = getTotalEffectiveDays($semesterId);
    $percentage = ($totalScore / $totalDaysEffective) * 100;

    return min(100, max(0, $percentage)); // Cap at 0-100%
}
```

### 4.2 Attendance Grading

```
A  : 95% - 100% (Excellent - Sangat Baik)
B+ : 90% - 94%  (Very Good - Baik Sekali)
B  : 85% - 89%  (Good - Baik)
C+ : 80% - 84%  (Fair - Cukup Baik)
C  : 75% - 79%  (Sufficient - Cukup)
D  : 70% - 74%  (Poor - Kurang)
E  : < 70%      (Very Poor - Sangat Kurang)

Academic Impact:
- < 75% : Warning letter to parents
- < 70% : Cannot take final exam (need special permission)
- < 60% : Repeat the semester
```

---

## 5. VIOLATION TRACKING & SANCTIONS

### 5.1 Violation Types

```sql
CREATE TABLE attendance_violations (
    id BIGINT PRIMARY KEY,
    student_id BIGINT,
    attendance_id BIGINT NULL,
    violation_type ENUM(
        'unauthorized_early_checkout',  -- Pulang cepat tanpa izin
        'truant',                        -- Bolos
        'excessive_lateness',            -- Terlambat > 3x/minggu
        'no_checkout',                   -- Tidak absen pulang
        'fake_excuse'                    -- Keterangan palsu
    ),
    severity ENUM('ringan', 'sedang', 'berat'),
    date DATE,
    description TEXT,
    evidence VARCHAR(255),
    handled_by BIGINT NULL,
    sanction VARCHAR(255) NULL,
    sanction_date DATE NULL,
    resolved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP
);
```

### 5.2 Automated Violation Detection

```
Daily Check (23:59):
1. Excessive Lateness Check:
   ├─ Count late arrivals this week
   ├─ If >= 3 times → Create violation "excessive_lateness"
   └─ Alert wali kelas + BK

2. Truancy Pattern Detection:
   ├─ Check alpha + bolos count this month
   ├─ If >= 3 times → Create violation "truant"
   └─ Escalate to BK + orang tua

3. Unauthorized Early Checkout:
   ├─ Auto-created when checkout < 15:30 without approval
   └─ Walas must review & confirm

Weekly Report (every Monday):
- Send violation summary to wali kelas
- Highlight students needing intervention
- Suggest follow-up actions
```

### 5.3 Sanction Escalation

```
Level 1 (Warning - Ringan):
├─ Terlambat 3x dalam 1 minggu
├─ Pulang cepat 1x tanpa izin
└─ Action: Teguran lisan + surat peringatan

Level 2 (Moderate - Sedang):
├─ Alpha 2x dalam 1 bulan
├─ Bolos 1x
├─ Terlambat 5x dalam 1 bulan
└─ Action: Surat peringatan ke ortu + poin pelanggaran

Level 3 (Severe - Berat):
├─ Alpha ≥ 3x dalam 1 bulan
├─ Bolos ≥ 2x
├─ Keterangan palsu (caught)
└─ Action: Skorsing + panggilan ortu + poin berat

Automated Sanctions:
- System auto-generate surat peringatan
- Track poin pelanggaran
- Integration dengan sistem BK (optional)
```

---

## 6. APPROVAL WORKFLOW (ADVANCED)

### 6.1 Multi-Level Approval for Edge Cases

```
Scenario: Siswa sakit > 7 hari berturut-turut

Workflow:
1. Wali Kelas input sakit (day 1-3):
   - Status: "sick"
   - Evidence: Chat orang tua
   - Approval: Auto-approved

2. Day 4-7:
   - Wali Kelas input
   - Status: "sick_pending_verification"
   - System alert: "Perlu surat dokter"
   - Walas must upload medical certificate

3. If no medical cert by day 7:
   - Auto-escalate to admin
   - Admin review required
   - Decision:
     ├─ Approve as sick (with justification)
     ├─ Convert to izin (reduce credit)
     └─ Reject → mark as alpha (if fabricated)
```

### 6.2 Bulk Approval Interface

```
USE CASE: Wali kelas need to approve multiple early checkouts

Dashboard Feature: "Pending Approvals"
Shows list of:
├─ Students who checked out early (unauthorized)
├─ Students with pending manual input
└─ Students flagged by system

Bulk Actions:
☑ Select multiple students
├─ Approve All (change status to permitted)
├─ Reject All (keep as violation)
├─ Approve with note
└─ Request more evidence

Reduces manual work for walas
```

---

## 7. INTEGRATION WITH OTHER SCHOOL SYSTEMS

### 7.1 Integration dengan Raport (Nilai Kehadiran)

```
At semester end:
1. Calculate final attendance percentage
2. Convert to attendance grade (A, B, C, etc.)
3. Export to raport system
4. Include details:
   ├─ Total hari efektif
   ├─ Hadir: X hari
   ├─ Sakit: Y hari
   ├─ Izin: Z hari
   ├─ Alpha: A hari
   └─ Persentase: XX%
```

### 7.2 Integration dengan BK (Bimbingan Konseling)

```
Auto-referral triggers:
├─ Alpha ≥ 3x dalam 1 bulan → Refer to BK
├─ Late ≥ 10x dalam 1 semester → Counseling needed
├─ Attendance < 75% → Intensive intervention
└─ Violation level 3 → Mandatory counseling

BK Dashboard shows:
- Students at risk (attendance < 80%)
- Recent violations
- Suggested interventions
```

### 7.3 Parent Portal (Optional Enhancement)

```
Features:
1. View child's attendance real-time
2. Receive notifications:
   - Child didn't check in (by 08:45)
   - Child checked out early
   - Violation detected
3. Submit excuse letters online
4. Download monthly attendance report
5. Chat with wali kelas

Security:
- Parent account linked to student
- OTP verification
- Read-only access
```

---

## 8. REPORTING ENHANCEMENTS

### 8.1 Advanced Reports

```
1. Attendance Trend Analysis:
   - Graph showing attendance over time
   - Identify patterns (e.g., Senin paling banyak alpha)
   - Seasonal trends

2. Risk Score Report:
   - Calculate "risk score" per student
   - Based on: alpha count, late count, violations
   - Early warning for potential dropouts

3. Comparative Class Report:
   - Compare attendance across classes
   - Same jurusan, same grade level
   - Identify best practices from top classes

4. Method Analysis:
   - % NFC card vs mobile vs manual
   - Identify students frequently using manual
   - Flag potential abuse

5. Location Analysis (Geofencing):
   - Which location most used
   - Outlier detection (always at furthest valid point)
   - GPS accuracy distribution
```

---

## 9. DATA INTEGRITY & AUDIT

### 9.1 Change Tracking

```
Every attendance record change must log:
├─ What changed (before → after)
├─ Who changed it (user_id)
├─ When changed (timestamp)
├─ Why changed (reason)
└─ Evidence (if any)

Immutable after:
- Semester closed
- Report exported to raport
- Unless admin override with justification
```

### 9.2 Anomaly Detection

```
System flags for review:
├─ Same student checked in from 2 locations simultaneously
├─ Manual input time conflicts with NFC tap
├─ GPS shows student at school but didn't tap
├─ Student always manual input (never NFC) → investigate
├─ Wali kelas approves too many early checkouts → audit
└─ Sudden change in attendance pattern → check

Weekly anomaly report to admin
```

---

## 10. SUMMARY: FLEXIBILITY VS CONTROL

### ✅ FLEXIBILITY (User-Friendly):
- Multiple input methods (NFC card, mobile, manual)
- Grace periods for late reporting
- Wali kelas can help students who forgot
- Weighted attendance (not just binary present/absent)
- Special circumstances accommodated

### 🔒 CONTROL (Data Integrity):
- All manual inputs must have reason + evidence
- Time limits for retroactive changes
- Approval workflows for edge cases
- Comprehensive audit trail
- Violation tracking and sanctions
- Anomaly detection

### 🎯 BALANCE:
The system is:
- **Forgiving** for honest mistakes (lupa tap, kartu hilang)
- **Strict** for violations (bolos, pulang diam-diam)
- **Transparent** (all actions logged)
- **Fair** (consistent rules, clear sanctions)

---

**Document Control:**
- **Author:** Business Logic Architect
- **Version:** 2.0 Enhanced
- **Last Updated:** 13 Desember 2025

