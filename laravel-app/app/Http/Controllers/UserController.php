<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        // Only Master Admin can see this page
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $users = User::orderBy('name')->get();
        $departments = ['Architecture', 'MEP', 'Structural', 'Infrastructure', 'Visualization'];
        $roles = [
            'admin' => 'IT Manager', 
            'team_leader' => 'Contract Manager',
            'management' => 'Management'
        ];
        
        // Get all unique usernames from ActivityLog and UserProfile
        $activityUsernames = \App\Models\ActivityLog::distinct('user_name')->pluck('user_name')->toArray();
        $profileUsernames = \App\Models\UserProfile::distinct('user_name')->pluck('user_name')->toArray();
        $allMonitorableUsernames = array_unique(array_merge($activityUsernames, $profileUsernames));
        sort($allMonitorableUsernames);

        // Get existing assignments for all leaders
        $assignments = \App\Models\MonitorAssignment::all()->groupBy('leader_id');

        return view('user_management', compact('users', 'departments', 'roles', 'allMonitorableUsernames', 'assignments'));
    }

    public function syncMonitorAssignments(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'leader_id' => 'required|exists:users,id',
            'monitored_usernames' => 'nullable|array',
            'monitored_usernames.*' => 'string'
        ]);

        $leaderId = $request->leader_id;
        $monitoredUsernames = $request->monitored_usernames ?? [];

        // Simple sync: delete old and insert new
        \App\Models\MonitorAssignment::where('leader_id', $leaderId)->delete();
        
        foreach ($monitoredUsernames as $username) {
            \App\Models\MonitorAssignment::create([
                'leader_id' => $leaderId,
                'monitored_user_name' => $username
            ]);
        }

        return redirect()->back()->with('success', 'Monitoring assignments updated successfully.');
    }

    public function store(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['admin', 'team_leader', 'management'])],
            'department' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'department' => $request->department,
            'occupation' => $request->occupation,
        ]);

        if ($user->role === 'team_leader' && $request->has('monitored_usernames')) {
            foreach ($request->monitored_usernames as $username) {
                \App\Models\MonitorAssignment::create([
                    'leader_id' => $user->id,
                    'monitored_user_name' => $username
                ]);
            }
        }

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'team_leader', 'management'])],
            'department' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'department' => $request->department,
            'occupation' => $request->occupation,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($user->role === 'team_leader') {
            \App\Models\MonitorAssignment::where('leader_id', $user->id)->delete();
            if ($request->has('monitored_usernames')) {
                foreach ($request->monitored_usernames as $username) {
                    \App\Models\MonitorAssignment::create([
                        'leader_id' => $user->id,
                        'monitored_user_name' => $username
                    ]);
                }
            }
        } else {
            \App\Models\MonitorAssignment::where('leader_id', $user->id)->delete();
        }

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        // Prevent self-deletion
        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
