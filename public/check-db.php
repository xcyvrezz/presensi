<?php
// Simple DB check without Laravel bootstrap
$host = 'localhost';
$db   = 'absensi_mifare';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Active Students with Card UIDs:\n";
    echo "================================\n\n";

    $stmt = $pdo->query("
        SELECT id, full_name, nis, card_uid
        FROM students
        WHERE is_active = 1
        AND card_uid IS NOT NULL
        ORDER BY id
        LIMIT 20
    ");

    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($students)) {
        echo "No active students with cards found.\n";
    } else {
        foreach ($students as $student) {
            echo "ID: {$student['id']}\n";
            echo "Name: {$student['full_name']}\n";
            echo "NIS: {$student['nis']}\n";
            echo "Card UID: {$student['card_uid']}\n";
            echo "UID Length: " . strlen($student['card_uid']) . " chars\n";
            echo "---\n";
        }
    }

    echo "\n\nTotal: " . count($students) . " students\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    echo "\nPastikan:\n";
    echo "- MySQL running (XAMPP Control Panel)\n";
    echo "- Database 'absensi_mifare' ada\n";
    echo "- Username/password benar\n";
}
