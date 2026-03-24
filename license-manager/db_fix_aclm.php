<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->exec("DROP DATABASE IF EXISTS `aclm` ");
    $pdo->exec("CREATE DATABASE `aclm` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Recreated aclm database successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
