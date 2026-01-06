<?php
// Simple script to get student cards
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Active Students with Cards:\n";
echo "==========================\n\n";

$students = App\Models\Student::where('is_active', true)
    ->whereNotNull('card_uid')
    ->with('class')
    ->limit(10)
    ->get();

if ($students->isEmpty()) {
    echo "No active students with cards found.\n";
} else {
    foreach ($students as $student) {
        echo "Card UID: {$student->card_uid}\n";
        echo "Name: {$student->full_name}\n";
        echo "Class: " . ($student->class->name ?? 'N/A') . "\n";
        echo "---\n";
    }
}
