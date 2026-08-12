<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo '<h1>Diagnostic Report</h1>';

echo '<h2>PHP Info</h2>';
echo 'PHP Version: '.phpversion().'<br>';

echo '<h2>Directory Permissions</h2>';
$dirs = [
    'storage' => __DIR__.'/storage',
    'storage/logs' => __DIR__.'/storage/logs',
    'storage/framework' => __DIR__.'/storage/framework',
    'storage/framework/views' => __DIR__.'/storage/framework/views',
    'storage/framework/cache' => __DIR__.'/storage/framework/cache',
    'storage/framework/sessions' => __DIR__.'/storage/framework/sessions',
    'bootstrap/cache' => __DIR__.'/bootstrap/cache',
];

foreach ($dirs as $name => $path) {
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $writable = is_writable($path) ? 'Writable' : 'NOT Writable';
        echo "$name ($path): $perms - $writable<br>";
    } else {
        echo "$name ($path): Does NOT exist<br>";
    }
}

echo '<h2>Environment File</h2>';
if (file_exists(__DIR__.'/.env')) {
    echo '.env exists<br>';
    $env = file_get_contents(__DIR__.'/.env');
    if (preg_match('/DB_DATABASE=(.*)/', $env, $matches)) {
        echo 'Database from .env: '.trim($matches[1]).'<br>';
    }
} else {
    echo '.env does NOT exist<br>';
}

echo '<h2>Database Connection Test</h2>';
try {
    include '.env'; // This won't work directly but let's assume variables are loaded or parse manually
    $lines = file(__DIR__.'/.env');
    $config = [];
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) == 2) {
            $config[trim($parts[0])] = trim($parts[1]);
        }
    }

    $host = $config['DB_HOST'] ?? 'localhost';
    $dbname = $config['DB_DATABASE'] ?? '';
    $user = $config['DB_USERNAME'] ?? '';
    $pass = $config['DB_PASSWORD'] ?? '';

    echo "Attempting to connect to $dbname at $host with user $user...<br>";
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    echo 'SUCCESS: Database connection established!<br>';
} catch (PDOException $e) {
    echo 'FAILURE: Database connection failed: '.$e->getMessage().'<br>';
}

echo '<h2>Missing Vendor Check</h2>';
if (is_dir(__DIR__.'/vendor')) {
    echo 'Vendor directory exists<br>';
} else {
    echo 'Vendor directory is MISSING!<br>';
}
