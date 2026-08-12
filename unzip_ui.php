<?php

$zipFile = '../ui_update.zip';
$extractTo = '../';

$zip = new ZipArchive;
if ($zip->open($zipFile) === true) {
    $zip->extractTo($extractTo);
    $zip->close();
    echo 'UI Update successful!';
} else {
    echo 'Failed to open the zip file.';
}
