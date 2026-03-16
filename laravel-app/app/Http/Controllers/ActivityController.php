<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog; // Import your model

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

        // 2. Save it to the Database
        ActivityLog::create([
            'machine_name' => $validated['machine_name'],
            'user_name' => $validated['user_name'],
            'application' => $validated['application'],
            'status' => $validated['status'],
            'recorded_at' => $validated['timestamp'], // Map JS timestamp to DB column
        ]);

        // 3. Return Success to the Node Script
        return response()->json(['message' => 'Log saved successfully'], 201);
    }
}
