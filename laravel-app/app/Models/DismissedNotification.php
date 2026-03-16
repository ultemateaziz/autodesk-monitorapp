<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DismissedNotification extends Model
{
    protected $fillable = ['user_name', 'software_name'];
}
