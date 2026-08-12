<?php

/**
 * Copy Storage to Public Folder
 * Workaround for when symlinks are disabled on shared hosting
 */
$source = __DIR__.'/../storage/app/public';
$destination = __DIR__.'/storage';

function recurseCopy($src, $dst)
{
    $dir = opendir($src);
    @mkdir($dst, 0755, true);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src.'/'.$file)) {
                recurseCopy($src.'/'.$file, $dst.'/'.$file);
            } else {
                copy($src.'/'.$file, $dst.'/'.$file);
            }
        }
    }
    closedir($dir);
}

echo '<h1>Storage Copy Utility</h1>';
echo '<pre>';
echo "Source: $source\n";
echo "Destination: $destination\n\n";

if (! file_exists($source)) {
    echo "ERROR: Source directory does not exist.\n";
    exit;
}

if (! file_exists($destination)) {
    mkdir($destination, 0755, true);
    echo "Created destination directory.\n";
}

echo "Copying files...\n";
recurseCopy($source, $destination);
echo "DONE! Files copied successfully.\n";
echo '</pre>';
