<?php

echo 'Current directory: '.__DIR__."\n";
$files = scandir(__DIR__);
foreach ($files as $file) {
    echo $file.(is_link($file) ? ' (LINK -> '.readlink($file).')' : (is_dir($file) ? ' [DIR]' : ''))."\n";
}
