<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LicenseActivationController extends Controller
{
    public function show()
    {
        $cached = Cache::get('license_status');
        return view('license.activate', compact('cached'));
    }

    public function activate(Request $request)
    {
        $request->validate([
            'license_key'       => ['required', 'string', 'regex:/^AEPRO-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/'],
            'license_server_url'=> ['required', 'url'],
        ], [
            'license_key.regex' => 'Key format must be: AEPRO-XXXX-XXXX-XXXX',
        ]);

        $key       = strtoupper(trim($request->license_key));
        $serverUrl = rtrim($request->license_server_url, '/');

        // ── Call LicenseHub to activate ───────────────────────────
        try {
            $response = Http::withoutVerifying()->timeout(15)->post("{$serverUrl}/api/license/activate", [
                'license_key' => $key,
                'hardware_id' => $this->getMachineId(),
                'machine_id'  => gethostname(),
            ]);

            $body   = $response->json();
            $status = $body['status'] ?? 'error';

        } catch (\Exception $e) {
            return back()->withInput()->with('error',
                'Cannot reach the LicenseHub server. Check the URL and try again. (' . $e->getMessage() . ')'
            );
        }

        // ── Handle response ───────────────────────────────────────
        if (in_array($status, ['activated', 'valid'])) {

            // Write key + URL to .env so it persists after restart
            $this->writeToEnv('LICENSE_KEY', $key);
            $this->writeToEnv('LICENSE_MANAGER_URL', $serverUrl);

            // Update config in memory for this request
            config(['services.license_manager.key' => $key]);
            config(['services.license_manager.url' => $serverUrl]);

            // Cache the valid status
            Cache::put('license_status', [
                'status'     => 'valid',
                'tier'       => $body['tier']          ?? '',
                'days_left'  => $body['days_left']     ?? null,
                'expires_at' => $body['expires_at']    ?? '',
                'customer'   => $body['customer_name'] ?? '',
                'checked'    => now()->toDateTimeString(),
            ], now()->addMinutes(10));

            return redirect()->route('dashboard')
                ->with('success', '✅ License activated successfully! Welcome to ArchEng Pro.');
        }

        // ── Activation failed ─────────────────────────────────────
        $message = match($status) {
            'already_activated', 'hardware_mismatch' => 'This key is already locked to a different machine. Please contact your administrator.',
            'expired'           => 'This license key has expired. Please contact your administrator for a new key.',
            'locked'            => 'This license has been locked. Contact your administrator.',
            'invalid'           => 'Invalid license key. Please check the key and try again.',
            default             => 'Activation failed: ' . ($body['message'] ?? 'Unknown error') . ' (Server Raw: ' . substr($response->body(), 0, 200) . ')',
        };

        return back()->withInput()->with('error', $message);
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function getMachineId(): string
    {
        // Use Windows MachineGUID from registry if available
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('reg query "HKEY_LOCAL_MACHINE\\SOFTWARE\\Microsoft\\Cryptography" /v MachineGuid 2>nul');
            if ($output && preg_match('/MachineGuid\s+REG_SZ\s+([^\s]+)/', $output, $m)) {
                return $m[1];
            }
        }
        return gethostname() . '-' . php_uname('n');
    }

    private function writeToEnv(string $key, string $value): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        if (str_contains($content, "{$key}=")) {
            // Replace existing value
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
        } else {
            // Append new key
            $content .= "\n{$key}={$value}";
        }

        file_put_contents($envPath, $content);
    }
}
