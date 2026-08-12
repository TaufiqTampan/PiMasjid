<?php

$zipFile = 'deploy.zip';
$extractTo = './';

$zip = new ZipArchive;
if ($zip->open($zipFile) === true) {
    $zip->extractTo($extractTo);
    $zip->close();
    echo 'Extraction successful!<br>';
    // Optional: unlink(__FILE__); // Delete this script
    // Optional: unlink($zipFile); // Delete the zip file
} else {
    echo 'Failed to open the zip file.';
}
