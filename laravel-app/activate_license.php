<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$data = [
    'license_key'  => 'AEPRO-ODTL-5X8X-JF57',
    'server_url'   => 'https://steelblue-newt-798585.hostingersite.com',
    'hardware_id'  => '50eeb630-8f4d-4294-9dba-cb4bdb66f6f0',
    'activated_at' => now()->toDateTimeString(),
    'max_machines' => 1,
];
file_put_contents(storage_path('app/license.json'), json_encode($data, JSON_PRETTY_PRINT));

\Illuminate\Support\Facades\Cache::put('license_status', [
    'status'     => 'valid',
    'tier'       => '7D',
    'days_left'  => 6,
    'expires_at' => '2026-06-09 08:12:09',
    'customer'   => 'Azizi',
    'checked'    => now()->toDateTimeString(),
], now()->addDays(6));

echo "License activated OK\n";
