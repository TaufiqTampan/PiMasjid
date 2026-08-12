<?php

// Load Laravel's autoload and app
use App\Models\User;
use Illuminate\Support\Facades\Hash;

define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');

$email = 'admin@pimasjid.com';
$password = 'admin123';

try {
    $user = User::where('email', $email)->first();

    if ($user) {
        echo 'User found: '.$user->name.' (Role: '.$user->role.")\n";
        echo "Resetting password to 'admin123'...\n";
        $user->password = Hash::make($password);
        $user->save();
        echo "Password updated successfully!\n";
    } else {
        echo "User not found. Creating new Super Admin...\n";
        User::create([
            'name' => 'Super Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        echo "Super Admin created successfully!\n";
    }
} catch (\Exception $e) {
    echo 'Error: '.$e->getMessage();
}
