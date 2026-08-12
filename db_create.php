<?php

$db_host = 'localhost';
$db_user = 'n1025777_muhdanfyan';
$db_pass = 'syahriani334';
$db_name = 'n1025777_pimasjid';

try {
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo 'Database created or already exists!';
} catch (PDOException $e) {
    echo 'Error: '.$e->getMessage();
}
