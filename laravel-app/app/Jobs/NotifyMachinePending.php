<?php

namespace App\Jobs;

use App\Models\LicensedMachine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Fire-and-forget: email admin when a new machine registers as pending.
 * Dispatched by: AgentLicenseController::register()
 */
class NotifyMachinePending implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public int $machineId) {}

    public function handle(): void
    {
        $machine = LicensedMachine::find($this->machineId);
        if (!$machine) return;

        $adminEmail = config('mail.admin_email', env('MAIL_ADMIN', null));
        if (!$adminEmail) {
            Log::info("[MachineLicense] New pending machine: {$machine->hostname} ({$machine->machine_id}) — no admin email configured.");
            return;
        }

        Mail::raw(
            "New machine pending approval:\n\nHostname: {$machine->hostname}\nMachine ID: {$machine->machine_id}\nIP: {$machine->ip_address}\nRegistered: {$machine->registered_at}\n\nApprove at: " . url('/machine-licensing'),
            function ($msg) use ($adminEmail, $machine) {
                $msg->to($adminEmail)
                    ->subject("[ARCHLAM] New machine pending — {$machine->hostname}");
            }
        );
    }
}
