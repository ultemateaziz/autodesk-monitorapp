<?php

namespace App\Jobs;

use App\Models\LicensedMachine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fire-and-forget: report seat usage to LicenseHub after approve/revoke.
 * Dispatched by: AgentLicenseController::approve() / revoke()
 */
class SyncMachineCountToLicenseHub implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function handle(): void
    {
        $licenseHubUrl = config('services.license_manager.url');
        $licenseKey    = config('services.license_manager.key');

        if (!$licenseHubUrl || !$licenseKey) {
            Log::warning('[MachineLicense] SyncMachineCount: LicenseHub URL or key not configured.');
            return;
        }

        $counts = [
            'license_key' => $licenseKey,
            'active'      => LicensedMachine::active()->count(),
            'pending'     => LicensedMachine::pending()->count(),
            'revoked'     => LicensedMachine::revoked()->count(),
            'synced_at'   => now()->toISOString(),
        ];

        try {
            Http::timeout(10)->post("{$licenseHubUrl}/api/license/machine-sync", $counts);
            Log::info("[MachineLicense] Synced machine counts to LicenseHub: {$counts['active']} active.");
        } catch (\Exception $e) {
            Log::warning("[MachineLicense] SyncMachineCount failed: {$e->getMessage()}");
        }
    }
}
