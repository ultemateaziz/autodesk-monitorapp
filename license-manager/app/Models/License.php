<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class License extends Model
{
    protected $fillable = [
        'license_key',
        'tier',
        'is_active',
        'expires_at',
        'machine_id',
        'machine_name',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function activations(): HasMany
    {
        return $this->hasMany(Activation::class);
    }
}
