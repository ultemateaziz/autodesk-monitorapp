<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activation extends Model
{
    protected $fillable = [
        'license_id',
        'machine_id',    // os.hostname() — human-readable PC name
        'hardware_id',   // Windows MachineGUID — real hardware fingerprint (lock key)
        'machine_name',  // friendly display name
        'ip_address',
        'last_pulse',
        'status',
    ];

    protected $casts = [
        'last_pulse' => 'datetime',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
}
