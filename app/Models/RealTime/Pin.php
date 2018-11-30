<?php

namespace App\Models\RealTime;

use Illuminate\Database\Eloquent\Model;

class Pin extends Model
{
    protected $table = 'real_time_pins';
    protected $hidden = [
    	'index',
    	'content_id',
    	'organisation_id',
    	'group_id',
    	'user_id'
    ];
}
