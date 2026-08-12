<?php

use App\Models\User;

define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');

$email = 'admin@pimasjid.com';

try {
    $user = User::where('email', $email)->first();

    if ($user) {
        echo 'User found: '.$user->name."\n";
        echo 'Old Role: '.$user->role."\n";

        $user->role = 'super_admin';
        $user->save();

        echo 'New Role: '.$user->role."\n";
        echo "Role updated successfully!\n";
    } else {
        echo "User not found. Please run the reset script first.\n";
    }
} catch (\Exception $e) {
    echo 'Error: '.$e->getMessage();
}
