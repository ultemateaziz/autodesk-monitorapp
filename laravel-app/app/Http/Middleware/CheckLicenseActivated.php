<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        // Key exists → check cached status
        $cached = Cache::get('license_status');
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
}
