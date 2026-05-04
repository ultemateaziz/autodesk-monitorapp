<?php

namespace App\Http\Controllers;

use App\Models\LicensedMachine;
use App\Jobs\NotifyMachinePending;
use App\Jobs\AuditMachineStatusChange;
use App\Jobs\SyncMachineCountToLicenseHub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentLicenseController extends Controller
{
    // ── AGENT API ─────────────────────────────────────────────────────────────

    /**
     * Agent calls this on startup to register itself.
     * Returns a token if active, or pending/revoked status.
     *
     * POST /api/agent/register
     * Body: { machine_id, hostname, license_key }
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'machine_id'  => 'required|string|max:64',
            'hostname'    => 'required|string|max:255',
            'license_key' => 'nullable|string|max:64',
        ]);

        $ip = $request->ip();

        $machine = LicensedMachine::firstOrNew(['machine_id' => $data['machine_id']]);
        $isNew   = !$machine->exists;

        if ($isNew) {
            $machine->hostname     = $data['hostname'];
            $machine->ip_address   = $ip;
            $machine->license_key  = $data['license_key'] ?? null;
            $machine->status       = 'pending';
            $machine->agent_token  = LicensedMachine::generateToken();
            $machine->registered_at = now();
            $machine->save();

            NotifyMachinePending::dispatch($machine->id)->afterCommit();
        } else {
            // Refresh hostname/IP on re-registration
            $machine->update([
                'hostname'    => $data['hostname'],
                'ip_address'  => $ip,
                'last_seen_at' => now(),
            ]);
        }

        return response()->json([
            'status'      => $machine->status,
            'agent_token' => $machine->isActive() ? $machine->agent_token : null,
            'message'     => match ($machine->status) {
                'active'  => 'Machine licensed. Monitoring active.',
                'pending' => 'Registration received. Awaiting admin approval.',
                'revoked' => 'Machine license revoked. Contact administrator.',
                default   => 'Unknown status.',
            },
        ]);
    }

    /**
     * Agent calls this periodically to check if still authorized.
     *
     * POST /api/agent/validate
     * Header: X-Agent-Token: <token>
     */
    public function validate(Request $request)
    {
        $token = $request->header('X-Agent-Token');

        if (!$token) {
            return response()->json(['authorized' => false, 'reason' => 'No token'], 401);
        }

        $machine = LicensedMachine::where('agent_token', $token)->first();

        if (!$machine) {
            return response()->json(['authorized' => false, 'reason' => 'Unknown machine'], 401);
        }

        $machine->touchLastSeen();

        return response()->json([
            'authorized' => $machine->isActive(),
            'status'     => $machine->status,
        ]);
    }

    // ── ADMIN DASHBOARD ───────────────────────────────────────────────────────

    /**
     * GET /machine-licensing
     */
    public function index()
    {
        $machines = LicensedMachine::orderByRaw("FIELD(status, 'pending', 'active', 'revoked')")
            ->orderByDesc('last_seen_at')
            ->get();

        $counts = [
            'total'   => $machines->count(),
            'active'  => $machines->where('status', 'active')->count(),
            'pending' => $machines->where('status', 'pending')->count(),
            'revoked' => $machines->where('status', 'revoked')->count(),
        ];

        return view('machine_licensing', compact('machines', 'counts'));
    }

    /**
     * POST /machine-licensing/{id}/approve
     */
    public function approve(Request $request, $id)
    {
        $machine = LicensedMachine::findOrFail($id);

        if ($machine->status === 'active') {
            return back()->with('info', 'Machine already active.');
        }

        // Seat cap check — reads max_machines from license.json set by LicenseHub
        $licenseFile = storage_path('app/license.json');
        if (file_exists($licenseFile)) {
            $licenseData = json_decode(file_get_contents($licenseFile), true) ?? [];
            $maxMachines = isset($licenseData['max_machines']) ? (int) $licenseData['max_machines'] : null;
            if ($maxMachines !== null && LicensedMachine::active()->count() >= $maxMachines) {
                return back()->with('error',
                    "Seat limit reached ({$maxMachines} licensed machines). Upgrade your license to add more."
                );
            }
        }

        $previous = $machine->status;
        $machine->update([
            'status'      => 'active',
            'approved_by' => Auth::user()->name ?? Auth::user()->username,
        ]);

        AuditMachineStatusChange::dispatch($machine->id, $previous, 'active', Auth::id())->afterCommit();
        SyncMachineCountToLicenseHub::dispatch()->afterCommit();

        return back()->with('success', "Machine {$machine->hostname} approved.");
    }

    /**
     * POST /machine-licensing/{id}/revoke
     */
    public function revoke(Request $request, $id)
    {
        $machine = LicensedMachine::findOrFail($id);

        if ($machine->status === 'revoked') {
            return back()->with('info', 'Machine already revoked.');
        }

        $previous = $machine->status;
        $machine->update([
            'status'     => 'revoked',
            'revoked_by' => Auth::user()->name ?? Auth::user()->username,
        ]);

        AuditMachineStatusChange::dispatch($machine->id, $previous, 'revoked', Auth::id())->afterCommit();
        SyncMachineCountToLicenseHub::dispatch()->afterCommit();

        return back()->with('success', "Machine {$machine->hostname} revoked.");
    }

    /**
     * DELETE /machine-licensing/{id}
     * Hard-delete only revoked machines.
     */
    public function destroy($id)
    {
        $machine = LicensedMachine::findOrFail($id);

        if ($machine->status !== 'revoked') {
            return back()->with('error', 'Only revoked machines can be deleted.');
        }

        $hostname = $machine->hostname;
        $machine->delete();

        return back()->with('success', "Machine {$hostname} permanently removed.");
    }
}
