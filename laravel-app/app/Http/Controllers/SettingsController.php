<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $departments = ['Architecture', 'MEP', 'Structural', 'Infrastructure', 'Visualization'];
        $roles = ['admin' => 'Master Admin', 'team_leader' => 'Team Leader'];
        
        $users = [];
        if (auth()->check() && auth()->user()->role === 'admin') {
            $users = User::orderBy('name')->get();
        }

        return view('settings', compact('departments', 'roles', 'users'));
    }
}
