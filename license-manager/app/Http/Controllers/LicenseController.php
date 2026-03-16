<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\Activation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LicenseController extends Controller
{
    public function dashboard()
    {
        $totalLicenses  = License::count();
        $activeLicenses = License::where('is_active', true)->count();
        $lockedLicenses = Activation::where('status', 'locked')->distinct('license_id')->count();

        return view('dashboard', compact('totalLicenses', 'activeLicenses', 'lockedLicenses'));
    }

    public function generateKey(Request $request)
    {
        $request->validate([
            'tier'          => 'required|in:7D,15D,6M,1Y',
            'customer_name' => 'required|string|max:255',
        ]);

        $key = 'AEPRO-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));

        License::create([
            'license_key'   => $key,
            'customer_name' => $request->customer_name,
            'tier'          => $request->tier,
            'is_active'     => false,
            'expires_at'    => null,
        ]);

        return back()->with('success', "License Key Generated: $key");
    }

    public function toggleLock($id)
    {
        $activation = Activation::findOrFail($id);
        $activation->status = $activation->status === 'locked' ? 'active' : 'locked';
        $activation->save();

        return back()->with('success', "Machine status updated.");
    }

    public function index()
    {
        $licenses = License::with('activations')->withCount('activations')->latest()->paginate(20);
        return view('licenses', compact('licenses'));
    }

    public function destroy($id)
    {
        $license = License::findOrFail($id);
        $key = $license->license_key;

        // Delete all activation records (hardware locks)
        $license->activations()->delete();

        // Delete the license itself
        $license->delete();

        return back()->with('success', "License key <strong>$key</strong> deleted successfully. All hardware locks cleared.");
    }

    public function regenerate($id)
    {
        $old = License::findOrFail($id);

        // Generate a fresh key with same tier, reset activation state
        $newKey = 'AEPRO-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));

        // ── OPTION 1: Clean slate ──
        // Delete all old activation records (hardware locks)
        $old->activations()->delete();

        // Reset license to fresh state — can be activated on ANY hardware
        $old->update([
            'license_key'  => $newKey,
            'is_active'    => false,
            'expires_at'   => null,
            'machine_id'   => null,
            'machine_name' => null,
            'hardware_id'  => null,  // ← Clear old hardware lock
        ]);

        return back()->with('success', "✅ Key regenerated: <strong>$newKey</strong><br/>All old activations cleared. Customer must re-activate on their machine.");
    }

    public function apiReference()
    {
        return view('api_reference');
    }

    public function settings()
    {
        return view('settings');
    }

    // ── API: called by monitor agent on first use ─────────────
    // hardware_id = Windows MachineGUID (real hardware fingerprint — the lock)
    // machine_id  = os.hostname() (human-readable display name)
    public function apiActivate(Request $request)
    {
        $data = $request->validate([
            'license_key' => 'required|string',
            'hardware_id' => 'required|string',   // Windows MachineGUID — REQUIRED for hardware lock
            'machine_id'  => 'nullable|string',   // hostname — display name only
            'ip_address'  => 'nullable|string',
        ]);

        $license = License::where('license_key', $data['license_key'])->first();

        if (! $license) {
            return response()->json(['status' => 'invalid', 'message' => 'License key not found.'], 404);
        }

        // ── Hardware ID Lock: if already activated on a DIFFERENT hardware ──
        if ($license->is_active && $license->hardware_id && $license->hardware_id !== $data['hardware_id']) {
            return response()->json([
                'status'  => 'hardware_mismatch',
                'message' => 'This key is already locked to a different machine. Contact your administrator.',
            ], 403);
        }

        // ── Check not expired ──
        if ($license->expires_at && Carbon::now()->isAfter($license->expires_at)) {
            return response()->json(['status' => 'expired', 'message' => 'This license key has expired.'], 403);
        }

        $days = match($license->tier) {
            '7D'  => 7,
            '15D' => 15,
            '6M'  => 180,
            '1Y'  => 365,
            default => 365,
        };

        $expiresAt    = $license->expires_at ?? Carbon::now()->addDays($days);
        $machineName  = $data['machine_id'] ?? $data['hardware_id'];  // hostname as display name

        $license->update([
            'is_active'    => true,
            'expires_at'   => $expiresAt,
            'machine_id'   => $machineName,    // hostname for display
            'machine_name' => $machineName,    // same — friendly name
            'hardware_id'  => $data['hardware_id'],  // MachineGUID — the real lock
        ]);

        // Create or update activation record — keyed on hardware_id
        $activation = Activation::firstOrCreate(
            [
                'license_id'  => $license->id,
                'hardware_id' => $data['hardware_id'],
            ],
            [
                'machine_id'   => $machineName,
                'machine_name' => $machineName,
                'ip_address'   => $data['ip_address'] ?? $request->ip(),
                'last_pulse'   => now(),
                'status'       => 'active',
            ]
        );

        $activation->update([
            'machine_id'   => $machineName,
            'machine_name' => $machineName,
            'last_pulse'   => now(),
            'ip_address'   => $data['ip_address'] ?? $request->ip(),
        ]);

        return response()->json([
            'status'      => 'activated',
            'tier'        => $license->tier,
            'expires_at'  => $expiresAt->toDateTimeString(),
            'days_left'   => (int) Carbon::now()->diffInDays($expiresAt, false),
            'machine'     => $machineName,
            'hardware_id' => $data['hardware_id'],
        ]);
    }

    // ── API: called on app start to verify status ─────────────
    public function apiVerify(Request $request)
    {
        $data = $request->validate([
            'license_key' => 'required|string',
            'hardware_id' => 'required|string',   // Must match what was registered at activation
            'machine_id'  => 'nullable|string',
        ]);

        $license = License::where('license_key', $data['license_key'])->first();

        if (! $license) {
            return response()->json(['status' => 'invalid', 'message' => 'License key not found.'], 404);
        }

        // ── Hardware ID Lock check ──
        if ($license->hardware_id && $license->hardware_id !== $data['hardware_id']) {
            return response()->json([
                'status'  => 'hardware_mismatch',
                'message' => 'This key is registered to a different machine.',
            ], 403);
        }

        // ── Find activation by hardware_id ──
        $activation = Activation::where('license_id', $license->id)
            ->where('hardware_id', $data['hardware_id'])
            ->first();

        if (! $activation) {
            return response()->json(['status' => 'not_activated', 'message' => 'Not activated on this machine. Please activate first.'], 403);
        }

        if ($activation->status === 'locked') {
            return response()->json(['status' => 'locked', 'message' => 'This machine has been locked by the administrator.'], 403);
        }

        if ($license->expires_at && Carbon::now()->isAfter($license->expires_at)) {
            $activation->update(['status' => 'expired']);
            return response()->json([
                'status'     => 'expired',
                'message'    => 'Subscription expired. Please renew.',
                'expired_at' => $license->expires_at->toDateTimeString(),
            ], 403);
        }

        return response()->json([
            'status'      => 'valid',
            'tier'        => $license->tier,
            'customer'    => $license->customer_name,
            'expires_at'  => $license->expires_at?->toDateTimeString(),
            'days_left'   => $license->expires_at ? (int) Carbon::now()->diffInDays($license->expires_at, false) : null,
            'machine'     => $activation->machine_name ?? $activation->machine_id,
        ]);
    }

    // ── API: heartbeat while monitor app is running ───────────
    public function apiPulse(Request $request)
    {
        $data = $request->validate([
            'license_key' => 'required|string',
            'hardware_id' => 'required|string',   // hardware lock
            'machine_id'  => 'nullable|string',
        ]);

        $license = License::where('license_key', $data['license_key'])->first();

        if (! $license) {
            return response()->json(['status' => 'invalid'], 404);
        }

        // ── Hardware ID Lock check ──
        if ($license->hardware_id && $license->hardware_id !== $data['hardware_id']) {
            return response()->json(['status' => 'hardware_mismatch'], 403);
        }

        $activation = Activation::where('license_id', $license->id)
            ->where('hardware_id', $data['hardware_id'])
            ->first();

        if (! $activation) {
            return response()->json(['status' => 'not_activated'], 403);
        }

        if ($activation->status === 'locked') {
            return response()->json(['status' => 'locked', 'message' => 'Machine locked by administrator.'], 403);
        }

        if ($license->expires_at && Carbon::now()->isAfter($license->expires_at)) {
            $activation->update(['status' => 'expired']);
            return response()->json(['status' => 'expired'], 403);
        }

        $activation->update(['last_pulse' => now()]);

        return response()->json([
            'status'    => 'ok',
            'days_left' => $license->expires_at ? (int) Carbon::now()->diffInDays($license->expires_at, false) : null,
        ]);
    }
}
