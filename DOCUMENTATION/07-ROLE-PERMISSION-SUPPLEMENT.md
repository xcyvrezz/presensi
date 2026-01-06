# ROLE & PERMISSION MATRIX - SUPPLEMENT
## Enhanced Manual Attendance Permissions

**Versi:** 2.0 - Enhanced Edition
**Tanggal:** 13 Desember 2025
**Purpose:** Additional permissions untuk manual attendance workflow

---

## 1. NEW PERMISSION GROUPS

### 1.1 Permission Group: MANUAL_ATTENDANCE (Enhanced)

| Permission | Name | Display Name | Description |
|------------|------|--------------|-------------|
| P-MATT-001 | manual_att.input_checkin | Input Manual Check-In | Wali kelas/admin input absen datang manual |
| P-MATT-002 | manual_att.input_checkout | Input Manual Check-Out | Wali kelas/admin input absen pulang manual |
| P-MATT-003 | manual_att.input_izin | Input Izin | Input siswa izin (sama seperti sebelumnya) |
| P-MATT-004 | manual_att.input_sakit | Input Sakit | Input siswa sakit |
| P-MATT-005 | manual_att.input_dispensasi | Input Dispensasi | Input dispensasi |
| P-MATT-006 | manual_att.input_alpha | Input Alpha | Confirm alpha (admin/walas) |
| P-MATT-007 | manual_att.approve_early_checkout | Approve Pulang Cepat | Approve siswa pulang cepat |
| P-MATT-008 | manual_att.bulk_input | Bulk Input | Input multiple siswa sekaligus (untuk dispensasi massal) |
| P-MATT-009 | manual_att.retroactive_h1 | Input H-1 (Kemarin) | Input data kemarin (grace period) |
| P-MATT-010 | manual_att.retroactive_any | Input Tanggal Lama | Input data tanggal kapan saja (admin only) |

### 1.2 Permission Group: VIOLATIONS

| Permission | Name | Display Name | Description |
|------------|------|--------------|-------------|
| P-VIO-001 | violations.view_all | Lihat Semua Pelanggaran | Admin/Kepala Sekolah lihat semua |
| P-VIO-002 | violations.view_own_class | Lihat Pelanggaran Kelas | Wali kelas lihat kelas sendiri |
| P-VIO-003 | violations.create | Buat Laporan Pelanggaran | Report violation |
| P-VIO-004 | violations.handle | Handle Pelanggaran | Proses & beri sanksi (BK/admin) |
| P-VIO-005 | violations.resolve | Resolve Pelanggaran | Mark as resolved |
| P-VIO-006 | violations.export | Export Pelanggaran | Export violation report |

### 1.3 Permission Group: APPROVALS

| Permission | Name | Display Name | Description |
|------------|------|--------------|-------------|
| P-APP-001 | approvals.view_pending | Lihat Pending Approval | View yang perlu di-approve |
| P-APP-002 | approvals.approve_level1 | Approve Level 1 | Wali kelas approve |
| P-APP-003 | approvals.approve_level2 | Approve Level 2 | Admin/Kepala approve (multi-level) |
| P-APP-004 | approvals.reject | Reject Approval | Reject request |
| P-APP-005 | approvals.bulk_approve | Bulk Approve | Approve banyak sekaligus |

### 1.4 Permission Group: ANOMALIES

| Permission | Name | Display Name | Description |
|------------|------|--------------|-------------|
| P-ANO-001 | anomalies.view | Lihat Anomali | View flagged anomalies |
| P-ANO-002 | anomalies.review | Review Anomali | Review & take action |
| P-ANO-003 | anomalies.dismiss | Dismiss Anomali | Mark sebagai false positive |

---

## 2. UPDATED ROLE-PERMISSION MAPPING

### 2.1 Role: ADMIN (Administrator)

**TAMBAHAN dari versi sebelumnya:**

| Group | New Permissions |
|-------|-----------------|
| Manual Attendance | P-MATT-001 s/d P-MATT-010 (ALL) |
| Violations | P-VIO-001, P-VIO-003, P-VIO-004, P-VIO-005, P-VIO-006 |
| Approvals | P-APP-001, P-APP-002, P-APP-003, P-APP-004, P-APP-005 |
| Anomalies | P-ANO-001, P-ANO-002, P-ANO-003 |

**Total New Permissions:** 24
**Total Permissions for Admin:** 48 + 24 = **72 permissions**

---

### 2.2 Role: KEPALA SEKOLAH (Kepala Sekolah)

**TAMBAHAN:**

| Group | New Permissions | Notes |
|-------|-----------------|-------|
| Violations | P-VIO-001, P-VIO-006 | View all + export (monitoring) |
| Approvals | P-APP-001, P-APP-003 | View pending + approve level 2 (untuk kasus escalated) |
| Anomalies | P-ANO-001 | View saja (untuk awareness) |

