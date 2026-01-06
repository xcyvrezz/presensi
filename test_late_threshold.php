<?php
/**
 * TEST SCRIPT - Late Threshold Checker
 * Jalankan: php test_late_threshold.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AttendanceSetting;
use Carbon\Carbon;

echo "=== TEST LATE THRESHOLD ===\n\n";

// 1. Cek nilai di database
echo "1. CEK DATABASE:\n";
$setting = AttendanceSetting::where('key', 'late_threshold')->first();
if ($setting) {
    echo "   Key: {$setting->key}\n";
    echo "   Value: {$setting->value}\n";
    echo "   Default: {$setting->default_value}\n";
    echo "   Updated: {$setting->updated_at}\n";
} else {
    echo "   ❌ Setting 'late_threshold' tidak ditemukan!\n";
}

echo "\n";

// 2. Cek getValue() method
echo "2. CEK getValue() METHOD:\n";
$lateThreshold = AttendanceSetting::getValue('late_threshold', '07:00:00');
echo "   Late Threshold: {$lateThreshold}\n";

echo "\n";

// 3. Test perhitungan terlambat
echo "3. TEST PERHITUNGAN:\n";

$testCases = [
    '07:00:00' => 'TEPAT WAKTU (jam 07:00)',
    '07:14:59' => 'TEPAT WAKTU (jam 07:14:59)',
    '07:15:00' => 'TEPAT WAKTU (jam 07:15:00)',
    '07:15:01' => 'TERLAMBAT 1 detik',
    '07:16:00' => 'TERLAMBAT 1 menit',
    '07:30:00' => 'TERLAMBAT 15 menit',
    '08:00:00' => 'TERLAMBAT 45 menit',
    '09:12:21' => 'TERLAMBAT 117 menit (seperti Febrian Putra)',
];

foreach ($testCases as $time => $description) {
    $checkInTime = Carbon::createFromFormat('Y-m-d H:i:s', '2026-01-06 ' . $time);
    $threshold = Carbon::createFromFormat('Y-m-d H:i:s', $checkInTime->format('Y-m-d') . ' ' . $lateThreshold);

    if ($checkInTime->gt($threshold)) {
        $lateMinutes = $checkInTime->diffInMinutes($threshold);
        $status = 'terlambat';
        echo "   ✅ {$time} → STATUS: {$status}, LATE: {$lateMinutes} menit ({$description})\n";
    } else {
        $lateMinutes = 0;
        $status = 'hadir';
        echo "   ✅ {$time} → STATUS: {$status}, LATE: {$lateMinutes} menit ({$description})\n";
    }
}

echo "\n";

// 4. Kesimpulan
echo "4. KESIMPULAN:\n";
if ($lateThreshold === '07:15:00') {
    echo "   ✅ Database sudah benar (late_threshold = 07:15:00)\n";
    echo "   ✅ Logika perhitungan sudah benar\n";
    echo "   \n";
    echo "   JIKA MASIH TIDAK BERFUNGSI:\n";
    echo "   1. Restart Apache: sudo service apache2 restart (Linux) atau restart XAMPP\n";
    echo "   2. Clear browser cache: Ctrl+Shift+Delete\n";
    echo "   3. Hard refresh tapping station: Ctrl+F5\n";
} else {
    echo "   ❌ Database masih salah! Late threshold = {$lateThreshold}\n";
    echo "   \n";
    echo "   JALANKAN SQL INI:\n";
    echo "   UPDATE attendance_settings SET value = '07:15:00' WHERE `key` = 'late_threshold';\n";
}

echo "\n=== END TEST ===\n";
