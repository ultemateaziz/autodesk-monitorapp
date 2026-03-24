<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=aclm', 'root', '');
$stmt = $pdo->query('SHOW TABLES');
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Tables in aclm:\n";
foreach ($tables as $t) {
    echo "- $t\n";
}
