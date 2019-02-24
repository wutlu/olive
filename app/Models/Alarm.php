<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alarm extends Model
{
    protected $table = 'alarms';

    protected $fillable = [
        'name',
        'hit',
        'interval',
        'start_time',
        'end_time',
        'weekdays',
        'emails',

        'sended_at',
    ];

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

    protected $dates = [
        'sended_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];
}
