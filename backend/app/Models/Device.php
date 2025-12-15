<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = ['socket_id', 'device_info'];

    protected $casts = [
        'device_info' => 'array',
    ];
}
