<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckLicense extends Command
{
    protected $signature = 'license:check';
    protected $description = 'Ping the LicenseHub server to verify this monitor app\'s subscription key';

    public function handle(): void
    {
        $url = rtrim(config('services.license_manager.url'), '/');
        $key = config('services.license_manager.key');

        // ── No key configured yet ──────────────────────────────
        if (empty($key)) {
            Cache::put('license_status', [
                'status' => 'not_configured',
                'message' => 'No subscription key set. Add LICENSE_KEY to .env',
                'checked' => now()->toDateTimeString(),
            ], now()->addMinutes(10));

            $this->warn('[LICENSE] No LICENSE_KEY configured in .env');
            return;
        }

        // ── Send pulse to LicenseHub ───────────────────────────
        try {
            $response = Http::withoutVerifying()->timeout(10)->post("{$url}/api/license/pulse", [
                'license_key' => $key,
                'machine_id' => gethostname(),
            ]);

            $body = $response->json();
            $status = $body['status'] ?? 'unknown';

            // Save result to cache — dashboard reads this
            Cache::put('license_status', [
                'status' => $status,
                'message' => $body['message'] ?? '',
                'tier' => $body['tier'] ?? '',
                'expires_at' => $body['expires_at'] ?? '',
                'days_left' => $body['days_left'] ?? null,
                'customer' => $body['customer_name'] ?? '',
                'checked' => now()->toDateTimeString(),
            ], now()->addMinutes(10));

            // ── Log outcome ────────────────────────────────────
            match ($status) {
                'valid' => $this->info("[LICENSE] ✅ Valid — {$body['tier']} — {$body['days_left']} days left"),
                'locked' => $this->error('[LICENSE] 🔒 Locked — contact LicenseHub admin'),
                'expired' => $this->error('[LICENSE] ❌ Expired — renew your subscription key'),
                default => $this->warn("[LICENSE] ⚠ Unknown status: {$status}"),
            };

        } catch (\Exception $e) {
            // Cannot reach LicenseHub — store last known + error
            Cache::put('license_status', [
                'status' => 'unreachable',
                'message' => 'Cannot reach LicenseHub server: ' . $e->getMessage(),
                'checked' => now()->toDateTimeString(),
            ], now()->addMinutes(10));

            $this->error('[LICENSE] ⚠ LicenseHub unreachable: ' . $e->getMessage());
            Log::warning('License check failed: ' . $e->getMessage());
        }
    }
}
