<?php

$public_storage = __DIR__.'/storage';
$target_storage = __DIR__.'/../storage/app/public';

if (! file_exists($target_storage)) {
    mkdir($target_storage, 0755, true);
    echo "Created target directory: $target_storage\n";
}

if (! file_exists($public_storage)) {
    echo "Attempting to create symlink...\n";
    if (symlink($target_storage, $public_storage)) {
        echo "SUCCESS: Symlink created via PHP symlink()\n";
    } else {
        echo "FAIL: PHP symlink() failed.\n";
        echo "Attempting shell command...\n";
        exec("ln -s $target_storage $public_storage", $output, $return_var);
        if ($return_var === 0) {
            echo "SUCCESS: Symlink created via shell ln -s\n";
        } else {
            echo "FAIL: Shell command failed with code $return_var\n";
            echo "Attempting relative symlink...\n";
            exec("ln -s ../storage/app/public $public_storage", $output2, $return_var2);
            if ($return_var2 === 0) {
                echo "SUCCESS: Relative symlink created via shell\n";
            } else {
                echo "FAIL: Relative link also failed.\n";
            }
        }
    }
} else {
    echo "public/storage already exists.\n";
}
