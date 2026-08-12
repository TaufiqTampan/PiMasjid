<?php

/**
 * STORAGE COPY HELPER
 * Karena symlink() disabled di hosting, kita copy manual
 * Akses: https://domainanda.com/copy_storage.php
 * HAPUS setelah selesai!
 */
echo '<h1>Storage Copy Helper</h1>';

$source = __DIR__.'/../storage/app/public';
$destination = __DIR__.'/storage';

// Function untuk copy recursive
function copyDirectory($src, $dst)
{
    $dir = opendir($src);
    @mkdir($dst, 0755, true);

    $count = 0;
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src.'/'.$file)) {
                $count += copyDirectory($src.'/'.$file, $dst.'/'.$file);
            } else {
                copy($src.'/'.$file, $dst.'/'.$file);
                $count++;
            }
        }
    }
    closedir($dir);

    return $count;
}

// Buat folder storage di public
if (! is_dir($destination)) {
    mkdir($destination, 0755, true);
}

// Copy semua file
try {
    $copied = copyDirectory($source, $destination);
    echo "<p style='color: green;'>✅ <strong>BERHASIL!</strong></p>";
    echo "<p>Total file yang dicopy: <strong>$copied</strong></p>";
    echo '<p>Dari: <code>storage/app/public</code> → <code>public/storage</code></p>';
    echo '<hr>';
    echo "<h3 style='color: orange;'>⚠️ CATATAN PENTING:</h3>";
    echo '<ul>';
    echo '<li>Setiap kali upload file baru (gambar, dokumen), file akan tersimpan di <code>storage/app/public</code></li>';
    echo '<li>Anda perlu jalankan script ini lagi untuk copy file baru ke <code>public/storage</code></li>';
    echo '<li>Atau, copy manual via FTP/File Manager</li>';
    echo '</ul>';
    echo "<h3 style='color: red;'>🔒 HAPUS FILE INI SEKARANG!</h3>";
    echo '<p>Hapus <code>public/copy_storage.php</code> untuk keamanan.</p>';
    echo "<p><a href='/' style='padding: 10px 20px; background: green; color: white; text-decoration: none; border-radius: 5px;'>Kembali ke Website</a></p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: ".$e->getMessage().'</p>';
}
