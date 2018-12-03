<?php

namespace App\Models\RealTime;

use Illuminate\Database\Eloquent\Model;

class Pin extends Model
{
    protected $table = 'real_time_pins';
    protected $hidden = [
        'index',
        'type',
        'id',

        'organisation_id',
        'group_id',
        'user_id'
    ];
    protected $fillable = [
        'comment',

        'index',
        'type',
        'id',

        'group_id'
    ];
}
