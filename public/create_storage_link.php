<?php

/**
 * Helper untuk membuat storage link di shared hosting
 * Akses via browser: https://domainanda.com/create_storage_link.php
 * HAPUS file ini setelah berhasil!
 */
echo '<h1>MasjidVision - Storage Link Creator</h1>';

$link = __DIR__.'/storage';
$target = __DIR__.'/../storage/app/public';

// Cek apakah link sudah ada
if (file_exists($link)) {
    if (is_link($link)) {
        echo "<p style='color: orange;'>⚠️ Storage link sudah ada. Menghapus yang lama...</p>";
        unlink($link);
    } else {
        echo "<p style='color: red;'>❌ Folder 'storage' sudah ada tapi bukan symlink. Hapus manual dulu!</p>";
        exit;
    }
}

// Cek apakah target folder ada
if (! is_dir($target)) {
    echo "<p style='color: red;'>❌ Folder storage/app/public tidak ditemukan!</p>";
    exit;
}

// Buat symlink
if (symlink($target, $link)) {
    echo "<p style='color: green;'>✅ <strong>BERHASIL!</strong> Storage link telah dibuat.</p>";
    echo '<p>Link: <code>public/storage</code> → <code>storage/app/public</code></p>';
    echo '<hr>';
    echo "<h3 style='color: red;'>⚠️ PENTING: HAPUS FILE INI SEKARANG!</h3>";
    echo '<p>File ini mengandung kode yang berbahaya jika dibiarkan. Hapus <code>public/create_storage_link.php</code> sekarang!</p>';
    echo "<p><a href='/' style='padding: 10px 20px; background: green; color: white; text-decoration: none; border-radius: 5px;'>Kembali ke Website</a></p>";
} else {
    echo "<p style='color: red;'>❌ Gagal membuat storage link. Hubungi support hosting Anda.</p>";
    echo '<p>Error: Symlink tidak didukung di server ini.</p>';
}
