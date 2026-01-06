<?php

/**
 * Manual Storage Link Creator
 * Run this file directly with PHP to create storage symlink
 */

$target = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public';
$link = __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'storage';

// Check if link already exists
if (file_exists($link)) {
    echo "❌ Storage link already exists at: {$link}\n";

    if (is_link($link)) {
        echo "✅ It is a symbolic link pointing to: " . readlink($link) . "\n";
    } else {
        echo "⚠️  WARNING: It exists but is NOT a symbolic link!\n";
        echo "Please delete it manually first, then run this script again.\n";
    }

    exit(0);
}

// Check if target directory exists
if (!is_dir($target)) {
    echo "⚠️  Creating target directory: {$target}\n";
    mkdir($target, 0755, true);
}

// Create symlink
echo "Creating storage link...\n";
echo "Target: {$target}\n";
echo "Link: {$link}\n";

if (symlink($target, $link)) {
    echo "✅ Storage link created successfully!\n";
    echo "\nYou can now access uploaded files via:\n";
    echo "http://localhost:8000/storage/your-file.jpg\n";
} else {
    echo "❌ Failed to create storage link!\n";
    echo "\nTry running this command manually as Administrator:\n";
    echo "mklink /D \"public\\storage\" \"..\\storage\\app\\public\"\n";
}