**Total New Permissions:** 4
**Total Permissions for Kepala Sekolah:** 13 + 4 = **17 permissions**

**Business Logic:**
- Kepala sekolah bisa approve level 2 untuk kasus khusus (misal: sakit > 2 minggu, dll)
- Monitoring violations untuk evaluasi kedisiplinan sekolah

---

### 2.3 Role: WALI KELAS (Wali Kelas)

**TAMBAHAN - INI YANG PENTING!:**

| Group | New Permissions | Notes |
|-------|-----------------|-------|
| Manual Attendance | P-MATT-001, P-MATT-002, P-MATT-003, P-MATT-004, P-MATT-005, P-MATT-007, P-MATT-008, P-MATT-009 | **Hampir semua manual input!** |
| Violations | P-VIO-002, P-VIO-003 | View kelas sendiri + create violation report |
| Approvals | P-APP-001, P-APP-002, P-APP-004, P-APP-005 | Approve/reject level 1 + bulk |
| Anomalies | P-ANO-001 | View (untuk kelas sendiri) |

**Total New Permissions:** 13
**Total Permissions for Wali Kelas:** 11 + 13 = **24 permissions**

**Detailed Capabilities:**

1. **Input Manual Check-In (P-MATT-001):**
   - Siswa lupa tap → Wali kelas input jam datang
   - Scope: Hanya siswa di kelasnya
   - Time limit: H-0 dan H-1 (P-MATT-009)
   - Required: Alasan + evidence (opsional)

2. **Input Manual Check-Out (P-MATT-002):**
   - Siswa lupa tap pulang → Wali kelas input jam pulang
   - Sama seperti check-in

3. **Input Izin/Sakit/Dispensasi (P-MATT-003/004/005):**
   - Existing functionality (sudah ada)
   - Enhanced dengan approval workflow

4. **Approve Pulang Cepat (P-MATT-007):**
   - Siswa pulang < 15:30 → system flag as "needs approval"
   - Wali kelas bisa approve dengan alasan
   - Change status from "unauthorized" → "permitted"

5. **Bulk Input (P-MATT-008):**
   - USE CASE: Dispensasi massal (10 siswa ikut lomba)
   - Select multiple siswa → input sekali jalan

6. **Input H-1 (P-MATT-009):**
   - Grace period: bisa input data kemarin sampai jam 12:00 hari ini
   - USE CASE: Siswa lapor pagi hari bahwa kemarin lupa tap
   - Setelah jam 12:00 → harus via admin

7. **View & Create Violations (P-VIO-002/003):**
   - Wali kelas bisa report pelanggaran siswa kelasnya
   - Misal: siswa bolos, caught red-handed

8. **Approval Workflow (P-APP-001/002/004/005):**
   - Dashboard showing "Pending Approvals"
   - Approve/reject dengan 1 klik
   - Bulk approve untuk efficiency

**Restrictions:**
- ❌ TIDAK BISA: Input alpha manual (P-MATT-006) → only admin
- ❌ TIDAK BISA: Input retroactive any date (P-MATT-010) → only admin
- ❌ TIDAK BISA: Handle violations (P-VIO-004) → only admin/BK
- ❌ TIDAK BISA: Review anomalies (P-ANO-002) → only admin

---

### 2.4 Role: SISWA (Siswa)

**TIDAK ADA TAMBAHAN PERMISSIONS**
- Siswa tetap hanya bisa tap untuk absen
- Tidak bisa input manual sendiri
- Must contact wali kelas for manual input

**Total Permissions for Siswa:** 5 permissions (unchanged)

---

## 3. UPDATED COMPLETE PERMISSION MATRIX

