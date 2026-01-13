<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Student\CheckIn;
use App\Livewire\Student\CheckOut;
use App\Livewire\Admin\Users\UserIndex;
use App\Livewire\Admin\Users\UserCreate;
use App\Livewire\Admin\Users\UserEdit;
use App\Livewire\Admin\Students\StudentIndex;
use App\Livewire\Admin\Students\StudentCreate;
use App\Livewire\Admin\Students\StudentEdit;
use App\Livewire\Admin\Students\StudentImport;
use App\Livewire\Admin\Classes\ClassIndex;
use App\Livewire\Admin\Classes\ClassCreate;
use App\Livewire\Admin\Classes\ClassEdit;
use App\Livewire\Admin\Departments\DepartmentIndex;
use App\Livewire\Admin\Departments\DepartmentCreate;
use App\Livewire\Admin\Departments\DepartmentEdit;
use App\Livewire\Admin\Attendance\AttendanceIndex;
use App\Livewire\Admin\Attendance\ManualAttendance as AdminManualAttendance;
use App\Livewire\Admin\Attendance\MarkBolos;
use App\Livewire\Admin\Settings\AttendanceSettings;
use App\Livewire\Admin\Settings\SemesterSettings;
use App\Livewire\Admin\Calendar\AcademicCalendarManagement;
use App\Livewire\Admin\Analytics\AdvancedReports;
use App\Livewire\Admin\System\ActivityLogs;
use App\Livewire\Public\TappingStation;
use App\Livewire\WaliKelas\Dashboard as WaliKelasDashboard;
use App\Livewire\WaliKelas\Students as WaliKelasStudents;
use App\Livewire\WaliKelas\AttendanceReport as WaliKelasAttendance;
use App\Livewire\WaliKelas\AbsenceRequestApproval;
use App\Livewire\WaliKelas\ManualAttendance;
use App\Livewire\Student\Dashboard as StudentDashboard;
use App\Livewire\Student\AttendanceHistory;
use App\Livewire\Student\AbsenceRequest;
use App\Livewire\Student\Statistics;
use App\Livewire\Student\Profile;
use App\Livewire\Student\NfcCheckInOut;
use App\Livewire\KepalaSekolah\Dashboard as KepalaSekolahDashboard;
use App\Livewire\KepalaSekolah\Reports as KepalaSekolahReports;
use App\Http\Controllers\StorageController;

// Storage file serving route (public access for photos)
Route::get('/storage/{path}', [StorageController::class, 'serve'])
    ->where('path', '.*')
    ->name('storage.serve')
    ->withoutMiddleware(['auth', 'verified']);

// Public tapping station - default landing page
Route::get('/', TappingStation::class)->name('tapping');

