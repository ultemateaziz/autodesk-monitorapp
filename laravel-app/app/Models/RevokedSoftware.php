<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevokedSoftware extends Model
{
    protected $table = 'revoked_software';

    protected $fillable = [
        'user_name',
        'software_name',
        'revoked_by',
    ];
}
