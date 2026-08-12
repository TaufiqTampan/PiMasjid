<?php

/**
 * Unzip Cloudinary Update
 */
$zipFile = __DIR__.'/../deploy_cloudinary.zip';
$extractTo = __DIR__.'/../';

if (! file_exists($zipFile)) {
    echo 'Error: deploy_cloudinary.zip not found';
    exit(1);
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === true) {
    $zip->extractTo($extractTo);
    $zip->close();
    unlink($zipFile);
    echo 'Cloudinary update successful!';
} else {
    echo 'Failed to extract zip';
}
