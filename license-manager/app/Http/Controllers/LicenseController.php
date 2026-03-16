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
        $totalLicenses = License::count();
        $activeLicenses = License::where('is_active', true)->count();
        $lockedLicenses = Activation::where('status', 'locked')->distinct('license_id')->count();
        $recentActivations = Activation::with('license')->orderBy('created_at', 'desc')->take(10)->get();

        return view('dashboard', compact('totalLicenses', 'activeLicenses', 'lockedLicenses', 'recentActivations'));
    }

    public function generateKey(Request $request)
    {
        $request->validate([
            'tier' => 'required|in:7D,15D,6M,1Y',
        ]);

        $days = match($request->tier) {
            '7D' => 7,
            '15D' => 15,
            '6M' => 180,
            '1Y' => 365,
        };

        $key = 'AEPRO-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));

        License::create([
            'license_key' => $key,
            'tier' => $request->tier,
            'is_active' => false, // Will activate on first use
            'expires_at' => null, // Will set on first activation
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
}
