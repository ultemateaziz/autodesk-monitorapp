<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        // Only admins and management can view audit trail
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'management'])) {
            abort(403, 'Unauthorized access.');
        }

        $query = AuditLog::orderBy('created_at', 'desc');

        // Filter by action type
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by target user
        if ($request->filled('user')) {
            $query->where('target_user', 'like', '%' . $request->user . '%');
        }

        // Filter by date
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->paginate(50)->withQueryString();

        $actionTypes = AuditLog::distinct('action')->pluck('action')->sort()->values();

        return view('audit_trail', compact('logs', 'actionTypes'));
    }
}
