<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$stmt = $pdo->query("SHOW VARIABLES LIKE 'datadir'");
echo $stmt->fetch(PDO::FETCH_ASSOC)['Value'];