| Permission | Admin | Kepala Sekolah | Wali Kelas | Siswa |
|------------|:-----:|:--------------:|:----------:|:-----:|
| **MANUAL ATTENDANCE** |
| manual_att.input_checkin | ✅ | ❌ | ✅ | ❌ |
| manual_att.input_checkout | ✅ | ❌ | ✅ | ❌ |
| manual_att.input_izin | ✅ | ❌ | ✅ | ❌ |
| manual_att.input_sakit | ✅ | ❌ | ✅ | ❌ |
| manual_att.input_dispensasi | ✅ | ❌ | ✅ | ❌ |
| manual_att.input_alpha | ✅ | ❌ | ❌ | ❌ |
| manual_att.approve_early_checkout | ✅ | ❌ | ✅ | ❌ |
| manual_att.bulk_input | ✅ | ❌ | ✅ | ❌ |
| manual_att.retroactive_h1 | ✅ | ❌ | ✅ | ❌ |
| manual_att.retroactive_any | ✅ | ❌ | ❌ | ❌ |
| **VIOLATIONS** |
| violations.view_all | ✅ | ✅ | ❌ | ❌ |
| violations.view_own_class | ❌ | ❌ | ✅ | ❌ |
| violations.create | ✅ | ❌ | ✅ | ❌ |
| violations.handle | ✅ | ❌ | ❌ | ❌ |
| violations.resolve | ✅ | ❌ | ❌ | ❌ |
| violations.export | ✅ | ✅ | ❌ | ❌ |
| **APPROVALS** |
| approvals.view_pending | ✅ | ✅ | ✅ | ❌ |
| approvals.approve_level1 | ✅ | ❌ | ✅ | ❌ |
| approvals.approve_level2 | ✅ | ✅ | ❌ | ❌ |
| approvals.reject | ✅ | ❌ | ✅ | ❌ |
| approvals.bulk_approve | ✅ | ❌ | ✅ | ❌ |
| **ANOMALIES** |
| anomalies.view | ✅ | ✅ | ✅ | ❌ |
| anomalies.review | ✅ | ❌ | ❌ | ❌ |
| anomalies.dismiss | ✅ | ❌ | ❌ | ❌ |

---

## 4. MIDDLEWARE IMPLEMENTATION (Laravel)

### 4.1 Middleware: CheckManualInputScope

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckManualInputScope
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        $studentId = $request->input('student_id');

        // Admin can input for anyone
        if ($user->role->name === 'admin') {
            return $next($request);
        }

        // Wali kelas can only input for their class
        if ($user->role->name === 'wali_kelas') {
            $student = Student::find($studentId);

            if (!$student) {
                abort(404, 'Siswa tidak ditemukan');
            }

            // Check if user is homeroom teacher of this student's class
            $allowedClassIds = $user->homeroomClasses()->pluck('id')->toArray();

            if (!in_array($student->class_id, $allowedClassIds)) {
                abort(403, 'Anda tidak memiliki akses untuk input absensi siswa ini');
            }

            // Check time restriction for H-1
            if ($request->input('date') < today()) {
                if (!$user->can('manual_att.retroactive_h1')) {
                    abort(403, 'Anda tidak memiliki izin input data kemarin');
                }

                // Check if within grace period (until 12:00 next day)
                $inputDate = Carbon::parse($request->input('date'));
                $graceDeadline = $inputDate->copy()->addDay()->setTime(12, 0);

                if (now() > $graceDeadline) {
                    abort(403, 'Grace period untuk input data kemarin sudah berakhir (sampai jam 12:00 hari berikutnya)');
                }
            }

            return $next($request);
        }

        abort(403, 'Anda tidak memiliki izin untuk input manual');
    }
}
```

### 4.2 Middleware: CheckApprovalAuthority

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApprovalAuthority
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        $approvalId = $request->route('id');
        $approval = AttendanceApproval::findOrFail($approvalId);

        // Admin can approve anything
        if ($user->role->name === 'admin') {
            return $next($request);
        }

        // Kepala sekolah can approve level 2
        if ($user->role->name === 'kepala_sekolah') {
            if ($approval->approver_level_1 === null) {
                abort(403, 'Approval ini belum di-approve oleh wali kelas (level 1)');
            }
            return $next($request);
        }

        // Wali kelas can approve level 1 for their class
        if ($user->role->name === 'wali_kelas') {
            $student = $approval->student;
            $allowedClassIds = $user->homeroomClasses()->pluck('id')->toArray();

            if (!in_array($student->class_id, $allowedClassIds)) {
                abort(403, 'Bukan siswa kelas Anda');
            }

            return $next($request);
        }

        abort(403, 'Anda tidak memiliki izin approval');
    }
}
```

---

## 5. ROUTE DEFINITIONS (Laravel)

