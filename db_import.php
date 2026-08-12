<?php

$db_host = 'localhost';
$db_name = 'n1025777_pimasjid';
$db_user = 'n1025777_muhdanfyan';
$db_pass = 'syahriani334';
$sql_file = '../pimasjid.sql';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents($sql_file);
    if ($sql === false) {
        exit('Error: Could not read SQL file.');
    }

    // Split SQL by semicolon, but try to handle multi-line statements simply
    // Note: This is a basic parser. For large/complex files, it's better to use CLI mysql.
    $pdo->exec($sql);

    echo 'Database import successful!';
} catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
} catch (Exception $e) {
    echo 'General error: '.$e->getMessage();
}
