<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class MonitorAssignment extends Model
{
    protected $fillable = [
        'leader_id',
        'monitored_user_name',
    ];

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }
}