```php
// routes/web.php

Route::middleware(['auth'])->group(function () {

    // Manual Attendance Routes
    Route::prefix('manual-attendance')->name('manual_attendance.')->group(function () {

        // Input Manual Check-In
        Route::post('/checkin', [ManualAttendanceController::class, 'storeCheckIn'])
            ->middleware(['permission:manual_att.input_checkin', CheckManualInputScope::class])
            ->name('checkin.store');

        // Input Manual Check-Out
        Route::post('/checkout', [ManualAttendanceController::class, 'storeCheckOut'])
            ->middleware(['permission:manual_att.input_checkout', CheckManualInputScope::class])
            ->name('checkout.store');

        // Input Izin/Sakit/Dispensasi
        Route::post('/excused', [ManualAttendanceController::class, 'storeExcused'])
            ->middleware(['permission:manual_att.input_izin', CheckManualInputScope::class])
            ->name('excused.store');

        // Bulk Input
        Route::post('/bulk', [ManualAttendanceController::class, 'bulkStore'])
            ->middleware(['permission:manual_att.bulk_input', CheckManualInputScope::class])
            ->name('bulk.store');
    });

    // Approval Routes
    Route::prefix('approvals')->name('approvals.')->group(function () {

        // View Pending
        Route::get('/', [ApprovalController::class, 'index'])
            ->middleware('permission:approvals.view_pending')
            ->name('index');

        // Approve
        Route::post('/{id}/approve', [ApprovalController::class, 'approve'])
            ->middleware([CheckApprovalAuthority::class])
            ->name('approve');

        // Reject
        Route::post('/{id}/reject', [ApprovalController::class, 'reject'])
            ->middleware(['permission:approvals.reject', CheckApprovalAuthority::class])
            ->name('reject');

        // Bulk Approve
        Route::post('/bulk-approve', [ApprovalController::class, 'bulkApprove'])
            ->middleware('permission:approvals.bulk_approve')
            ->name('bulk_approve');
    });

    // Violation Routes
    Route::resource('violations', ViolationController::class)
        ->middleware('permission:violations.view_all|violations.view_own_class');

});
```

---

## 6. DASHBOARD WIDGETS untuk Wali Kelas

### 6.1 Pending Approvals Widget

```php
// Wali Kelas Dashboard

<div class="card">
    <div class="card-header">
        <h3>Pending Approvals <span class="badge badge-warning">{{ $pendingCount }}</span></h3>
    </div>
    <div class="card-body">
        @foreach($pendingApprovals as $approval)
            <div class="approval-item">
                <strong>{{ $approval->student->name }}</strong> - {{ $approval->approval_type }}
                <br>
                <small>{{ $approval->request_reason }}</small>
                <div class="btn-group">
                    <button class="btn btn-sm btn-success" onclick="approve({{ $approval->id }})">
                        Approve
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="reject({{ $approval->id }})">
                        Reject
                    </button>
                </div>
            </div>
        @endforeach

        @if($pendingCount > 5)
            <a href="{{ route('approvals.index') }}">Lihat semua ({{ $pendingCount }})</a>
        @endif
    </div>
</div>
```

### 6.2 Quick Manual Input Widget

```php
<div class="card">
    <div class="card-header">
        <h3>Input Manual Cepat</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('manual_attendance.checkin.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Siswa</label>
                <select name="student_id" class="form-control" required>
                    <option value="">Pilih Siswa</option>
                    @foreach($myStudents as $student)
                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="date" class="form-control"
                       value="{{ today()->format('Y-m-d') }}"
                       max="{{ today()->format('Y-m-d') }}"
                       required>
            </div>

            <div class="form-group">
                <label>Tipe</label>
                <select name="type" class="form-control" required>
                    <option value="checkin">Absen Datang</option>
                    <option value="checkout">Absen Pulang</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="dispensasi">Dispensasi</option>
                </select>
            </div>

            <div class="form-group" id="time-input" style="display:none;">
                <label>Jam</label>
                <input type="time" name="time" class="form-control">
            </div>

            <div class="form-group">
                <label>Alasan</label>
                <textarea name="reason" class="form-control" rows="2" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Submit</button>
        </form>
    </div>
</div>
```

---

## 7. SUMMARY

### Wali Kelas Sekarang Bisa:

✅ **Input manual absen datang** - untuk siswa yang lupa tap
✅ **Input manual absen pulang** - untuk siswa yang lupa tap pulang
✅ **Input izin/sakit/dispensasi** - existing functionality (enhanced)
✅ **Approve pulang cepat** - siswa pulang < 15:30 dengan alasan
✅ **Bulk input** - dispensasi massal untuk banyak siswa
✅ **Input H-1** - grace period sampai jam 12:00 hari berikutnya
✅ **View & create violations** - report pelanggaran siswa
✅ **Approve/reject requests** - workflow approval
✅ **View anomalies** - lihat flagged attendance anomalies

### Yang Masih Restricted:

❌ **Input alpha manual** - hanya admin (untuk avoid abuse)
❌ **Input retroactive any date** - hanya admin (data integrity)
❌ **Handle violations** - hanya admin/BK (sanksi)
❌ **Edit/delete attendance** - hanya admin (audit trail)

### Catatan Keterangan di Laporan:

Setiap attendance record akan menampilkan **metode**:
- 🔵 **NFC Card** - Tap menggunakan kartu fisik
- 📱 **NFC Mobile** - Tap via smartphone
- ✍️ **Manual (Wali Kelas)** - Input manual oleh wali kelas + alasan
- ⚙️ **Manual (Admin)** - Input/koreksi oleh admin

---

**Document Control:**
- **Author:** Permission Architect
- **Version:** 2.0 Enhanced
- **Status:** Ready for Implementation