// Guest routes (login)
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');

    // Admin routes
    Route::prefix('admin')->middleware('can:dashboard.view_admin')->group(function () {
        Route::get('/dashboard', AdminDashboard::class)->name('admin.dashboard');

        // User Management
        Route::get('/users', UserIndex::class)->name('admin.users.index');
        Route::get('/users/create', UserCreate::class)->name('admin.users.create');
        Route::get('/users/{user}/edit', UserEdit::class)->name('admin.users.edit');

        // Student Management
        Route::get('/students', StudentIndex::class)->name('admin.students.index');
        Route::get('/students/import', StudentImport::class)->name('admin.students.import');
        Route::get('/students/create', StudentCreate::class)->name('admin.students.create');
        Route::get('/students/{student}/edit', StudentEdit::class)->name('admin.students.edit');

        // Class Management
        Route::get('/classes', ClassIndex::class)->name('admin.classes.index');
        Route::get('/classes/create', ClassCreate::class)->name('admin.classes.create');
        Route::get('/classes/{classes}/edit', ClassEdit::class)->name('admin.classes.edit');

        // Department Management
        Route::get('/departments', DepartmentIndex::class)->name('admin.departments.index');
        Route::get('/departments/create', DepartmentCreate::class)->name('admin.departments.create');
        Route::get('/departments/{department}/edit', DepartmentEdit::class)->name('admin.departments.edit');

        // Attendance Data
        Route::get('/attendance', AttendanceIndex::class)->name('admin.attendance.index');
        Route::get('/attendance/manual', AdminManualAttendance::class)->name('admin.attendance.manual');
        Route::get('/attendance/mark-bolos', MarkBolos::class)->name('admin.attendance.mark-bolos');
        Route::get('/attendance/generate-alpha', \App\Livewire\Admin\Attendance\GenerateAlpha::class)->name('admin.attendance.generate-alpha');

        // Analytics
        Route::get('/analytics', AdvancedReports::class)->name('admin.analytics');

        // System
        Route::get('/system/activity-logs', ActivityLogs::class)->name('admin.system.logs');

        // Settings
        Route::get('/settings/attendance', AttendanceSettings::class)->name('admin.settings.attendance');
        Route::get('/settings/semester', SemesterSettings::class)->name('admin.settings.semester');
        Route::get('/settings/calendar', AcademicCalendarManagement::class)->name('admin.settings.calendar');
    });

    // Kepala Sekolah routes
    Route::prefix('kepala-sekolah')->middleware('can:dashboard.view_principal')->group(function () {
        Route::get('/dashboard', KepalaSekolahDashboard::class)->name('kepala-sekolah.dashboard');
        Route::get('/reports', KepalaSekolahReports::class)->name('kepala-sekolah.reports');
    });

    // Wali Kelas routes
    Route::prefix('wali-kelas')->middleware('can:dashboard.view_teacher')->group(function () {
        Route::get('/dashboard', WaliKelasDashboard::class)->name('wali-kelas.dashboard');
        Route::get('/students', WaliKelasStudents::class)->name('wali-kelas.students');
        Route::get('/attendance', WaliKelasAttendance::class)->name('wali-kelas.attendance');
        Route::get('/absence-requests', AbsenceRequestApproval::class)->name('wali-kelas.absence-requests');
        Route::get('/manual-attendance', ManualAttendance::class)->name('wali-kelas.manual-attendance');
    });

    // Siswa routes (Student Portal)
    Route::prefix('siswa')->name('student.')->middleware('can:dashboard.view_student')->group(function () {
        Route::get('/dashboard', StudentDashboard::class)->name('dashboard');

        // NFC Check-In/Out
        Route::get('/nfc', NfcCheckInOut::class)->name('nfc');

        // Attendance History
        Route::get('/attendance/history', AttendanceHistory::class)->name('attendance.history');

        // Absence Request
        Route::get('/absence/request', AbsenceRequest::class)->name('absence.request');

        // Statistics
        Route::get('/statistics', Statistics::class)->name('statistics');

        // Profile
        Route::get('/profile', Profile::class)->name('profile');

        // Legacy routes for backward compatibility
        Route::get('/check-in', CheckIn::class)->name('check-in');
        Route::get('/check-out', CheckOut::class)->name('check-out');
    });

    // Default dashboard - redirect based on role
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Load role relationship if not already loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        // Check if user has role assigned
        if (!$user->role) {
            abort(403, 'User tidak memiliki role yang valid. Hubungi administrator.');
        }

        // Redirect based on user role
        switch ($user->role->name) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'kepala_sekolah':
                return redirect()->route('kepala-sekolah.dashboard');
            case 'wali_kelas':
                return redirect()->route('wali-kelas.dashboard');
            case 'siswa':
                return redirect()->route('student.dashboard');
            default:
                abort(403, 'Role tidak dikenali: ' . $user->role->name);
        }
    })->middleware('auth')->name('dashboard');

    // TEMPORARY: Run migration route (REMOVE AFTER USE)
    Route::get('/run-migration-temp', function () {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $output = \Illuminate\Support\Facades\Artisan::output();
            return '<pre>' . $output . '</pre><br><strong style="color: green;">Migration completed successfully!</strong><br><br><a href="/siswa/absence/request">Go to Absence Request Page</a><br><br><strong style="color: red;">IMPORTANT: Please remove this route from routes/web.php after use!</strong>';
        } catch (\Exception $e) {
            return '<strong style="color: red;">Error:</strong> ' . $e->getMessage();
        }
    });

    // TEMPORARY: Create active semester (REMOVE AFTER USE)
    Route::get('/create-semester-temp', function () {
        try {
            $semester = \App\Models\Semester::create([
                'name' => 'Semester 1 2025/2026',
                'academic_year' => '2025/2026',
                'start_date' => '2025-07-01',
                'end_date' => '2025-12-31',
                'semester' => 1,
                'is_active' => true,
            ]);
            return '<strong style="color: green;">Semester created successfully!</strong><br><br>' .
                   'ID: ' . $semester->id . '<br>' .
                   'Name: ' . $semester->name . '<br>' .
                   'Academic Year: ' . $semester->academic_year . '<br>' .
                   'Period: ' . $semester->start_date . ' - ' . $semester->end_date . '<br><br>' .
                   '<a href="/wali-kelas/absence-requests">Go to Absence Requests</a><br><br>' .
                   '<strong style="color: red;">IMPORTANT: Please remove this route from routes/web.php after use!</strong>';
        } catch (\Exception $e) {
            return '<strong style="color: red;">Error:</strong> ' . $e->getMessage();
        }
    });
});

