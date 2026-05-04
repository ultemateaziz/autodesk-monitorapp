<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class CheckLicenseActivated
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip the activation route itself to avoid redirect loop
        if ($request->routeIs('license.activate') || $request->routeIs('license.activate.post')) {
            return $next($request);
        }

        $key = config('services.license_manager.key');

        // Fallback: read from storage/app/license.json when .env write failed
        if (empty($key)) {
            $stored = $this->readLicenseJson();
            $key    = $stored['license_key'] ?? '';

            if (! empty($key)) {
                config(['services.license_manager.key' => $key]);
                config(['services.license_manager.url' => $stored['server_url'] ?? '']);
            }
        }

        // No key anywhere → force activation page
        if (empty($key)) {
            return redirect()->route('license.activate')
                ->with('info', 'Please activate your subscription key to continue.');
        }

        // Cache expired or missing → auto-refresh now (no scheduler needed)
        $cached = Cache::get('license_status');
        if (! $cached) {
            $cached = $this->pingLicenseHub($key);
        }

        $status = $cached['status'] ?? null;

        $blockMessages = [
            'invalid'          => 'License key not recognised. Please enter a valid key.',
            'expired'          => 'Your subscription has expired. Enter a new license key to continue.',
            'locked'           => 'This subscription has been locked by the administrator. Contact your LicenseHub admin.',
            'not_activated'    => 'This license key is not activated on this machine. Please activate to continue.',
            'hardware_mismatch'=> 'This license key is registered to a different machine. Contact your administrator.',
        ];

        if (isset($blockMessages[$status])) {
            return redirect()->route('license.activate')
                ->with('error', $blockMessages[$status]);
        }

        return $next($request);
    }

    private function pingLicenseHub(string $key): array
    {
        $url = rtrim(config('services.license_manager.url'), '/');

        try {
            $response = Http::withoutVerifying()->timeout(8)->post("{$url}/api/license/pulse", [
                'license_key' => $key,
                'hardware_id' => $this->getMachineId(),
                'machine_id'  => gethostname(),
            ]);

            $body   = $response->json();
            $status = $body['status'] ?? 'unknown';

            $cached = [
                'status'     => $status,
                'message'    => $body['message']      ?? '',
                'tier'       => $body['tier']          ?? '',
                'expires_at' => $body['expires_at']    ?? '',
                'days_left'  => $body['days_left']     ?? null,
                'customer'   => $body['customer_name'] ?? '',
                'checked'    => now()->toDateTimeString(),
            ];

        } catch (\Exception $e) {
            // LicenseHub unreachable — allow access but store unreachable status
            $cached = [
                'status'  => 'unreachable',
                'message' => 'Cannot reach LicenseHub: ' . $e->getMessage(),
                'checked' => now()->toDateTimeString(),
            ];
        }

        Cache::put('license_status', $cached, now()->addMinutes(10));

        return $cached;
    }

    private function getMachineId(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('reg query "HKEY_LOCAL_MACHINE\\SOFTWARE\\Microsoft\\Cryptography" /v MachineGuid 2>nul');
            if ($output && preg_match('/MachineGuid\s+REG_SZ\s+([^\s]+)/', $output, $m)) {
                return $m[1];
            }
        }
        return gethostname() . '-' . php_uname('n');
    }

    private function readLicenseJson(): array
    {
        $file = storage_path('app' . DIRECTORY_SEPARATOR . 'license.json');

        if (! file_exists($file)) {
            return [];
        }

        try {
            $data = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            return is_array($data) ? $data : [];
        } catch (\Throwable) {
            return [];
        }
    }
}
