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
        $license->activations()->delete();
        $license->delete();

        return back()->with('success', 'License key deleted successfully.');
    }

    public function regenerate($id)
    {
        $old = License::findOrFail($id);

        // Generate a fresh key with same tier, reset activation state
        $newKey = 'AEPRO-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));

        $old->activations()->delete();
        $old->update([
            'license_key' => $newKey,
            'is_active'   => false,
            'expires_at'  => null,
            'machine_id'  => null,
            'machine_name'=> null,
        ]);

        return back()->with('success', "Key regenerated: $newKey — machine must re-activate.");
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
    public function apiActivate(Request $request)
    {
        $data = $request->validate([
            'license_key' => 'required|string',
            'machine_id'  => 'required|string',
            'ip_address'  => 'nullable|string',
        ]);

        $license = License::where('license_key', $data['license_key'])->first();

        if (! $license) {
            return response()->json(['status' => 'invalid', 'message' => 'License key not found.'], 404);
        }

        // Already activated on a DIFFERENT machine
        if ($license->is_active && $license->machine_id && $license->machine_id !== $data['machine_id']) {
            return response()->json(['status' => 'invalid', 'message' => 'Key already activated on another machine.'], 403);
        }

        $days = match($license->tier) {
            '7D'  => 7,
            '15D' => 15,
            '6M'  => 180,
            '1Y'  => 365,
            default => 365,
        };

        $expiresAt = $license->expires_at ?? Carbon::now()->addDays($days);

        $license->update([
            'is_active'    => true,
            'expires_at'   => $expiresAt,
            'machine_id'   => $data['machine_id'],
            'machine_name' => $data['machine_id'],
        ]);

        $activation = Activation::firstOrCreate(
            ['license_id' => $license->id, 'machine_id' => $data['machine_id']],
            ['ip_address' => $data['ip_address'] ?? $request->ip(), 'last_pulse' => now(), 'status' => 'active']
        );
        $activation->update(['last_pulse' => now(), 'ip_address' => $data['ip_address'] ?? $request->ip()]);

        return response()->json([
            'status'     => 'activated',
            'tier'       => $license->tier,
            'expires_at' => $expiresAt->toDateTimeString(),
            'days_left'  => (int) Carbon::now()->diffInDays($expiresAt, false),
        ]);
    }

    // ── API: called on app start to verify status ─────────────
    public function apiVerify(Request $request)
    {
        $data = $request->validate([
            'license_key' => 'required|string',
            'machine_id'  => 'required|string',
        ]);

        $license = License::where('license_key', $data['license_key'])->first();

        if (! $license) {
            return response()->json(['status' => 'invalid', 'message' => 'License key not found.'], 404);
        }

        $activation = Activation::where('license_id', $license->id)
            ->where('machine_id', $data['machine_id'])
            ->first();

        if (! $activation) {
            return response()->json(['status' => 'not_activated', 'message' => 'Not activated on this machine.'], 403);
        }

        if ($activation->status === 'locked') {
            return response()->json(['status' => 'locked', 'message' => 'Access locked by administrator.'], 403);
        }

        if ($license->expires_at && Carbon::now()->isAfter($license->expires_at)) {
            $activation->update(['status' => 'expired']);
            return response()->json(['status' => 'expired', 'message' => 'Subscription expired.', 'expired_at' => $license->expires_at->toDateTimeString()], 403);
        }

        return response()->json([
            'status'     => 'valid',
            'tier'       => $license->tier,
            'expires_at' => $license->expires_at?->toDateTimeString(),
            'days_left'  => $license->expires_at ? (int) Carbon::now()->diffInDays($license->expires_at, false) : null,
        ]);
    }

    // ── API: heartbeat while monitor app is running ───────────
    public function apiPulse(Request $request)
    {
        $data = $request->validate([
            'license_key' => 'required|string',
            'machine_id'  => 'required|string',
        ]);

        $license = License::where('license_key', $data['license_key'])->first();

        if (! $license) {
            return response()->json(['status' => 'invalid'], 404);
        }

        $activation = Activation::where('license_id', $license->id)
            ->where('machine_id', $data['machine_id'])
            ->first();

        if (! $activation || $activation->status === 'locked') {
            return response()->json(['status' => $activation?->status ?? 'not_activated'], 403);
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
