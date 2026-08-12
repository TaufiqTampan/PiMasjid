<?php

$public_storage = __DIR__.'/storage';
$target_storage = __DIR__.'/../storage/app/public';

echo "Public Storage Path: $public_storage\n";
echo "Target Storage Path: $target_storage\n\n";

// 1. Check if public/storage is a symlink or directory
if (file_exists($public_storage)) {
    if (is_link($public_storage)) {
        echo "public/storage is already a symlink.\n";
        echo 'Link points to: '.readlink($public_storage)."\n";
    } else {
        echo "public/storage is a DIRECTORY. Attempting to delete...\n";
        // Simple recursive delete
        function deleteDir($dirPath)
        {
            if (! is_dir($dirPath)) {
                return;
            }
            $files = array_diff(scandir($dirPath), ['.', '..']);
            foreach ($files as $file) {
                (is_dir("$dirPath/$file")) ? deleteDir("$dirPath/$file") : unlink("$dirPath/$file");
            }

            return rmdir($dirPath);
        }
        if (deleteDir($public_storage)) {
            echo "Successfully deleted public/storage directory.\n";
        } else {
            echo "FAILED to delete public/storage directory.\n";
            exit;
        }
    }
}

// 2. Create the symlink
if (! file_exists($public_storage)) {
    echo "Creating symlink...\n";
    if (symlink($target_storage, $public_storage)) {
        echo "Successfully created symlink!\n";
    } else {
        echo "FAILED to create symlink using PHP symlink().\n";

        // Alternative: Try shell if allowed
        echo "Attempting shell command (ln -s)...\n";
        system("ln -s $target_storage $public_storage", $retval);
        if ($retval === 0) {
            echo "Successfully created symlink via shell!\n";
        } else {
            echo "FAILED all attempts to create symlink.\n";
        }
    }
} else {
    echo "public/storage already exists (as link or folder).\n";
}

// 3. Ensure target directory exists and is writable
if (! file_exists($target_storage)) {
    echo "Creating target directory $target_storage...\n";
    mkdir($target_storage, 0755, true);
}

// 4. Test writing
$test_file = $target_storage.'/link_test.txt';
if (file_put_contents($test_file, 'test')) {
    echo "Successfully wrote test file to target storage.\n";
    if (file_exists($public_storage.'/link_test.txt')) {
        echo "VERIFIED: Test file is accessible via public/storage!\n";
    } else {
        echo "ERROR: Test file NOT accessible via public link.\n";
    }
}
