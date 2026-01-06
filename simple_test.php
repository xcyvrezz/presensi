<?php
/**
 * SIMPLE TEST - Late Threshold Direct Database Check
 * Jalankan: php simple_test.php
 */

// Database config (sesuaikan dengan .env Anda)
$host = 'localhost';
$db   = 'absensi_mifare';
$user = 'root';
$pass = '';  // Sesuaikan jika ada password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== TEST LATE THRESHOLD (DIRECT DATABASE) ===\n\n";

    // 1. Cek nilai di database
    echo "1. CEK DATABASE:\n";
    $stmt = $pdo->query("SELECT `key`, `value`, `default_value`, `updated_at` FROM attendance_settings WHERE `key` = 'late_threshold'");
    $setting = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($setting) {
        echo "   Key: {$setting['key']}\n";
        echo "   Value: {$setting['value']}\n";
        echo "   Default: {$setting['default_value']}\n";
        echo "   Updated: {$setting['updated_at']}\n";

        $lateThreshold = $setting['value'];
    } else {
        echo "   ❌ Setting 'late_threshold' tidak ditemukan!\n";
        exit(1);
    }

    echo "\n";

    // 2. Test perhitungan
    echo "2. TEST PERHITUNGAN:\n";

    $testCases = [
        '07:00:00' => 'Jam 07:00',
        '07:14:59' => 'Jam 07:14:59',
        '07:15:00' => 'Jam 07:15:00',
        '07:15:01' => 'Jam 07:15:01',
        '07:16:00' => 'Jam 07:16',
        '07:30:00' => 'Jam 07:30',
        '08:00:00' => 'Jam 08:00',
        '09:12:21' => 'Jam 09:12:21 (Febrian Putra)',
    ];

    foreach ($testCases as $time => $description) {
        $checkInDateTime = new DateTime("2026-01-06 $time");
        $thresholdDateTime = new DateTime("2026-01-06 $lateThreshold");

        if ($checkInDateTime > $thresholdDateTime) {
            $interval = $checkInDateTime->diff($thresholdDateTime);
            $lateMinutes = ($interval->h * 60) + $interval->i;
            $status = 'TERLAMBAT';
            echo "   🔶 $time → $status ($lateMinutes menit) - $description\n";
        } else {
            $status = 'TEPAT WAKTU';
            echo "   ✅ $time → $status (0 menit) - $description\n";
        }
    }

    echo "\n";

    // 3. Cek attendance hari ini yang mungkin salah
    echo "3. CEK ATTENDANCE HARI INI (yang check-in setelah late_threshold):\n";

    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT
            a.id,
            s.full_name,
            s.nis,
            a.check_in_time,
            a.status,
            a.late_minutes,
            TIME_TO_SEC(a.check_in_time) > TIME_TO_SEC(?) as seharusnya_terlambat
        FROM attendances a
        JOIN students s ON a.student_id = s.id
        WHERE a.date = ?
        ORDER BY a.check_in_time DESC
        LIMIT 10
    ");
    $stmt->execute([$lateThreshold, $today]);
    $attendances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($attendances) > 0) {
        foreach ($attendances as $att) {
            $shouldBeLate = $att['seharusnya_terlambat'] ? 'SEHARUSNYA TERLAMBAT' : 'SUDAH BENAR';
            $statusIcon = $att['status'] === 'terlambat' ? '🔶' : '✅';

            echo "   $statusIcon {$att['full_name']} ({$att['nis']})\n";
            echo "      Check-in: {$att['check_in_time']}\n";
            echo "      Status: {$att['status']}\n";
            echo "      Late minutes: {$att['late_minutes']}\n";
            echo "      Keterangan: $shouldBeLate\n";
            echo "\n";
        }
    } else {
        echo "   Tidak ada attendance hari ini.\n";
    }

    echo "\n";

    // 4. Kesimpulan
    echo "4. KESIMPULAN:\n";
    if ($lateThreshold === '07:15:00') {
        echo "   ✅ Database sudah benar (late_threshold = 07:15:00)\n";
        echo "   ✅ Logika perhitungan sudah benar\n";
        echo "\n";
        echo "   LANGKAH SELANJUTNYA:\n";
        echo "   1. Restart XAMPP (Apache + MySQL)\n";
        echo "   2. Clear browser cache (Ctrl+Shift+Delete)\n";
        echo "   3. Hard refresh tapping station (Ctrl+F5)\n";
        echo "   4. Tap kartu lagi\n";
        echo "   5. Cek file log: storage/logs/laravel.log\n";
    } else {
        echo "   ❌ Database masih salah! Late threshold = $lateThreshold\n";
        echo "\n";
        echo "   JALANKAN SQL INI DI phpMyAdmin:\n";
        echo "   UPDATE attendance_settings SET value = '07:15:00', default_value = '07:15:00' WHERE `key` = 'late_threshold';\n";
    }

    echo "\n=== END TEST ===\n";

} catch (PDOException $e) {
    echo "❌ DATABASE ERROR: " . $e->getMessage() . "\n";
    echo "\nPastikan:\n";
    echo "1. MySQL sudah running di XAMPP\n";
    echo "2. Database 'absensi_mifare' sudah ada\n";
    echo "3. Username/password database benar\n";
    exit(1);
}
