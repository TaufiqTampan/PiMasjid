<?php

// Set temporary write directory for Vercel Serverless environment
$_ENV['APP_STORAGE'] = '/tmp';
$_ENV['VIEW_COMPILED_PATH'] = '/tmp';
$_ENV['LOG_CHANNEL'] = 'stderr';

// Ensure storage subdirectories exist in /tmp
if (!file_exists('/tmp/framework/views')) {
    @mkdir('/tmp/framework/views', 0755, true);
}
if (!file_exists('/tmp/framework/sessions')) {
    @mkdir('/tmp/framework/sessions', 0755, true);
}
if (!file_exists('/tmp/framework/cache')) {
    @mkdir('/tmp/framework/cache', 0755, true);
}

require __DIR__ . '/../public/index.php';
