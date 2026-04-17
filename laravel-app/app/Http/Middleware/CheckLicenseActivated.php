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

        // No key stored in .env → force activation page
        if (empty($key)) {
            return redirect()->route('license.activate')
                ->with('info', 'Please activate your subscription key to continue.');
        }

        // Cache expired or missing → auto-refresh now (no scheduler needed)
        $cached = Cache::get('license_status');
        if (!$cached) {
            $cached = $this->pingLicenseHub($key);
        }

        $status = $cached['status'] ?? null;

        // If locked or expired → force activation page
        if (in_array($status, ['locked', 'expired'])) {
            return redirect()->route('license.activate')
                ->with('error', match($status) {
                    'locked'  => 'Your license has been locked. Please contact your administrator.',
                    'expired' => 'Your subscription has expired. Please enter a new license key.',
                    default   => 'License issue detected.',
                });
        }

        return $next($request);
    }

    private function pingLicenseHub(string $key): array
    {
        $url = rtrim(config('services.license_manager.url'), '/');

        try {
            $response = Http::withoutVerifying()->timeout(8)->post("{$url}/api/license/pulse", [
                'license_key' => $key,
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
}
