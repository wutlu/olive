<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alarm extends Model
{
    protected $table = 'alarms';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
		'weekdays' => 'array',
		'modules' => 'array',
		'emails' => 'array',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'modules'
    ];
}
