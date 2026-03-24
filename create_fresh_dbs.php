<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `aclm_new` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `autodesk_monitor_v2` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Fresh databases created successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
