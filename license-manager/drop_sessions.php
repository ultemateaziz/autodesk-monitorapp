<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=aclm', 'root', '');
    $pdo->exec("DROP TABLE IF EXISTS `sessions` CASCADE");
    echo "Drop attempted.\n";
} catch (Exception $e) {
    echo "Drop failed: " . $e->getMessage() . "\n";
}
