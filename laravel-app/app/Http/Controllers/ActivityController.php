<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\LicensedMachine;
use App\Jobs\NotifyMachinePending;

class ActivityController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validate the incoming data
        $validated = $request->validate([
            'machine_name' => 'required|string',
            'user_name' => 'required|string',
            'application' => 'required|string',
            'status' => 'required|string',
            'timestamp' => 'required|date',
        ]);

        // 2. Machine license gate — blocks revoked, auto-registers unknown as pending
        $licenseCheck = $this->handleMachineLicense($validated['machine_name'], $request->ip());
        if ($licenseCheck === 'revoked') {
            return response()->json(['message' => 'Machine license revoked. Contact administrator.'], 403);
        }

        // 3. Save it to the Database
        ActivityLog::create([
            'machine_name' => $validated['machine_name'],
            'user_name' => $validated['user_name'],
            'application' => $validated['application'],
            'status' => $validated['status'],
            'ip_address' => $request->ip(),
            'recorded_at' => $validated['timestamp'],
        ]);

        // 4. Return Success to the Node Script
        return response()->json(['message' => 'Log saved successfully'], 201);
    }

    /**
     * Auto-registers unknown machines as pending (grace period).
     * Blocks revoked machines. Updates last_seen_at for known machines.
     * Returns: 'active' | 'pending' | 'revoked'
     */
    private function handleMachineLicense(string $machineName, string $ip): string
    {
        $machine = LicensedMachine::where('hostname', $machineName)->first();

        if (!$machine) {
            // Auto-register as pending — admin must approve to keep it active
            $machine = LicensedMachine::create([
                'machine_id'    => hash('sha256', $machineName . $ip),
                'hostname'      => $machineName,
                'ip_address'    => $ip,
                'status'        => 'pending',
                'agent_token'   => LicensedMachine::generateToken(),
                'registered_at' => now(),
            ]);

            NotifyMachinePending::dispatch($machine->id)->afterCommit();

            return 'pending'; // Still allow this first heartbeat (grace)
        }

        if ($machine->isRevoked()) {
            return 'revoked';
        }

        $machine->touchLastSeen();

        return $machine->status;
    }
}

