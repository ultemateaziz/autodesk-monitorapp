<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LicensedMachine extends Model
{
    protected $fillable = [
        'machine_id',
        'hostname',
        'ip_address',
        'license_key',
        'status',
        'agent_token',
        'last_seen_at',
        'registered_at',
        'approved_by',
        'revoked_by',
    ];

    protected $casts = [
        'last_seen_at'  => 'datetime',
        'registered_at' => 'datetime',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRevoked($query)
    {
        return $query->where('status', 'revoked');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public static function generateToken(): string
    {
        return hash('sha256', Str::uuid() . microtime());
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }

    public function touchLastSeen(): void
    {
        $this->update(['last_seen_at' => now()]);
    }
}
