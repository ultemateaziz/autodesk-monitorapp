<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\LicensedMachine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Fire-and-forget: write audit trail entry when machine status changes.
 * Dispatched by: AgentLicenseController::approve() / revoke()
 */
class AuditMachineStatusChange implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int    $machineId,
        public string $from,
        public string $to,
        public ?int   $adminUserId
    ) {}

    public function handle(): void
    {
        $machine = LicensedMachine::find($this->machineId);
        if (!$machine) return;

        $admin = $this->adminUserId
            ? (\App\Models\User::find($this->adminUserId)?->name ?? 'Admin')
            : 'System';

        AuditLog::create([
            'performed_by' => $admin,
            'action'       => "machine_license_{$this->to}",
            'target_user'  => $machine->hostname,
            'description'  => "Machine {$machine->hostname} ({$machine->machine_id}) status: {$this->from} → {$this->to}",
            'ip_address'   => null,
        ]);
    }
}
