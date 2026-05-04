<?php

namespace App\Jobs;

use App\Models\LicensedMachine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Fire-and-forget: alert admin about active machines not seen in 7+ days.
 * Intended trigger: daily via scheduler (php artisan schedule:run)
 * Register in: app/Console/Kernel.php → $schedule->job(new FlagInactiveMachines)->daily()
 */
class FlagInactiveMachines implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function handle(): void
    {
        $stale = LicensedMachine::active()
            ->where('last_seen_at', '<', now()->subDays(7))
            ->get();

        if ($stale->isEmpty()) return;

        $adminEmail = config('mail.admin_email', env('MAIL_ADMIN', null));

        $list = $stale->map(function ($m) {
            $lastSeen = $m->last_seen_at ? $m->last_seen_at->toDateString() : 'never';
            return "- {$m->hostname} (last seen: {$lastSeen})";
        })->join("\n");

        Log::warning("[MachineLicense] {$stale->count()} active machines not seen in 7+ days:\n{$list}");

        if ($adminEmail) {
            Mail::raw(
                "The following licensed machines have not checked in for 7+ days:\n\n{$list}\n\nReview at: " . url('/machine-licensing'),
                fn($msg) => $msg->to($adminEmail)->subject('[ARCHLAM] Inactive licensed machines detected')
            );
        }
    }
}
