<?php
/**
 * Clear Cache Script
 * Akses via browser: http://localhost/absensi-mifare/public/clear-cache.php
 */

// Load Laravel
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

echo "<h1>Clear Cache</h1>";
echo "<pre>";

try {
    // Clear config cache
    echo "Clearing config cache...\n";
    $app->make('Illuminate\Contracts\Console\Kernel')->call('config:clear');
    echo "✓ Config cache cleared\n\n";

    // Clear route cache
    echo "Clearing route cache...\n";
    $app->make('Illuminate\Contracts\Console\Kernel')->call('route:clear');
    echo "✓ Route cache cleared\n\n";

    // Clear view cache
    echo "Clearing view cache...\n";
    $app->make('Illuminate\Contracts\Console\Kernel')->call('view:clear');
    echo "✓ View cache cleared\n\n";

    // Clear cache
    echo "Clearing application cache...\n";
    $app->make('Illuminate\Contracts\Console\Kernel')->call('cache:clear');
    echo "✓ Application cache cleared\n\n";

    echo "✅ ALL CACHE CLEARED SUCCESSFULLY!\n";
    echo "\n<a href='/absensi-mifare/public'>← Back to Application</a>";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString();
}

echo "</pre>";
