<?php
$f = 'C:/autodesk/app/Http/Controllers/LicenseActivationController.php';
$old = "withOptions(['proxy' => ''])->timeout(15)";
$new = "withOptions(['curl' => [CURLOPT_PROXY => '', CURLOPT_NOPROXY => '*']])->timeout(15)";
$content = file_get_contents($f);
$updated = str_replace($old, $new, $content);
file_put_contents($f, $updated);
echo str_contains($updated, 'NOPROXY') ? "OK - fixed\n" : "FAIL - not found\n";
