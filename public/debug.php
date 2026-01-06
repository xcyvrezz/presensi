<?php
/**
 * Debug Script
 * Akses via: http://localhost/absensi-mifare/debug.php
 */

// Load Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Boot Laravel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<h1>Debug Information</h1>";
echo "<style>body{font-family:monospace;padding:20px;} .ok{color:green;} .error{color:red;} pre{background:#f5f5f5;padding:10px;}</style>";

echo "<h2>1. Environment</h2>";
echo "<pre>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Laravel Version: " . app()->version() . "\n";
echo "APP_URL: " . config('app.url') . "\n";
echo "ASSET_URL: " . env('ASSET_URL', 'not set') . "\n";
echo "APP_ENV: " . config('app.env') . "\n";
echo "</pre>";

echo "<h2>2. Database Connection</h2>";
echo "<pre>";
try {
    $pdo = DB::connection()->getPdo();
    echo "<span class='ok'>✓ Database Connected!</span>\n";
    echo "Database: " . config('database.connections.mysql.database') . "\n";
    echo "Host: " . config('database.connections.mysql.host') . "\n";
} catch (\Exception $e) {
    echo "<span class='error'>✗ Database Connection Failed!</span>\n";
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<h2>3. Check Users Table</h2>";
echo "<pre>";
try {
    $userCount = DB::table('users')->count();
    echo "<span class='ok'>✓ Users table exists</span>\n";
    echo "Total users: {$userCount}\n\n";

    // Check admin user
    $admin = DB::table('users')->where('email', 'admin@smkn10pdg.sch.id')->first();
    if ($admin) {
        echo "<span class='ok'>✓ Admin user found!</span>\n";
        echo "Name: {$admin->name}\n";
        echo "Email: {$admin->email}\n";
        echo "Is Active: " . ($admin->is_active ? 'Yes' : 'No') . "\n";
        echo "Role ID: {$admin->role_id}\n";
    } else {
        echo "<span class='error'>✗ Admin user NOT found!</span>\n";
        echo "You need to run seeders.\n";
    }
} catch (\Exception $e) {
    echo "<span class='error'>✗ Error checking users table</span>\n";
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<h2>4. Asset URLs</h2>";
echo "<pre>";
echo "asset('build/manifest.webmanifest') = " . asset('build/manifest.webmanifest') . "\n";
echo "asset('build/assets/app-B2jXeZXv.js') = " . asset('build/assets/app-B2jXeZXv.js') . "\n";
echo "</pre>";

echo "<h2>5. Livewire Status</h2>";
echo "<pre>";
try {
    echo "Livewire class: " . \Livewire\Livewire::class . "\n";
    echo "<span class='ok'>✓ Livewire loaded</span>\n";

    // Test Livewire script URL
    $livewireJs = \Livewire\Livewire::scriptConfig();
    echo "\nLivewire config:\n";
    print_r($livewireJs);
} catch (\Exception $e) {
    echo "<span class='error'>✗ Livewire error</span>\n";
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<br><a href='/absensi-mifare'>← Back to Application</a>";
