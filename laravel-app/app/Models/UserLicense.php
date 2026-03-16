<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLicense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_name',
        'software_name',
        'assigned_date',
    ];

    protected $casts = [
        'assigned_date' => 'date',
    ];
}
