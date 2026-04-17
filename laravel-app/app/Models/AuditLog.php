<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'performed_by',
        'action',
        'target_user',
        'description',
        'ip_address',
    ];

    /**
     * Quick helper to log an action from anywhere in the app.
     *
     * Usage:
     *   AuditLog::record($request, 'license_assigned', 'john.doe', 'Assigned AutoCAD 2026 to john.doe');
     */
    public static function record($request, string $action, ?string $targetUser, string $description): void
    {
        static::create([
            'performed_by' => auth()->user()->name ?? 'System',
            'action'       => $action,
            'target_user'  => $targetUser,
            'description'  => $description,
            'ip_address'   => $request->ip(),
        ]);
    }
}
