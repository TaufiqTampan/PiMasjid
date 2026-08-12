<?php

/**
 * Run Cloudinary Migration on Production
 */
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

echo '<pre>';
echo "Running Cloudinary migration...\n\n";

$tables = [
    'slides' => 'image_path',
    'posts' => 'image_path',
    'committee_members' => 'photo_path',
    'transactions' => 'proof_image_path',
    'settings' => 'value',
];

foreach ($tables as $table => $afterColumn) {
    if (! Schema::hasColumn($table, 'cloudinary_public_id')) {
        Schema::table($table, function (Blueprint $t) use ($afterColumn) {
            $t->string('cloudinary_public_id')->nullable()->after($afterColumn);
        });
        echo "✅ Added cloudinary_public_id to $table\n";
    } else {
        echo "⏭️ $table already has cloudinary_public_id\n";
    }
}

echo "\nMigration complete!";
echo '</pre>';
