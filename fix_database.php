<?php
/**
 * FIX LATE THRESHOLD - Direct Database Update
 * Jalankan: php fix_database.php
 */

// Database config (sesuaikan dengan .env Anda)
$host = 'localhost';
$db   = 'absensi_mifare';
$user = 'root';
$pass = '';  // Sesuaikan jika ada password

echo "=== FIX LATE THRESHOLD DATABASE ===\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Koneksi database berhasil!\n\n";

    // 1. Cek nilai sekarang
    echo "1. NILAI SEBELUM UPDATE:\n";
    $stmt = $pdo->query("SELECT `key`, `value`, `default_value`, `updated_at` FROM attendance_settings WHERE `key` = 'late_threshold'");
    $before = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($before) {
        echo "   Key: {$before['key']}\n";
        echo "   Value: {$before['value']}\n";
        echo "   Default: {$before['default_value']}\n";
        echo "   Updated: {$before['updated_at']}\n";
    } else {
        echo "   ❌ Setting 'late_threshold' tidak ditemukan!\n";
        exit(1);
    }

    echo "\n";

    // 2. Update
    echo "2. MELAKUKAN UPDATE...\n";

    $stmt = $pdo->prepare("
        UPDATE attendance_settings
        SET value = '07:15:00',
            default_value = '07:15:00',
            updated_at = NOW()
        WHERE `key` = 'late_threshold'
    ");
    $stmt->execute();

    echo "   ✅ Update berhasil!\n\n";

    // 3. Verify
    echo "3. NILAI SETELAH UPDATE:\n";
    $stmt = $pdo->query("SELECT `key`, `value`, `default_value`, `updated_at` FROM attendance_settings WHERE `key` = 'late_threshold'");
    $after = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($after) {
        echo "   Key: {$after['key']}\n";
        echo "   Value: {$after['value']}\n";
        echo "   Default: {$after['default_value']}\n";
        echo "   Updated: {$after['updated_at']}\n";
    }

    echo "\n";

    // 4. Kesimpulan
    if ($after['value'] === '07:15:00') {
        echo "✅✅✅ DATABASE SUDAH BENAR!\n\n";
        echo "LANGKAH SELANJUTNYA:\n";
        echo "1. Restart XAMPP (Apache + MySQL)\n";
        echo "2. Jalankan: php simple_test.php (untuk verify)\n";
        echo "3. Clear browser cache\n";
        echo "4. Hard refresh tapping station (Ctrl+F5)\n";
        echo "5. Tap kartu lagi untuk test\n";
    } else {
        echo "❌ UPDATE GAGAL! Nilai masih: {$after['value']}\n";
    }

    echo "\n=== SELESAI ===\n";

} catch (PDOException $e) {
    echo "❌ DATABASE ERROR: " . $e->getMessage() . "\n\n";
    echo "TROUBLESHOOTING:\n";
    echo "1. Pastikan MySQL sudah running di XAMPP\n";
    echo "2. Pastikan database 'absensi_mifare' ada\n";
    echo "3. Cek username/password database di .env\n";
    echo "4. Jika pakai password MySQL, ubah variable \$pass di script ini\n";
    exit(1);
}
