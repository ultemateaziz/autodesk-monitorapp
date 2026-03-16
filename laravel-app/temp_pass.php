<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::where('email', 'ultemateaziz64@gmail.com')->first();
if ($u) {
    $u->password = Illuminate\Support\Facades\Hash::make('password');
    $u->save();
    echo "Password updated.";
} else {
    echo "User not found.";
}
