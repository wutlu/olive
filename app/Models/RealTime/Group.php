<?php

namespace App\Models\RealTime;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'real_time_groups';
    protected $fillable = [
    	'name'
    ];
}
