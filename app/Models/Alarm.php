<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alarm extends Model
{
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
}
